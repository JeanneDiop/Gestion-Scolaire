<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompteComptable;
use App\Http\Requests\CompteComptable\CreateCompteComptableRequest;
use App\Http\Requests\CompteComptable\UpdateCompteComptableRequest;
use Exception;
class CompteComptableController extends Controller
{
    public function store(CreateCompteComptableRequest $request)
    {
        try {
            // Créer un nouvel objet CompteComptable
            $compteComptable = new CompteComptable();

            // Assigner les valeurs des champs de la requête
            $compteComptable->nom_compte_comptable = $request->nom_compte_comptable ?? null;
            $compteComptable->code_compte_comptable = $request->code_compte_comptable ?? null;
            $compteComptable->user_id = $request->user_id  ?? null;
            $compteComptable->save();

            // Retourner une réponse JSON indiquant le succès
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Compte comptable ajouté avec succès',
                'data' => $compteComptable,
            ], 200);

        } catch (Exception $e) {
            // En cas d'erreur, retourner une réponse JSON avec un message d'erreur
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du compte comptable',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCompteComptableRequest $request, $id)
    {
        try {
            // Trouver de la compte par son ID
            $compteComptable = CompteComptable::findOrFail($id);

            // Mettre à jour les champs de la compte avec les nouvelles données
            $compteComptable->nom_compte_comptable = $request->nom_compte_comptable ?? null;
            $compteComptable->code_compte_comptable = $request->code_compte_comptable ?? null;
            $compteComptable->user_id = $request->user_id ?? null;

            // Sauvegarder les modifications
            $compteComptable->update();

            // Retourner une réponse JSON indiquant le succès
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Compte Comptable mise à jour avec succès',
                'data' =>$compteComptable,
            ], 200);

        } catch (Exception $e) {
            // En cas d'erreur, retourner une réponse JSON avec un message d'erreur
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la Compte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
{
    try {
        // Trouver  de la compte par son ID
        $compte = CompteComptable::findOrFail($id);

        // Supprimer  de la compte
        $compte->delete();

        // Retourner une réponse JSON indiquant le succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Compte Comptable supprimée avec succès',
        ], 200);

    } catch (Exception $e) {
        // En cas d'erreur, retourner une réponse JSON avec un message d'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la Compte Comptable',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}



