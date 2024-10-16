<?php

namespace App\Http\Requests\PresenceAbsence;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreatePresenceAbsenceRequest extends FormRequest
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
            'type_utilisateur' => 'required|in:apprenant,enseignant',
            'statut' => ['sometimes', 'string', Rule::in(['present', 'absent','retard'])],
            'date_present' => 'nullable|date',
            'date_absent' => 'nullable|date',
            'heure_arrivee' => 'nullable|regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/',
            'duree_retard' => 'nullable|regex:/^([0-9]+):([0-5][0-9]):([0-5][0-9])$/',
            'raison_absence' => 'nullable|string|max:255',
            'apprenant_id' => 'nullable|exists:apprenant,id',
            //'enseignant_id' => 'nullable|exists:enseignant,id',
            'cours_id' => 'required|exists:cours,id',
        ];
    }

    public function messages()
    {
        return [
            'type_utilisateur.required' => 'Le type d\'utilisateur est requis.',
            'type_utilisateur.in' => 'Le type d\'utilisateur doit être "apprenant" ou "enseignant".',
            'statut.sometimes' => 'Le statut est optionnel.',
            'statut.string' => 'Le statut doit être une chaîne de caractères.',
            'statut.in' => 'Le statut doit être soit "present" soit "absent" soit "retard".',
            'date_present.date' => 'La date de présence doit être une date valide.',
            'date_absent.date' => 'La date d\'absence doit être une date valide.',
            'heure_arrivee.date_format' => 'L\'heure d\'arrivée doit être au format HH:MM.',
            'duree_retard.date_format' => 'La durée de retard doit être au format HH:MM.',
            'raison_absence.string' => 'La raison d\'absence doit être une chaîne de caractères.',
            'raison_absence.max' => 'La raison d\'absence ne doit pas dépasser 255 caractères.',
            'apprenant_id.required' => 'Le champ apprenant est requis.',
            'apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',
            //'enseignant_id.required' => 'L\'ID de l\'enseignant est requis.',
            //'enseignant_id.exists' => 'L\'ID de l\'enseignant sélectionné n\'existe pas.',
            'cours_id.required' => 'L\'ID du cours est requis.',
            'cours_id.exists' => 'L\'ID du cours doit exister dans la base de données.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {

        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
