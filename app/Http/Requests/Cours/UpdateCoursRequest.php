<?php

namespace App\Http\Requests\Cours;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class UpdateCoursRequest extends FormRequest
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
            'description' => 'nullable|string',
            'niveau_education' => 'required|string|max:255',
            'matiere' => 'nullable|string|max:255',
            'type' => 'required|in:cours,activité',
            'duree' => 'required|regex:/^([0-9]+):([0-5][0-9])$/',
            'etat' => 'required|in:encours,terminé,annulé',
            'credits' => 'nullable|integer|min:0',
            'enseignant_id' => 'nullable|exists:enseignants,id', // Assurez-vous que l'enseignant existe
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le champ nom est obligatoire.',
            'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
            'nom.max' => 'Le champ nom ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'niveau_education.required' => 'Le niveau d\'éducation est obligatoire.',
            'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
            'niveau_education.max' => 'Le niveau d\'éducation ne doit pas dépasser 255 caractères.',
            'matiere.string' => 'La matière doit être une chaîne de caractères.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être soit "cours" soit "activité".',
            'duree.required' => 'La durée est obligatoire.',
           'duree.regex' => 'La durée doit être au format valide, comme "2h" ou "30m".',
            'etat.required' => 'L\'état est obligatoire.',
            'etat.in' => 'L\'état doit être soit "encours", "terminé" ou "annulé".',
            'credits.integer' => 'Les crédits doivent être un nombre entier.',
            'credits.min' => 'Les crédits doivent être supérieurs ou égaux à 0.',
            'enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
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
