<?php

namespace App\Http\Requests\Employe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateEmployeRequest extends FormRequest
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
    public function rules(): array
{
    return [
        'nom' => ['required', 'string', 'max:255'],
        'prenom' => ['required', 'string', 'max:255'],
        'telephone' => [
            'required',
            'regex:/^\+221(77|78|76|70|75|33)\d{7}$/',
            'unique:employes,telephone'
        ],
        'email' => [
            'nullable',
            'string',
            'email',
            'max:255',
            'regex:/^[A-Za-z]+[A-Za-z0-9._%+-]+@+[A-Za-z][A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
            'unique:employes,email'
        ],
        'adresse' => ['required', 'string'],
        'poste' => ['required', 'string'],
        'image' => ['nullable' ,'string'],
        'date_embauche' => ['required', 'date'],
        'statut' => ['required', 'in:permanent,vacataire,contractuel,honoraire'],
        'type_salaire' => ['required', 'in:fixe,horaire'],
        'date_naissance' => ['required', 'date'],
        'lieu_naissance' => ['required', 'string'],
        'genre' => ['required', 'in:Femme,Homme'],
        'statut_marital' => ['required', 'in:marié,celibataire,divorcé,veuve,veuf'],
        'numero_CNI' => ['string', 'unique:employes,numero_CNI'],
        'numero_securite_social' => ['nullable','string', 'unique:employes,numero_securite_social'],
        'date_fin_contrat' => ['required', 'date'],
    ];
}
public function messages(): array
{
    return [
        'nom.required' => 'Le nom est obligatoire.',
        'prenom.required' => 'Le prénom est obligatoire.',
        'telephone.required' => 'Le numéro de téléphone est obligatoire.',
        'telephone.regex' => 'Le numéro de téléphone doit être au format +221 suivi du bon indicatif.',
        'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        'email.required' => 'L\'email est obligatoire.',
        'email.email' => 'L\'adresse email doit être valide.',
        'email.regex' => 'Le format de l\'email est incorrect.',
        'email.unique' => 'Cet email est déjà utilisé.',
        'adresse.required' => 'L\'adresse est obligatoire.',
        'poste.required' => 'Le poste est obligatoire.',
        'image.required' => 'L\'image est obligatoire.',
        'date_embauche.required' => 'La date d\'embauche est obligatoire.',
        'statut.required' => 'Le statut de l\'emploi est obligatoire.',
        'statut.in' => 'Le statut de l\'emploi doit être permanent, vacataire, contractuel ou honoraire.',
        'type_salaire.required' => 'Le type de salaire est obligatoire.',
        'type_salaire.in' => 'Le type de salaire doit être fixe ou horaire.',
        'date_naissance.required' => 'La date de naissance est obligatoire.',
        'lieu_naissance.required' => 'Le lieu de naissance est obligatoire.',
        'genre.required' => 'Le genre est obligatoire.',
        'genre.in' => 'Le gnere doit être soit Homme soit Femme.',
        'statut_marital.required' => 'Le statut marital est obligatoire.',
        'statut_marital.in' => 'Le statut marital doit être marié, célibataire, divorcé, veuf ou veuve.',
        'numero_CNI.required' => 'Le numéro CNI est obligatoire.',
        'numero_CNI.unique' => 'Ce numéro CNI est déjà utilisé.',
        'numero_securite_social.unique' => 'Ce numéro de sécurité sociale est déjà utilisé.',
        'date_fin_contrat.required' => 'La date de fin de contrat est obligatoire.',
    ];
}
protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->toArray();
    throw new HttpResponseException(response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
}
}
