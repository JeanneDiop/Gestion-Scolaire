<?php

namespace App\Http\Requests\Employe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class EditEmployeRequest extends FormRequest
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
            'telephone' => [
                'required',
                'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',

            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',

            ],
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
            'numero_CNI' => ['required','string'],


        ];
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
        'telephone.regex' => 'Le numéro de téléphone doit être au format +221 suivi de 7 chiffres.',

        'email.string' => 'L\'email doit être une chaîne de caractères.',
        'email.email' => 'L\'email doit être une adresse email valide.',
        'email.max' => 'L\'email ne doit pas dépasser 255 caractères.',
        'email.regex' => 'L\'email doit commencer par une lettre et respecter le format standard.',

        'adresse.required' => 'L\'adresse est obligatoire.',
        'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

        'genre.required' => 'Le genre est obligatoire.',
        'genre.in' => 'Le genre doit être soit Femme soit Homme.',

        'date_naissance.required' => 'La date de naissance est obligatoire.',
        'date_naissance.date' => 'La date de naissance doit être une date valide.',

        'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
        'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',

        'poste_occupé.required' => 'Le poste occupé est obligatoire.',
        'poste_occupé.string' => 'Le poste occupé doit être une chaîne de caractères.',

        'image.string' => 'L\'image doit être une chaîne de caractères.',

        'date_debut_service.required' => 'La date de début de service est obligatoire.',
        'date_debut_service.date' => 'La date de début de service doit être une date valide.',

        'statut_employé.required' => 'Le statut de l\'employé est obligatoire.',
        'statut_employé.in' => 'Le statut de l\'employé doit être Permanent, Temporaire ou Vacataire.',

        'type_contrat.required' => 'Le type de contrat est obligatoire.',
        'type_contrat.in' => 'Le type de contrat doit être CDI, CDD, Contrat ou Vacataire.',

        'salaire_base.required' => 'Le salaire de base est obligatoire.',
        'salaire_base.string' => 'Le salaire de base doit être une chaîne de caractères.',

        'type_salaire.required' => 'Le type de salaire est obligatoire.',
        'type_salaire.in' => 'Le type de salaire doit être Mensuel ou Horaire.',

        'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
        'mode_paiement.in' => 'Le mode de paiement doit être Virement, Bancaire, Espèce ou Chèque.',

        'numero_CNI.string' => 'Le numéro CNI doit être une chaîne de caractères.',
        'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',

        'evaluation_performance.numeric' => 'L\'évaluation de performance doit être un nombre.',

        'horaires_travail.string' => 'Les horaires de travail doivent être une chaîne de caractères.',
        'prime_indemnités.string' => 'Les primes et indemnités doivent être une chaîne de caractères.',
        'cotisation_sociales.string' => 'Les cotisations sociales doivent être une chaîne de caractères.',
        'part_employeur.string' => 'La part de l\'employeur doit être une chaîne de caractères.',
        'retenue_salaire.string' => 'La retenue sur le salaire doit être une chaîne de caractères.',
        'banque_domiciliation.string' => 'La banque de domiciliation doit être une chaîne de caractères.',
        'numero_compte_bancaire.string' => 'Le numéro de compte bancaire doit être une chaîne de caractères.',
        'cv_diplomes.string' => 'Le CV ou diplômes doivent être une chaîne de caractères.',
        'contrat_travail.string' => 'Le contrat de travail doit être une chaîne de caractères.',
        'ancienneté.string' => 'L\'ancienneté doit être une chaîne de caractères.',
        'commentaires_notes.string' => 'Les commentaires et notes doivent être une chaîne de caractères.',
    ];
}
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}

