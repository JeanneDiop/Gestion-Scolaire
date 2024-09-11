<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class LogUserRequest extends FormRequest
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
            'email' => ['required','string','email','max:255','regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/','unique:users,email'],
            'password' => 'required|min:8',

        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Le champ email est requis.',
            'email.email' => 'Le champ email doit être une adresse email valide et doit etre unique.',
            'password.min' => 'Le champ mot de passe doit avoir au moins :min 8 caractères et doit etre unique.',
            "password.confirmed" => 'Les mots de passe ne sont pas conforment',
        ];
    }
}
