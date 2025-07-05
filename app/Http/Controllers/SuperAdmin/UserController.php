<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Resources\UserResource;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::with(['rol', 'balneario'])->paginate(10);
        return UserResponse::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nombre_completo' => 'required|string|max:255',
            'rol_id' => 'required|exists:roles,id',
            'balneario_id' => 'nullable|exists:balnearios,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nombre_completp' => $request->nombre_completo,
            'balneario_id' => $request->balneario_id,
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('balneario', 'roles');

        return new UserResource($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $usuario->id,
            'password' => 'sometimes|min:8',
            'role' => 'sometimes|in:checador, admin, superadmin',
            'balneario_id' => 'sometimes|exists:balneario,id'
        ]);

        $data = $request->all();
        if ($request->has('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $usuario->update($data);
        return response()->json($usuario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $usuario->delete();
        return response()->json(null, 204);
    }
}
