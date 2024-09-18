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
            'email' => ['required', 'string', 'email', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/', 'unique:users'],
            'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/', 'unique:users'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:homme,femme',
            'specialite' => 'required|string|max:255',
            'statut_marital' => 'required|in:celibataire,marié',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'numero_CNI' => 'required|string|unique:enseignants,numero_CNI',
            'numero_securite_social' => 'required|string|unique:enseignants,numero_securite_social',
            'statut' => 'required|in:permanent,vacataire,contractuel,honorariat',
            'date_embauche' => 'required|date',
            'date_fin_contrat' => 'required|date',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages()
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
            'email.regex' => 'Le champ email ne correspond pas au format attendu.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'password.required' => 'Le champ mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'telephone.required' => 'Le champ téléphone est obligatoire.',
            'telephone.regex' => 'Le numéro de téléphone doit commencer par +221 suivi d\'un code opérateur valide.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            // 'image.required' => 'Le champ image est obligatoire.', // Ajoutez ce message si vous décidez de rendre ce champ obligatoire

            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.string' => 'Le champ adresse doit être une chaîne de caractères.',

            'etat.sometimes' => 'Le champ état est parfois requis.',
            'etat.string' => 'Le champ état doit être une chaîne de caractères.',
            'etat.in' => 'Le champ état doit être l\'un des suivants : actif, inactif.',

            'genre.required' => 'Le champ genre est obligatoire.',
            'genre.string' => 'Le champ genre doit être une chaîne de caractères.',
            'genre.in' => 'Le champ genre doit être l\'un des suivants : homme, femme.',
            'specialite.required' => 'La spécialité est requise.',
            'specialite.string' => 'La spécialité doit être une chaîne de caractères.',
            'specialite.max' => 'La spécialité ne peut pas dépasser 255 caractères.',
            'statut_marital.required' => 'Le statut marital est requis.',
            'statut_marital.in' => 'Le statut marital doit être l\'un des suivants : celibataire, marié.',
            'date_naissance.required' => 'La date de naissance est requise.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',
            'lieu_naissance.required' => 'Le lieu de naissance est requis.', // Ajouté
            'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.', // Ajouté
            'lieu_naissance.max' => 'Le lieu de naissance ne peut pas dépasser 255 caractères.', // Ajouté
            'numero_CNI.required' => 'Le numéro de CNI est requis.',
            'numero_CNI.string' => 'Le numéro de CNI doit être une chaine de caractaire.',
            'numero_CNI.unique' => 'Le numéro de CNI est déjà utilisé.',
            'numero_securite_social.required' => 'Le numéro de sécurité sociale est requis.',
            'numero_securite_social.string' => 'Le numéro de sécurité sociale doit être une chaine de carectaire.',
            'numero_securite_social.unique' => 'Le numéro de sécurité sociale est déjà utilisé.',
            'statut.required' => 'Le statut est requis.',
            'statut.in' => 'Le statut doit être l\'un des suivants : permanent, vacataire, contractuel, honorariat.',
            'date_embauche.required' => 'La date d\'embauche est requise.',
            'date_embauche.date' => 'La date d\'embauche doit être une date valide.',
            'date_fin_contrat.required' => 'La date_fin_contrat est requise.',
            'date_fin_contrat.date' => 'La date_fin_contrat doit être une date valide.',
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
