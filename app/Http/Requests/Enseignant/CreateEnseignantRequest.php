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
