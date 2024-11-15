<?php

namespace App\Http\Requests\Evenement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class UpdateEvenementRequest extends FormRequest
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
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_heure' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
            'lieu' => 'nullable|in:Salle,Exterieur,En ligne',
            'recurrence' => 'nullable|in:Quotidien,Hebdomadaire,Mensuel,Annuel',
            'ressource' => 'nullable|string|max:255',
            'responsable_id' => 'nullable|exists:users,id',
            'type_evenement' => 'nullable|string|max:255',
            'participant' => 'nullable|array', // Tableau de participants
            'participant.*.id' => 'exists:users,id',
        ];
    }

    /**
     * Messages d'erreur personnalisés pour chaque règle.
     */
    public function messages()
    {
        return [
            'titre.required' => 'Le titre de l\'événement est obligatoire.',
            'titre.string' => 'Le titre doit être une chaîne de caractères.',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'date_heure.regex' => 'Le format de la date et heure doit être valide. Utilisez le format : "1h30min" par exemple.',

            'lieu.in' => 'Le lieu doit être l\'une des options suivantes : Salle, Exterieur, ou En ligne.',

            'recurrence.in' => 'La récurrence doit être l\'une des options suivantes : Quotidien, Hebdomadaire, Mensuel, ou Annuel.',

            'ressource.string' => 'La ressource doit être une chaîne de caractères.',
            'ressource.max' => 'La ressource ne doit pas dépasser 255 caractères.',

            'responsable_id.exists' => 'Le responsable sélectionné doit exister dans la table des utilisateurs.',

            'type_evenement.string' => 'Le type d\'événement doit être une chaîne de caractères.',
            'type_evenement.max' => 'Le type d\'événement ne doit pas dépasser 255 caractères.',
            'participant.*.id.exists' => 'Chaque participant doit être un utilisateur valide.',
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
