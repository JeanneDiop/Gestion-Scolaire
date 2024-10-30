<?php

namespace App\Http\Requests\Enseignant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateEnseignantRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/'],
            //'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/'],
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:Homme,Femme',
            'role_nom' => 'required|string',
            'image' => ['nullable', 'string'],
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'nationalité' => 'nullable|string|max:255',
            'numero_CNI' => 'required|string|max:50',
            'numero_identification_enseignant' => 'required|string|max:255',
            'matiere_enseignée' => 'required|string|max:255',
            'niveau_enseignant' => 'required|string|max:255',
            'statut_enseignant' => 'required|in:Permanent,Vacataire,Temporaire',
            'date_debut_service' => 'required|date',
            'type_contrat' => 'required|in:CDI,CDD,Contrat,Vacataire',
            'heure_travail_hebdomadaire' => 'nullable|string',
            'salaire_base' => 'required|numeric',
            'type_salaire' => 'required|in:Mensuel,Horaire',
            'prime_indemnités' => 'nullable|string',
            'cotisation_sociales' => 'nullable|string',
            'part_employeur' => 'nullable|string',
            'retenue_salaire' => 'nullable|string',
            'mode_paiement' => 'required|in:Virement,Bancaire,Espèce,Chèque',
            'banque_domiciliation' => 'nullable|string',
            'numero_RIB' => 'nullable|string',
            'cv_diplomes' => 'nullable|string',
            'contrat_travail' => 'nullable|string',
            'ancienneté' => 'nullable|string',
            'evaluation_performance' => 'nullable|numeric',
            'commentaires_notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
{
    return [
        'nom.required' => 'Le champ nom est requis.',
        'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
        'nom.max' => 'Le champ nom ne doit pas dépasser 255 caractères.',

        'prenom.required' => 'Le champ prénom est requis.',
        'prenom.string' => 'Le champ prénom doit être une chaîne de caractères.',
        'prenom.max' => 'Le champ prénom ne doit pas dépasser 255 caractères.',

        'email.required' => 'Le champ email est requis.',
        'email.string' => 'Le champ email doit être une chaîne de caractères.',
        'email.email' => 'Le champ email doit être une adresse email valide.',
        'email.max' => 'Le champ email ne doit pas dépasser 255 caractères.',
        'email.regex' => 'Le format de l\'email est invalide.',

        //'password.required' => 'Le champ mot de passe est requis.',
        //'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',

        'telephone.required' => 'Le champ téléphone est requis.',
        'telephone.regex' => 'Le numéro de téléphone doit commencer par +221 et suivre le format spécifié.',

        'adresse.required' => 'Le champ adresse est requis.',
        'adresse.string' => 'Le champ adresse doit être une chaîne de caractères.',
        'role_nom.required' => 'Le champ role_nom est requis.',
        'role_nom.string' => 'Le champ role_nom doit être une chaîne de caractères.',

        'etat.sometimes' => 'Le champ état doit être spécifié.',
        'etat.string' => 'Le champ état doit être une chaîne de caractères.',
        'etat.in' => 'L\'état doit être soit "actif" soit "inactif".',

        'genre.required' => 'Le champ genre est requis.',
        'genre.string' => 'Le champ genre doit être une chaîne de caractères.',
        'genre.in' => 'Le genre doit être soit "Homme" soit "Femme".',

        'image.string' => 'Le champ image doit être une chaîne de caractères.',

        'date_naissance.required' => 'Le champ date de naissance est requis.',
        'date_naissance.date' => 'Le champ date de naissance doit être une date valide.',

        'lieu_naissance.required' => 'Le champ lieu de naissance est requis.',
        'lieu_naissance.string' => 'Le champ lieu de naissance doit être une chaîne de caractères.',
        'lieu_naissance.max' => 'Le champ lieu de naissance ne doit pas dépasser 255 caractères.',

        'nationalité.string' => 'Le champ nationalité doit être une chaîne de caractères.',
        'nationalité.max' => 'Le champ nationalité ne doit pas dépasser 255 caractères.',

        'numero_CNI.required' => 'Le champ numéro CNI est requis.',
        'numero_CNI.string' => 'Le champ numéro CNI doit être une chaîne de caractères.',
        'numero_CNI.max' => 'Le champ numéro CNI ne doit pas dépasser 50 caractères.',

        'numero_identification_enseignant.required' => 'Le champ numéro d\'identification enseignant est requis.',
        'numero_identification_enseignant.string' => 'Le champ numéro d\'identification enseignant doit être une chaîne de caractères.',
        'numero_identification_enseignant.max' => 'Le champ numéro d\'identification enseignant ne doit pas dépasser 255 caractères.',

        'matiere_enseignée.required' => 'Le champ matière enseignée est requis.',
        'matiere_enseignée.string' => 'Le champ matière enseignée doit être une chaîne de caractères.',
        'matiere_enseignée.max' => 'Le champ matière enseignée ne doit pas dépasser 255 caractères.',

        'niveau_enseignant.required' => 'Le champ niveau enseignant est requis.',
        'niveau_enseignant.string' => 'Le champ niveau enseignant doit être une chaîne de caractères.',
        'niveau_enseignant.max' => 'Le champ niveau enseignant ne doit pas dépasser 255 caractères.',

        'statut_enseignant.required' => 'Le champ statut enseignant est requis.',
        'statut_enseignant.in' => 'Le statut enseignant doit être soit "Permanent", "Vacataire" ou "Temporaire".',

        'date_debut_service.required' => 'Le champ date de début de service est requis.',
        'date_debut_service.date' => 'Le champ date de début de service doit être une date valide.',

        'type_contrat.required' => 'Le champ type de contrat est requis.',
        'type_contrat.in' => 'Le type de contrat doit être soit "CDI", "CDD", "Contrat" ou "Vacataire".',

        'heure_travail_hebdomadaire.string' => 'Le champ heure de travail hebdomadaire doit être une chaîne de caractères.',

        'salaire_base.required' => 'Le champ salaire de base est requis.',
        'salaire_base.numeric' => 'Le champ salaire de base doit être un nombre.',

        'type_salaire.required' => 'Le champ type de salaire est requis.',
        'type_salaire.in' => 'Le type de salaire doit être soit "Mensuel" ou "Horaire".',

        'prime_indemnités.string' => 'Le champ prime et indemnités doit être une chaîne de caractères.',

        'cotisation_sociales.string' => 'Le champ cotisations sociales doit être une chaîne de caractères.',

        'part_employeur.string' => 'Le champ part employeur doit être une chaîne de caractères.',

        'retenue_salaire.string' => 'Le champ retenue salaire doit être une chaîne de caractères.',

        'mode_paiement.required' => 'Le champ mode de paiement est requis.',
        'mode_paiement.in' => 'Le mode de paiement doit être soit "Virement", "Bancaire", "Espèce" ou "Chèque".',

        'banque_domiciliation.string' => 'Le champ banque de domiciliation doit être une chaîne de caractères.',

        'numero_RIB.string' => 'Le champ numéro RIB doit être une chaîne de caractères.',

        'cv_diplomes.string' => 'Le champ CV et diplômes doit être une chaîne de caractères.',

        'contrat_travail.string' => 'Le champ contrat de travail doit être une chaîne de caractères.',

        'ancienneté.string' => 'Le champ ancienneté doit être une chaîne de caractères.',

        'evaluation_performance.numeric' => 'Le champ évaluation de performance doit être un nombre.',

        'commentaires_notes.string' => 'Le champ commentaires et notes doit être une chaîne de caractères.',
        'commentaires_notes.max' => 'Le champ commentaires et notes ne doit pas dépasser 1000 caractères.',
    ];
}

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
