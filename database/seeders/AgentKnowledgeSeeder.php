<?php

namespace Database\Seeders;

use App\Models\AgentKnowledge;
use Illuminate\Database\Seeder;

class AgentKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        AgentKnowledge::create([
            'role' => 'lettings_negotiator',
            'type' => 'workflow_rule',
            'category' => 'tenancy_setup',
            'trigger' => 'application_submitted',
            'action' => 'verify_right_to_rent_and_deposit_protection',
            'description' => 'Ensure Right to Rent checks and deposit are protected before tenancy begins.',
        ]);

        AgentKnowledge::create([
            'role' => 'lettings_negotiator',
            'type' => 'checklist',
            'category' => 'pre_tenancy',
            'trigger' => 'offer_accepted',
            'action' => 'generate_pre_tenancy_checklist',
            'description' => 'Confirm: deposit protection, signed agreement, ID checked, and keys arranged.',
        ]);

        AgentKnowledge::create([
            'role' => 'lettings_negotiator',
            'type' => 'prompt',
            'category' => 'viewings',
            'trigger' => 'lead_qualified',
            'action' => 'send_property_list',
            'description' => 'Send a curated list of properties with available viewing slots.',
        ]);
    }
}
