<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnalyticPageView;

class AnalyticPageViewSeeder extends Seeder {

    public function run(): void {
        AnalyticPageView::factory()->count(15)->create();
    }
	
}