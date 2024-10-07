<?php

namespace App\Http\Requests\ApprenantClasse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateApprenantClasseRequest extends FormRequest
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
            'classe_id' => 'required|exists:classe,id',
            'apprenant_id' => 'required|exists:apprenant,id',
        ];
    }
    public function messages()
    {
        return [
            'classe_id.required' => 'Le champ classe_id est requis.',
            'classe_id.exists' => 'La classe sélectionné n\'existe pas.',

            'apprenant_id.required' => 'Le champ apprenant_id est requis.',
            'apprenant_id.exists' => 'Le apprenant sélectionné n\'existe pas.',
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
