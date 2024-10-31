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
            'cours.*.type_exercice' => 'nullable|string|max:255',
            'cours.*.niveau_education' => 'required|in:maternelle,primaire,secondaire,superieur',
            'cours.*.niveau_classe' => 'required|string|max:255',
            'cours.*.bareme' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $niveauEducation = request()->input('niveau_education');

                    if ($niveauEducation === 'maternelle' && !in_array($value, ['Acquis', 'En Progression'])) {
                        $fail('Pour le niveau "maternelle", le bareme doit être "Acquis" ou "En Progression".');
                    }

                    if ($niveauEducation === 'primaire' && $value !== '/10') {
                        $fail('Pour le niveau "primaire", le bareme doit être "/10".');
                    }

                    if ($niveauEducation === 'secondaire' && $value !== '/20') {
                        $fail('Pour le niveau "secondaire", le bareme doit être "/20".');
                    }
                },
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
            'cours.*.enseignant_id' => 'required|exists:enseignants,id',
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
            'nom.required' => 'Le nom du programme est obligatoire.',
            'nom.string' => 'Le nom du programme doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du programme ne peut pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'niveau_education.required' => 'Le niveau d\'éducation est obligatoire.',
            'niveau_education.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',


            'niveau_classe.required' => 'Le niveau de la classe est obligatoire.',
            'niveau_classe.string' => 'Le niveau de la classe doit être une chaîne de caractères.',
            'niveau_classe.max' => 'Le niveau de la classe ne peut pas dépasser 255 caractères.',

            'annee_scolaire.required' => 'Le champ annee_scolaire est requis.',
            'annee_scolaire.string' => 'Le champ annee_scolaire doit être une chaîne de caractères.',


            'langue_enseignee.string' => 'La langue enseignée doit être une chaîne de caractères.',
            'langue_enseignee.max' => 'La langue enseignée ne peut pas dépasser 255 caractères.',


            'objectif_generaux.string' => 'Les objectifs généraux doivent être une chaîne de caractères.',
            'objectif_generaux.max' => 'Les objectifs généraux ne peuvent pas dépasser 255 caractères.',


            'objectif_specifiques.string' => 'Les objectifs spécifiques doivent être une chaîne de caractères.',
            'objectif_specifiques.max' => 'Les objectifs spécifiques ne peuvent pas dépasser 255 caractères.',

            'importer_programme.required' => 'Le champ "importer programme" est obligatoire.',
            'importer_programme.string' => 'Le champ "importer programme" doit être une chaîne de caractères.',
            'importer_programme.max' => 'Le champ "importer programme" ne peut pas dépasser 255 caractères.',

            'exporter_programme.required' => 'Le champ "exporter programme" est obligatoire.',
            'exporter_programme.string' => 'Le champ "exporter programme" doit être une chaîne de caractères.',
            'exporter_programme.max' => 'Le champ "exporter programme" ne peut pas dépasser 255 caractères.',

            'cours.required' => 'Vous devez fournir au moins un cours pour ce programme.',
            'cours.array' => 'Les cours doivent être fournis sous forme de tableau.',

            'cours.*.nom.required' => 'Le nom du cours est obligatoire.',
            'cours.*.nom.string' => 'Le nom du cours doit être une chaîne de caractères.',
            'cours.*.nom.max' => 'Le nom du cours ne peut pas dépasser 255 caractères.',
            'cours.*.frequence_evaluation.required' => 'La fréquence d\'évaluation est obligatoire.',
            'cours.*.frequence_evaluation.in' => 'La fréquence d\'évaluation doit être soit "Hebdomadaire", "Mensuel", "Semestre" ou "Trimestriel".',

            'cours.*.type_evaluation.required' => 'Le type d\'évaluation est obligatoire.',
            'cours.*.type_evaluation.in' => 'Le type d\'évaluation doit être soit "Formative" soit "Sommative".',

            'cours.*.type_exercice.string' => 'Le champ type d\'exercice doit être une chaîne de caractères.',
            'cours.*.type_exercice.max' => 'Le champ type d\'exercice ne peut pas dépasser 255 caractères.',
            'cours.*.description.string' => 'La description du cours doit être une chaîne de caractères.',

           'cours.*.niveau_education.required' => 'Le niveau d\'éducation est requis pour chaque cours.',
            'cours.*.niveau_education.in' => 'Le niveau d\'éducation doit être l\'une des valeurs suivantes : maternelle, primaire, secondaire ou supérieur.',

            'cours.*.heure_allouee.required' => 'Le champ "heure allouée" est requis pour chaque cours.',
            'cours.*.heure_allouee.regex' => 'Le format de "heure allouée" doit être un nombre suivi de "h" (ex : 2h).',

            'cours.*.duree_recommander_sceance.required' => 'La durée recommandée pour chaque séance est obligatoire.',
            'cours.*.duree_recommander_sceance.regex' => 'Le format de "durée recommandée" doit être un nombre suivi de "h" (ex : 2h).',

            'cours.*.categorie_cours.required' => 'La catégorie du cours est obligatoire.',
            'cours.*.categorie_cours.string' => 'La catégorie du cours doit être une chaîne de caractères.',

            'cours.*.bareme.string' => 'Le barème doit être une chaîne de caractères.',
            'cours.*.bareme.max' => 'Le barème ne peut pas dépasser 255 caractères.',

            'cours.*.categorie_cours.max' => 'La catégorie du cours ne peut pas dépasser 255 caractères.',

            'cours.*.etat.in' => 'L\'état du cours doit être soit "encours" soit "complet".',

            'cours.*.credits.integer' => 'Les crédits doivent être un nombre entier.',
            'cours.*.credits.min' => 'Les crédits ne peuvent pas être négatifs.',

            'cours.*.coefficient.integer' => 'Le coefficient doit être un nombre entier.',
            'cours.*.coefficient.min' => 'Le coefficient ne peut pas être négatif.',

            'cours.*.semestre.integer' => 'Le semestre doit être un nombre entier.',
            'cours.*.semestre.min' => 'Le semestre doit être au moins 1.',
            'cours.*.semestre.max' => 'Le semestre ne peut pas être supérieur à 2.',

            'cours.*.enseignant_id.required' => 'Un enseignant doit être assigné à chaque cours.',
            'cours.*.enseignant_id.exists' => 'L\'enseignant spécifié doit exister dans la base de données.',

            // Messages pour les compétences
            'cours.*.competences.array' => 'Les compétences doivent être fournies sous forme de tableau.',
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
