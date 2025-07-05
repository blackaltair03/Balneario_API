<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'requiered|string|mas:255',
            'email' => 'required|string|email|mas:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol_id' => 'required|in:checador,admin,superadmin',
            'balneario_id' => $data['balneario_id'] ?? null
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'balneario_id' => $request->balneario_id
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales invalidas'
            ], 401);
        }


        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesion Cerrada Exitosamente'
        ]);
    }

    public function userProfile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|mas:255',
            'password' => 'sometimes|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de Validacion',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
        ]);
    }
}
