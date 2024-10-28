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
            'date_naissance' => ['required', 'date'],
            'lieu_naissance' => ['required', 'string'],
            'poste_occupé' => ['required', 'string'],
            'image' => ['nullable' ,'string'],
            'date_debut_service' => ['required', 'date'],
            'statut_employé' => ['required', 'in:Permanent,Temporaire,Vacataire'],
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
            'numero_compte_bancaire' => ['nullable', 'string'],
            'cv_diplomes' => ['nullable', 'string'],
            'contrat_travail' => ['nullable', 'string'],
            'ancienneté' => ['nullable', 'string'],
            'evaluation_performance' => ['nullable', 'numeric'],
            'commentaires_notes' => ['nullable', 'string'],
            'numero_CNI' => ['string', 'unique:personnel_administratifs,numero_CNI'],


        ];
    }
    public function messages(): array
{
    return [
        'nom.required' => 'Le champ nom est requis.',
        'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
        'nom.max' => 'Le champ nom ne peut pas dépasser 255 caractères.',

        'prenom.required' => 'Le champ prénom est requis.',
        'prenom.string' => 'Le champ prénom doit être une chaîne de caractères.',
        'prenom.max' => 'Le champ prénom ne peut pas dépasser 255 caractères.',

        'email.required' => 'Le champ email est requis.',
        'email.string' => 'Le champ email doit être une chaîne de caractères.',
        'email.email' => 'Le format de l\'email est invalide.',
        'email.max' => 'Le champ email ne peut pas dépasser 255 caractères.',
        'email.regex' => 'Le format de l\'email est incorrect.',
        'email.unique' => 'Cet email est déjà utilisé.',

        'password.required' => 'Le champ mot de passe est requis.',
        'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',

        'telephone.required' => 'Le champ téléphone est requis.',
        'telephone.regex' => 'Le format du téléphone est invalide.',
        'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

        'adresse.required' => 'Le champ adresse est requis.',
        'adresse.string' => 'Le champ adresse doit être une chaîne de caractères.',
        'adresse.max' => 'Le champ adresse ne peut pas dépasser 255 caractères.',

        'genre.required' => 'Le champ genre est requis.',
        'genre.in' => 'Le genre doit être soit Homme, soit Femme.',

        'etat.string' => 'Le champ état doit être une chaîne de caractères.',
        'etat.in' => 'L\'état doit être soit actif, soit inactif.',

        'poste.required' => 'Le champ poste est requis.',
        'poste.string' => 'Le champ poste doit être une chaîne de caractères.',

        'image.string' => 'Le champ image doit être une chaîne de caractères.',

        'date_embauche.required' => 'Le champ date d\'embauche est requis.',
        'date_embauche.date' => 'Le champ date d\'embauche doit être une date valide.',

        'statut_emploie.required' => 'Le champ statut d\'emploi est requis.',
        'statut_emploie.in' => 'Le statut d\'emploi doit être soit permanent, soit vacataire, soit contractuel, soit honoraire.',

        'type_salaire.required' => 'Le champ type de salaire est requis.',
        'type_salaire.in' => 'Le type de salaire doit être soit fixe, soit horaire.',
        'departement_service.required' => 'Le champ departement et service est requis.',
        'departement_service.in' => 'Le departement et service doit être soit fixe, soit horaire.',

        'date_naissance.required' => 'Le champ date de naissance est requis.',
        'date_naissance.date' => 'Le champ date de naissance doit être une date valide.',

        'lieu_naissance.required' => 'Le champ lieu de naissance est requis.',
        'lieu_naissance.string' => 'Le champ lieu de naissance doit être une chaîne de caractères.',

        'statut_marital.required' => 'Le champ statut marital est requis.',
        'statut_marital.in' => 'Le statut marital doit être soit marié, soit célibataire, soit divorcé, soit veuve, soit veuf.',

        'numero_CNI.string' => 'Le champ numéro CNI doit être une chaîne de caractères.',
        'numero_CNI.unique' => 'Ce numéro de CNI est déjà utilisé.',

        'numero_securite_social.string' => 'Le champ numéro de sécurité sociale doit être une chaîne de caractères.',
        'numero_securite_social.unique' => 'Ce numéro de sécurité sociale est déjà utilisé.',

        'date_fin_contrat.required' => 'Le champ date de fin de contrat est requis.',
        'date_fin_contrat.date' => 'Le champ date de fin de contrat doit être une date valide.',
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
