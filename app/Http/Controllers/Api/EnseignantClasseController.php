<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EnseignantClasse\CreateEnseignantClasseRequest;
use App\Models\EnseignantClasse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class EnseignantClasseController extends Controller
{
    public function store(CreateEnseignantClasseRequest $request)
    {
        try {
            $enseignantclasse = new EnseignantClasse();
            $enseignantclasse->enseignant_id = $request->enseignant_id;
            $enseignantclasse->classe_id = $request->classe_id;
            $enseignantclasse->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'enseignantclasse a été ajoutée',
                'data' =>  $enseignantclasse,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du enseignantclasse',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(CreateEnseignantClasseRequest $request, $id)
{
    try {
        // Récupérer l'association existante
        $enseignantClasse = EnseignantClasse::findOrFail($id);

        // Mettre à jour les informations de l'association
        $enseignantClasse->enseignant_id = $request->enseignant_id;
        $enseignantClasse->classe_id = $request->classe_id; // Corrigez la clé ici pour correspondre à 'classe_id'
        $enseignantClasse->save();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'association enseignant-classe a été mise à jour avec succès',
            'data' => $enseignantClasse,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Association non trouvée',
            'error' => 'L\'association avec l\'ID spécifié n\'existe pas.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'association',
            'error' => $e->getMessage(),
        ]);
    }
}
public function index()
{
    try {
        // Récupérer toutes les associations avec les informations de l'enseignant, de la classe et de la salle
        $enseignantClasses = EnseignantClasse::with(['enseignant', 'classe.salle'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Toutes les associations enseignant-classe ont été récupérées avec succès',
            'data' => $enseignantClasses,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des associations',
            'error' => $e->getMessage(),
        ]);
    }
}

public function show($id)
{
    try {
        // Récupérer l'association spécifique avec les informations de l'enseignant, de la classe et de la salle
        $enseignantClasse = EnseignantClasse::with(['enseignant', 'classe.salle'])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Association enseignant-classe récupérée avec succès',
            'data' => $enseignantClasse,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Association enseignant-classe non trouvée',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de l\'association',
            'error' => $e->getMessage(),
        ]);
    }
}
public function destroy($id)
{
    try {
        // Trouver l'association par ID
        $enseignantClasse = EnseignantClasse::findOrFail($id);

        // Supprimer l'association
        $enseignantClasse->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Association enseignant-classe supprimée avec succès',
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Association enseignant-classe non trouvée',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de l\'association',
            'error' => $e->getMessage(),
        ]);
    }
}


}
