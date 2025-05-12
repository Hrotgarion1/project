<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //creo el rol de administrador
        $role_admin = Role::create(['name' => 'admin']);

        //Defino los permisos especificos del rol de administrador, tener en cuenta que debo de tener aquí todos los permisos de la plataforma.

        //Permisos para el Sidebar
        $permission_ver_panel_admin = Permission::create(['name' => 'Admin panel']);
        $permission_ver_panel_centros_estudios = Permission::create(['name' => 'Type A Panel']);
        $permission_ver_panel_empresas = Permission::create(['name' => 'Type B Panel']);
        $permission_ver_panel_cursos_online = Permission::create(['name' => 'Type F Panel']);
        $permission_ver_panel_emprendedores = Permission::create(['name' => 'Type E Panel']);
        $permission_ver_panel_autonomo = Permission::create(['name' => 'Type D Panel']);
        $permission_ver_panel_entidades_sociales = Permission::create(['name' => 'Type C Panel']);
        $permission_ver_panel_inversores = Permission::create(['name' => 'Type G Panel']);
        $permission_ver_panel_reclutadores = Permission::create(['name' => 'Type H Panel']);
        $permission_ver_panel_socios = Permission::create(['name' => 'Partner panel']);
        $permission_ver_panel_usuarios = Permission::create(['name' => 'Users panel']);
        
        //Creo los permisos
        $permission_ver_role = Permission::create(['name' => 'ver role']);
        $permission_crear_role = Permission::create(['name' => 'crear role']);
        $permission_editar_role = Permission::create(['name' => 'editar role']);
        $permission_eliminar_role = Permission::create(['name' => 'eliminar role']);

        $permission_view_media = Permission::create(['name' => 'view-media']);
        $permission_upload_media = Permission::create(['name' => 'upload-media']);
        $permission_delete_media = Permission::create(['name' => 'delete-media']);
        $permission_reorder_media = Permission::create(['name' => 'reorder-media']);

        $permission_ver_permiso = Permission::create(['name' => 'ver permiso']);

        $permission_ver_usuario = Permission::create(['name' => 'ver usuario']);
        $permission_crear_usuario = Permission::create(['name' => 'crear usuario']);
        $permission_editar_usuario = Permission::create(['name' => 'editar usuario']);
        $permission_eliminar_usuario = Permission::create(['name' => 'eliminar usuario']);

        $permissions_admin = [$permission_ver_panel_admin, $permission_ver_panel_centros_estudios, $permission_ver_panel_empresas,
        $permission_ver_panel_cursos_online, $permission_ver_panel_emprendedores, $permission_ver_panel_autonomo, $permission_ver_panel_entidades_sociales,
        $permission_ver_panel_inversores, $permission_ver_panel_reclutadores, $permission_ver_panel_socios, $permission_ver_panel_usuarios, $permission_ver_role, $permission_crear_role,
         $permission_editar_role, $permission_eliminar_role, $permission_ver_permiso,
        $permission_ver_usuario, $permission_crear_usuario, $permission_editar_usuario, $permission_eliminar_usuario,
        $permission_view_media, $permission_upload_media, $permission_delete_media, $permission_reorder_media];

        $role_admin->syncPermissions($permissions_admin);
    }
}
