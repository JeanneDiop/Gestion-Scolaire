<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\CreateRoleRequest;
use Illuminate\Http\Request;
use Exception;
use App\Models\Role;

class RoleController extends Controller
{
    public function register(CreateRoleRequest $request)
    {
        try {
        Role::FindOrFail($request->role_id);
          $role = new Role();
          $role->nom = $request->nom;
          $role->save();
    
          return response()->json([
            'status_code' => 200,
            'status_message' => 'user a été ajouté',
            'data' => $role
          ]);
        } catch (Exception $e) {
          return response()->json($e);
        }
    }
}