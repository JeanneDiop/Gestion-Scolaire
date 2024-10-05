<?php

namespace App\Http\Requests\EnseignantClasse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateEnseignantClasseRequest extends FormRequest
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
            'enseignant_id' => 'nullable|exists:enseignants,id', // Peut être nul ou doit exister dans la table enseignants
            'classe_id' => 'required|exists:classes,id', // Doit correspondre à un ID valide dans la table classes
        ];
    }

    /**
     * Définit les messages d'erreur personnalisés pour les règles de validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
            'classe_id.required' => 'L\'ID de la classe est obligatoire.',
            'classe_id.exists' => 'La classe sélectionnée n\'existe pas.',
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
