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
            'nom' => 'required|string|max:255',
            'niveau_classe' => 'required|string|max:255',
            'salle_id' => 'required|integer|exists:salles,id',

            // Validation pour les cours
            'cours' => 'required|array',
            'cours.*.nom' => 'required|string|max:255',
            'cours.*.description' => 'nullable|string',
            'cours.*.heure_allouée' =>'required|regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/',
            'cours.*.etat' => 'nullable|string|in:encours,terminé',
            'cours.*.credits' => 'nullable|integer|min:1',
            'cours.*.coefficient' => 'nullable|numeric|min:1',
            'cours.*.enseignant_id' => 'nullable|integer|exists:enseignants,id',
        ];
    }

    public function messages()
{
    return [
        // Messages de validation pour la classe
        'nom.required' => 'Le nom de la classe est requis.',
        'nom.string' => 'Le nom de la classe doit être une chaîne de caractères.',
        'nom.max' => 'Le nom de la classe ne peut pas dépasser 255 caractères.',

        'niveau_classe.required' => 'Le niveau de la classe est requis.',
        'niveau_classe.string' => 'Le niveau de la classe doit être une chaîne de caractères.',
        'niveau_classe.max' => 'Le niveau de la classe ne peut pas dépasser 255 caractères.',

        'salle_id.required' => 'L\'ID de la salle est requis.',
        'salle_id.integer' => 'L\'ID de la salle doit être un entier.',
        'salle_id.exists' => 'La salle sélectionnée n\'existe pas.',

        // Messages de validation pour les cours
        'cours.required' => 'Au moins un cours doit être fourni.',
        'cours.array' => 'Les cours doivent être un tableau.',

        'cours.*.nom.required' => 'Le nom du cours est requis.',
        'cours.*.nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
        'cours.*.nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',

        'cours.*.description.string' => 'La description du cours doit être une chaîne de caractères.',

        'cours.*.heure_allouée.required' => 'L\'heure allouée au cours est requise.',
        'cours.*.heure_allouée.regex' => 'Le format de l\'heure allouée doit être sous le format HH:MM:SS.',

        'cours.*.etat.string' => 'L\'état du cours doit être une chaîne de caractères.',
        'cours.*.etat.in' => 'L\'état du cours doit être "encours" ou "terminé".',

        'cours.*.credits.integer' => 'Le nombre de crédits doit être un entier.',
        'cours.*.credits.min' => 'Le nombre de crédits doit être d\'au moins 1.',

        'cours.*.coefficient.numeric' => 'Le coefficient doit être un nombre.',
        'cours.*.coefficient.min' => 'Le coefficient doit être d\'au moins 1.',

        'cours.*.enseignant_id.integer' => 'L\'ID de l\'enseignant doit être un entier.',
        'cours.*.enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
    ];
}
    protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->toArray();
    throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
}
}
