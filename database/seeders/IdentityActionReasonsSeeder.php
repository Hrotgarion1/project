<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IdentityActionReason;

class IdentityActionReasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            // Motivos de suspensión
            [
                'action_type' => 'suspended',
                'code' => 'improper_use',
                'title' => 'Uso indebido del rol',
                'description' => 'El usuario ha utilizado el rol asignado de manera inapropiada o abusiva.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'action_type' => 'suspended',
                'code' => 'missing_docs',
                'title' => 'Falta de documentación',
                'description' => 'El usuario no ha proporcionado la documentación requerida.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'action_type' => 'suspended',
                'code' => 'suspicious_activity',
                'title' => 'Actividad sospechosa',
                'description' => 'Se han detectado patrones que requieren investigación.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Motivos de eliminación
            [
                'action_type' => 'deleted',
                'code' => 'policy_violation',
                'title' => 'Violación grave de normas',
                'description' => 'El usuario ha incumplido gravemente las políticas.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'action_type' => 'deleted',
                'code' => 'fraudulent',
                'title' => 'Identidad fraudulenta',
                'description' => 'Se ha confirmado que la identidad es falsa.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'action_type' => 'deleted',
                'code' => 'user_request',
                'title' => 'Solicitud del usuario',
                'description' => 'El usuario ha solicitado la eliminación.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'action_type' => 'deleted',
                'code' => 'inactivity',
                'title' => 'Inactividad prolongada',
                'description' => 'La identidad no ha sido utilizada por mucho tiempo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Motivo para in_progress
            [
                'action_type' => 'in_progress',
                'code' => 'started',
                'title' => 'Inicio de gestión',
                'description' => 'Un gestor ha tomado tu solicitud para su revisión.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Motivo para approved
            [
                'action_type' => 'approved',
                'code' => 'success',
                'title' => 'Solicitud aprobada',
                'description' => 'Tu solicitud cumple con todos los requisitos y ha sido aprobada.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Motivo para rejected
            [
                'action_type' => 'rejected',
                'code' => 'insufficient_docs',
                'title' => 'Documentación insuficiente',
                'description' => 'La documentación aportada no cumple con los requisitos de la plataforma.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Motivo para reassigned
            [
                'action_type' => 'reassigned',
                'code' => 'reassigned_to',
                'title' => 'Tu identidad ha sido reasignada',
                'description' => 'Tu solicitud a sido reasignada a otro gestor.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'action_type' => 'waiting',
                'code' => 'more_docs_requested',
                'title' => 'Solicitud de más documentación',
                'description' => 'Se requieren documentos adicionales para completar la revisión de tu solicitud.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        IdentityActionReason::insert($reasons);
    
    }
}
