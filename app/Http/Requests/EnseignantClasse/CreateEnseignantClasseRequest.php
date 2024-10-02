<?php

namespace App\Http\Requests\EnseignantClasse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
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
            'classe_id' => 'required|integer',
            'enseignant_id' => 'required|integer', // Assurez-vous que l'enseignant existe
        ];
    }

    public function messages()
    {
        return [
            'classe_id.required' => 'L\'ID de la classe est obligatoire.',
            'classe_id.integer' => 'L\'ID de la classe doit être un nombre entier.', // Classe obligatoire, doit exister dans la table classes
            'enseignant_id.required' => 'L\'ID de la enseignant est obligatoire.',
            'enseignant_id.integer' => 'L\'ID de la enseignant doit être un nombre entier.',
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
