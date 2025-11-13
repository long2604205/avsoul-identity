<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\UserToken;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|unique:users,email',
            'password'  => 'required|string'
        ]);

        // Tạo user
        $user = \App\Models\User::create([
            'person_id' => 1,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,
            'email_verified_at' => now(),
            'status'    => 1,
            'version'   => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ]);

        // Login user ngay sau khi đăng ký
        $token = auth()->login($user);

        // tạo refresh token
        $refreshToken = \Illuminate\Support\Str::random(64);
        \App\Models\UserToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'ip_address' => hash('sha256', $request->ip()),
            'user_agent' => $request->header('User-Agent'),
            'is_revoked' => false,
            'expires_at' => now()->addDays(30),
        ]);

        return response()
            ->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ])
            ->cookie('refresh_token', $refreshToken, 60 * 24 * 30, '/', null, true, true, false, 'Strict');
    }

    // LOGIN
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $refreshToken = Str::random(64);
        UserToken::create([
            'user_id' => auth()->id(),
            'token' => hash('sha256', $refreshToken),
            'ip_address' => hash('sha256', $request->ip()),
            'user_agent' => $request->header('User-Agent'),
            'is_revoked' => false,
            'expires_at' =>  Carbon::now()->addDays(30),
        ]);

        return response()
            ->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ])
            ->cookie('refresh_token', $refreshToken, 60 * 24 * 30, '/', null, true, true, false, 'Strict');
    }

    // REFRESH ACCESS TOKEN
    public function refresh(Request $request): JsonResponse
    {
        $raw = $request->cookie('refresh_token');
        if (!$raw) return response()->json(['error' => 'Missing refresh token'], 401);

        $hashed = hash('sha256', $raw);

        $refreshToken = UserToken::where('token', $hashed)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$refreshToken) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }

        $token = auth()->loginUsingId($refreshToken->user_id);

        // ROTATE refresh token
        $new = Str::random(64);
        $refreshToken->update([
            'token' => hash('sha256', $new),
            'expires_at' => now()->addDays(30)
        ]);

        return response()
            ->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ])
            ->cookie('refresh_token', $new, 60 * 24 * 30, '/', null, true, true, false, 'Strict');
    }

    // LOGOUT
    public function logout(Request $request): JsonResponse
    {
        $raw = $request->cookie('refresh_token');

        if ($raw) {
            $hashed = hash('sha256', $raw);
            UserToken::where('token', $hashed)
                ->update(['is_revoked' => true]);
        }

        auth()->logout();

        return response()
            ->json(['message' => 'Logged out successfully'])
            ->cookie('refresh_token', '', -1);
    }

    // PROFILE
    public function profile(): JsonResponse
    {
        return response()->json(auth()->user());
    }
}
