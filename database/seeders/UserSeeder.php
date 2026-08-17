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
                'email' => 'hirose@gmail.com',
                'password' => bcrypt('ayaka123'),
                'first_name' => 'Ayaka',
                'last_name' => 'Hirose',
                'role' => 'originator',
            ],
            [
                'email' => 'itsuki@gmail.com',
                'password' => bcrypt('itsuki123'), 
                'first_name' => 'Itsuki',
                'last_name' => 'Ishikawa',
                'role' => 'originator',
            ],
            [
                'email' => 'akiro@gmail.com',
                'password' => bcrypt('akiro123'), 
                'first_name' => 'Akiro',
                'last_name' => 'Kagutsuki',
                'role' => 'coordinator',
            ],
            [
                'email' => 'lumino@gmail.com',
                'password' => bcrypt('lumino123'), 
                'first_name' => 'Haru',
                'last_name' => 'Lumino',
                'role' => 'superior',
            ],
            [
                'email' => 'zyrion@gmail.com',
                'password' => bcrypt('zyrion123'), 
                'first_name' => 'Zyrion',
                'last_name' => 'Luxelle',
                'role' => 'manager',
            ],

        ];

        foreach ($users as $user) {
            User::create($user);
        }

    }
}
