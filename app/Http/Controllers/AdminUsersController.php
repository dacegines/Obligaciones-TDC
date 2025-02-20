<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;



class AdminUsersController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('superUsuario')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $users = DB::table('users')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->leftJoin('model_has_permissions', 'users.id', '=', 'model_has_permissions.model_id')
            ->leftJoin('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->leftJoin('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
            ->leftJoin('authorizations', 'model_has_authorizations.authorization_id', '=', 'authorizations.id')
            ->select(
                'users.id',
                'users.name as user_name',
                'users.email',
                'users.puesto',
                'roles.name as role_name',
                'permissions.name as permission_name',
                DB::raw('COALESCE(authorizations.name) as authorization_name')
            )
            ->get();


        $permissions = DB::table('permissions')->select('id', 'name', 'created_at')->orderBy('id', 'asc')->get();
        $roles = DB::table('roles')->select('id', 'name', 'created_at')->orderBy('id', 'asc')->get();
        $authorizations = DB::table('authorizations')->select('id', 'name', 'created_at')->orderBy('id', 'asc')->get();

        return view('gestion_cumplimiento.admin_usuarios.index', compact('users', 'permissions', 'roles', 'authorizations'));
    }


    public function register(Request $request)
    {
       
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', 
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*]/',
                'confirmed',
            ],
        ]);

        
        $user = User::create([
            'name' => $validatedData['name'],
            'puesto' => $validatedData['puesto'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        
        return redirect()->back()->with('success', 'Usuario registrado exitosamente.');
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        if ($exists) {
            return response()->json(['message' => 'El correo electrónico ya está registrado.', 'status' => 'error'], 200);
        }

        return response()->json(['message' => 'El correo electrónico está disponible.', 'status' => 'success'], 200);
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
            'model_type' => 'required|string',
        ]);

        // Verificar si el registro ya existe
        $exists = DB::table('model_has_permissions')
            ->where('model_id', $request->input('model_id'))
            ->where('model_type', $request->input('model_type'))
            ->exists();

        if ($exists) {
            // Si ya existe, sobrescribir el permiso
            DB::table('model_has_permissions')
                ->where('model_id', $request->input('model_id'))
                ->where('model_type', $request->input('model_type'))
                ->update([
                    'permission_id' => $request->input('permission_id'),
                ]);

            return redirect()->back()->with('success', 'El permiso se ha actualizado correctamente.');
        }

        // Si no existe, insertar un nuevo registro
        DB::table('model_has_permissions')->insert([
            'permission_id' => $request->input('permission_id'),
            'model_id' => $request->input('model_id'),
            'model_type' => $request->input('model_type'),
        ]);

        return redirect()->back()->with('success', 'Permiso asignado correctamente.');
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
            'model_type' => 'required|string',
        ]);

        // Verificar si el registro ya existe
        $exists = DB::table('model_has_roles')
            ->where('model_id', $request->input('model_id'))
            ->where('model_type', $request->input('model_type'))
            ->exists();

        if ($exists) {
            // Si ya existe, sobrescribir el rol
            DB::table('model_has_roles')
                ->where('model_id', $request->input('model_id'))
                ->where('model_type', $request->input('model_type'))
                ->update([
                    'role_id' => $request->input('role_id'),
                ]);

            return redirect()->back()->with('success', 'El rol se ha actualizado correctamente.');
        }

        // Si no existe, insertar un nuevo registro
        DB::table('model_has_roles')->insert([
            'role_id' => $request->input('role_id'),
            'model_id' => $request->input('model_id'),
            'model_type' => $request->input('model_type'),
        ]);

        return redirect()->back()->with('success', 'Rol asignado correctamente.');
    }

    public function destroy($id)
    {
       
        DB::table('users')->where('id', $id)->delete();


        DB::table('model_has_permissions')->where('model_id', $id)->delete();


        DB::table('model_has_roles')->where('model_id', $id)->delete();


        DB::table('model_has_authorizations')->where('model_id', $id)->delete();


        return redirect()->back()->with('success', 'Usuario eliminado exitosamente.');
    }


    public function update(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $request->user_id,
            'puesto' => 'required|string|max:255',
        ]);

        User::where('id', $request->user_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'puesto' => $request->puesto,
        ]);

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        
        Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return redirect()->back()->with('success', 'Rol creado exitosamente.');
    }


    public function createPermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
        ]);

        
        Permission::create([
            'name' => $request->name,
            'guard_name' => 'web', 
        ]);

        return redirect()->back()->with('success', 'Permiso creado exitosamente.');
    }

    public function deleteRole($id)
    {
        Role::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Rol eliminado correctamente.');
    }

    public function deletePermission($id)
    {
        Permission::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Permiso eliminado correctamente.');
    }

    public function storeAuthorization(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:users,id',
            'authorization_id' => 'required|exists:authorizations,id',
            'model_type' => 'required|string',
        ]);

        // Verificar si ya existe
        $exists = DB::table('model_has_authorizations')
            ->where('model_id', $request->input('model_id'))
            ->where('model_type', $request->input('model_type'))
            ->exists();

        if ($exists) {
            DB::table('model_has_authorizations')
                ->where('model_id', $request->input('model_id'))
                ->where('model_type', $request->input('model_type'))
                ->update(['authorization_id' => $request->input('authorization_id')]);

            return redirect()->back()->with('success', 'Autorización actualizada correctamente.');
        }

        // Si no existe, insertar
        DB::table('model_has_authorizations')->insert([
            'authorization_id' => $request->input('authorization_id'),
            'model_id' => $request->input('model_id'),
            'model_type' => $request->input('model_type'),
        ]);

        return redirect()->back()->with('success', 'Autorización asignada correctamente.');
    }

    public function createAuthorization(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:authorizations,name',
        ]);

        // Crear la autorización con un valor predeterminado para 'guard_name'
        DB::table('authorizations')->insert([
            'name' => $request->input('name'),
            'guard_name' => 'web', // Valor por defecto para guard_name
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Autorización creada exitosamente.');
    }

    public function deleteAuthorization($id)
    {
        DB::table('authorizations')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Autorización eliminada correctamente.');
    }
}
