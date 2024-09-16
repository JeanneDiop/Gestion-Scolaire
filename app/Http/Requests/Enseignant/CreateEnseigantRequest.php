<?php

namespace App\Http\Requests\Enseignant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEnseigantRequest extends FormRequest
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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => ['required','string','email','max:255','regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/','unique:users'],
            'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/','unique:users'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre'=>'required|string|in:homme,femme',
            'profession' => 'required|string',
        ];
    }
}
