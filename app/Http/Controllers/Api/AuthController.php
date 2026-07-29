<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  use ApiResponse;

  public function login(LoginRequest $request)
  {
    $credentials = $request->validated();

    $user = User::where('email', $credentials['email'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
      throw ValidationException::withMessages([
        'email' => ['The provided credentials are incorrect.'],
      ]);
    }

    $token = $user->createToken($credentials['device_name'] ?? 'api-token')->plainTextToken;

    return $this->success([
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
      ],
      'token' => $token,
    ], 'Logged in successfully.');
  }

  public function logout()
  {
    Auth::user()?->currentAccessToken()->delete();

    return $this->success(null, 'Logged out successfully.');
  }

  public function me()
  {
    $user = Auth::user();

    return $this->success([
      'id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
    ]);
  }
}
