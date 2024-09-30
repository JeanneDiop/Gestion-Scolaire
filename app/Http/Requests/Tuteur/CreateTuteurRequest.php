<?php

namespace App\Http\Requests\Tuteur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
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
            'email' => ['required', 'string', 'email', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/', 'unique:users,email'],
            'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/', 'unique:users,telephone'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre'=>'required|string|in:homme,femme',
            'profession' => 'required|string',
            'statut_marital' => ['nullable', 'string', Rule::in(['celibataire', 'marié'])],
            'numero_CNI' => ['nullable', 'string', 'unique:tuteurs',],
            'image' => ['nullable' ,'string'],
        ];
    }
    public function messages()
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'image.required' => 'L\'image est obligatoire.',

            'prenom.required' => 'Le prénom est requis.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne peut pas dépasser 255 caractères.',

            'email.required' => 'L\'adresse email est requise.',
            'email.string' => 'L\'adresse email doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse email doit être au format valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.regex' => 'L\'adresse email n\'est pas dans un format valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.regex' => 'Le numéro de téléphone doit être au format valide (+22177XXXXXXX).',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'adresse.required' => 'L\'adresse est requise.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',

            'etat.sometimes' => 'L\'état est optionnel.',
            'etat.string' => 'L\'état doit être une chaîne de caractères.',
            'etat.in' => 'L\'état doit être soit "actif" soit "inactif".',

            'genre.required' => 'Le genre est requis.',
            'genre.string' => 'Le genre doit être une chaîne de caractères.',
            'genre.in' => 'Le genre doit être soit "homme" soit "femme".',

            'profession.required' => 'La profession est requise.',
            'profession.string' => 'La profession doit être une chaîne de caractères.',

            'statut_marital.nullable' => 'Le statut marital est optionnel.',
            'statut_marital.string' => 'Le statut marital doit être une chaîne de caractères.',
            'statut_marital.in' => 'Le statut marital doit être soit "célibataire" soit "marié".',

            'numero_CNI.nullable' => 'Le numéro CNI est optionnel.',
            'numero_CNI.string' => 'Le numéro CNI doit être une chaîne de caractères.',
            'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',
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
