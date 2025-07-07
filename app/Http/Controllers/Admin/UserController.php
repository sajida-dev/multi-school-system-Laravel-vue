<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles:id,name')->select('id', 'name', 'email')->get();
        return response()->json($users);
    }

    public function roles()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }

    public function assignRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $roles = $request->input('roles', []);
        $user->syncRoles($roles);
        return response()->json(['success' => true]);
    }
}
