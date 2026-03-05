<?php

namespace Database\Seeders;

use App\Contracts\UserType;
use App\Models\Navigation;
use Illuminate\Database\Seeder;

class NavigationsSeeder extends Seeder {
    public function run(): void {
        Navigation::truncate();
        $now = now();
        $navs = [
            [
                'logo' => 'mat:insights',
                'name' => 'Analytics',
                'link' => '/dashboard/analytics',
                'header' => 'Dashboard',
				'type' => UserType::Staff
            ],
            [
                'logo' => 'mat:group',
                'name' => 'Members',
                'link' => '/dashboard/members',
                'header' => 'Membership',
				'type' => UserType::Staff
            ],
            [
                'logo' => 'mat:person',
                'name' => 'Admins',
                'link' => '/dashboard/admins/all',
                'header' => 'Membership',
				'type' => UserType::Staff
            ],
			[
                'logo' => 'mat:person_add',
                'name' => 'Create User',
                'link' => '/dashboard/create/member',
                'header' => 'Administration',
				'type' => UserType::Admin
            ],
            [
                'logo' => 'mat:list',
                'name' => 'Event Logs',
                'link' => '/dashboard/logs',
                'header' => 'Logs',
				'type' => UserType::Staff
            ],
            [
                'logo' => 'mat:settings',
                'name' => 'Configuration',
                'link' => '/dashboard/configuration',
                'header' => 'Administration',
				'type' => UserType::Staff
			],
        ];

        foreach ($navs as &$nav) {
            $nav['created_at'] = $now;
            $nav['updated_at'] = $now;
        }

        Navigation::insert($navs);
    }
}
