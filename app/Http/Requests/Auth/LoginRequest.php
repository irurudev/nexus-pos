<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username harus diisi',
            'username.string' => 'Username harus berupa teks',
            'username.max' => 'Username tidak boleh lebih dari 50 karakter',
            'password.required' => 'Password harus diisi',
            'password.string' => 'Password harus berupa teks',
            'password.min' => 'Password minimal 6 karakter',
        ];
    }
}
