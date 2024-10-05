<?php

namespace App\Http\Requests\Programme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class UpdateProgrammeRequest extends FormRequest
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
            'nom' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'niveau_education' => 'nullable|string|max:255',
            'credits' => 'nullable|integer|min:0',
            'date_debut' => 'nullable|date|before_or_equal:date_fin',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'cours_id' => 'required|exists:cours,id', // S'assure que le cours existe
        ];
    }

    /**
     * Messages d'erreur personnalisés pour les règles de validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nom.string' => 'Le nom du programme doit être une chaîne de caractères.',
            'credits.integer' => 'Les crédits doivent être un entier.',
            'date_debut.before_or_equal' => 'La date de début doit être antérieure ou égale à la date de fin.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'cours_id.required' => 'Le champ cours est obligatoire.',
            'cours_id.exists' => 'Le cours sélectionné est invalide.',
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
