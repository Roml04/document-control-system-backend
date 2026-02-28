<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
          'first_name' => 'Ayaka',
          'last_name' => 'Hirose',
          'email' => 'hirose@gmail.com',
          'role' => 'originator',
        ],
        [
          'first_name' => 'Itsuki',
          'last_name' => 'Ishikawa',
          'email' => 'itsuki@gmail.com',
          'role' => 'originator',
        ],
        [
          'first_name' => 'Akiro',
          'last_name' => 'Kagutsuki',
          'email' => 'akiro@gmail.com',
          'role' => 'coordinator',
        ],
        [
          'first_name' => 'Haru',
          'last_name' => 'Lumino',
          'email' => 'lumino@gmail.com',
          'role' => 'superior',
        ],
        
      ];
      
      foreach($users as $user) {
        User::create([...$user, "password" => bcrypt('password123')]);
      }

    }
}
