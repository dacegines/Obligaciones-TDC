<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InsertInitialData extends Migration
{
    public function up()
    {
        // Insertar usuario admin en la tabla `users` solo si no existe
        if (!DB::table('users')->where('id', 1)->exists()) {
            DB::table('users')->insert([
                'id' => 1,
                'name' => 'admin',
                'email' => 'admin@admin',
                'password' => Hash::make('password'), // Contraseña cifrada
                'puesto' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insertar permisos en la tabla `permissions` solo si no existen
        if (!DB::table('permissions')->where('id', 12)->exists()) {
            DB::table('permissions')->insert([
                [
                    'id' => 12,
                    'name' => 'obligaciones de concesión',
                    'guard_name' => 'web',
                    'created_at' => '2025-02-23 19:36:12',
                    'updated_at' => '2025-02-23 19:36:12',
                ],
            ]);
        }

        if (!DB::table('permissions')->where('id', 13)->exists()) {
            DB::table('permissions')->insert([
                [
                    'id' => 13,
                    'name' => 'superUsuario',
                    'guard_name' => 'web',
                    'created_at' => '2025-02-23 19:36:12',
                    'updated_at' => '2025-02-23 19:36:12',
                ],
            ]);
        }

        // Insertar relación en la tabla `model_has_roles` solo si no existe
        if (!DB::table('model_has_roles')->where('role_id', 1)->where('model_id', 1)->exists()) {
            DB::table('model_has_roles')->insert([
                'role_id' => 1,
                'model_type' => 'App\Models\User',
                'model_id' => 1,
            ]);
        }

        // Insertar autorizaciones en la tabla `authorizations` solo si no existen
        if (!DB::table('authorizations')->where('id', 7)->exists()) {
            DB::table('authorizations')->insert([
                [
                    'id' => 7,
                    'name' => 'Ver todas las obligaciones disponibles',
                    'guard_name' => 'web',
                    'created_at' => '2025-02-23 19:30:36',
                    'updated_at' => '2025-02-23 19:30:36',
                ],
            ]);
        }

        if (!DB::table('authorizations')->where('id', 8)->exists()) {
            DB::table('authorizations')->insert([
                [
                    'id' => 8,
                    'name' => 'Limitado a obligaciones',
                    'guard_name' => 'web',
                    'created_at' => '2025-02-23 19:30:36',
                    'updated_at' => '2025-02-23 19:30:36',
                ],
            ]);
        }

        // Insertar relación en la tabla `model_has_authorizations` solo si no existe
        if (!DB::table('model_has_authorizations')->where('authorization_id', 7)->where('model_id', 1)->exists()) {
            DB::table('model_has_authorizations')->insert([
                'authorization_id' => 7,
                'model_type' => 'App\Models\User',
                'model_id' => 1,
                'created_at' => '2025-02-23 19:57:36',
                'updated_at' => '2025-02-23 19:57:36',
            ]);
        }

        // Insertar relación en la tabla `model_has_permissions` solo si no existe
        if (!DB::table('model_has_permissions')->where('permission_id', 13)->where('model_id', 1)->exists()) {
            DB::table('model_has_permissions')->insert([
                'permission_id' => 13,
                'model_type' => 'App\Models\User',
                'model_id' => 1,
            ]);
        }
    }

    public function down()
    {
        // Eliminar registros en orden inverso
        DB::table('model_has_permissions')->where('permission_id', 13)->where('model_id', 1)->delete();
        DB::table('model_has_authorizations')->where('authorization_id', 7)->where('model_id', 1)->delete();
        DB::table('authorizations')->whereIn('id', [7, 8])->delete();
        DB::table('model_has_roles')->where('role_id', 1)->where('model_id', 1)->delete();
        DB::table('permissions')->whereIn('id', [12, 13])->delete();
        DB::table('users')->where('id', 1)->delete();
    }
}