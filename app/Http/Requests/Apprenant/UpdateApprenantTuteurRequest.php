<?php

namespace App\Http\Requests\Apprenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class UpdateApprenantTuteurRequest extends FormRequest
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
            // Règles communes pour Apprenant
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'regex:/^[A-Za-z][A-Za-z0-9._%+-]*@[A-Za-z][A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',

            ],
            'password' => 'nullable|min:8',
            'telephone' => [
                'nullable',
                'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',

            ],
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:Homme,Femme',
            //'role_nom' => 'required|string',

            // Règles spécifiques à l'apprenant
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'numero_CNI' => ['nullable', 'string', 'max:50'],
            'numero_identification_eleve' => 'required|string|max:50',
            'niveau_education' => 'required|string|max:255',
            'nationalité' => 'required|string|max:255',
            'regime_paiement' => 'nullable|string|in:trimestriel,semestriel,annuel',
            'reduction_bourse' => 'nullable|string|max:50',
            'statut_paiement_actuel' => ['nullable', 'string', Rule::in(['à jour', 'retard'])],
            'references_factures' => 'nullable|string|max:255',
            'conditions_medicales' => 'nullable|string|max:255',
            'contact_urgence' => 'nullable|string|max:255',
            'note_resultat_anterieur' => 'nullable|string|max:255',
            'evaluations_specifiques' => 'nullable|string|max:255',
            'langue_parlee_maison' => 'nullable|string|in:Français,Anglais,Wolof,Sérère,Diola',
            'activités_extrascolaires' => 'nullable|string|max:255',
            'remarque_eleve' => 'nullable|string|max:255',
            'acte_naissance' => 'nullable|string|max:255',
            'autorisation_parentale' => 'nullable|string|max:255',
            'année_inscription' => ['nullable', 'date'],
            'niveau_entrée' => ['nullable', 'string', 'max:255'],
            'statut_inscription' => ['nullable', 'in:Inscrit,En attente,Autre'],
            'transport_scolaire' => ['nullable', 'in:Oui,Non'],
            // Validation conditionnelle pour le service de transport
            'service_transport' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($this->input('transport_scolaire') === 'Oui' && empty($value)) {
                        $fail('Le champ service de transport est requis si le transport scolaire est Oui.');
                    }
                }
            ],
            'programme_special' => ['nullable', 'string'],
            'tuteur_id' => 'nullable|exists:tuteurs,id',
            'classe_id' => 'nullable|exists:classes,id',

            // Règles spécifiques au tuteur
            'tuteur.nom' => 'required|string|max:255',
            'tuteur.prenom' => 'required|string|max:255',
            'tuteur.email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'regex:/^[A-Za-z][A-Za-z0-9._%+-]*@[A-Za-z][A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',

            ],
            'tuteur.password' => 'nullable|min:8',
            'tuteur.telephone' => [
                'nullable',
                'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',

            ],
            'tuteur.adresse' => 'required|string',
            'tuteur.genre' => 'required|string|in:Homme,Femme',
            //'tuteur.role_nom' => 'required|string',
            'tuteur.profession' => 'required|string',
            'tuteur.nationalité' => 'required|string|max:255',
            'tuteur.nombre_enfants_inscrits'  => 'nullable|string',
            'tuteur.numero_CNI' => ['nullable', 'string'],
            'tuteur.image'=>  ['nullable', 'string'],
            'tuteur.lien_parenté'  => ['required', 'string', Rule::in(['père', 'mère', 'tuteur', 'autre'])],
        ];
    }

    public function messages(): array
    {
        return [
            // Messages pour l'apprenant
            'nom.required' => 'Le champ nom est obligatoire.',
            'prenom.required' => 'Le champ prénom est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.regex' => 'Le format de l\'adresse email est invalide.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'telephone.regex' => 'Le numéro de téléphone doit être au format +221 suivi de 9 chiffres.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'genre.required' => 'Le champ genre est obligatoire.',
            'genre.in' => 'Le genre doit être soit Homme soit Femme.',
            //'role_nom.required' => 'Le champ role_nom est obligatoire.',
            'date_naissance.required' => 'Le champ date de naissance est obligatoire.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'lieu_naissance.required' => 'Le champ lieu de naissance est obligatoire.',
            'niveau_education.required' => 'Le champ niveau d\'éducation est obligatoire.',
            'nationalité.required' => 'Le champ nationalité est obligatoire.',
            'regime_paiement.in' => 'Le régime de paiement doit être trimestriel, semestriel ou annuel.',
            'statut_paiement_actuel.in' => 'Le statut de paiement doit être soit "à jour" soit "retard".',
            'contact_urgence.regex' => 'Le numéro de téléphone d\'urgence doit être au format +221 suivi de 9 chiffres.',
            'langue_parlee_maison.in' => 'La langue parlée à la maison doit être Français, Anglais, Wolof, Sérère ou Diola.',
            'acte_naissance.mimes' => 'L\'acte de naissance doit être un fichier de type jpg, jpeg, png ou pdf.',
            'acte_naissance.max' => 'La taille de l\'acte de naissance ne doit pas dépasser 2 Mo.',
            'classe_id.exists' => 'La classe sélectionnée n\'existe pas.',
            'tuteur_id.exists' => 'Le tuteur sélectionné n\'existe pas.',
            'numero_CNI.unique' => 'Ce numéro de CNI est déjà utilisé.',
            'numero_identification_eleve.required' => 'Le numéro d\'identification de l\'élève est obligatoire.',
            'numero_identification_eleve.unique' => 'Ce numéro d\'identification est déjà utilisé.',
            'image.max' => 'La taille de l\'image ne doit pas dépasser 255 caractères.',
            'année_inscription.date' => 'La date d\'année d\'inscription doit être une date valide.',
            'niveau_entrée.string' => 'Le champ niveau d\'entrée doit être une chaîne de caractères.',
            'niveau_entrée.max' => 'Le champ niveau d\'entrée ne peut pas dépasser 255 caractères.',
            'statut_inscription.in' => 'Le statut d\'inscription doit être Inscrit, En attente, ou Autre.',
            'transport_scolaire.required' => 'Le champ transport scolaire est requis.',
            'transport_scolaire.in' => 'Le champ transport scolaire doit être Oui ou Non.',
            'service_transport.string' => 'Le champ service de transport doit être une chaîne de caractères.',
            // Pas besoin de message spécifique pour service_transport car c'est géré par la logique conditionnelle
            'programme_special.string' => 'Le champ programme spécial doit être une chaîne de caractères.',

            // Messages pour le tuteur
            'tuteur.nom.required' => 'Le nom du tuteur est obligatoire.',
            'tuteur.prenom.required' => 'Le prénom du tuteur est obligatoire.',
            'tuteur.email.email' => 'L\'adresse email du tuteur doit être valide.',
            'tuteur.password.min' => 'Le mot de passe du tuteur doit contenir au moins 8 caractères.',
            'tuteur.telephone.regex' => 'Le numéro de téléphone du tuteur doit être au format +221 suivi de 9 chiffres.',
            'tuteur.adresse.required' => 'L\'adresse du tuteur est obligatoire.',
            'tuteur.genre.required' => 'Le champ genre du tuteur est obligatoire.',
            'tuteur.genre.in' => 'Le genre du tuteur doit être soit Homme soit Femme.',
            //'tuteur.role_nom.required' => 'Le role_nom du tuteur est obligatoire.',
            'tuteur.profession.required' => 'La profession du tuteur est obligatoire.',
            'tuteur.nationalité.required' => 'La nationalité du tuteur est obligatoire.',
            'tuteur.nombre_enfants_inscrits.required' => 'Le nombre d\'enfants inscrits par le tuteur est obligatoire.',
            'tuteur.numero_CNI.unique' => 'Le numéro de CNI du tuteur est déjà utilisé.',
            'tuteur.lien_parenté.required' => 'Le lien de parenté est obligatoire.',
            'tuteur.lien_parenté.in' => 'Le lien de parenté doit être père, mère, tuteur ou autre.',
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
