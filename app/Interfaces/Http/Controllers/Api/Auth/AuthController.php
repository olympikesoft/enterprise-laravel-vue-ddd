<?php

namespace App\Interfaces\Http\Controllers\Api\Auth;

use Illuminate\Routing\Controller;
use App\Infrastructure\Persistence\Models\User;
use App\Interfaces\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(new UserResource($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) { // 'remember' is more for web, but fine
            $user = Auth::user();
            // For Sanctum API tokens, you might issue a token here if not using cookie-based SPA auth
            // $token = $user->createToken('api-token')->plainTextToken;
            // return response()->json(['user' => new UserResource($user), 'token' => $token]);
            // For SPA cookie-based auth with Sanctum, just returning user is fine after session is established
            $request->session()->regenerate(); // Important for session-based SPA auth
            return response()->json(new UserResource($user));
        }

        return response()->json([
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout(); // For Sanctum SPA cookie-based auth
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If using API tokens:
        // $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function user(Request $request): JsonResponse|UserResource
    {
        if (!Auth::check()) { // Should be protected by auth:sanctum middleware anyway
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        return new UserResource($request->user());
    }
}
