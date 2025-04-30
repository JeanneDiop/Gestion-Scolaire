<?php

namespace App\Http\Requests\PersonnelAdministratif;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreatePersonnelAdministratifRequest extends FormRequest
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
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
           'telephone' => ['nullable','required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/', 'unique:users,telephone',],
            'email' => ['required', 'string', 'email','nullable', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/', 'unique:users,email',],
            'password' => 'required|min:8',
            'adresse' => ['required', 'string'],
            'genre' => ['required', 'in:Femme,Homme'],
            'role_nom' => ['required', 'string'],
            'date_naissance' => ['required', 'date'],
            'lieu_naissance' => ['required', 'string'],
            'poste_occupé' => ['required', 'string'],
            'image' => ['nullable' ,'string'],
            'date_debut_service' => ['required', 'date'],
            'statut_employé' => ['required', 'in:Permanent,Temporaire,Vacataire'],
            'numero_identification_employe' => 'required|string|max:255|unique:personnel_administratifs,numero_identification_employe',
            'type_contrat' => ['required', 'in:CDI,CDD,Contrat,Vacataire'],
            'salaire_base' => ['required', 'string'],
            'horaires_travail' => ['nullable', 'string'],
            'type_salaire' => ['required', 'in:Mensuel,Horaire'],
            'departement_service' => ['required', 'in:Administratif,Comptabilité,Maintenance'],
            'prime_indemnités' => ['nullable', 'string'],
            'cotisation_sociales' => ['nullable', 'string'],
            'part_employeur' => ['nullable', 'string'],
            'retenue_salaire' => ['nullable', 'string'],
            'mode_paiement' => ['required', 'in:Virement,Bancaire,Espèce,Chèque'],
            'banque_domiciliation' => ['nullable', 'string'],
            'numero_compte_bancaire' => ['nullable', 'string','unique:personnel_administratifs,numero_compte_bancaire'],
            'cv_diplomes' => ['nullable', 'string'],
            'certification_formations' => ['nullable', 'string'],
            'superviseur' => ['nullable', 'string'],
            'contrat_travail' => ['nullable', 'string'],
            'ancienneté' => ['nullable', 'string'],
            'evaluation_performance' => ['nullable', 'numeric'],
            'commentaires_notes' => ['nullable', 'string'],
            'numero_CNI' => ['string', 'unique:personnel_administratifs,numero_CNI'],


        ];
        if (auth()->check() && auth()->user()->role_nom === 'admin') {
            $rules = array_merge($rules, [
                'type_visibilite' => 'required|in:globale,limite',
                'academies' => 'required|boolean',
                'ressources' => 'required|boolean',
                'rapports' => 'required|boolean',
            ]);
        }
        return $rules;
    }
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',

            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères.',

            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit commencer par +221 et être suivi de 7 chiffres.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'email.required' => 'L\'adresse email est obligatoire.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.regex' => 'L\'adresse email doit être au format correct.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',

            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

            'genre.required' => 'Le genre est obligatoire.',
            'genre.in' => 'Le genre doit être "Femme" ou "Homme".',

            'role_nom.required' => 'Le rôle est obligatoire.',
            'role_nom.string' => 'Le rôle doit être une chaîne de caractères.',

            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',

            'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
            'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',

            'poste_occupé.required' => 'Le poste occupé est obligatoire.',
            'poste_occupé.string' => 'Le poste occupé doit être une chaîne de caractères.',

            'image.string' => 'L\'image doit être une chaîne de caractères.',

            'numero_identification_employe.required' => 'Le numéro d\'identification de l\'enseignant est requis.',
            'numero_identification_employe.string' => 'Le numéro d\'identification doit être une chaîne de caractères.',
            'numero_identification_employe.max' => 'Le numéro d\'identification ne doit pas dépasser 255 caractères.',
            'numero_identification_employe.unique' => 'Ce numéro d\'identification est déjà utilisé.',

            'date_debut_service.required' => 'La date de début de service est obligatoire.',
            'date_debut_service.date' => 'La date de début de service doit être une date valide.',

            'statut_employé.required' => 'Le statut de l\'employé est obligatoire.',
            'statut_employé.in' => 'Le statut doit être "Permanent", "Temporaire" ou "Vacataire".',

            'type_contrat.required' => 'Le type de contrat est obligatoire.',
            'type_contrat.in' => 'Le type de contrat doit être "CDI", "CDD", "Contrat" ou "Vacataire".',

            'salaire_base.required' => 'Le salaire de base est obligatoire.',
            'salaire_base.string' => 'Le salaire de base doit être une chaîne de caractères.',

            'horaires_travail.string' => 'Les horaires de travail doivent être une chaîne de caractères.',

            'type_salaire.required' => 'Le type de salaire est obligatoire.',
            'type_salaire.in' => 'Le type de salaire doit être "Mensuel" ou "Horaire".',

            'departement_service.required' => 'Le département/service est obligatoire.',
            'departement_service.in' => 'Le département/service doit être "Administratif", "Comptabilité" ou "Maintenance".',

            'prime_indemnités.string' => 'Les primes et indemnités doivent être une chaîne de caractères.',

            'cotisation_sociales.string' => 'Les cotisations sociales doivent être une chaîne de caractères.',

            'part_employeur.string' => 'La part employeur doit être une chaîne de caractères.',

            'retenue_salaire.string' => 'La retenue sur salaire doit être une chaîne de caractères.',

            'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
            'mode_paiement.in' => 'Le mode de paiement doit être "Virement", "Bancaire", "Espèce" ou "Chèque".',

            'banque_domiciliation.string' => 'La banque de domiciliation doit être une chaîne de caractères.',

            'numero_compte_bancaire.string' => 'Le numéro de compte bancaire doit être une chaîne de caractères.',
            'numero_compte_bancaire.unique' => 'Ce numéro de compte bancaire est déjà utilisé.',

            'cv_diplomes.string' => 'Les diplômes/CV doivent être une chaîne de caractères.',
            'certification_formation.string' => 'Les certification/formations doivent être une chaîne de caractères.',
            'superviseur.string' => 'Le superviseur doivent être une chaîne de caractères.',

            'contrat_travail.string' => 'Le contrat de travail doit être une chaîne de caractères.',

            'ancienneté.string' => 'L\'ancienneté doit être une chaîne de caractères.',

            'evaluation_performance.numeric' => 'L\'évaluation de la performance doit être un nombre.',

            'commentaires_notes.string' => 'Les commentaires/notes doivent être une chaîne de caractères.',

            'numero_CNI.string' => 'Le numéro CNI doit être une chaîne de caractères.',
            'numero_CNI.unique' => 'Ce numéro de CNI est déjà utilisé.',

            'type_visibilite.required' => 'Le type de visibilité est requis.',
            'type_visibilite.in' => 'Le type de visibilité doit être soit "globale", soit "limite".',

            'academies.required' => 'Le champ "académies" est requis.',
            'academies.boolean' => 'Le champ "académies" doit être vrai ou faux.',

            'ressources.required' => 'Le champ "ressources" est requis.',
            'ressources.boolean' => 'Le champ "ressources" doit être vrai ou faux.',

            'rapports.required' => 'Le champ "rapports" est requis.',
            'rapports.boolean' => 'Le champ "rapports" doit être vrai ou faux.',
        ];
    }
/**
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
