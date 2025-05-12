<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTipoASeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Creo el rol deseado para los centros de estudios
        $roleName = 'tipo A';

        // Crear o buscar el rol con el nombre y guard_name
        $role = Role::firstOrCreate(
            ['name' => $roleName, 'guard_name' => 'web']
        );

        // Obtener permisos del rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->command->error('El rol "admin" no existe. Corre primero el seeder de roles base.');
            return;
        }

        $adminPermissions = $adminRole->permissions;

        // Filtrar los permisos que querés que tenga el rol Tipo A
        $permissions = $adminPermissions->filter(function ($permission) {
            return in_array($permission->name, [
                'Users panel',
                'Type A Panel',
                'view-media',
                'upload-media',
                'delete-media',
                'reorder-media'
                // Agregá más si necesitás
            ]);
        });

        // Asignar permisos al rol
        $role->syncPermissions($permissions);

        $this->command->info("Rol '$roleName' creado y permisos asignados.");
    }
}
