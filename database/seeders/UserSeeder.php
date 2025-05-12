<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Jose',
                'email' => 'jose@mail.com',
                'password' => '12345678',
                'role' => 'admin',
                'pais_id' => '40',
            ],
            [
                'name' => 'Pedro',
                'email' => 'pedro@mail.com',
                'password' => '12345678',
                'role' => 'admin',
                'pais_id' => '40',
            ],
            [
                'name' => 'Editor',
                'email' => 'editor@mail.com',
                'password' => '12345678',
                'role' => 'editor',
                'pais_id' => '40',
            ],
            [
                'name' => 'Editor1',
                'email' => 'editor1@mail.com',
                'password' => '12345678',
                'role' => 'editor',
                'pais_id' => '40',
            ],
            [
                'name' => 'Editor2',
                'email' => 'editor2@mail.com',
                'password' => '12345678',
                'role' => 'editor',
                'pais_id' => '40',
            ],
            [
                'name' => 'Invitado',
                'email' => 'invitado@mail.com',
                'password' => '12345678',
                'role' => 'invitado',
                'pais_id' => '40',
            ],
            [
                'name' => 'Invitado1',
                'email' => 'invitado1@mail.com',
                'password' => '12345678',
                'role' => 'invitado',
                'pais_id' => '40',
            ],
        ];

        foreach($users as $user) {
            $created_user = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
                'pais_id' => $user['pais_id'],
            ]);

            $created_user->assignRole($user['role']);
        }

        User::factory()->count(5)->create(); // Crea usuarios sin roles específicos (puedo dejarlos como 'invitado')
    }
}
