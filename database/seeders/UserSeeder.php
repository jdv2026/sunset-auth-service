<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
	public function run(): void {
        $exists = DB::table('users')
            ->where('username', 'jdasv')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('users')->insert([
            'first_name' => 'John Dennis',
            'last_name'  => 'Vistal',
			'dob'        => '1997-01-14 00:00:00',
			'height'     => 165,
			'address'    => 'Manila, Philippines',
			'profile_picture' => null,
            'username'   => 'jdasv',
            'password'   => Hash::make('123123aA?'),
			'phone'       => '09168511673',
			'type'        => 'Admin',
            'created_at' => now(),
            'created_by' => 1,
            'updated_at' => now(),
            'updated_by' => 1,
        ]);

		DB::table('users')->insert([
            'first_name' => 'Guest',
            'last_name'  => 'User',
			'dob'        => '1997-01-14 00:00:00',
			'height'     => 165,
			'address'    => 'Manila, Philippines',
			'profile_picture' => null,
            'username'   => 'guest',
            'password'   => Hash::make('wwwm,asjdojnzjfogjosfo45u3SADASD4k5lkdzfg?'),
			'phone'       => '09123456678',
			'type'        => 'Guest',
            'created_at' => now(),
            'created_by' => 1,
            'updated_at' => now(),
            'updated_by' => 1,
        ]);
    }
}
