<?php

namespace App\Http\Requests\Tuteur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateTuteurRequest extends FormRequest
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
            'email' => ['required','string','email','max:255','regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/','unique:users'],
            'password' => 'required|min:8',
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/','unique:users'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['sometimes', 'string', Rule::in(['actif', 'inactif'])],
            'genre'=>'required|string|in:homme,femme',
            'profession' => 'required|string',
            'statut_marital' => ['nullable', 'string', Rule::in(['celibataire', 'marié'])],
            'numero_CNI' => ['nullable', 'string', 'unique:tuteurs'],
        ];
    }
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

            'profession.required' => 'Le champ profession est obligatoire.',
            'profession.string' => 'Le champ profession doit être une chaîne de caractères.',
            'statut_marital.nullable' => 'Le statut marital est facultatif.',
            'statut_marital.string' => 'Le champ statut marital doit être une chaîne de caractères.',
            'statut_marital.in' => 'Le champ statut marital doit être l\'un des suivants : célibataire, marié.',

            'numero_CNI.nullable' => 'Le numéro de CNI est facultatif.',
            'numero_CNI.string' => 'Le champ numéro de CNI doit être une chaîne de caractères.',
            'numero_CNI.unique' => 'Ce numéro de CNI est déjà enregistré.',
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
