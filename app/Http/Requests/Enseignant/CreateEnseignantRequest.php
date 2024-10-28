<?php

namespace App\Http\Requests\Enseignant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateEnseignantRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/', 'unique:users,email'],
            'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/', 'unique:users,telephone'],
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:Homme,Femme',
            'image' => ['nullable', 'string'],
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'nationalité' => 'nullable|string|max:255',
            'numero_CNI' => 'required|string|max:50|unique:enseignants,numero_CNI',
            'numero_identification_enseignant' => 'required|string|max:255|unique:enseignants,numero_identification_enseignant',
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
            'nom.required' => 'Le nom est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',

            'prenom.required' => 'Le prénom est requis.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères.',

            'email.required' => 'L\'adresse email est requise.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'email.regex' => 'L\'adresse email n\'est pas au format valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.regex' => 'Le numéro de téléphone doit être au format +221 suivi de 9 chiffres.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'adresse.required' => 'L\'adresse est requise.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

            'etat.string' => 'L\'état doit être une chaîne de caractères.',
            'etat.in' => 'L\'état doit être soit actif soit inactif.',

            'genre.required' => 'Le genre est requis.',
            'genre.string' => 'Le genre doit être une chaîne de caractères.',
            'genre.in' => 'Le genre doit être soit Homme soit Femme.',

            'image.string' => 'L\'image doit être une chaîne de caractères.',

            'date_naissance.required' => 'La date de naissance est requise.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',

            'lieu_naissance.required' => 'Le lieu de naissance est requis.',
            'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',
            'lieu_naissance.max' => 'Le lieu de naissance ne doit pas dépasser 255 caractères.',

            'nationalité.required' => 'La nationalité est requise.',
            'nationalité.string' => 'La nationalité doit être une chaîne de caractères.',
            'nationalité.max' => 'La nationalité ne doit pas dépasser 255 caractères.',

            'numero_CNI.required' => 'Le numéro de CNI est requis.',
            'numero_CNI.string' => 'Le numéro de CNI doit être une chaîne de caractères.',
            'numero_CNI.max' => 'Le numéro de CNI ne doit pas dépasser 50 caractères.',
            'numero_CNI.unique' => 'Ce numéro de CNI est déjà utilisé.',

            'numero_identification_enseignant.required' => 'Le numéro d\'identification de l\'enseignant est requis.',
            'numero_identification_enseignant.string' => 'Le numéro d\'identification doit être une chaîne de caractères.',
            'numero_identification_enseignant.max' => 'Le numéro d\'identification ne doit pas dépasser 255 caractères.',
            'numero_identification_enseignant.unique' => 'Ce numéro d\'identification est déjà utilisé.',

            'matiere_enseignée.required' => 'La matière enseignée est requise.',
            'matiere_enseignée.string' => 'La matière enseignée doit être une chaîne de caractères.',
            'matiere_enseignée.max' => 'La matière enseignée ne doit pas dépasser 255 caractères.',

            'niveau_enseignant.required' => 'Le niveau de l\'enseignant est requis.',
            'niveau_enseignant.string' => 'Le niveau de l\'enseignant doit être une chaîne de caractères.',
            'niveau_enseignant.max' => 'Le niveau de l\'enseignant ne doit pas dépasser 255 caractères.',

            'statut_enseignant.required' => 'Le statut de l\'enseignant est requis.',
            'statut_enseignant.in' => 'Le statut de l\'enseignant doit être Permanent, Vacataire, ou Temporaire.',

            'date_debut_service.required' => 'La date de début de service est requise.',
            'date_debut_service.date' => 'La date de début de service doit être une date valide.',

            'type_contrat.required' => 'Le type de contrat est requis.',
            'type_contrat.in' => 'Le type de contrat doit être CDI, CDD, Contrat ou Vacataire.',

            'heure_travail_hebdomadaire.string' => 'L\'heure de travail hebdomadaire doit être une chaîne de caractères.',

            'salaire_base.required' => 'Le salaire de base est requis.',
            'salaire_base.numeric' => 'Le salaire de base doit être un nombre.',

            'type_salaire.required' => 'Le type de salaire est requis.',
            'type_salaire.in' => 'Le type de salaire doit être Mensuel ou Horaire.',

            'prime_indemnités.string' => 'Les primes et indemnités doivent être une chaîne de caractères.',

            'cotisation_sociales.string' => 'Les cotisations sociales doivent être une chaîne de caractères.',

            'part_employeur.string' => 'La part employeur doit être une chaîne de caractères.',

            'retenue_salaire.string' => 'La retenue sur le salaire doit être une chaîne de caractères.',

            'mode_paiement.required' => 'Le mode de paiement est requis.',
            'mode_paiement.in' => 'Le mode de paiement doit être Virement, Bancaire, Espèce ou Chèque.',

            'banque_domiciliation.string' => 'La banque de domiciliation doit être une chaîne de caractères.',

            'numero_RIB.string' => 'Le numéro de RIB doit être une chaîne de caractères.',

            'cv_diplomes.string' => 'Le CV et les diplômes doivent être une chaîne de caractères.',

            'contrat_travail.string' => 'Le contrat de travail doit être une chaîne de caractères.',

            'ancienneté.string' => 'L\'ancienneté doit être une chaîne de caractères.',

            'evaluation_performance.numeric' => 'L\'évaluation de la performance doit être un nombre.',

            'commentaires_notes.string' => 'Les commentaires et notes doivent être une chaîne de caractères.',
            'commentaires_notes.max' => 'Les commentaires et notes ne doivent pas dépasser 1000 caractères.',
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
