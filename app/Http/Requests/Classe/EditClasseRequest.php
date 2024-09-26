<?php

namespace App\Http\Requests\Classe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class EditClasseRequest extends FormRequest
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
            'nom' => 'required|string|max:255', // Nom de la classe
            'niveau_classe' => 'required|string|max:255',
            'enseignant_id' => 'required|integer',
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
        'enseignant_id.required' => 'L\'ID de l\'enseignant est obligatoire.',
        'enseignant_id.integer' => 'L\'ID de l\'enseignant doit être un nombre entier.',
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
