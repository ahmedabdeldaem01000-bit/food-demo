<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        // Admin واحد ثابت
      
         $user = User::create([
            'name' => 'Admin User',
            'email' => 'admain@example.com',
            'phone' => '01155987452',
            'birth_date' => null,
            'address' => 'address is giza',
            'password' => bcrypt('password'),
        ]);

        // اسناد رول
        $user->assignRole('admin');

        // مستخدمين عاديين
        User::factory(10)->create();
    }
}
