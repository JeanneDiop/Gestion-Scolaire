<?php

namespace App\Http\Requests\CompteComptable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateCompteComptableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom_compte_comptable' => 'required|string|max:255',
            'code_compte_comptable' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ];
    }
    public function messages()
    {
        return [
            'nom_compte_comptable.required' => 'Le nom du compte comptable est requis.',
            'nom_compte_comptable.string' => 'Le nom du compte comptable doit être une chaîne de caractères.',
            'nom_compte_comptable.max' => 'Le nom du compte comptable ne peut pas dépasser 255 caractères.',

            'code_compte_comptable.required' => 'Le code du compte comptable est requis.',
            'code_compte_comptable.string' => 'Le code du compte comptable doit être une chaîne de caractères.',
            'code_compte_comptable.max' => 'Le code du compte comptable ne peut pas dépasser 255 caractères.',

            'user_id.required' => 'L\'ID de l\'utilisateur est requis.',
            'user_id.exists' => 'L\'ID de l\'utilisateur doit être un ID valide existant dans la table des utilisateurs.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        // Si la validation échoue, vous pouvez accéder aux erreurs
        $errors = $validator->errors()->toArray();

        // Retournez les erreurs dans la réponse JSON
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}

