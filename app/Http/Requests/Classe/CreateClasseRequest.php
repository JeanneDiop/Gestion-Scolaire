<?php

namespace App\Http\Requests\Classe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateClasseRequest extends FormRequest
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
            'niveau_classe' => 'required|string|max:255',
            'niveau_education' => 'required|string|max:255',
            'programme_classe_id' => 'sometimes|integer|exists:programme_classes,id',
            'salle_id' => 'required|integer',
        ];
    }
    public function messages(): array
{
    return [
        'nom.required' => 'Le nom de la classe est obligatoire.',
        'nom.string' => 'Le nom de la classe doit être une chaîne de caractères.',
        'nom.max' => 'Le nom de la classe ne doit pas dépasser 255 caractères.',
        'niveau_classe.required' => 'Le niveau de la classe est obligatoire.',
        'niveau_classe.string' => 'Le niveau de la classe doit être une chaîne de caractères.',
        'niveau_classe.max' => 'Le niveau de la classe ne doit pas dépasser 255 caractères.',
        'niveau_education.required' => 'Le niveau d\education est obligatoire.',
        'niveau_education.string' => 'Le niveau d\'education doit être une chaîne de caractères.',
        'niveau_education.max' => 'Le niveau d\'education ne doit pas dépasser 255 caractères.',
        'programme_classe_id.integer' => 'L\'ID du programme doit être un nombre entier.',
        'programme_classe_id.exists' => 'Le programme sélectionné n\'existe pas.',
        'salle_id.required' => 'L\'ID de la salle est obligatoire.',
        'salle_id.integer' => 'L\'ID de la salle doit être un nombre entier.',
    ];
}
protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->toArray();
    throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
}

}
