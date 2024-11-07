<?php

namespace App\Http\Requests\Directeur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateDirecteurRequest extends FormRequest
{
    public function authorize()
    {
        // Autorise toujours la requête pour l'instant
        return true;
    }

    public function rules()
    {
        $userId = $this->userId; // Assurez-vous que cela correspond à l'ID de l'utilisateur à mettre à jour
        $directeurId = $this->directeurId; // Assurez-vous que cela correspond à l'ID de directeur à mettre à jour
        // Règles de validation pour la création du directeur
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
           'telephone' => ['nullable','required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',],
            'email' => ['required', 'string', 'email','nullable', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/',],
            //'password' => 'required|min:8',
            'adresse' => ['required', 'string'],
            'genre' => ['required', 'in:Femme,Homme'],
            //'role_nom' => ['required', 'string'],
            'date_naissance' => ['required', 'date'],
            'lieu_naissance' => ['required', 'string'],
            'image' => ['nullable' ,'string'],
            'date_debut_service' => ['required', 'date'],
            'statut_employé' => ['required', 'in:Permanent,Temporaire,Vacataire'],
            'numero_identification_directeur' => 'required|string|max:255',
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
            'certification_formations' => ['nullable', 'string'],
            'contrat_travail' => ['nullable', 'string'],
            'ancienneté' => ['nullable', 'string'],
            'evaluation_performance' => ['nullable', 'numeric'],
            'commentaires_notes' => ['nullable', 'string'],
            'numero_CNI' => ['string'],


        ];
    }

    public function messages()
    {
        // Messages d'erreur personnalisés pour chaque règle
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',

            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères.',

            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit commencer par +221 et être suivi de 7 chiffres.',


            'email.required' => 'L\'adresse email est obligatoire.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.regex' => 'L\'adresse email doit être au format correct.',

            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',

            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

            'genre.required' => 'Le genre est obligatoire.',
            'genre.in' => 'Le genre doit être "Femme" ou "Homme".',

            //'role_nom.required' => 'Le rôle est obligatoire.',
            //'role_nom.string' => 'Le rôle doit être une chaîne de caractères.',

            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',

            'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
            'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',

            'poste_occupé.required' => 'Le poste occupé est obligatoire.',
            'poste_occupé.string' => 'Le poste occupé doit être une chaîne de caractères.',

            'image.string' => 'L\'image doit être une chaîne de caractères.',


            'numero_identification_directeur.required' => 'Le numéro d\'identification de l\'enseignant est requis.',
            'numero_identification_directeur.string' => 'Le numéro d\'identification doit être une chaîne de caractères.',
            'numero_identification_directeur.max' => 'Le numéro d\'identification ne doit pas dépasser 255 caractères.',


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


            'cv_diplomes.string' => 'Les diplômes/CV doivent être une chaîne de caractères.',
            'certification_formation.string' => 'Les certification/formations doivent être une chaîne de caractères.',
            'superviseur.string' => 'Le superviseur doivent être une chaîne de caractères.',

            'contrat_travail.string' => 'Le contrat de travail doit être une chaîne de caractères.',

            'ancienneté.string' => 'L\'ancienneté doit être une chaîne de caractères.',

            'evaluation_performance.numeric' => 'L\'évaluation de la performance doit être un nombre.',

            'commentaires_notes.string' => 'Les commentaires/notes doivent être une chaîne de caractères.',

            'numero_CNI.string' => 'Le numéro CNI doit être une chaîne de caractères.',
          
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
