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
            // Règles communes pour Apprenant
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/',
                'unique:users,email',
            ],
            'password' => 'nullable|min:8',
            'telephone' => [
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
            'statut_marital' => ['nullable', 'string', Rule::in(['célibataire', 'marié'])],
            'numero_CNI' => ['nullable', 'string', 'max:50', 'unique:apprenants,numero_CNI'],
            'numero_carte_scolaire' => 'nullable|string|max:50|unique:apprenants,numero_carte_scolaire',
            'niveau_education' => 'required|string|max:255',
            'classe_id' => 'required|integer',

            // Règles spécifiques au tuteur
            'tuteur.nom' => 'required|string|max:255',
            'tuteur.prenom' => 'required|string|max:255',
            'tuteur.email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'tuteur.password' => 'nullable|min:8',
            'tuteur.telephone' => [
                'nullable',
                'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',
                'unique:users,telephone',
            ],
            'tuteur.adresse' => 'required|string',
            'tuteur.genre' => 'required|string|in:homme,femme',
            'tuteur.profession' => 'required|string',
            'tuteur.statut_marital' => ['nullable', 'string', Rule::in(['célibataire', 'marié'])],
            'tuteur.numero_CNI' => ['nullable', 'string', 'unique:tuteurs,numero_CNI'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'prenom.required' => 'Le champ prénom est obligatoire.',
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'Le champ email doit être une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'telephone.regex' => 'Le numéro de téléphone doit commencer par +221 et être suivi de 9 chiffres.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'genre.required' => 'Le champ genre est obligatoire.',
            'date_naissance.required' => 'Le champ date de naissance est obligatoire.',
            'lieu_naissance.required' => 'Le champ lieu de naissance est obligatoire.',
            'niveau_education.required' => 'Le champ niveau d\'éducation est obligatoire.',
            'classe_id.required' => 'Le champ classe est obligatoire.',

            // Messages pour le tuteur
            'tuteur.nom.required' => 'Le champ nom du tuteur est obligatoire.',
            'tuteur.prenom.required' => 'Le champ prénom du tuteur est obligatoire.',
            'tuteur.email.email' => 'Le champ email du tuteur doit être une adresse email valide.',
            'tuteur.email.unique' => 'Cette adresse email du tuteur est déjà utilisée.',
            'tuteur.telephone.regex' => 'Le numéro de téléphone du tuteur doit commencer par +221 et être suivi de 9 chiffres.',
            'tuteur.adresse.required' => 'Le champ adresse du tuteur est obligatoire.',
            'tuteur.genre.required' => 'Le champ genre du tuteur est obligatoire.',
            'tuteur.profession.required' => 'Le champ profession du tuteur est obligatoire.',
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

