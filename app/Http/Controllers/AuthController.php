<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        Log::create([
            'description' => 'Logou no sistema',
            'user_id' => $user->id,
            'request' => json_encode(['email' => $request->email]),
        ]);

        return response()->json([
            'status' => true,
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        $user = auth()->user();

        Log::create([
            'description' => 'Deslogou do sistema',
            'user_id' => Auth::user()->id,
            'request' => json_encode([]),
        ]);

        return response()->json(['status' => true, 'user' => $user, 'message' => 'Logout realizado com sucesso']);
    }

    public function validateToken(Request $request)
    {
        try {
            $token = JWTAuth::parseToken()->authenticate();

            return response()->json(['status' => true, 'message' => 'Token válido']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token de autorização não encontrado'], 401);
        }
    }
}
