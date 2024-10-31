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
            'annee_scolaire' => 'required|string',
            'niveau_classe' => 'required|string|max:255',
            'langue_enseignee' => 'nullable|string|max:255',
            'objectif_generaux' => 'nullable|string|max:255',
            'objectif_specifiques' => 'nullable|string|max:255',
            'importer_programme' => 'nullable|string|max:255',
            'exporter_programme' => 'nullable|string|max:255',
            'cours' => 'required|array',
            'cours.*.nom' => 'required|string|max:255',
            'cours.*.description' => 'nullable|string',
            'cours.*.niveau_education' => 'required|in:maternelle,primaire,secondaire,supérieur',
            'cours.*.niveau_classe' => 'required|string|max:255',
             'cours.*.type_exercice' => 'nullable|string|max:255',
            'cours.*.bareme' => [
    'nullable',
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
       'cours.*.heure_allouee' => 'required|regex:/^[0-9]+h$/',
        'cours.*.duree_recommander_sceance' => 'required|regex:/^[0-9]+h$/',
        'cours.*.categorie_cours' => 'required|string|max:255',
        'cours.*.etat' => 'nullable|string|in:encours,complet',
        'cours.*.frequence_evaluation' => 'required|in:Hebdomadaire,Mensuel,Semestre,Trimestriel',
        'cours.*.type_evaluation' => 'required|in:Formative,Sommative',
        'cours.*.credits' => 'nullable|integer|min:0',
        'cours.*.coefficient' => 'nullable|integer|min:0',
        'cours.*.semestre' => 'nullable|integer|min:1|max:2',
        'cours.*.enseignant_id' => 'nullable|exists:enseignants,id',
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
        'nom.string' => 'Le nom doit être une chaîne de caractères.',
        'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

        'niveau_education.required' => 'Le niveau d\'éducation est obligatoire.',
        'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',

        'annee_scolaire.required' => 'L\'année scolaire est obligatoire.',
        'annee_scolaire.string' => 'L\'année scolaire doit être une chaîne de caractères.',

        'niveau_classe.required' => 'Le niveau de classe est obligatoire.',
        'niveau_classe.string' => 'Le niveau de classe doit être une chaîne de caractères.',
        'niveau_classe.max' => 'Le niveau de classe ne peut pas dépasser 255 caractères.',


        'langue_enseignee.string' => 'La langue enseignée doit être une chaîne de caractères.',
        'langue_enseignee.max' => 'La langue enseignée ne peut pas dépasser 255 caractères.',


        'objectif_generaux.string' => 'Les objectifs généraux doivent être une chaîne de caractères.',
        'objectif_generaux.max' => 'Les objectifs généraux ne peuvent pas dépasser 255 caractères.',


        'objectif_specifiques.string' => 'Les objectifs spécifiques doivent être une chaîne de caractères.',
        'objectif_specifiques.max' => 'Les objectifs spécifiques ne peuvent pas dépasser 255 caractères.',

        'cours.required' => 'Le champ cours est obligatoire.',
        'cours.array' => 'Le champ cours doit être un tableau.',

        'cours.*.nom.required' => 'Le nom du cours est obligatoire.',
        'cours.*.nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
        'cours.*.nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',

        'cours.*.niveau_education.required' => 'Le niveau d\'éducation du cours est obligatoire.',
        'cours.*.niveau_education.in' => 'Le niveau d\'éducation du cours doit être l\'un des suivants : maternelle, primaire, secondaire, supérieur.',

        'cours.*.niveau_classe.required' => 'Le niveau de classe du cours est obligatoire.',
        'cours.*.niveau_classe.string' => 'Le niveau de classe du cours doit être une chaîne de caractères.',
        'cours.*.niveau_classe.max' => 'Le niveau de classe du cours ne peut pas dépasser 255 caractères.',

        
        'cours.*.bareme.string' => 'Le barème doit être une chaîne de caractères.',
        'cours.*.bareme.max' => 'Le barème ne peut pas dépasser 255 caractères.',

        'cours.*.heure_allouee.required' => 'L\'heure allouée est obligatoire.',
        'cours.*.heure_allouee.regex' => 'Le format de l\'heure allouée est invalide (ex: 3h).',

        'cours.*.duree_recommander_sceance.required' => 'La durée recommandée pour la séance est obligatoire.',
        'cours.*.duree_recommander_sceance.regex' => 'Le format de la durée recommandée pour la séance est invalide (ex: 2h).',

        'cours.*.categorie_cours.required' => 'La catégorie du cours est obligatoire.',
        'cours.*.categorie_cours.string' => 'La catégorie du cours doit être une chaîne de caractères.',
        'cours.*.categorie_cours.max' => 'La catégorie du cours ne peut pas dépasser 255 caractères.',

        'cours.*.frequence_evaluation.required' => 'La fréquence d\'évaluation est obligatoire.',
        'cours.*.frequence_evaluation.in' => 'La fréquence d\'évaluation doit être Hebdomadaire, Mensuel, Semestre ou Trimestriel.',

        'cours.*.type_evaluation.required' => 'Le type d\'évaluation est obligatoire.',
        'cours.*.type_evaluation.in' => 'Le type d\'évaluation doit être Formative ou Sommative.',

        'cours.*.enseignant_id.required' => 'L\'identifiant de l\'enseignant est obligatoire.',
        'cours.*.enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',

        'cours.*.competences.*.nom.required' => 'Le nom de la compétence est obligatoire.',
        'cours.*.competences.*.nom.string' => 'Le nom de la compétence doit être une chaîne de caractères.',
        'cours.*.competences.*.nom.max' => 'Le nom de la compétence ne peut pas dépasser 255 caractères.',

        // Autres messages génériques
        'cours.*.etat.in' => 'L\'état du cours doit être soit "encours" soit "complet".',
        'cours.*.credits.integer' => 'Les crédits doivent être un nombre entier positif.',
        'cours.*.credits.min' => 'Les crédits doivent être au minimum 0.',
        'cours.*.coefficient.integer' => 'Le coefficient doit être un nombre entier positif.',
        'cours.*.coefficient.min' => 'Le coefficient doit être au minimum 0.',
        'cours.*.semestre.integer' => 'Le semestre doit être un nombre entier.',
        'cours.*.semestre.min' => 'Le semestre doit être au minimum 1.',
        'cours.*.semestre.max' => 'Le semestre doit être au maximum 2.'
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
