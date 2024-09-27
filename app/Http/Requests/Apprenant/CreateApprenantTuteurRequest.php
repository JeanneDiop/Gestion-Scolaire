<?php

namespace App\Http\Requests\Apprenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class CreateApprenantTuteurRequest extends FormRequest
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
            // Règles communes pour Apprenant et Tuteur
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'nullable',
                'max:255',
                'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/',
                'unique:users,email',
            ],
            'password' => 'nullable|required|min:8',
            'telephone' => [
                'required',
                'nullable',
                'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',
                'unique:users,telephone',
            ],
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:homme,femme',


            // Règles spécifiques à l'apprenant
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'statut_marital' => ['nullable', 'string', Rule::in(['celibataire', 'marié'])],
            'numero_CNI' => ['nullable', 'string', 'max:50', 'unique:apprenants,numero_CNI'],
            'numero_carte_scolaire' => 'nullable|string|max:50|unique:apprenants,numero_carte_scolaire',
            'niveau_education' => 'required|string|max:255',
            'classe_id' => 'required|integer',
            'tuteur_id' => 'required|integer',

            // Règles spécifiques au tuteur
            'profession' => 'required|string',
            'statut_marital' => ['nullable', 'string', Rule::in(['celibataire', 'marié'])],
            'numero_CNI' => ['nullable', 'string', 'unique:tuteurs',],
        ];
    }

    public function messages(): array
{
    return [
        'nom.required' => 'Le champ nom est obligatoire.',
        'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
        'nom.max' => 'Le champ nom ne peut pas dépasser 255 caractères.',

        'prenom.required' => 'Le champ prénom est obligatoire.',
        'prenom.string' => 'Le champ prénom doit être une chaîne de caractères.',
        'prenom.max' => 'Le champ prénom ne peut pas dépasser 255 caractères.',

        'email.required' => 'Le champ email est obligatoire.',
        'email.string' => 'Le champ email doit être une chaîne de caractères.',
        'email.email' => 'Le champ email doit être une adresse email valide.',
        'email.max' => 'Le champ email ne peut pas dépasser 255 caractères.',
        'email.regex' => 'Le format de l\'adresse email est invalide.',
        'email.unique' => 'Cette adresse email est déjà utilisée.',

        'password.required' => 'Le champ mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',

        'telephone.required' => 'Le champ téléphone est obligatoire.',
        'telephone.regex' => 'Le numéro de téléphone doit commencer par +221 et être suivi de 9 chiffres.',
        'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

        'adresse.required' => 'Le champ adresse est obligatoire.',
        'adresse.string' => 'Le champ adresse doit être une chaîne de caractères.',

        'etat.string' => 'Le champ état doit être une chaîne de caractères.',
        'etat.in' => 'L\'état doit être soit "actif" soit "inactif".',

        'genre.required' => 'Le champ genre est obligatoire.',
        'genre.string' => 'Le champ genre doit être une chaîne de caractères.',
        'genre.in' => 'Le genre doit être soit "homme" soit "femme".',

        // Règles spécifiques à l'apprenant
        'date_naissance.required' => 'Le champ date de naissance est obligatoire.',
        'date_naissance.date' => 'Le champ date de naissance doit être une date valide.',

        'lieu_naissance.required' => 'Le champ lieu de naissance est obligatoire.',
        'lieu_naissance.string' => 'Le champ lieu de naissance doit être une chaîne de caractères.',
        'lieu_naissance.max' => 'Le champ lieu de naissance ne peut pas dépasser 255 caractères.',

        'statut_marital.string' => 'Le champ statut marital doit être une chaîne de caractères.',
        'statut_marital.in' => 'Le statut marital doit être soit "célibataire" soit "marié".',

        'numero_CNI.string' => 'Le numéro CNI doit être une chaîne de caractères.',
        'numero_CNI.max' => 'Le numéro CNI ne peut pas dépasser 50 caractères.',
        'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',

        'numero_carte_scolaire.string' => 'Le numéro de carte scolaire doit être une chaîne de caractères.',
        'numero_carte_scolaire.max' => 'Le numéro de carte scolaire ne peut pas dépasser 50 caractères.',
        'numero_carte_scolaire.unique' => 'Ce numéro de carte scolaire est déjà utilisé.',

        'niveau_education.required' => 'Le champ niveau d\'éducation est obligatoire.',
        'niveau_education.string' => 'Le champ niveau d\'éducation doit être une chaîne de caractères.',
        'niveau_education.max' => 'Le champ niveau d\'éducation ne peut pas dépasser 255 caractères.',

        'classe_id.required' => 'Le champ classe est obligatoire.',
        'classe_id.integer' => 'L\'identifiant de la classe doit être un nombre entier.',

        'tuteur_id.required' => 'Le champ tuteur est obligatoire.',
        'tuteur_id.integer' => 'L\'identifiant du tuteur doit être un nombre entier.',

        // Règles spécifiques au tuteur
        'profession.required' => 'Le champ profession est obligatoire.',
        'profession.string' => 'Le champ profession doit être une chaîne de caractères.',

        'statut_marital.string' => 'Le champ statut marital doit être une chaîne de caractères.',
        'statut_marital.in' => 'Le statut marital doit être soit "célibataire" soit "marié".',

        'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé pour un tuteur.',
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

