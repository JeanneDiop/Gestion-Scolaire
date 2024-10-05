<?php

namespace App\Http\Requests\Parcours;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateParcoursRequest extends FormRequest
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
            'nom' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'credits' => 'nullable|integer|min:0',
            'date_creation' => 'nullable|date',
            'date_modification' => 'nullable|date|after_or_equal:date_creation',
            'apprenant_id' => 'required|exists:apprenants,id',
            'programme_id' => 'required|exists:programmes,id',
        ];
    }

    public function messages()
    {
        return [
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
            'credits.integer' => 'Les crédits doivent être un entier.',
            'credits.min' => 'Les crédits doivent être supérieurs ou égaux à 0.',
            'date_creation.date' => 'La date de création doit être une date valide.',
            'date_modification.date' => 'La date de modification doit être une date valide.',
            'date_modification.after_or_equal' => 'La date de modification doit être égale ou postérieure à la date de création.',
            'apprenant_id.required' => 'L\'identifiant de l\'apprenant est requis.',
            'apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',
            'programme_id.required' => 'L\'identifiant du programme est requis.',
            'programme_id.exists' => 'Le programme sélectionné n\'existe pas.',
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
