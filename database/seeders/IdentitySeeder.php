<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Identity;
use App\Models\IdentityChangeRequest;
use App\Models\IdentityMeta;
use Illuminate\Support\Str;

class IdentitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar usuario existente
        $user = User::where('name', 'invitado1')->first();

        if (!$user) {
            $this->command->warn('Usuario invitado1 no encontrado. Seeder cancelado.');
            return;
        }

        // Crear identidad
        $identity = Identity::create([
            'id' => Str::uuid(), // Generar UUID
            'user_id' => $user->id,
            'type' => 'tipo A',
            'email' => 'invitado1@mail.com',
            'name' => 'Invitado Uno - Identidad A',
            'address' => 'Calle Falsa 123',
            'phone' => '12345678',
            'status' => 'approved',
            'notes' => 'Solicitud aprobada directamente por seeder.',
            'requested_changes' => null,
            'has_updates' => false,
        ]);

        // Crear metadatos asociados
        IdentityMeta::create([
            'identity_id' => $identity->id,
            'key' => 'profile_image',
            'value' => 'default_avatar.png',
        ]);

        IdentityMeta::create([
            'identity_id' => $identity->id,
            'key' => 'bio',
            'value' => 'Este es un usuario de prueba con identidad tipo A.',
        ]);

        IdentityChangeRequest::create([
            'requestable_type' => 'App\Models\Identity',
            'requestable_id' => $identity->id,
            'message' => 'Please upload your ID',
            'sent_at' => now(),
            'sent_by' => $user->id,
        ]);
    
    }
}
