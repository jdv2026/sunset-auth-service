<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

		$exists = DB::table('settings')
            ->exists();

		if ($exists) {
			return;
		}

		$default = [
			'isGeneralSetting' => true,
			'theme_name' => 'Default',
			'theme_className' => 'vex-theme-default',
			'orientation' => 'left',
			'toolbar' => true,
			'footer' => true,
			'footer_fixed' => true,
			'created_by' => 1,
			'updated_by' => 1,
			'created_at' => now(),
			'updated_at' => now(),
		];

		DB::table('settings')->insert($default);
    }
}
