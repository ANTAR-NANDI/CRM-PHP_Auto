<?php

namespace Database\Seeders;

use App\Models\ActivitySubjectType;
use Illuminate\Database\Seeder;

class ActivitySubjectTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Customer', 'key' => 'customer'],
            ['name' => 'Lead', 'key' => 'lead'],
            ['name' => 'Contact', 'key' => 'contact'],
            ['name' => 'Organization', 'key' => 'organization'],
        ] as $subjectType) {
            ActivitySubjectType::updateOrCreate(['key' => $subjectType['key']], $subjectType + ['is_active' => true]);
        }
    }
}
