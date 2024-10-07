<?php

namespace App\Http\Requests\PlanifierCour; // Corrigé : Namespace correct

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
class CreatePlanifierCourRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande.
     */
    public function authorize(): bool
    {
        return true; // Autoriser toutes les demandes pour cet exemple
    }

    /**
     * Obtenir les règles de validation qui s'appliquent à la demande.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array // Typage ajouté
    {
        return [
            'date_cours' => 'required|date', // Date du cours obligatoire et doit être une date valide
            'heure_debut' => 'required|date_format:H:i', // Heure de début obligatoire et au bon format
            'heure_fin' => 'required|date_format:H:i|after:heure_debut', // Heure de fin doit être après l'heure de début
            'jour_semaine' => 'required|string', // Jour de la semaine obligatoire
           'duree' => 'nullable|regex:/^([0-9]+):([0-5][0-9])$/', // Durée optionnelle, doit être une chaîne (par exemple "2h" ou "30m")
           'statut' => ['sometimes', 'string', Rule::in(['prévu', 'reporté','annulé'])], // Statut optionnel, doit être parmi les valeurs définies
            'annee_scolaire' => 'required|string', // Année scolaire obligatoire, doit être un entier valide
            'semestre' => 'required|integer|min:1|max:2', // Semestre obligatoire, doit être soit 1 soit 2
            'cours_id' => 'required|integer',
        ];
    }

    /**
     * Obtenir les messages de validation personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array // Typage ajouté
    {
        return [
            'date_cours.required' => 'La date du cours est obligatoire.',
            'date_cours.date' => 'La date du cours doit être une date valide.',
            'heure_debut.required' => 'L\'heure de début est obligatoire.',
            'heure_debut.date_format' => 'L\'heure de début doit être au format H:i.',
            'heure_fin.required' => 'L\'heure de fin est obligatoire.',
            'heure_fin.date_format' => 'L\'heure de fin doit être au format H:i.',
            'heure_fin.after' => 'L\'heure de fin doit être après l\'heure de début.',
            'jour_semaine.required' => 'Le jour de la semaine est obligatoire.',
            'duree.regex' => 'La durée doit être au format valide, comme "2h" ou "30m".',// Modifié pour refléter le type string
            'statut.sometimes' => 'statut est optionnel.',
           'statut.string' => 'statut doit être une chaîne de caractères.',
           'statut.in' => 'L\'état doit être soit "prévu" soit "annulé" soit "reporté".',
            'annee_scolaire.required' => 'L\'année scolaire est obligatoire.',
            'annee_scolaire.integer' => 'L\'année scolaire doit être un entier.',
            'annee_scolaire.min' => 'L\'année scolaire doit être supérieure ou égale à 1900.',
            'annee_scolaire.max' => 'L\'année scolaire ne peut pas dépasser ' . date('Y') . '.',
            'semestre.required' => 'Le semestre est obligatoire.',
            'semestre.integer' => 'Le semestre doit être un entier.',
            'semestre.min' => 'Le semestre doit être au moins 1.',
            'semestre.max' => 'Le semestre doit être au maximum 2.',
        'cours_id.required' => 'L\'ID de la cours est obligatoire.',
        'cours_id.integer' => 'L\'ID de la cours doit être un nombre entier.',
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
