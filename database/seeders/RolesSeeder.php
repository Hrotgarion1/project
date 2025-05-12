<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuro los roles y permisos en cada seeder individual
        $this->call([
            RolesAdminSeeder::class,
            RolesEditorSeeder::class,
            RolesInvitadoSeeder::class,
            RolesTipoASeeder::class,
            RolesTipoA1Seeder::class,
            RolesTipoA2Seeder::class,
            RolesTipoBSeeder::class,
            RolesTipoB1Seeder::class,
            RolesTipoB2Seeder::class,
            RolesTipoCSeeder::class,
            RolesTipoC1Seeder::class,
            RolesTipoC2Seeder::class,
            RolesTipoDSeeder::class,
            RolesTipoESeeder::class,
            RolesTipoFSeeder::class,
            RolesTipoGSeeder::class,
            RolesTipoHSeeder::class,
            RolesSupervisorSeeder::class,
        ]);
    }
}
