<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Диспетчеры
        DB::table('users')->insert([
            [
                'fio' => 'Иванов Иван Иванович',
                'login' => 'ivanov',
                'password' => Hash::make('1234'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Петрова Анна Сергеевна',
                'login' => 'petrova',
                'password' => Hash::make('1234'),
                'role' => 'dispatcher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Мастера
        DB::table('users')->insert([
            [
                'fio' => 'Сидоров Петр Петрович',
                'login' => 'sidorov',
                'password' => Hash::make('1234'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Козлов Андрей Андреевич',
                'login' => 'kozlov',
                'password' => Hash::make('1234'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fio' => 'Михайлов Сергей Михайлович',
                'login' => 'mikhailov',
                'password' => Hash::make('1234'),
                'role' => 'master',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
