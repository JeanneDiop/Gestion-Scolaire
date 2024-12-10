<?php

namespace App\Http\Requests\Evenement;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

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
            'participant.*.apprenant_id' => 'nullable|exists:apprenants,id', // Assurez-vous que l'apprenant existe dans la table 'apprenants'
            'participant.*.enseignant_id' => 'nullable|exists:enseignants,id',
            'participant.*.classe_id' => 'nullable|exists:classes,id', // classe_id doit être une classe existante

            // Validation conditionnelle basée sur la valeur de 'lieu'
            'salle_id' => 'nullable|exists:salles,id', // Ajouter la validation pour salle_id
            'lieu_exterieur' => 'nullable|string|max:255', // Ajouter la validation pour lieu_exterieur
            'lien_evenement' => 'nullable|url', // Ajouter la validation pour lien_evenement
        ];
    }
    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Vérifier les conditions basées sur le lieu
        if ($this->lieu == 'Salle' && !$this->salle_id) {
            $validator->errors()->add('salle_id', 'Le champ salle_id est requis lorsque le lieu est "Salle".');
        }

        if ($this->lieu == 'Exterieur' && !$this->lieu_exterieur) {
            $validator->errors()->add('lieu_exterieur', 'Le champ lieu_exterieur est requis lorsque le lieu est "Exterieur".');
        }

        if ($this->lieu == 'En ligne' && !$this->lien_evenement) {
            $validator->errors()->add('lien_evenement', 'Le champ lien_evenement est requis lorsque le lieu est "En ligne".');
        }

        // Vérifier si des participants sont fournis
        if ($this->has('participant')) {
            foreach ($this->participant as $participant) {

                // Si 'apprenant_id' est présent
                if (isset($participant['apprenant_id'])) {
                    // Trouver l'utilisateur associé à 'apprenant_id'
                    $user = User::find($participant['apprenant_id']);

                    // Vérifier si l'utilisateur existe
                    if (!$user) {
                        $validator->errors()->add('participant.*.apprenant_id', 'L\'apprenant sélectionné n\'existe pas.');
                    }
                }

                // Si 'enseignant_id' est présent
                if (isset($participant['enseignant_id'])) {
                    // Trouver l'utilisateur associé à 'enseignant_id'
                    $user = User::find($participant['enseignant_id']);

                    // Vérifier si l'utilisateur existe
                    if (!$user) {
                        $validator->errors()->add('participant.*.enseignant_id', 'L\'enseignant sélectionné n\'existe pas.');
                    }
                }
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
            // Messages généraux
            'titre.required' => 'Le titre de l\'événement est requis.',
            'titre.string' => 'Le titre doit être une chaîne de caractères.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'date_heure.regex' => 'Le format de la date et de l\'heure est invalide. Exemple : "12h30min".',

            'lieu.in' => 'Le lieu doit être l\'une des valeurs suivantes : Salle, Exterieur, En ligne.',

            'recurrence.in' => 'La récurrence doit être l\'une des valeurs suivantes : Quotidien, Hebdomadaire, Mensuel, Annuel.',

            'ressource.string' => 'La ressource doit être une chaîne de caractères.',
            'ressource.max' => 'La ressource ne peut pas dépasser 255 caractères.',

            'responsable_id.exists' => 'Le responsable sélectionné n\'existe pas.',

            'type_evenement.string' => 'Le type d\'événement doit être une chaîne de caractères.',
            'type_evenement.max' => 'Le type d\'événement ne peut pas dépasser 255 caractères.',

            'participant.required' => 'Les participants sont requis.',
            'participant.array' => 'Les participants doivent être dans un tableau.',

            // Messages pour chaque participant
            'participant.*.apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',
            'participant.*.enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
            'participant.*.classe_id.exists' => 'La classe sélectionnée n\'existe pas.',

            // Messages conditionnels
            'salle_id.exists' => 'La salle spécifiée n\'existe pas.',

            'lieu_exterieur.string' => 'Le lieu extérieur doit être une chaîne de caractères.',
            'lieu_exterieur.max' => 'Le lieu extérieur ne peut pas dépasser 255 caractères.',

            'lien_evenement.url' => 'Le lien de l\'événement doit être une URL valide.',

            // Messages conditionnels en fonction de "lieu"
            'salle_id.required_if' => 'Le champ salle_id est requis lorsque le lieu est "Salle".',
            'lieu_exterieur.required_if' => 'Le champ lieu_exterieur est requis lorsque le lieu est "Exterieur".',
            'lien_evenement.required_if' => 'Le champ lien_evenement est requis lorsque le lieu est "En ligne".',
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
