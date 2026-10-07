<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'profesionalfarm273@gmail.com',
                'password' => bcrypt('ayaka123'),
                'first_name' => 'Ayaka',
                'last_name' => 'Hirose',
                'role' => 'originator',
            ],
            [
                'email' => 'ik6027769@gmail.com',
                'password' => bcrypt('itsuki123'), 
                'first_name' => 'Itsuki',
                'last_name' => 'Ishikawa',
                'role' => 'originator',
            ],
            [
                'email' => 'liamarkwright.1214@gmail.com',
                'password' => bcrypt('akiro123'), 
                'first_name' => 'Akiro',
                'last_name' => 'Kagutsuki',
                'role' => 'coordinator',
            ],
            [
                'email' => 'garaisrommel04@gmail.com',
                'password' => bcrypt('lumino123'), 
                'first_name' => 'Haru',
                'last_name' => 'Lumino',
                'role' => 'superior',
            ],
            [
                'email' => 'lux.zyrion14@gmail.com',
                'password' => bcrypt('zyrion123'), 
                'first_name' => 'Zyrion',
                'last_name' => 'Luxelle',
                'role' => 'manager',
            ],
            [
              'email' => 'kyrisrsd04@gmail.com',
              'password' => bcrypt('kyris123'),
              'first_name' => 'Kyris',
              'last_name' => 'RSD',
              'role' => 'manager'
            ],
            [
              'email' => 'niwa.studio.main@dcs.local',
              'password' => bcrypt('niwastudio123'),
              'first_name' => 'Niwa',
              'last_name' => 'Studio',
              'role' => 'sysadmin'
            ],
            [
              'email' => 'admin@dcs.local',
              'password' => bcrypt('Admin@1234'),
              'first_name' => 'System',
              'last_name' => 'Admin',
              'role' => 'sysadmin'
            ],

        ];

        foreach ($users as $user) {
            User::create($user);
        }

    }
}
