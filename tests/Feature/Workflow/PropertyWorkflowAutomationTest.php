<?php

namespace Tests\Feature\Workflow;

use App\Jobs\SyncPropertyToPortal;
use App\Models\PartnerIntegration;
use App\Models\Property;
use App\Models\Workflow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PropertyWorkflowAutomationTest extends TestCase
{
    public function test_status_change_creates_follow_up_task_and_sync_job(): void
    {
        Http::fake();
        Queue::fake();

        $workflow = Workflow::create([
            'name' => 'Publish to Rightmove',
            'description' => 'Auto sync when a property goes live',
            'active' => true,
        ]);

        $workflow->triggers()->create([
            'type' => 'model_event',
            'data' => [
                'event' => 'property.status_changed',
                'model' => Property::class,
                'conditions' => [
                    'changes' => [
                        'status' => ['to' => 'live'],
                    ],
                ],
            ],
        ]);

        $workflow->actions()->create([
            'type' => 'task',
            'order' => 1,
            'data' => [
                'title' => 'Review live listing',
                'description' => 'Property moved from {{ context.changes.status.from }} to {{ context.changes.status.to }}',
                'due_in_days' => 1,
            ],
        ]);

        $workflow->actions()->create([
            'type' => 'portal_sync',
            'order' => 2,
            'data' => ['portal' => 'rightmove'],
        ]);

        $property = Property::factory()->create([
            'type' => 'sales',
            'status' => 'draft',
            'price' => 250000,
            'publish_to_portal' => true,
        ]);

        $property->status = 'live';
        $property->save();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Review live listing',
            'taskable_id' => $property->id,
            'taskable_type' => Property::class,
        ]);

        Queue::assertPushed(SyncPropertyToPortal::class, function ($job) use ($property) {
            return $job->propertyId === $property->id && $job->portalKey === 'rightmove';
        });
    }

    public function test_webhook_action_posts_payload_to_active_integration(): void
    {
        Queue::fake();
        Http::fake();

        $integration = PartnerIntegration::create([
            'name' => 'Lifecycle Automation',
            'provider' => 'Lifecycle',
            'type' => 'automation',
            'settings' => ['webhook_url' => 'https://lifecycle.example/hooks'],
            'credentials' => [],
            'active' => true,
        ]);

        $workflow = Workflow::create([
            'name' => 'Notify Lifecycle',
            'description' => 'Send webhook on save',
            'active' => true,
        ]);

        $workflow->triggers()->create([
            'type' => 'model_event',
            'data' => [
                'event' => 'saved',
                'model' => Property::class,
            ],
        ]);

        $workflow->actions()->create([
            'type' => 'webhook',
            'order' => 1,
            'data' => [
                'integration_id' => $integration->id,
                'event' => 'property.updated',
            ],
        ]);

        $property = Property::factory()->create([
            'type' => 'sales',
            'status' => 'draft',
        ]);

        $property->price = 300000;
        $property->save();

        Http::assertSent(function ($request) use ($integration) {
            return $request->url() === $integration->settings['webhook_url']
                && $request['event'] === 'property.updated'
                && $request['model']['id'] !== null;
        });
    }
}

