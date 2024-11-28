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
            'nom_evaluation' => 'required|string|max:255',
            'niveau_education' => 'required|string|max:255',
            'categorie' => 'nullable|string|max:255',
            'type_evaluation' => 'nullable|string|max:255',
            'date_evaluation' => 'required|date',
            'cours_id' => 'required|exists:cours,id', // L'ID du cours doit exister dans la table des cours
            'apprenant_id' => 'nullable|array', // Le champ apprenant_id est nullable mais doit être un tableau si présent
            'apprenant_id.*' => 'nullable|exists:apprenants,id', // Chaque élément du tableau, s'il existe, doit être un ID valide dans la table des apprenants
            'classe_id' => 'nullable|exists:classes,id',
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
            'nom_evaluation.required' => 'Le nom de l\'évaluation est requis.',
            'nom_evaluation.string' => 'Le nom de l\'évaluation doit être une chaîne de caractères.',
            'nom_evaluation.max' => 'Le nom de l\'évaluation ne peut pas dépasser 255 caractères.',

            'niveau_education.required' => 'Le niveau d\'éducation est requis.',
            'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
            'niveau_education.max' => 'Le niveau d\'éducation ne peut pas dépasser 255 caractères.',

          
            'categorie.string' => 'La catégorie doit être une chaîne de caractères.',
            'categorie.max' => 'La catégorie ne peut pas dépasser 255 caractères.',

           
            'type_evaluation.string' => 'Le type d\'évaluation doit être une chaîne de caractères.',
            'type_evaluation.max' => 'Le type d\'évaluation ne peut pas dépasser 255 caractères.',

            'date_evaluation.required' => 'La date de l\'évaluation est requise.',
            'date_evaluation.date' => 'La date de l\'évaluation doit être une date valide.',

            'cours_id.required' => 'L\'ID du cours est requis.',
            'cours_id.exists' => 'Le cours spécifié n\'existe pas.',

            'apprenant_id.array' => 'Les apprenants doivent être un tableau.',
            'apprenant_id.*.exists' => 'Un ou plusieurs identifiants d\'apprenant sont invalides.',

            'classe_id.exists' => 'La classe spécifiée n\'existe pas.',
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
