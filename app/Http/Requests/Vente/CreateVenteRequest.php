<?php

namespace App\Http\Requests\Vente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\JsonResponse;
class CreateVenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'numero_vente' => 'nullable|string|max:255',
            'nom_vente' => 'nullable|string|max:255',
            'prix_vente' => 'nullable|string|max:255',
            'quantite' => 'nullable|integer|min:0',
            'quantite_disponible_stock' => 'nullable|integer|min:0',
            'type_vente' => 'nullable|in:produit,service',
            'compte_comptable_id' => 'nullable|exists:compte_comptables,id',
            'user_id' => 'nullable|exists:users,id',

        ];
    }




    public function messages()
    {
        return [
            'numero_vente.string' => 'Le numéro de vente doit être une chaîne de caractères.',
            'numero_vente.max' => 'Le numéro de vente ne doit pas dépasser 255 caractères.',
            'nom_vente.string' => 'Le nom de la vente doit être une chaîne de caractères.',
            'nom_vente.max' => 'Le nom de la vente ne doit pas dépasser 255 caractères.',
            'prix_vente.numeric' => 'Le prix de vente doit être un nombre.',
            'prix_vente.min' => 'Le prix de vente ne peut pas être inférieur à 0.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité ne peut pas être inférieure à 0.',
            'quantite_disponible_stock.integer' => 'La quantité disponible en stock doit être un nombre entier.',
            'quantite_disponible_stock.min' => 'La quantité disponible en stock ne peut pas être inférieure à 0.',
            'type_vente.in' => 'Le type de vente doit être "produit" ou "service".',
            'compte_comptable_id.exists' => 'Le compte comptable sélectionné n\'existe pas.',
            'user_id.exists' => 'L\'utilisateur sélectionné n\'existe pas.',
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



