<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateProfileRequest extends FormRequest
{
   public function authorize(): bool
   {
      return true;
   }

   public function rules(): array
   {
      return [
         'name' => ['required', 'string', 'max:255'],
         'current_password' => ['required', 'string'],
      ];
   }

   public function withValidator($validator): void
   {
      $validator->after(function ($validator) {
         if (!Hash::check($this->input('current_password'), $this->user()->password)) {
            $validator->errors()->add('current_password', 'The current password is incorrect.');
         }
      });
   }

   public function messages(): array
   {
      return [
         'current_password.required' => 'Please enter your password to confirm this change.',
      ];
   }
}
