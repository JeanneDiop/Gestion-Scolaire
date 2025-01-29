<?php

namespace App\Http\Requests\Rapport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateRapportRequest extends FormRequest
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
            'nom_rapport' => 'nullable|string|max:255',
            'type_utilisateur' => 'required|in:apprenant,enseignant',
            'date_commentaire' => 'nullable|date',
            'nullable|string|required_if:statut,retard',
            'commentaire_apprenant' => 'nullable|string|required_if:type_utilisateur,apprenant',
            'commentaire_enseignant' => 'nullable|string|required_if:type_utilisateur,enseignant',
            'apprenant_id' => 'nullable|exists:apprenants,id|required_if:type_utilisateur,apprenant',
            'enseignant_id' => 'nullable|exists:enseignants,id|required_if:type_utilisateur,enseignant',
        ];
    }

    /**
     * Obtenir les messages de validation personnalisés.
     *
     * @return array
     */
    public function messages()
{
    return [
        'nom_rapport.nullable' => 'Le champ nom_rapport est optionnel.',
        'nom_rapport.string' => 'Le champ nom_rapport doit être une chaîne de caractères.',
        'nom_rapport.max' => 'Le champ nom_rapport ne peut pas dépasser 255 caractères.',
        
        'type_utilisateur.required' => 'Le champ type_utilisateur est requis.',
        'type_utilisateur.in' => 'Le champ type_utilisateur doit être soit "apprenant", soit "enseignant".',
        
        'date_commentaire.nullable' => 'Le champ date_commentaire est optionnel.',
        'date_commentaire.date' => 'Le champ date_commentaire doit être une date valide.',
        
        'commentaire_apprenant.nullable' => 'Le champ commentaire_apprenant est optionnel.',
        'commentaire_apprenant.string' => 'Le champ commentaire_apprenant doit être une chaîne de caractères.',
        'commentaire_apprenant.required_if' => 'Le champ commentaire_apprenant est requis lorsque le type d\'utilisateur est "apprenant".',
        
        'commentaire_enseignant.nullable' => 'Le champ commentaire_enseignant est optionnel.',
        'commentaire_enseignant.string' => 'Le champ commentaire_enseignant doit être une chaîne de caractères.',
        'commentaire_enseignant.required_if' => 'Le champ commentaire_enseignant est requis lorsque le type d\'utilisateur est "enseignant".',
        
        'apprenant_id.nullable' => 'Le champ apprenant_id est optionnel.',
        'apprenant_id.exists' => 'L\'ID de l\'apprenant doit exister dans la table des apprenants.',
        'apprenant_id.required_if' => 'Le champ apprenant_id est requis lorsque le type d\'utilisateur est "apprenant".',
        
        'enseignant_id.nullable' => 'Le champ enseignant_id est optionnel.',
        'enseignant_id.exists' => 'L\'ID de l\'enseignant doit exister dans la table des enseignants.',
        'enseignant_id.required_if' => 'Le champ enseignant_id est requis lorsque le type d\'utilisateur est "enseignant".',
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
