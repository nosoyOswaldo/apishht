<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // REGISTRO DE USUARIO
    public function register(Request $request)
    {
        // 1. Validamos que los datos sean seguros y correctos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // 2. Creamos el usuario (La contraseña se hashea automáticamente por Laravel)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Generamos el token de acceso
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '¡Bienvenido al Rock and SHIT Market!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        // 1. Validamos entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Buscamos al usuario
        $user = User::where('email', $request->email)->first();

        // 3. Verificamos contraseña y existencia
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas, intenta de nuevo.'],
            ]);
        }

        // 4. Si pasa, entregamos token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        // Borra el token actual para cerrar sesión
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}