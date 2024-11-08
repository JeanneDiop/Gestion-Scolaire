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

        'type_utilisateur' => 'required|string|in:apprenant,enseignant',
        'statut' => 'required|string|in:present,absent,retard',
        'date_present' => 'required_if:statut,present|date',
        'date_absent' => 'required_if:statut,absent|date',
        'heure_arrivee' => 'required_if:statut,retard|string',
        'duree_retard' => 'required_if:statut,retard|string',
        'raison_absence' => 'required_if:statut,absent|string|max:255',
        'apprenant_id' => 'required_if:type_utilisateur,apprenant|exists:apprenants,id',
        'enseignant_id' => 'required_if:type_utilisateur,enseignant|exists:enseignants,id',
        'cours_id' => 'required|exists:cours,id',

    ];

}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Validation pour le type utilisateur
        $typeUtilisateur = $this->input('type_utilisateur');
        if ($typeUtilisateur === 'apprenant' && !$this->input('apprenant_id')) {
            $validator->errors()->add('apprenant_id', 'L\'apprenant_id est requis lorsque le type d\'utilisateur est apprenant.');
        }
        if ($typeUtilisateur === 'enseignant' && !$this->input('enseignant_id')) {
            $validator->errors()->add('enseignant_id', 'L\'enseignant_id est requis lorsque le type d\'utilisateur est enseignant.');
        }

        // Validation pour le statut
        $statut = $this->input('statut');
        if ($statut === 'present' && !$this->input('date_present')) {
            $validator->errors()->add('date_present', 'La date de présence est requise lorsque le statut est présent.');
        }
        if ($statut === 'absent') {
            if (!$this->input('date_absent')) {
                $validator->errors()->add('date_absent', 'La date d\'absence est requise lorsque le statut est absent.');
            }
            if (!$this->input('raison_absence')) {
                $validator->errors()->add('raison_absence', 'La raison de l\'absence est requise lorsque le statut est absent.');
            }
        }
        if ($statut === 'retard') {
            if (!$this->input('heure_arrivee')) {
                $validator->errors()->add('heure_arrivee', 'L\'heure d\'arrivée est requise lorsque le statut est en retard.');
            }
            if (!$this->input('duree_retard')) {
                $validator->errors()->add('duree_retard', 'La durée du retard est requise lorsque le statut est en retard.');
            }
        }
    });
}
    public function messages()
{
    return [
        'type_utilisateur.required' => 'Le type d\'utilisateur est requis.',
        'type_utilisateur.string' => 'Le type d\'utilisateur doit être une chaîne de caractères.',
        'type_utilisateur.in' => 'Le type d\'utilisateur doit être soit "apprenant" ou "enseignant".',
        'statut.required' => 'Le statut est requis.',
        'statut.string' => 'Le statut doit être une chaîne de caractères.',
        'statut.in' => 'Le statut doit être soit "present", "absent" ou "retard".',
        'date_present.required_if' => 'La date de présence est requise lorsque le statut est "present".',
        'date_present.date' => 'La date de présence doit être une date valide.',
        'date_absent.required_if' => 'La date d\'absence est requise lorsque le statut est "absent".',
        'date_absent.date' => 'La date d\'absence doit être une date valide.',
        'heure_arrivee.required_if' => 'L\'heure d\'arrivée est requise lorsque le statut est "retard".',
        'heure_arrivee.string' => 'L\'heure d\'arrivée doit être une chaîne de caractères.',
        'duree_retard.required_if' => 'La durée de retard est requise lorsque le statut est "retard".',
        'duree_retard.string' => 'La durée de retard doit être une chaîne de caractères.',
        'raison_absence.required_if' => 'La raison de l\'absence est requise lorsque le statut est "absent".',
        'raison_absence.string' => 'La raison de l\'absence doit être une chaîne de caractères.',
        'raison_absence.max' => 'La raison de l\'absence ne doit pas dépasser 255 caractères.',
        'apprenant_id.required_if' => 'L\'ID de l\'apprenant est requis lorsque le type d\'utilisateur est "apprenant".',
        'apprenant_id.exists' => 'L\'apprenant sélectionné n\'existe pas.',
        'enseignant_id.required_if' => 'L\'ID de l\'enseignant est requis lorsque le type d\'utilisateur est "enseignant".',
        'enseignant_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
        'cours_id.required' => 'L\'ID du cours est requis.',
        'cours_id.exists' => 'Le cours sélectionné n\'existe pas dans la base de données.',
    ];
}
    protected function failedValidation(Validator $validator)
    {

        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
