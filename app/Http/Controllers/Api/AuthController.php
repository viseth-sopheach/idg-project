<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;

class AuthController extends Controller
{
   use ApiResponse;

   public function login(LoginRequest $request)
   {
      $credentials = $request->validated();

      $user = User::where('email', $credentials['email'])->first();

      if (!$user || !Hash::check($credentials['password'], $user->password)) {
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
      $user = Auth::user();

      if ($user instanceof \App\Models\User) {
         /** @var \Laravel\Sanctum\PersonalAccessToken $token */
         $token = $user->currentAccessToken();
         $token?->delete();
      }

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

   public function updateProfile(UpdateProfileRequest $request)
   {
      $user = $request->user();
      $user->update(['name' => $request->validated('name')]);

      return $this->success([
         'id' => $user->id,
         'name' => $user->name,
         'email' => $user->email,
      ], 'Profile updated successfully.');
   }

   public function updatePassword(UpdatePasswordRequest $request)
   {
      $user = $request->user();
      $user->update(['password' => Hash::make($request->validated('password'))]);
      $currentTokenId = $user->currentAccessToken()?->id;
      $user->tokens()->when($currentTokenId, fn($q) => $q->where('id', '!=', $currentTokenId))->delete();

      return $this->success(null, 'Password updated successfully.');
   }
}
