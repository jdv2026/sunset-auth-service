<?php

namespace Database\Seeders;

use App\Models\EventLog;
use Illuminate\Database\Seeder;

class EventLogSeeder extends Seeder
{
    public function run(): void
    {
        EventLog::factory()->count(1000)->create();
    }
}
