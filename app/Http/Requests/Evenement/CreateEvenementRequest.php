<?php

namespace App\Http\Requests\Evenement;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class CreateEvenementRequest extends FormRequest
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
            'participant' => 'required|array', // Le participant doit être un tableau
        'participant.*' => 'required|array', // Chaque élément de participant doit être un tableau
        'participant.*.apprenant_id' => 'nullable|exists:users,id', // apprenant_id doit être un utilisateur existant
        'participant.*.enseignant_id' => 'nullable|exists:users,id', // enseignant_id doit être un utilisateur existant
        'participant.*.classe_id' => 'nullable|exists:classes,id', // classe_id doit être une classe existante
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Récupérer les participants
        $participants = $this->input('participant', []);

        foreach ($participants as $key => $participant) {
            // Vérifiez que l'un des champs (apprenant_id, enseignant_id, classe_id) soit renseigné
            if (!isset($participant['apprenant_id']) && !isset($participant['enseignant_id']) && !isset($participant['classe_id'])) {
                $validator->errors()->add("participant.$key", "Un champ 'apprenant_id', 'enseignant_id' ou 'classe_id' doit être spécifié pour chaque participant.");
            }

            // Si 'apprenant_id' et 'enseignant_id' sont tous deux définis, cela crée une erreur
            if (isset($participant['apprenant_id']) && isset($participant['enseignant_id'])) {
                $validator->errors()->add("participant.$key", "Vous ne pouvez pas spécifier à la fois 'apprenant_id' et 'enseignant_id' pour un participant.");
            }

            // Si 'apprenant_id' et 'classe_id' sont tous deux définis, cela crée une erreur
            if (isset($participant['apprenant_id']) && isset($participant['classe_id'])) {
                $validator->errors()->add("participant.$key", "Vous ne pouvez pas spécifier à la fois 'apprenant_id' et 'classe_id' pour un participant.");
            }

            // Si 'enseignant_id' et 'classe_id' sont tous deux définis, cela crée une erreur
            if (isset($participant['enseignant_id']) && isset($participant['classe_id'])) {
                $validator->errors()->add("participant.$key", "Vous ne pouvez pas spécifier à la fois 'enseignant_id' et 'classe_id' pour un participant.");
            }
        }
    });
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
            'participant.*.user_id.exists' => 'L\'identifiant de l\'utilisateur n\'existe pas dans la base de données.',
            'participant.*.user_id.nullable' => 'L\'identifiant de l\'utilisateur est facultatif.',
            'participant.*.classe_id.exists' => 'L\'ID de la classe doit exister dans la table des classes.',
            'participant.*.classe_id.nullable' => 'La classe_id est optionnelle et peut être laissée vide.',
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
