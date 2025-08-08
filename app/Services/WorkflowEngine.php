<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowAction;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    /**
     * Process a model event against active workflows.
     */
    public function processModelEvent(string $event, $model): void
    {
        Workflow::with('triggers', 'actions')->where('active', true)->get()
            ->each(function (Workflow $workflow) use ($event, $model) {
                foreach ($workflow->triggers as $trigger) {
                    if ($trigger->type === 'model_event' && ($trigger->data['event'] ?? null) === $event && ($trigger->data['model'] ?? null) === get_class($model)) {
                        $this->runActions($workflow, $model);
                    }
                }
            });
    }

    /**
     * Execute scheduled workflows.
     */
    public function runScheduled(): void
    {
        Workflow::with('triggers', 'actions')->where('active', true)->get()
            ->each(function (Workflow $workflow) {
                foreach ($workflow->triggers as $trigger) {
                    if ($trigger->type === 'schedule') {
                        $this->runActions($workflow);
                    }
                }
            });
    }

    protected function runActions(Workflow $workflow, $model = null): void
    {
        foreach ($workflow->actions as $action) {
            $this->dispatchAction($action, $model);
        }
    }

    protected function dispatchAction(WorkflowAction $action, $model = null): void
    {
        switch ($action->type) {
            case 'email':
                Log::info('Workflow email action', ['workflow' => $action->workflow_id]);
                break;
            case 'task':
                Log::info('Workflow task action', ['workflow' => $action->workflow_id]);
                break;
        }
    }
}
