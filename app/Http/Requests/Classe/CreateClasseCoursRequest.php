<?php

namespace App\Http\Requests\Classe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateClasseCoursRequest extends FormRequest
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
            // Validation pour la classe
            'nom' => 'required|string|max:255',
            'niveau_classe' => 'required|string|max:255',
            'salle_id' => 'required|exists:salles,id',

            // Validation pour le cours
            'cours_nom' => 'required|string|max:255',
            'cours_description' => 'nullable|string',
            'cours_heure_allouée' =>'required|regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/',
            'cours_etat' => 'nullable|string|in:encours,termine',
            'cours_credits' => 'nullable|integer|min:1',
            'cours_coefficient' => 'nullable|integer',
            'enseignant_id' => 'required|exists:enseignants,id',
        ];
    }

    public function messages()
{
    return [
        // Messages de validation pour la classe
        'nom.required' => 'Le nom est requis.',
        'nom.string' => 'Le nom doit être une chaîne de caractères.',
        'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

        'niveau_classe.required' => 'Le niveau de classe est requis.',
        'niveau_classe.string' => 'Le niveau de classe doit être une chaîne de caractères.',
        'niveau_classe.max' => 'Le niveau de classe ne peut pas dépasser 255 caractères.',

        'salle_id.required' => 'L\'ID de la salle est requis.',
        'salle_id.exists' => 'La salle sélectionnée n\'existe pas.',

        // Messages de validation pour le cours
        'cours_nom.required' => 'Le nom du cours est requis.',
        'cours_nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
        'cours_nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',

        'cours_description.string' => 'La description du cours doit être une chaîne de caractères.',

        'cours_heure_allouée.required' => 'L\'heure allouée au cours est requise.',
        'cours_heure_allouée.regex' => 'Le format de l\'heure allouée doit être HH:MM:SS.',

        'cours_etat.string' => 'L\'état du cours doit être une chaîne de caractères.',
        'cours_etat.in' => 'L\'état du cours doit être soit "encours" soit "termine".',

        'cours_credits.integer' => 'Les crédits du cours doivent être un nombre entier.',
        'cours_credits.min' => 'Les crédits du cours doivent être au moins de 1.',

        'cours_coefficient.integer' => 'Le coefficient du cours doit être un nombre.',

        'enseignant_id.required' => 'L\'ID de l\'enseignant est requis.',
        'enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
    ];
}
    protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->toArray();
    throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
}
}
