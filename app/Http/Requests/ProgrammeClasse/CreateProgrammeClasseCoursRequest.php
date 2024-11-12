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
        'niveau_education' => 'required|string',
        'annee_scolaire' => 'required|string',
        'niveau_classe' => 'required|string|max:255',
        'cycle' => 'required|string',
        'langue_enseignee' => 'nullable|string|max:255',
        'classe_id' => 'nullable|exists:classes,id',

        // Cours validations
        'cours.*.nom' => 'required|string|max:255',
        'cours.*.description' => 'nullable|string',
        'cours.*.niveau_education' => 'nullable|in:maternelle,primaire,secondaire,supérieur',
        'cours.*.niveau_classe' => 'required|string|max:255',
        'cours.*.heure_allouee' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',  
        'cours.*.etat' => 'nullable|string|in:encours,complet',
        'cours.*.credits' => 'nullable|integer|min:0',
        'cours.*.coefficient' => 'nullable|integer|min:0',
        'cours.*.objectif_generaux' => 'nullable|string|max:255',
        'cours.*.objectif_specifiques' => 'nullable|string|max:255',
        'cours.*.semestre' => 'nullable|integer|min:1|max:2',
        'cours.*.enseignant_id' => 'nullable|exists:enseignants,id',

        // CategorieCours validations
       


        'cours.*.categorie_cours.*.type_exercices' => 'nullable|string|max:255',
        'cours.*.categorie_cours.*.leçons' => 'nullable|string|max:255',
        'cours.*.categorie_cours.*.bareme' => [
            'nullable',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                $index = explode('.', $attribute)[1];  // Gets the index of the course
                $niveauEducation = request()->input("cours.$index.niveau_education");

                // Validation pour le niveau "maternelle"
                if ($niveauEducation === 'maternelle' && !in_array($value, ['Acquis', 'En Progression'])) {
                    $fail('Pour le niveau "maternelle", le barème doit être "Acquis" ou "En Progression".');
                }
                // Validation pour le niveau "primaire"
                if ($niveauEducation === 'primaire' && !preg_match('/^(10|[0-9])\/10$/', $value)) {
                    $fail('Pour le niveau "primaire", le barème doit être au format "X/10".');
                }
                // Validation pour le niveau "secondaire"
                if ($niveauEducation === 'secondaire' && !preg_match('/^(20|[1-9]?[0-9])\/20$/', $value)) {
                    $fail('Pour le niveau "secondaire", le barème doit être au format "X/20".');
                }
            }
        ],

        'cours.*.categorie_cours.*.frequence_evaluation' => 'nullable|in:Hebdomadaire,Mensuel,Semestre,Trimestriel',
        'cours.*.categorie_cours.*.mode_evaluation' => 'nullable|in:Formative,Sommative',
      
    'cours.*.categorie_cours.*.heure_debut' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
