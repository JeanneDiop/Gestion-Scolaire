<?php

namespace App\Http\Requests\Evaluation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateEvaluationRequest extends FormRequest
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
            'nom_evaluation' => 'required|string|max:255', // Nom de l'évaluation obligatoire
            'niveau_education' => 'required|string|max:255', // Niveau d'éducation obligatoire
            'categorie' => 'nullable|in:theorique,pratique,sport', // Catégorie peut être nulle ou doit être une des valeurs définies
            'type_evaluation' => 'nullable|in:devoir1,devoir2,examen', // Type d'évaluation peut être nul ou doit être une des valeurs définies
            'date_evaluation' => 'required|date', // Date d'évaluation obligatoire
            'apprenant_id' => 'required|exists:apprenants,id', // Doit correspondre à un ID valide dans la table apprenants
            'cours_id' => 'required|exists:cours,id', // Doit correspondre à un ID valide dans la table cours
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
            'nom_evaluation.required' => 'Le nom de l\'évaluation est obligatoire.',
            'nom_evaluation.string' => 'Le nom de l\'évaluation doit être une chaîne de caractères.',
            'nom_evaluation.max' => 'Le nom de l\'évaluation ne peut pas dépasser 255 caractères.',

            'niveau_education.required' => 'Le niveau d\'éducation est obligatoire.',
            'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
            'niveau_education.max' => 'Le niveau d\'éducation ne peut pas dépasser 255 caractères.',

            'categorie.in' => 'La catégorie doit être soit "theorique", "pratique" ou "sport".',

            'type_evaluation.in' => 'Le type d\'évaluation doit être soit "devoir1", "devoir2" ou "examen".',

            'date_evaluation.required' => 'La date de l\'évaluation est obligatoire.',
            'date_evaluation.date' => 'La date de l\'évaluation doit être une date valide.',

            'apprenant_id.required' => 'L\'ID de l\'apprenant est obligatoire.',
            'apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',

            'cours_id.required' => 'L\'ID du cours est obligatoire.',
            'cours_id.exists' => 'Le cours sélectionné n\'existe pas.',
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
