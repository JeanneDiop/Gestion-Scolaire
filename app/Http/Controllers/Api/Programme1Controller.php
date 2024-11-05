<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Programme\CreateProgrammeRequest;
use App\Http\Requests\Programme\UpdateProgrammeRequest;
use App\Models\Programme;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

class ProgrammeController extends Controller
{
    public function store(CreateProgrammeRequest $request)
    {
        try {
            $programme = new Programme();
            $programme ->nom = $request->nom;
            $programme ->description = $request->description;
            $programme ->niveau_education = $request->niveau_education;
            $programme ->credits = $request->credits;
            $programme ->date_debut = $request->date_debut;
            $programme ->date_fin = $request->date_fin;
            $programme ->cours_id = $request->cours_id;
            $programme ->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'programme a été ajoutée',
                'data' => $programme ,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du programme',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(UpdateProgrammeRequest $request, $id)
{
    try {
      
        $programme = Programme::findOrFail($id);
        $programme->nom = $request->nom;
        $programme->description = $request->description;
        $programme->niveau_education = $request->niveau_education;
        $programme->credits = $request->credits;
        $programme->date_debut = $request->date_debut;
        $programme->date_fin = $request->date_fin;
        $programme->cours_id = $request->cours_id;
        $programme->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le programme a été mis à jour avec succès.',
            'data' => $programme,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Programme non trouvé.',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour du programme.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupération du programme avec les relations cours et enseignant
        $programme = Programme::with(['cours.enseignant.user'])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme récupéré avec succès.',
            'data' => $programme,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Programme non trouvé.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération du programme.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function index()
{
    try {
        // Récupération de tous les programmes avec leurs relations cours et enseignant
        $programmes = Programme::with(['cours.enseignant.user'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des programmes récupérée avec succès.',
            'data' => $programmes,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des programmes.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    DB::beginTransaction();

    try {
        // Récupération du programme à supprimer
        $programme = Programme::findOrFail($id);

        // Suppression du programme
        $programme->delete();

        DB::commit(); // Valide la transaction

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le programme a été supprimé avec succès.',
        ]);
    } catch (ModelNotFoundException $e) {
        DB::rollBack(); // Annule la transaction si le modèle n'est pas trouvé

        return response()->json([
            'status_code' => 404,
            'status_message' => 'Programme non trouvé.',
        ], 404);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du programme.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
