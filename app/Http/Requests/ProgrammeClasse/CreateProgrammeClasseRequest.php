<?php

namespace App\Http\Requests\ProgrammeClasse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateProgrammeClasseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'nom' => 'required|string|max:255', // Nom obligatoire et ne dépassant pas 255 caractères
            'description' => 'nullable|string|max:500', // Description facultative, ne dépassant pas 500 caractères
            'niveau_education' => 'required|string|max:100', // Niveau d'éducation obligatoire et ne dépassant pas 100 caractères
            'periode' => 'nullable|in:annuelle,semestre', // Période facultative, peut être 'annuelle' ou 'semestre'
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
            'nom.max' => 'Le champ nom ne peut pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 500 caractères.',
            'niveau_education.required' => 'Le champ niveau d\'éducation est obligatoire.',
            'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
            'niveau_education.max' => 'Le niveau d\'éducation ne peut pas dépasser 100 caractères.',
            'periode.in' => 'La période doit être soit annuelle, soit semestrielle.',
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
