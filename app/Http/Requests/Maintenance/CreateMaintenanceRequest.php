<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class CreateMaintenanceRequest extends FormRequest
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
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|in:en_attente,en_cours,termine',
            'niveau_priorite' => 'nullable|in:faible,moyen,eleve',
            //'date_demande' => 'nullable|date|in:' . now()->toDateString(),
           'date_demande' => 'nullable|date',
            'emplacement' => 'nullable|string|max:255',
            'demandeur_id' => 'nullable|exists:users,id',
            'personnel_id' => 'nullable|exists:personnel_administratifs,id',
        ];
    }
    public function messages()
    {
        return [
            'description.string' => 'La description doit être une chaîne de caractères.',
            'status.in' => 'Le statut doit être l’un des suivants : en_attente, en_cours, termine.',
            'niveau_priorite.in' => 'Le niveau de priorité doit être l’un des suivants : faible, moyen, eleve.',
            'date_demande.in' => 'La date de la demande doit être aujourd’hui : ' . now()->toDateString() . '.',
            'emplacement.string' => 'L’emplacement doit être une chaîne de caractères.',
            'demandeur_id.exists' => 'Le demandeur sélectionné est invalide.',
            'personnel_id.exists' => 'Le personnel administratif sélectionné est invalide.',
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
