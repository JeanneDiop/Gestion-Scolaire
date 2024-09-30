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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => ['required', 'string', 'email','nullable', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/', 'unique:users,email',],
            'password' => 'nullable|required|min:8',
            'telephone' => ['nullable','required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/', 'unique:users,telephone',],
            'adresse' => 'required|string|max:255',
            'genre' => 'required|in:homme,femme',
            'etat' => 'nullable|string|in:actif,inactif',
            'statut_marital' => 'required|in:celibataire,marié',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'image' => ['nullable' ,'string'],
            'numero_CNI' => 'nullable|string|max:50|unique:apprenants,numero_CNI',
            'qualification_academique' => 'required|string|max:255',
            'annee_experience' => ['required', 'regex:/^\d+\s*(ans|année|années)?$/'],
            'date_prise_fonction' => 'required|integer|min:1900|max:' . date('Y'),
            'date_embauche' => 'required|date',
            'date_fin_contrat' => 'nullable|date',
        ];
    }

    public function messages()
    {
        // Messages d'erreur personnalisés pour chaque règle
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
            'image.required' => 'L\'image est obligatoire.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.regex' => 'Le numéro de téléphone doit être au format valide (+22177XXXXXXX).',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'adresse.required' => 'L\'adresse est requise.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',

            'genre.required' => 'Le genre est requis.',
            'genre.in' => 'Le genre doit être soit "homme" soit "femme".',

            'etat.nullable' => 'L\'état est optionnel.',
            'etat.string' => 'L\'état doit être une chaîne de caractères.',
            'etat.in' => 'L\'état doit être soit "actif" soit "inactif".',

            'statut_marital.required' => 'Le statut marital est requis.',
            'statut_marital.in' => 'Le statut marital doit être soit "célibataire" soit "marié".',

            'date_naissance.required' => 'La date de naissance est requise.',
            'date_naissance.date' => 'La date de naissance doit être une date valide.',

            'lieu_naissance.required' => 'Le lieu de naissance est requis.',
            'lieu_naissance.string' => 'Le lieu de naissance doit être une chaîne de caractères.',
            'lieu_naissance.max' => 'Le lieu de naissance ne peut pas dépasser 255 caractères.',

            'numero_CNI.max' => 'Le numéro CNI ne peut pas dépasser 50 caractères.',
            'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',

            'qualification_academique.required' => 'La qualification académique est requise.',
            'qualification_academique.string' => 'La qualification académique doit être une chaîne de caractères.',
            'qualification_academique.max' => 'La qualification académique ne peut pas dépasser 255 caractères.',

            'annee_experience.required' => 'L\'année d\'expérience est requise.',
            'annee_experience.regex' => 'L\'année d\'expérience doit être un nombre suivi de "ans", "année" ou "années".',

            'date_prise_fonction.required' => 'La date de prise de fonction est requise.',
            'date_prise_fonction.integer' => 'La date de prise de fonction doit être un entier.',
            'date_prise_fonction.min' => 'La date de prise de fonction doit être supérieure ou égale à 1900.',
            'date_prise_fonction.max' => 'La date de prise de fonction doit être inférieure ou égale à l\'année en cours.',

            'date_embauche.required' => 'La date d\'embauche est requise.',
            'date_embauche.date' => 'La date d\'embauche doit être une date valide.',

            'date_fin_contrat.date' => 'La date de fin de contrat doit être une date valide.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
