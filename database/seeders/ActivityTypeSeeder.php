<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Phone' => ['Incoming call', 'Outgoing call', 'Follow-up call'],
            'Meeting' => ['In person', 'Online meeting'],
            'Email' => ['Sent email', 'Received email'],
            'Facebook' => ['Page message', 'Comment', 'Lead form'],
            'Desk work' => ['Quotation', 'Documentation', 'Research'],
        ] as $name => $subTypes) {
            $type = ActivityType::updateOrCreate(['name' => $name], ['is_active' => true]);
            foreach ($subTypes as $subType) {
                $type->subTypes()->updateOrCreate(['name' => $subType], ['is_active' => true]);
            }
        }
    }
}
