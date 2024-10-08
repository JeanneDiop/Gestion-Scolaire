<?php

namespace App\Http\Requests\Ecole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateEcoleRequest extends FormRequest
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
    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
           'telephone' => ['required','regex:/^\+221(77|78|76|70|75|33)\d{7}$/','unique:ecoles,telephone',],
           'email' => ['required', 'string', 'email', 'max:255', 'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+.[A-Za-z]{2,}$/', 'unique:ecoles,email',],
           'siteweb' => 'nullable|url|max:255|unique:ecoles,siteweb',
           'logo' => 'nullable|url|max:255|unique:ecoles,logo',
            'annee_creation' => 'nullable|integer|min:1900|max:' . date('Y'),
            'type_ecole' => 'required|string|max:255',
            'niveau_education' => 'required|string|max:255',
            'directeur_id' => 'required|exists:directeurs,id', ];
    }

    public function messages()
{
    return [
        'nom.required' => 'Le nom de l\'école est requis.',
        'nom.string' => 'Le nom doit être une chaîne de caractères.',
        'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

        'adresse.required' => 'L\'adresse est requise.',
        'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
        'adresse.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',

        'telephone.required' => 'Le numéro de téléphone est requis.',
        'telephone.regex' => 'Le numéro de téléphone doit être au format +22177xxxxxxx ou 33xxxxxxx.',
        'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

        'email.required' => 'L\'email est requis.',
        'email.email' => 'Veuillez fournir un email valide.',
        'email.max' => 'L\'email ne peut pas dépasser 255 caractères.',
        'email.regex' => 'L\'email doit être au format valide.',
        'email.unique' => 'Cet email est déjà utilisé.',

        'siteweb.url' => 'Le site web doit être une URL valide.',
        'siteweb.max' => 'Le site web ne peut pas dépasser 255 caractères.',
        'siteweb.unique' => 'Ce site web est déjà utilisé.',

        'logo.url' => 'Le logo doit être une URL valide.',
        'logo.max' => 'Le logo ne peut pas dépasser 255 caractères.',
        'logo.unique' => 'Ce logo est déjà utilisé.',

        'annee_creation.integer' => 'L\'année de création doit être un nombre entier.',
        'annee_creation.min' => 'L\'année de création ne peut pas être inférieure à 1900.',
        'annee_creation.max' => 'L\'année de création ne peut pas être supérieure à l\'année en cours.',

        'type_ecole.required' => 'Le type d\'école est requis.',
        'type_ecole.string' => 'Le type d\'école doit être une chaîne de caractères.',
        'type_ecole.max' => 'Le type d\'école ne peut pas dépasser 255 caractères.',

        'niveau_education.required' => 'Le niveau d\'éducation est requis.',
        'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
        'niveau_education.max' => 'Le niveau d\'éducation ne peut pas dépasser 255 caractères.',

        'directeur_id.required' => 'L\'ID du directeur est requis.',
        'directeur_id.exists' => 'Le directeur spécifié n\'existe pas.',
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
