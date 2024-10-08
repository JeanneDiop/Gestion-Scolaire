<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NiveauEcole;
use App\Http\Requests\NiveauEcole\CreateNiveauEcoleRequest;
use App\Http\Requests\NiveauEcole\UpdateNiveauEcoleRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
class NiveauEcoleController extends Controller
{
    public function store(CreateNiveauEcoleRequest $request)
    {
        try {
            $niveauecole = new NiveauEcole();
            $niveauecole->ecole_id = $request->ecole_id;
            $niveauecole->niveau_id = $request->niveau_id;
            $niveauecole->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'niveauecole a été ajoutée',
                'data' =>  $niveauecole,
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de niveauecole',
                'error' => $e->getMessage(),
            ],500);
        }
    }
    public function update(UpdateNiveauEcoleRequest $request, $id)
    {
        try {
            // Trouver le niveau de l'école par son ID
            $niveauecole = NiveauEcole::findOrFail($id);

            // Mettre à jour les champs avec les données de la requête
            $niveauecole->ecole_id = $request->ecole_id;
            $niveauecole->niveau_id = $request->niveau_id;
            $niveauecole->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'niveauecole a été mise à jour avec succès',
                'data' => $niveauecole,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'NiveauEcole non trouvé',
                'error' => 'Aucun enregistrement ne correspond à cet ID',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la mise à jour de niveauecole',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            // Trouver le NiveauEcole par son ID
            $niveauecole = NiveauEcole::findOrFail($id);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'NiveauEcole trouvé avec succès',
                'data' => $niveauecole,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'NiveauEcole non trouvé',
                'error' => 'Aucun enregistrement ne correspond à cet ID',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la récupération de NiveauEcole',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function index()
    {
        try {
            $niveauxecoles = NiveauEcole::all();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Tous les niveaux d\'école récupérés avec succès',
                'data' => $niveauxecoles,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la récupération des niveaux d\'école',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy($id)
{
    try {

        $niveauecole = NiveauEcole::findOrFail($id);
        $niveauecole->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'NiveauEcole supprimé avec succès',
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'NiveauEcole non trouvé',
            'error' => 'Aucun enregistrement ne correspond à cet ID',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de NiveauEcole',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
