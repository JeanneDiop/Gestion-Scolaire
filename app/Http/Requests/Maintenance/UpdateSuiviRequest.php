<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class UpdateSuiviRequest extends FormRequest
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
    public function rules()
    {
        return [
            'status' => 'nullable|in:en_attente,en_cours,termine',
            'date_resolution' => 'nullable|date|after_or_equal:today', // La date de résolution doit être aujourd'hui ou dans le futur
            'commentaire' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut doit être soit "en_cours",soit  "en_attente", soit "termine".',
            'date_resolution.date' => 'La date de résolution doit être une date valide.',
            'date_resolution.after_or_equal' => 'La date de résolution doit être aujourd\'hui ou dans le futur.',
            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
            'commentaire.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
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
