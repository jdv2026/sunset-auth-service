<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnalyticsUniqueUser;

class AnalyticUniqueUserSeeder extends Seeder {

    public function run(): void {
        AnalyticsUniqueUser::factory()->count(15)->create();
    }
	
}