<?php

namespace App\Http\Requests\ClasseAssociation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateClasseAssociationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
        public function authorize()
        {
            return true; // Autoriser toutes les requêtes (vous pouvez ajouter une logique d'autorisation si nécessaire)
        }

        public function rules()
        {
            return [
                'apprenant_id' => 'nullable|exists:apprenants,id',
                'cours_id' => 'required|exists:cours,id',
                'enseignant_id' => 'required|exists:enseignants,id',
                'classe_id' => 'nullable|exists:classes,id',
            ];
        }

        public function messages()
    {
        return [
            'apprenant_id.required' => 'Le champ apprenant_id est requis.',
            'apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',

            'cours_id.required' => 'Le champ cours_id est requis.',
            'cours_id.exists' => 'Le cours sélectionné n\'existe pas.',

            'enseignant_id.required' => 'Le champ enseignant_id est requis.',
            'enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
            'classe_id.required' => 'Le champ classe_id est requis.',
            'classe_id.exists' => 'Le classe sélectionné n\'existe pas.',
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

