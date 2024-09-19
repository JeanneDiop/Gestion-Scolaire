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
        // Règles de validation pour la création du directeur
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:255',
            'genre' => 'required|in:homme,femme',
            'etat' => 'nullable|string|in:actif,inactif',
            'statut_marital' => 'required|in:celibataire,marié',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'numero_CNI' => 'nullable|string|max:50|unique:directeurs,numero_CNI',
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
            'prenom.required' => 'Le prénom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est requis.',
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'adresse.required' => 'L\'adresse est requise.',
            'genre.required' => 'Le genre est requis.',
            'etat.in' => 'L\'état doit être soit actif, soit inactif.',
            'statut_marital.required' => 'Le statut marital est requis.',
            'date_naissance.required' => 'La date de naissance est requise.',
            'lieu_naissance.required' => 'Le lieu de naissance est requis.',
            'numero_CNI.unique' => 'Ce numéro de CNI est déjà enregistré.',
            'qualification_academique.required' => 'La qualification académique est requise.',
           'annee_experience.required' => 'Le champ nombre d\'années d\'expérience est requis.',
            'annee_experience.regex' => 'Le champ nombre d\'années d\'expérience doit être un nombre suivi de "ans", "année" ou "années".',
            'date_prise_fonction.required' => 'Le champ date de prise de fonction est requis.',
            'date_prise_fonction.integer' => 'Le champ date de prise de fonction doit être un nombre entier.',
            'date_prise_fonction.min' => 'Le champ date de prise de fonction doit être une année valide, supérieure ou égale à 1900.',
            'date_prise_fonction.max' => 'Le champ date de prise de fonction doit être une année valide, inférieure ou égale à ' . date('Y') . '.',
            'date_embauche.required' => 'Le champ date d\'embauche est obligatoire.',
            'date_embauche.date' => 'Le champ date d\'embauche doit être une date valide.',

            'date_fin_contrat.nullable' => 'Le champ date de fin de contrat est facultatif.',
            'date_fin_contrat.date' => 'Le champ date de fin de contrat doit être une date valide.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
