<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
    User::create([
      'name' => 'Administrator',
      'email' => 'admin@gmail.com',
      'email_verified_at' => now(),
      'password' => bcrypt('password'), // password
      'status' => true,
    ]);
  }
}
