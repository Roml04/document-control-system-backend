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
                'email' => config("dev.users.email.user1"),
                'password' => bcrypt('ayaka123'),
                'first_name' => 'Ayaka',
                'last_name' => 'Hirose',
                'role' => 'originator',
            ],
            [
                'email' => config("dev.users.email.user2"),
                'password' => bcrypt('itsuki123'), 
                'first_name' => 'Itsuki',
                'last_name' => 'Ishikawa',
                'role' => 'originator',
            ],
            [
                'email' => config("dev.users.email.user3"),
                'password' => bcrypt('akiro123'), 
                'first_name' => 'Akiro',
                'last_name' => 'Kagutsuki',
                'role' => 'coordinator',
            ],
            [
                'email' => config("dev.users.email.user4"),
                'password' => bcrypt('lumino123'), 
                'first_name' => 'Haru',
                'last_name' => 'Lumino',
                'role' => 'superior',
            ],
            [
                'email' => config("dev.users.email.user5"),
                'password' => bcrypt('zyrion123'), 
                'first_name' => 'Zyrion',
                'last_name' => 'Luxelle',
                'role' => 'manager',
            ],
            [
              'email' => config("dev.users.email.user6"),
              'password' => bcrypt('kyris123'),
              'first_name' => 'Kyris',
              'last_name' => 'RSD',
              'role' => 'manager'
            ],
            [
              'email' => config("dev.users.email.admin1"),
              'password' => bcrypt('niwastudio123'),
              'first_name' => 'Niwa',
              'last_name' => 'Studio',
              'role' => 'sysadmin'
            ],
            [
              'email' => config("dev.users.email.admin2"),
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
