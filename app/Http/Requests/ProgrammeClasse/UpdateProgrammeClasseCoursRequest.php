<?php

namespace App\Http\Requests\ProgrammeClasse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class UpdateProgrammeClasseCoursRequest extends FormRequest
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
            'niveau_classe' => 'required|string|max:255',
            'periode' => 'required|in:annuelle,semestre',
            'cours' => 'required|array',
           'cours.*.id' => 'sometimes|exists:cours,id', // Vérifie que l'ID du cours existe
            'cours.*.nom' => 'required|string|max:255',
            'cours.*.description' => 'nullable|string',
            'cours.*.niveau_education' => 'required|string|max:255',
            'cours.*.heure_allouée' => 'required|regex:/^[0-9]+h$/',
            'cours.*.etat' => 'nullable|string|in:encours,complet',
            'cours.*.credits' => 'nullable|integer|min:0',
            'cours.*.coefficient' => 'nullable|integer|min:0',
            'cours.*.semestre' => 'nullable|integer|min:1|max:2',
            'cours.*.enseignant_id' => 'required|exists:enseignants,id',
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom du programme est obligatoire.',
            'nom.string' => 'Le nom du programme doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du programme ne peut pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'niveau_education.required' => 'Le niveau d\'éducation est obligatoire.',
            'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
            'niveau_education.max' => 'Le niveau d\'éducation ne peut pas dépasser 255 caractères.',

            'niveau_classe.required' => 'Le niveau de la classe est obligatoire.',
            'niveau_classe.string' => 'Le niveau de la classe doit être une chaîne de caractères.',
            'niveau_classe.max' => 'Le niveau de la classe ne peut pas dépasser 255 caractères.',

            'periode.required' => 'La période est obligatoire.',
            'periode.in' => 'La période doit être soit "annuelle", soit "semestre".',

            'cours.required' => 'Vous devez fournir au moins un cours pour ce programme.',
            'cours.array' => 'Les cours doivent être fournis sous forme de tableau.',

            'cours.*.id.required' => 'L\'ID du cours est obligatoire.',
            'cours.*.id.exists' => 'L\'ID du cours spécifié doit exister dans la base de données.',

            'cours.*.nom.required' => 'Le nom du cours est obligatoire.',
            'cours.*.nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
            'cours.*.nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',

            'cours.*.description.string' => 'La description du cours doit être une chaîne de caractères.',

            'cours.*.niveau_education.required' => 'Le niveau d\'éducation pour chaque cours est obligatoire.',
            'cours.*.niveau_education.string' => 'Le niveau d\'éducation pour chaque cours doit être une chaîne de caractères.',
            'cours.*.niveau_education.max' => 'Le niveau d\'éducation pour chaque cours ne peut pas dépasser 255 caractères.',
            'cours.*.heure_allouée.required' => 'Le champ "heure allouée" est requis pour chaque cours.',
            'cours.*.heure_allouée.regex' => 'Le format de "heure allouée" doit être un nombre suivi de "h" (ex : 2h).',

            'cours.*.etat.in' => 'L\'état du cours doit être soit "encours", soit "complet".',

            'cours.*.credits.integer' => 'Les crédits doivent être un nombre entier.',
            'cours.*.credits.min' => 'Les crédits ne peuvent pas être négatifs.',

            'cours.*.coefficient.integer' => 'Le coefficient doit être un nombre entier.',
            'cours.*.coefficient.min' => 'Le coefficient ne peut pas être négatif.',

            'cours.*.semestre.integer' => 'Le semestre doit être un nombre entier.',
            'cours.*.semestre.min' => 'Le semestre doit être au moins 1.',
            'cours.*.semestre.max' => 'Le semestre ne peut pas être supérieur à 2.',

            'cours.*.enseignant_id.required' => 'Un enseignant doit être assigné à chaque cours.',
            'cours.*.enseignant_id.exists' => 'L\'enseignant spécifié doit exister dans la base de données.',
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
