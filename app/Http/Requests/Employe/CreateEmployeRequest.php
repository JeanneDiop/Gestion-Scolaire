<?php

namespace App\Http\Requests\Employe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateEmployeRequest extends FormRequest
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
                'unique:employes,telephone'
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
                'unique:employes,email'
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
            'numero_CNI' => ['string', 'unique:employes,numero_CNI'],


        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit être au format +221 suivi du bon indicatif.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.regex' => 'Le format de l\'email est incorrect.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'poste_occupé.required' => 'Le poste occupé est obligatoire.',
            'image.required' => 'L\'image est obligatoire.',
            'date_debut_service.required' => 'La date de début de service est obligatoire.',
            'statut_employé.required' => 'Le statut de l\'employé est obligatoire.',
            'statut_employé.in' => 'Le statut de l\'employé doit être Permanent, Temporaire ou Vacataire.',
            'type_contrat.required' => 'Le type de contrat est obligatoire.',
            'type_contrat.in' => 'Le type de contrat doit être CDI, CDD, Contrat ou Vacataire.',
            'salaire_base.required' => 'Le salaire de base est obligatoire.',
            'type_salaire.required' => 'Le type de salaire est obligatoire.',
            'type_salaire.in' => 'Le type de salaire doit être Mensuel ou Horaire.',
            'prime_indemnités.required' => 'La prime ou les indemnités sont obligatoires.',
            'cotisation_sociales.required' => 'La cotisation sociales est obligatoire.',
            'part_employeur.required' => 'La part de l\'employeur est obligatoire.',
            'retenue_salaire.required' => 'La retenue de salaire est obligatoire.',
            'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
            'mode_paiement.in' => 'Le mode de paiement doit être Virement, Bancaire, Espèce ou Chèque.',
            'banque_domiciliation.required' => 'La banque de domiciliation est obligatoire.',
            'numero_compte_bancaire.required' => 'Le numéro de compte bancaire est obligatoire.',
            'cv_diplomes.required' => 'Le CV et diplômes sont obligatoires.',
            'contrat_travail.required' => 'Le contrat de travail est obligatoire.',
            'ancienneté.required' => 'L\'ancienneté est obligatoire.',
            'evaluation_performance.numeric' => 'L\'évaluation de performance doit être un nombre.',
            'commentaires_notes.string' => 'Les commentaires doivent être une chaîne de caractères.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
            'genre.required' => 'Le genre est obligatoire.',
            'genre.in' => 'Le genre doit être soit Homme soit Femme.',
            'statut_marital.required' => 'Le statut marital est obligatoire.',
            'statut_marital.in' => 'Le statut marital doit être marié, célibataire, divorcé, veuf ou veuve.',
            'numero_CNI.required' => 'Le numéro CNI est obligatoire.',
            'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',
            'numero_securite_social.unique' => 'Ce numéro de sécurité sociale est déjà utilisé.',
            'date_fin_contrat.required' => 'La date de fin de contrat est obligatoire.',
        ];
    }
protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->toArray();
    throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
}
}
