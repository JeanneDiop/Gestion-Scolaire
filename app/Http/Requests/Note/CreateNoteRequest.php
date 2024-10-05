<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateNoteRequest extends FormRequest
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
            'note' => 'required|numeric|min:0|max:20', // La note peut être décimale entre 0 et 20
            'type_note' => 'nullable|in:devoir1,devoir2,examen', // Le type de note peut être nul ou doit être l'une des valeurs spécifiées
            'date_note' => 'nullable|date', // La date de la note est optionnelle mais doit être au format date
            'evaluation_id' => 'nullable|exists:evaluations,id', // Doit correspondre à un ID valide dans la table evaluations
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
            'note.required' => 'La note est obligatoire.',
            'note.numeric' => 'La note doit être un nombre valide, y compris les décimales.',
            'note.min' => 'La note ne peut pas être inférieure à 0.',
            'note.max' => 'La note ne peut pas dépasser 20.',
            'type_note.in' => 'Le type de note doit être soit "devoir1", "devoir2" ou "examen".',
            'date_note.date' => 'La date de la note doit être une date valide.',
            'evaluation_id.exists' => 'L\'évaluation sélectionnée n\'existe pas.',
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
