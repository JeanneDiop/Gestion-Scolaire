<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnseignantClasse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\EnseignantClasse\CreateEnseignantClasseRequest;
use App\Http\Requests\EnseignantClasse\UpdateEnseignantClasseRequest;
class EnseignantClasseController extends Controller
{
    public function storeEnseignantClasse(CreateEnseignantClasseRequest $request)
 {
    try {
        $enseignantclasse = new EnseignantClasse();
        $enseignantclasse->enseignant_id = $request->enseignant_id;
        $enseignantclasse->classe_id = $request->classe_id;
        $enseignantclasse->save();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'enseignant de classe a été ajouté avec succès.',
            'data' => $enseignantclasse,
        ]);
    } catch (\Exception $e) { 
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de l\'enseignant de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function updateEnseignantClasse(UpdateEnseignantClasseRequest $request, $id)
{
    try {
        // Récupération de l'enseignant de classe à mettre à jour
        $enseignantclasse = EnseignantClasse::findOrFail($id);

        // Mise à jour des champs
        $enseignantclasse->enseignant_id = $request->enseignant_id;
        $enseignantclasse->classe_id = $request->classe_id;
        $enseignantclasse->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'enseignant de classe a été mis à jour avec succès.',
            'data' => $enseignantclasse,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'enseignant de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function index()
{
    try {
        // Récupération de tous les enseignants de classe
        $enseignantsClasses = EnseignantClasse::all();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des enseignants de classe récupérée avec succès.',
            'data' => $enseignantsClasses,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des enseignants de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupération de l'enseignant de classe par ID
        $enseignantClasse = EnseignantClasse::findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Enseignant de classe récupéré avec succès.',
            'data' => $enseignantClasse,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Enseignant de classe non trouvé.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de l\'enseignant de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    DB::beginTransaction();

    try {
        // Récupération de l'enseignant de classe à supprimer
        $enseignantClasse = EnseignantClasse::findOrFail($id);

        // Suppression de l'enseignant de classe
        $enseignantClasse->delete();

        DB::commit(); // Valide la transaction

        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'enseignant de classe a été supprimé avec succès.',
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack(); // Annule la transaction si le modèle n'est pas trouvé

        return response()->json([
            'status_code' => 404,
            'status_message' => 'Enseignant de classe non trouvé.',
        ], 404);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur est survenue lors de la suppression de l\'enseignant de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
