<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnseignantClasse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\EnseignantClasse\CreateEnseignantClasseRequest;
use App\Http\Requests\EnseignantClasse\UpdateEnseignantClasseRequest;
use App\Models\Enseignant;
use App\Models\Classe;

class EnseignantClasseController extends Controller
{
    public function storeEnseignantClasse(CreateEnseignantClasseRequest $request, $enseignant_id)
{
    try {

        $enseignant = Enseignant::find($enseignant_id);
        $classe = Classe::find($request->classe_id);

        if (!$enseignant || !$classe) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Enseignant ou classe non trouvé.',
            ], 404);
        }
        $enseignant->classes()->attach($classe);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'enseignant a été associé à la classe avec succès.',
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'association de l\'enseignant à la classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



public function show($id)
{
    try {
        // Récupération de l'enseignant de classe par ID avec les relations
        $enseignantClasse = EnseignantClasse::with(['classe.salle', 'enseignant.user'])->findOrFail($id);

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
