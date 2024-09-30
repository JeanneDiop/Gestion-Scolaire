<?php

namespace App\Http\Requests\Enseignant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateEnseignantRequest extends FormRequest
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
            'email' => ['required', 'string','nullable','email', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/'],
            //'password' => 'required|min:8',
            'telephone' => ['required','nullable','regex:/^\+221(77|78|76|70|75|33)\d{7}$/'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre' => 'required|string|in:homme,femme',
            'specialite' => 'required|string|max:255',
            'statut_marital' => 'required|in:celibataire,marié',
            'date_naissance' => 'required|date',
            'image' => ['nullable' ,'string'],
            'lieu_naissance' => 'required|string|max:255',
            'niveau_ecole' => 'required|string',
            'numero_CNI' => 'nullable|string|max:50',
            'numero_securite_social' => 'required|string',
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
           'nom.required' => 'Le nom est requis.',
        'nom.string' => 'Le nom doit être une chaîne de caractères.',
        'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

        'prenom.required' => 'Le prénom est requis.',
        'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
        'prenom.max' => 'Le prénom ne peut pas dépasser 255 caractères.',

        'email.required' => 'L\'adresse email est requise.',
        'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
        'email.email' => 'L\'adresse email doit être un format valide.',
        'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
        'email.regex' => 'L\'adresse email n\'est pas dans un format valide.',
        'email.unique' => 'Cette adresse email est déjà utilisée.',

        //'password.required' => 'Le mot de passe est requis.',
        //'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

        'telephone.required' => 'Le numéro de téléphone est requis.',
        'telephone.regex' => 'Le numéro de téléphone doit être au format valide (+22177XXXXXXX).',
        'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

        'adresse.required' => 'L\'adresse est requise.',
        'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

        'etat.sometimes' => 'L\'état est optionnel.',
        'etat.string' => 'L\'état doit être une chaîne de caractères.',
        'etat.in' => 'L\'état doit être soit "actif" soit "inactif".',
        'image.required' => 'L\'image est obligatoire.',
        'genre.required' => 'Le genre est requis.',
        'genre.string' => 'Le genre doit être une chaîne de caractères.',
        'genre.in' => 'Le genre doit être soit "homme" soit "femme".',

        'specialite.required' => 'La spécialité est requise.',
        'specialite.string' => 'La spécialité doit être une chaîne de caractères.',
        'specialite.max' => 'La spécialité ne peut pas dépasser 255 caractères.',

        'statut_marital.required' => 'Le statut marital est requis.',
        'statut_marital.in' => 'Le statut marital doit être soit "célibataire" soit "marié".',

        'date_naissance.required' => 'La date de naissance est requise.',
        'date_naissance.date' => 'La date de naissance doit être une date valide.',

        'lieu_naissance.required' => 'Le lieu de naissance est requis.',
        'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',
        'lieu_naissance.max' => 'Le lieu de naissance ne peut pas dépasser 255 caractères.',

        'niveau_ecole.required' => 'niveau_ecole est requise.',
        'niveau_ecole.string' => 'niveau_ecole doit être une chaîne de caractères.',

        'numero_CNI.max' => 'Le numéro CNI ne peut pas dépasser 50 caractères.',
        'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',

        'numero_securite_social.required' => 'Le numéro de sécurité sociale est requis.',
        'numero_securite_social.string' => 'Le numéro de sécurité sociale doit être une chaîne de caractères.',
        'numero_securite_social.unique' => 'Ce numéro de sécurité sociale est déjà utilisé.',

        'statut.required' => 'Le statut est requis.',
        'statut.in' => 'Le statut doit être soit "permanent", "vacataire", "contractuel" ou "honorariat".',

        'date_embauche.required' => 'La date d\'embauche est requise.',
        'date_embauche.date' => 'La date d\'embauche doit être une date valide.',

        'date_fin_contrat.required' => 'La date de fin de contrat est requise.',
        'date_fin_contrat.date' => 'La date de fin de contrat doit être une date valide.',
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
