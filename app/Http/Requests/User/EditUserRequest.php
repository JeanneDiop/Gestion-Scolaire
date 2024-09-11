<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditUserRequest extends FormRequest
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
            // 'password_confirmation' => ['required'],
            'telephone' => ['required', 'regex:/^\+221(77|78|76|70|75|33)\d{7}$/'],
            // 'image' => 'required|string',  // Vous devrez ajuster cette règle en fonction de vos besoins
            'adresse' => 'required|string',
            'etat' => ['required', 'string', Rule::in(['actif', 'inactif'])],
            'role_id' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'nom.required' => 'Le champ nom est requis.',
            'prenom.required' => 'Le champ prénom est requis.',
            'email.required' => 'Le champ email est requis.',
            'email.email' => 'Le champ email doit être une adresse email valide.',
            'password.min' => 'Le champ mot de passe doit avoir au moins :min 8 caractères.',
            "password.confirmed" => 'Les mots de passe ne sont pas conforment',
            // "password_confirmation.required" => ' le champ confirmation mot de passe est requis',
            'telephone.regex' => 'Le champ numéro de téléphone doit être au format spécifié.',
            'adresse.required' => 'Le champ adresse est requis.',
            'etat.in' => 'Le champ état doit être actif ou inactif.',
            'role_id.required' => 'Le champ role_id est requis.'
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