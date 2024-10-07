<?php

namespace App\Http\Requests\NiveauEcole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateNiveauEcoleRequest extends FormRequest
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
            'niveau_id' => 'required|exists:niveau,id',
            'ecole_id' => 'required|exists:ecole,id',
        ];
    }
    public function messages()
    {
        return [
            'niveau_id.required' => 'Le champ ,niveau_id est requis.',
            'niveau_id.exists' => 'Le niveau sélectionné n\'existe pas.',

            'ecole_id.required' => 'Le champ ecole_id est requis.',
            'ecole_id.exists' => 'Le ecole sélectionné n\'existe pas.',
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
