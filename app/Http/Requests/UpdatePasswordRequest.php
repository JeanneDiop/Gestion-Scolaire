<?php

namespace App\Http\Requests;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdatePasswordRequest extends FormRequest
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
            'password' => ['required', 'min:8'],
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'Le champ mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
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