'cours.*.categorie_cours.*.duree_seance' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
'cours.*.categorie_cours.*.heure_fin' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',

        // Competences validations
        'cours.*.competences' => 'nullable|array',
        'cours.*.competences.*.nom' => 'required|string|max:255',
        'cours.*.competences.*.description' => 'nullable|string',
    ];
}
 
    /**
     * Messages d'erreur personnalisés.
     */
    public function messages()
{
    return [
        'nom.required' => 'Le champ nom est obligatoire.',
        'nom.string' => 'Le champ nom doit être une chaîne de caractères.',
        'nom.max' => 'Le champ nom ne peut pas dépasser 255 caractères.',

        'niveau_education.required' => 'Le champ niveau d\'éducation est obligatoire.',
        'niveau_education.string' => 'Le champ niveau d\'éducation doit être une chaîne de caractères.',

        'annee_scolaire.required' => 'Le champ année scolaire est obligatoire.',
        'annee_scolaire.string' => 'Le champ année scolaire doit être une chaîne de caractères.',

        'niveau_classe.required' => 'Le champ niveau de classe est obligatoire.',
        'niveau_classe.string' => 'Le champ niveau de classe doit être une chaîne de caractères.',
        'niveau_classe.max' => 'Le champ niveau de classe ne peut pas dépasser 255 caractères.',

        'cycle.required' => 'Le champ cycle est obligatoire.',
        'cycle.string' => 'Le champ cycle doit être une chaîne de caractères.',

        'classe_id.exists' => 'La classe sélectionné n\'existe pas.',
        
        'langue_enseignee.string' => 'Le champ langue enseignée doit être une chaîne de caractères.',
        'langue_enseignee.max' => 'Le champ langue enseignée ne peut pas dépasser 255 caractères.',




        'cours.*.nom.required' => 'Le nom du cours est obligatoire.',
        'cours.*.nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
        'cours.*.nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',

        'cours.*.description.string' => 'La description du cours doit être une chaîne de caractères.',

        'cours.*.niveau_education.in' => 'Le niveau d\'éducation du cours doit être maternelle, primaire, secondaire ou supérieur.',

        'cours.*.niveau_classe.required' => 'Le niveau de classe du cours est obligatoire.',
        'cours.*.niveau_classe.string' => 'Le niveau de classe du cours doit être une chaîne de caractères.',
        'cours.*.niveau_classe.max' => 'Le niveau de classe du cours ne peut pas dépasser 255 caractères.',

        'cours.*.heure_allouee.regex' => 'Le champ heure allouée doit être au format "Xh" ou "Xmin".',

        'cours.*.etat.in' => 'L\'état du cours doit être encours ou complet.',

        'cours.*.credits.integer' => 'Les crédits du cours doivent être un entier.',
        'cours.*.credits.min' => 'Les crédits du cours doivent être au minimum 0.',

        'cours.*.coefficient.integer' => 'Le coefficient du cours doit être un entier.',
        'cours.*.coefficient.min' => 'Le coefficient du cours doit être au minimum 0.',

        'cours.*.objectif_generaux.string' => 'L\'objectif général du cours doit être une chaîne de caractères.',
        'cours.*.objectif_generaux.max' => 'L\'objectif général du cours ne peut pas dépasser 255 caractères.',

        'cours.*.objectif_specifiques.string' => 'L\'objectif spécifique du cours doit être une chaîne de caractères.',
        'cours.*.objectif_specifiques.max' => 'L\'objectif spécifique du cours ne peut pas dépasser 255 caractères.',

        'cours.*.semestre.integer' => 'Le semestre doit être un entier.',
        'cours.*.semestre.min' => 'Le semestre doit être au minimum 1.',
        'cours.*.semestre.max' => 'Le semestre doit être au maximum 2.',

        'cours.*.enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',

        'cours.*.categorie_cours.*.type_exercices.string' => 'Le type d\'exercice doit être une chaîne de caractères.',
        'cours.*.categorie_cours.*.type_exercices.max' => 'Le type d\'exercice ne peut pas dépasser 255 caractères.',

        'cours.*.categorie_cours.*.bareme.string' => 'Le barème doit être une chaîne de caractères.',
        'cours.*.categorie_cours.*.bareme.max' => 'Le barème ne peut pas dépasser 255 caractères.',

        'cours.*.categorie_cours.*.frequence_evaluation.in' => 'La fréquence d\'évaluation doit être Hebdomadaire, Mensuel, Semestre ou Trimestriel.',

        'cours.*.categorie_cours.*.type_evaluation.in' => 'Le type d\'évaluation doit être Formative ou Sommative.',

        'cours.*.categorie_cours.*.duree_recommander_sceance.regex' => 'La durée recommandée pour la séance doit être au format "Xh" ou "Xmin".',

        'cours.*.categorie_cours.string' => 'Le champ catégorie du cours doit être une chaîne de caractères.',
        'cours.*.categorie_cours.max' => 'Le champ catégorie du cours ne peut pas dépasser 255 caractères.',

        'cours.*.competences.array' => 'Le champ compétences doit être un tableau.',

        'cours.*.competences.*.nom.required' => 'Le nom de la compétence est obligatoire.',
        'cours.*.competences.*.nom.string' => 'Le nom de la compétence doit être une chaîne de caractères.',
        'cours.*.competences.*.nom.max' => 'Le nom de la compétence ne peut pas dépasser 255 caractères.',

        'cours.*.competences.*.description.string' => 'La description de la compétence doit être une chaîne de caractères.',
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
