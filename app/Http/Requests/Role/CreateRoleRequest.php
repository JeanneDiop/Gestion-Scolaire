<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // Assure que l'utilisateur est autorisé à faire cette demande
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:20', 'alpha', 'min:3']
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le champ nom est requis.',
            'nom.max' => 'Le nom ne peut pas dépasser 20 caractères.',
            'nom.alpha' => 'Le nom doit contenir uniquement des lettres.',
            'nom.min' => 'Le nom doit comporter au moins 3 caractères.'
        ];
    }
}
