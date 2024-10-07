<?php

namespace App\Http\Requests\Niveau;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class UpdateNiveauRequest extends FormRequest
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
            'nom' => 'required|string|max:255',
            'nombre_enseignant' => 'required|integer|min:0',
            'nombre_classe' => 'required|integer|min:0',
            'nombre_eleve' => 'required|integer|min:0',
        ];
    }
    public function messages()
    {
        return [
            'nom.required' => 'Le nom du niveau est requis.',
            'nom.string' => 'Le nom du niveau doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du niveau ne doit pas dépasser 255 caractères.',

            'nombre_enseignant.required' => 'Le nombre d\'enseignants est requis.',
            'nombre_enseignant.integer' => 'Le nombre d\'enseignants doit être un entier.',
            'nombre_enseignant.min' => 'Le nombre d\'enseignants ne peut pas être négatif.',

            'nombre_classe.required' => 'Le nombre de classes est requis.',
            'nombre_classe.integer' => 'Le nombre de classes doit être un entier.',
            'nombre_classe.min' => 'Le nombre de classes ne peut pas être négatif.',

            'nombre_eleve.required' => 'Le nombre d\'élèves est requis.',
            'nombre_eleve.integer' => 'Le nombre d\'élèves doit être un entier.',
            'nombre_eleve.min' => 'Le nombre d\'élèves ne peut pas être négatif.',
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
