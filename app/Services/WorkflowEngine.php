<?php

namespace App\Services;

use App\Jobs\SyncPropertyToPortal;
use App\Models\PartnerIntegration;
use App\Models\Property;
use App\Models\PropertyPortals;
use App\Models\Task;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowTrigger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    /**
     * Process a model event against active workflows.
     */
    public function processModelEvent(string $event, $model, array $context = []): void
    {
        $this->activeWorkflows()->each(function (Workflow $workflow) use ($event, $model, $context) {
            foreach ($workflow->triggers as $trigger) {
                if ($this->matchesModelEventTrigger($trigger, $event, $model, $context)) {
                    $this->runActions($workflow, $model, $context);
                }
            }
        });
    }

    /**
     * Execute scheduled workflows.
     */
    public function runScheduled(): void
    {
        $this->activeWorkflows()->each(function (Workflow $workflow) {
            foreach ($workflow->triggers as $trigger) {
                if ($trigger->type === 'schedule') {
                    $this->runActions($workflow);
                }
            }
        });
    }

    protected function matchesModelEventTrigger(WorkflowTrigger $trigger, string $event, $model, array $context): bool
    {
        if ($trigger->type !== 'model_event') {
            return false;
        }

        $data = $trigger->data ?? [];

        if (($data['event'] ?? null) !== $event) {
            return false;
        }

        if (($data['model'] ?? null) !== get_class($model)) {
            return false;
        }

        $conditions = $data['conditions'] ?? [];

        if (isset($conditions['changes']) && is_array($conditions['changes'])) {
            foreach ($conditions['changes'] as $attribute => $expectation) {
                $change = $context['changes'][$attribute] ?? null;

                if (! is_array($change)) {
                    return false;
                }

                if (array_key_exists('to', $expectation) && ($change['to'] ?? null) !== $expectation['to']) {
                    return false;
                }

                if (array_key_exists('from', $expectation) && ($change['from'] ?? null) !== $expectation['from']) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function runActions(Workflow $workflow, $model = null, array $context = []): void
    {
        foreach ($workflow->actions as $action) {
            $this->dispatchAction($workflow, $action, $model, $context);
        }
    }

    protected function dispatchAction(Workflow $workflow, WorkflowAction $action, $model = null, array $context = []): void
    {
        switch ($action->type) {
            case 'email':
                Log::info('Workflow email action', ['workflow' => $action->workflow_id]);
                break;
            case 'task':
                $this->createTaskFromAction($workflow, $action, $model, $context);
                break;
            case 'portal_sync':
                $this->dispatchPortalSync($action, $model);
                break;
            case 'webhook':
                $this->dispatchWebhook($workflow, $action, $model, $context);
                break;
        }
    }

    protected function createTaskFromAction(Workflow $workflow, WorkflowAction $action, $model = null, array $context = []): void
    {
        $data = $action->data ?? [];

        $title = $data['title'] ?? ($workflow->name . ' follow-up');
        $description = $this->applyContextPlaceholders($data['description'] ?? '', $workflow, $model, $context);
        $dueDate = now()->addDays((int) ($data['due_in_days'] ?? 0));

        Task::create([
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'status' => $data['status'] ?? 'open',
            'taskable_id' => $model && method_exists($model, 'getKey') ? $model->getKey() : null,
            'taskable_type' => $model ? get_class($model) : null,
            'user_id' => $data['user_id'] ?? null,
        ]);
    }

    protected function dispatchPortalSync(WorkflowAction $action, $model = null): void
    {
        if (! $model instanceof Property) {
            return;
        }

        $portalKey = $action->data['portal'] ?? null;

        if (! $portalKey || ! in_array($portalKey, PropertyPortals::PORTALS, true)) {
            Log::warning('Workflow portal sync skipped due to invalid portal', [
                'workflow' => $action->workflow_id,
                'portal' => $portalKey,
            ]);

            return;
        }

        SyncPropertyToPortal::dispatch($model->getKey(), $portalKey);
    }

    protected function dispatchWebhook(Workflow $workflow, WorkflowAction $action, $model = null, array $context = []): void
    {
        $integrationId = $action->data['integration_id'] ?? null;
        $eventName = $action->data['event'] ?? 'workflow.triggered';

        if (! $integrationId) {
            Log::warning('Workflow webhook missing integration id', ['workflow' => $workflow->id]);

            return;
        }

        $integration = PartnerIntegration::query()->active()->find($integrationId);

        if (! $integration) {
            Log::warning('Workflow webhook integration not found or inactive', [
                'workflow' => $workflow->id,
                'integration_id' => $integrationId,
            ]);

            return;
        }

        $webhookUrl = $integration->settings['webhook_url'] ?? null;

        if (! $webhookUrl) {
            Log::warning('Workflow webhook missing URL', [
                'workflow' => $workflow->id,
                'integration_id' => $integrationId,
            ]);

            return;
        }

        Http::withHeaders($integration->settings['headers'] ?? [])
            ->post($webhookUrl, [
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'event' => $eventName,
                'model' => $model ? [
                    'type' => get_class($model),
                    'id' => method_exists($model, 'getKey') ? $model->getKey() : null,
                    'attributes' => method_exists($model, 'toArray') ? $model->toArray() : [],
                ] : null,
                'context' => $context,
            ]);
    }

    protected function applyContextPlaceholders(string $template, Workflow $workflow, $model = null, array $context = []): string
    {
        $replacements = [
            '{{ workflow.name }}' => $workflow->name,
            '{{ workflow.id }}' => (string) $workflow->id,
        ];

        if ($model) {
            $replacements['{{ model.class }}'] = class_basename($model);

            if (method_exists($model, 'getKey')) {
                $replacements['{{ model.id }}'] = (string) $model->getKey();
            }

            foreach (['status', 'title', 'name'] as $attribute) {
                if (isset($model->{$attribute})) {
                    $replacements[sprintf('{{ model.%s }}', $attribute)] = (string) $model->{$attribute};
                }
            }
        }

        foreach ($context['changes'] ?? [] as $attribute => $change) {
            if (is_array($change)) {
                $replacements[sprintf('{{ context.changes.%s.from }}', $attribute)] = (string) ($change['from'] ?? '');
                $replacements[sprintf('{{ context.changes.%s.to }}', $attribute)] = (string) ($change['to'] ?? '');
            }
        }

        return strtr($template, $replacements);
    }

    protected function activeWorkflows(): Collection
    {
        try {
            return Workflow::with('triggers', 'actions')
                ->where('active', true)
                ->get();
        } catch (QueryException $exception) {
            if ($this->isMissingWorkflowTable($exception)) {
                return collect();
            }

            throw $exception;
        }
    }

    protected function isMissingWorkflowTable(QueryException $exception): bool
    {
        if ($exception->getCode() === '42S02') {
            return true;
        }

        return str_contains($exception->getMessage(), 'no such table');
    }
}
