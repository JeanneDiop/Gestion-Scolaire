<?php

namespace App\Http\Requests\PresenceAbsence;

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
            'absent' => 'nullable|in:oui,non', // Peut être nul ou l'une des valeurs "oui" ou "non"
            'present' => 'nullable|in:oui,non', // Peut être nul ou l'une des valeurs "oui" ou "non"
            'date_present' => 'nullable|date', // Peut être nul ou doit être une date valide
            'date_absent' => 'nullable|date', // Peut être nul ou doit être une date valide
            'raison_absence' => 'required|string|max:255', // Raison de l'absence obligatoire et limitée à 255 caractères
            'apprenant_id' => 'required|exists:apprenants,id', // L'ID de l'apprenant doit exister dans la table apprenants
            'cours_id' => 'required|exists:cours,id', // L'ID du cours doit exister dans la table cours
        ];
    }

    /**
     * Définit les messages d'erreur personnalisés pour les règles de validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'absent.in' => 'Le champ "absent" doit être "oui" ou "non".',
            'present.in' => 'Le champ "présent" doit être "oui" ou "non".',
            'date_present.date' => 'La date de présence doit être une date valide.',
            'date_absent.date' => 'La date d\'absence doit être une date valide.',
            'raison_absence.required' => 'La raison de l\'absence est obligatoire.',
            'raison_absence.string' => 'La raison de l\'absence doit être une chaîne de caractères.',
            'raison_absence.max' => 'La raison de l\'absence ne doit pas dépasser 255 caractères.',
            'apprenant_id.required' => 'L\'ID de l\'apprenant est obligatoire.',
            'apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',
            'cours_id.required' => 'L\'ID du cours est obligatoire.',
            'cours_id.exists' => 'Le cours sélectionné n\'existe pas.',
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
