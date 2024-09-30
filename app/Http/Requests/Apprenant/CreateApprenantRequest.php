<?php

namespace App\Http\Requests\Apprenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateApprenantRequest extends FormRequest
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
            'image' => ['nullable' ,'string'], // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:homme,femme',
            'date_naissance'=>'required|date',
            'lieu_naissance' => 'required|string|max:255',
           'numero_CNI' => 'nullable|string|max:50|unique:apprenants,numero_CNI',
           'numero_carte_scolaire' => 'nullable|string|max:50|unique:apprenants,numero_carte_scolaire',
           'niveau_education' => 'required|string|max:255',
           'statut_marital' => ['nullable', 'string', Rule::in(['celibataire', 'marié'])],
            'classe_id' => 'required|integer',
            'tuteur_id' => 'required|integer',
        ];
    }
    public function messages()
    {
        return [
            'nom.required' => 'Le nom est requis.',
        'nom.string' => 'Le nom doit être une chaîne de caractères.',
        'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

        'prenom.required' => 'Le prénom est requis.',
        'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
        'prenom.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
         'image.required' => 'L\'image est obligatoire.',
        'email.required' => 'L\'adresse email est requise.',
        'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
        'email.email' => 'L\'adresse email doit être un format valide.',
        'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
        'email.regex' => 'L\'adresse email n\'est pas dans un format valide.',
        'email.unique' => 'Cette adresse email est déjà utilisée.',

        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

        'telephone.required' => 'Le numéro de téléphone est requis.',
        'telephone.regex' => 'Le numéro de téléphone doit être un format valide.',
        'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

        'adresse.required' => 'L\'adresse est requise.',
        'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

        'etat.sometimes' => 'L\'état est optionnel.',
        'etat.string' => 'L\'état doit être une chaîne de caractères.',
        'etat.in' => 'L\'état doit être soit "actif" soit "inactif".',

        'genre.required' => 'Le genre est requis.',
        'genre.string' => 'Le genre doit être une chaîne de caractères.',
        'genre.in' => 'Le genre doit être soit "homme" soit "femme".',

        'date_naissance.required' => 'La date de naissance est requise.',
        'date_naissance.date' => 'La date de naissance doit être une date valide.',

        'lieu_naissance.required' => 'Le lieu de naissance est requis.',
        'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',
        'lieu_naissance.max' => 'Le lieu de naissance ne peut pas dépasser 255 caractères.',

        'numero_CNI.max' => 'Le numéro CNI ne peut pas dépasser 50 caractères.',
        'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',

        'numero_carte_scolaire.max' => 'Le numéro de carte scolaire ne peut pas dépasser 50 caractères.',
        'numero_carte_scolaire.unique' => 'Ce numéro de carte scolaire est déjà utilisé.',

        'niveau_education.required' => 'niveau_education est requise.',
        'niveau_education.string' => 'niveau_education doit être une chaîne de caractères.',

        'statut_marital.string' => 'Le statut marital doit être une chaîne de caractères.',
        'statut_marital.in' => 'Le statut marital doit être soit "célibataire" soit "marié".',

        'classe_id.required' => 'L\'ID de la classe est requis.',
        'classe_id.integer' => 'L\'ID de la classe doit être un entier.',

        'tuteur_id.required' => 'L\'ID du tuteur est requis.',
        'tuteur_id.integer' => 'L\'ID du tuteur doit être un entier.',
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
