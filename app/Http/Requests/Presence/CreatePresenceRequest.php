<?php

namespace App\Http\Requests\Presence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreatePresenceRequest extends FormRequest
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
        'date_present' => 'nullable_if:statut,present|date',
        'date_absent' => 'nullable_if:statut,absent|date',
        'heure_arrivee' => 'nullable_if:statut,retard|string',
        'duree_retard' => 'nullable_if:statut,retard|string',
        'raison_absence' => 'nullable_if:statut,absent|string|max:255',
        'apprenant_id' => 'nullable_if:type_utilisateur,apprenant|exists:apprenants,id',
        'enseignant_id' => 'nullable_if:type_utilisateur,enseignant|exists:enseignants,id',
        'cours_id' => 'required|exists:cours,id',

    ];

}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $typeUtilisateur = $this->input('type_utilisateur');
        $statut = $this->input('statut');

        // Validation du champ apprenant_id ou enseignant_id en fonction du type d'utilisateur
        if ($typeUtilisateur === 'apprenant' && !$this->input('apprenant_id')) {
            $validator->errors()->add('apprenant_id', 'L\'apprenant_id est requis lorsque le type d\'utilisateur est apprenant.');
        }
        if ($typeUtilisateur === 'enseignant' && !$this->input('enseignant_id')) {
            $validator->errors()->add('enseignant_id', 'L\'enseignant_id est requis lorsque le type d\'utilisateur est enseignant.');
        }

        // Gestion des validations en fonction du statut
        switch ($statut) {
            case 'present':
                // Pour le statut "present", vérifier l'absence des champs liés aux statuts "absent" et "retard"
                if (!$this->input('date_present')) {
                    $validator->errors()->add('date_present', 'La date de présence est requise lorsque le statut est présent.');
                }
                if ($this->input('date_absent')) {
                    $validator->errors()->add('date_absent', 'La date d\'absence n\'est requise que lorsque le statut est absent.');
                }
                if ($this->input('raison_absence')) {
                    $validator->errors()->add('raison_absence', 'La raison de l\'absence n\'est requise que lorsque le statut est absent.');
                }
                if ($this->input('heure_arrivee')) {
                    $validator->errors()->add('heure_arrivee', 'L\'heure d\'arrivée n\'est requise que lorsque le statut est en retard.');
                }
                if ($this->input('duree_retard')) {
                    $validator->errors()->add('duree_retard', 'La durée de retard n\'est requise que lorsque le statut est en retard.');
                }
                break;

            case 'absent':
                // Pour le statut "absent", vérifier l'absence des champs liés aux statuts "present" et "retard"
                if (!$this->input('date_absent')) {
                    $validator->errors()->add('date_absent', 'La date d\'absence est requise lorsque le statut est absent.');
                }
                if (!$this->input('raison_absence')) {
                    $validator->errors()->add('raison_absence', 'La raison de l\'absence est requise lorsque le statut est absent.');
                }
                if ($this->input('date_present')) {
                    $validator->errors()->add('date_present', 'La date de présence n\'est requise que lorsque le statut est présent.');
                }
                if ($this->input('heure_arrivee')) {
                    $validator->errors()->add('heure_arrivee', 'L\'heure d\'arrivée n\'est requise que lorsque le statut est en retard.');
                }
                if ($this->input('duree_retard')) {
                    $validator->errors()->add('duree_retard', 'La durée de retard n\'est requise que lorsque le statut est en retard.');
                }
                break;

            case 'retard':
                // Pour le statut "retard", vérifier l'absence des champs liés aux statuts "present" et "absent"
                if (!$this->input('heure_arrivee')) {
                    $validator->errors()->add('heure_arrivee', 'L\'heure d\'arrivée est requise lorsque le statut est en retard.');
                }
                if (!$this->input('duree_retard')) {
                    $validator->errors()->add('duree_retard', 'La durée de retard est requise lorsque le statut est en retard.');
                }
                if ($this->input('date_present')) {
                    $validator->errors()->add('date_present', 'La date de présence n\'est requise que lorsque le statut est présent.');
                }
                if ($this->input('date_absent')) {
                    $validator->errors()->add('date_absent', 'La date d\'absence n\'est requise que lorsque le statut est absent.');
                }
                if ($this->input('raison_absence')) {
                    $validator->errors()->add('raison_absence', 'La raison de l\'absence n\'est requise que lorsque le statut est absent.');
                }
                break;

            default:
                $validator->errors()->add('statut', 'Le statut spécifié est invalide.');
                break;
        }
    });
}
    public function messages()
{
    return [
       'type_utilisateur.required' => 'Le type d\'utilisateur est requis.',
        'type_utilisateur.in' => 'Le type d\'utilisateur doit être soit "apprenant", soit "enseignant".',
        'statut.required' => 'Le statut est requis.',
        'statut.in' => 'Le statut doit être "present", "absent" ou "retard".',
        'date_present.nullable_if' => 'La date de présence est requise si le statut est "present".',
        'date_present.date' => 'La date de présence doit être une date valide.',
        'date_absent.nullable_if' => 'La date d\'absence est requise si le statut est "absent".',
        'date_absent.date' => 'La date d\'absence doit être une date valide.',
        'heure_arrivee.nullable_if' => 'L\'heure d\'arrivée est requise si le statut est "retard".',
        'heure_arrivee.string' => 'L\'heure d\'arrivée doit être une chaîne de caractères.',
        'duree_retard.nullable_if' => 'La durée du retard est requise si le statut est "retard".',
        'duree_retard.string' => 'La durée du retard doit être une chaîne de caractères.',
        'raison_absence.nullable_if' => 'La raison de l\'absence est requise si le statut est "absent".',
        'raison_absence.string' => 'La raison de l\'absence doit être une chaîne de caractères.',
        'raison_absence.max' => 'La raison de l\'absence ne peut pas dépasser 255 caractères.',
        'apprenant_id.nullable_if' => 'L\'identifiant de l\'apprenant est requis si le type d\'utilisateur est "apprenant".',
        'apprenant_id.exists' => 'L\'identifiant de l\'apprenant doit exister dans la table des apprenants.',
        'enseignant_id.nullable_if' => 'L\'identifiant de l\'enseignant est requis si le type d\'utilisateur est "enseignant".',
        'enseignant_id.exists' => 'L\'identifiant de l\'enseignant doit exister dans la table des enseignants.',
        'cours_id.required' => 'L\'identifiant du cours est requis.',
        'cours_id.exists' => 'L\'identifiant du cours doit exister dans la table des cours.',
    ];
}
    protected function failedValidation(Validator $validator)
    {

        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
