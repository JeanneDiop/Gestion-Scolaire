<?php

namespace App\Http\Requests\ProgrammeClasse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateProgrammeClasseCoursRequest extends FormRequest
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
            'niveau_education' => 'required|string',
            'niveau_classe' => 'required|string|max:255',
            'type_exercice' => 'nullable|string|max:255',
            'langue_enseignee' => 'required|string|max:255',
            'objectif_generaux' => 'required|string|max:255',
            'objectif_specifiques' => 'required|string|max:255',
            'importer_programme' => 'required|string|max:255',
            'exporter_programme' => 'required|string|max:255',
            'cours' => 'required|array',
            'cours.*.nom' => 'required|string|max:255',
            'cours.*.description' => 'nullable|string',
            'cours.*.niveau_education' => 'required|in:maternelle,primaire,secondaire,supérieur',
            'cours.*.niveau_classe' => 'required|string|max:255',
            'cours.*.bareme' => [
    'required',
    'string',
    'max:255',
    function ($attribute, $value, $fail) {
        $index = explode('.', $attribute)[1];
        $niveauEducation = request()->input("cours.$index.niveau_education");

        // Validation pour le niveau "maternelle"
        if ($niveauEducation === 'maternelle') {
            if (!in_array($value, ['Acquis', 'En Progression'])) {
                $fail('Pour le niveau "maternelle", le barème doit être "Acquis" ou "En Progression".');
            }
        }

        // Validation pour le niveau "primaire"
        if ($niveauEducation === 'primaire') {
            if (!preg_match('/^(10|[0-9])\/10$/', $value)) {
                $fail('Pour le niveau "primaire", le barème doit être au format "X/10".');
            }
        }

        // Validation pour le niveau "secondaire"
        if ($niveauEducation === 'secondaire') {
            if (!preg_match('/^(20|[1-9]?[0-9])\/20$/', $value)) {
                $fail('Pour le niveau "secondaire", le barème doit être au format "X/20".');
            }
        }
    }
],
        ];
    }
    /**
     * Messages d'erreur personnalisés.
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le champ nom est requis.',
            'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
            'nom.max' => 'Le champ nom ne peut pas dépasser 255 caractères.',
            
            'description.string' => 'Le champ description doit être une chaîne de caractères.',
            
            'niveau_education.required' => 'Le champ niveau d\'éducation est requis.',
            'niveau_education.string' => 'Le champ niveau d\'éducation doit être une chaîne de caractères.',
            
            'niveau_classe.required' => 'Le champ niveau de classe est requis.',
            'niveau_classe.string' => 'Le champ niveau de classe doit être une chaîne de caractères.',
            'niveau_classe.max' => 'Le champ niveau de classe ne peut pas dépasser 255 caractères.',
            
            'type_exercice.string' => 'Le champ type d\'exercice doit être une chaîne de caractères.',
            'type_exercice.max' => 'Le champ type d\'exercice ne peut pas dépasser 255 caractères.',
            
            'langue_enseignee.required' => 'Le champ langue enseignée est requis.',
            'langue_enseignee.string' => 'Le champ langue enseignée doit être une chaîne de caractères.',
            'langue_enseignee.max' => 'Le champ langue enseignée ne peut pas dépasser 255 caractères.',
            
            'objectif_generaux.required' => 'Le champ objectif généraux est requis.',
            'objectif_generaux.string' => 'Le champ objectif généraux doit être une chaîne de caractères.',
            'objectif_generaux.max' => 'Le champ objectif généraux ne peut pas dépasser 255 caractères.',
            
            'objectif_specifiques.required' => 'Le champ objectif spécifiques est requis.',
            'objectif_specifiques.string' => 'Le champ objectif spécifiques doit être une chaîne de caractères.',
            'objectif_specifiques.max' => 'Le champ objectif spécifiques ne peut pas dépasser 255 caractères.',
            
            'importer_programme.required' => 'Le champ importer programme est requis.',
            'importer_programme.string' => 'Le champ importer programme doit être une chaîne de caractères.',
            'importer_programme.max' => 'Le champ importer programme ne peut pas dépasser 255 caractères.',
            
            'exporter_programme.required' => 'Le champ exporter programme est requis.',
            'exporter_programme.string' => 'Le champ exporter programme doit être une chaîne de caractères.',
            'exporter_programme.max' => 'Le champ exporter programme ne peut pas dépasser 255 caractères.',
            
            'cours.required' => 'Le champ cours est requis.',
            'cours.array' => 'Le champ cours doit être un tableau.',
            
            'cours.*.nom.required' => 'Le nom du cours est requis.',
            'cours.*.nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
            'cours.*.nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',
            
            'cours.*.description.string' => 'La description du cours doit être une chaîne de caractères.',
            
            'cours.*.niveau_education.required' => 'Le niveau d\'éducation du cours est requis.',
            'cours.*.niveau_education.in' => 'Le niveau d\'éducation du cours doit être l\'un des suivants : maternelle, primaire, secondaire, supérieur.',
            
            'cours.*.niveau_classe.required' => 'Le niveau de classe du cours est requis.',
            'cours.*.niveau_classe.string' => 'Le niveau de classe du cours doit être une chaîne de caractères.',
            'cours.*.niveau_classe.max' => 'Le niveau de classe du cours ne peut pas dépasser 255 caractères.',
            
            'cours.*.bareme.required' => 'Le barème du cours est requis.',
            'cours.*.bareme.string' => 'Le barème du cours doit être une chaîne de caractères.',
            'cours.*.bareme.max' => 'Le barème du cours ne peut pas dépasser 255 caractères.',
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
