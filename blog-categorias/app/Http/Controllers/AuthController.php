<?php

namespace App\Http\Controllers;

use App\Models\User;    
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
   /**
     * POST /auth/register
     * Crea un usuario y devuelve un token Sanctum
     */
    public function register(Request $request)
    {
        // 1. Validamos los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // 2. Creamos el usuario encriptando la contraseña
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // 3. Generamos el token de Sanctum (Día 6)
        // $token = $user->createToken('auth_token')->plainTextToken;
        $token = $user->createToken('auth_token', ['posts:read', 'posts:write'])->plainTextToken;

        // 4. Devolvemos el usuario y el token
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * POST /auth/login
     * Autentica al usuario y devuelve un token Sanctum
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificamos si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Generamos un nuevo token
        // $token = $user->createToken('auth_token')->plainTextToken;
        $token = $user->createToken('auth_token', ['posts:read', 'posts:write'])->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * POST /auth/logout
     * Revoca el token actual del usuario
     */
    public function logout(Request $request)
    {
        // Borramos el token que se usó para esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
