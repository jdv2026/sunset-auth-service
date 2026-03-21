<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('navigations')->upsert(
            [
                [
					'logo' => 'mat:dashboard',
					'name' => 'Overview',
					'link' => '/dashboard/physical/overview',
					'header' => 'Physical Focus',
					'order' => 9,
					'created_at' => $now,
					'updated_at' => $now
				],
                [
					'logo' => 'mat:fitness_center',
					'name' => 'Workouts',
					'link' => '/dashboard/physical/workouts',
					'header' => 'Physical Focus',
					'order' => 10,
					'created_at' => $now,
					'updated_at' => $now
				],
                [
					'logo' => 'mat:list_alt',
					'name' => 'Program',
					'link' => '/dashboard/physical/program',
					'header' => 'Physical Focus',
					'order' => 11,
					'created_at' => $now,
					'updated_at' => $now
				],
                [
					'logo' => 'mat:bar_chart',
					'name' => 'Progress Report',
					'link' => '/dashboard/physical/progress-report',
					'header' => 'Physical Focus',
					'order' => 12,
					'created_at' => $now,
					'updated_at' => $now
				],
                [
					'logo' => 'mat:calendar_month',
					'name' => 'Schedule',
					'link' => '/dashboard/physical/schedule',
					'header' => 'Physical Focus',
					'order' => 13,
					'created_at' => $now,
					'updated_at' => $now
				],
            ],
            ['link'],
            ['logo', 'name', 'header', 'order', 'updated_at']
        );
    }

    public function down(): void
    {
        DB::table('navigations')->whereIn('link', [
            '/dashboard/physical/overview',
            '/dashboard/physical/workouts',
            '/dashboard/physical/program',
            '/dashboard/physical/progress-report',
            '/dashboard/physical/schedule',
        ])->delete();
    }
};
