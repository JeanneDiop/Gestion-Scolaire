<?php

namespace App\Http\Requests\Tuteur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateTuteurRequest extends FormRequest
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
            'email' => ['required','string','email','max:255','regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/'],
            'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            //'etat' => ['required', 'string', Rule::in(['actif', 'inactif'])],
            'genre'=>'required|string|in:homme,femme',
            'profession' => 'required|string',
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
        
        'email.required' => 'L\'adresse e-mail est requise.',
        'email.string' => 'L\'adresse e-mail doit être une chaîne de caractères.',
        'email.email' => 'L\'adresse e-mail doit être une adresse e-mail valide.',
        'email.max' => 'L\'adresse e-mail ne peut pas dépasser 255 caractères.',
        'email.regex' => 'L\'adresse e-mail doit être dans un format valide.',
        
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        
        'telephone.required' => 'Le numéro de téléphone est requis.',
        'telephone.regex' => 'Le numéro de téléphone doit être au format +221 suivi de 77, 78, 76, 70, 75 ou 33, suivi de 7 chiffres.',
        
        // 'image.required' => 'L\'image est requise.', // Décommenter et ajuster si nécessaire
        
        'adresse.required' => 'L\'adresse est requise.',
        'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
        
        // 'etat.required' => 'L\'état est requis.', // Décommenter et ajuster si nécessaire
        // 'etat.string' => 'L\'état doit être une chaîne de caractères.', // Décommenter et ajuster si nécessaire
        // 'etat.in' => 'L\'état doit être "actif" ou "inactif".', // Décommenter et ajuster si nécessaire
        
        'genre.required' => 'Le genre est requis.',
        'genre.string' => 'Le genre doit être une chaîne de caractères.',
        'genre.in' => 'Le genre doit être soit "homme" soit "femme".',
        
        'profession.required' => 'La profession est requise.',
        'profession.string' => 'La profession doit être une chaîne de caractères.',
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
