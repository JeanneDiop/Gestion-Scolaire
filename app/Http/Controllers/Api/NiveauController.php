<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Niveau;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Niveau\CreateNiveauRequest;
use App\Http\Requests\Niveau\updateNiveauRequest;
class NiveauController extends Controller
{
    public function store(CreateNiveauRequest $request)
    {
        try {
            $niveau = new Niveau();
            $niveau->nom = $request->nom;
            $niveau->nombre_enseignant = $request->nombre_enseignant;
            $niveau->nombre_classe = $request->nombre_classe;
            $niveau->nombre_eleve= $request->nombre_eleve;
            $niveau->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'niveau a été ajoutée',
                'data' =>  $niveau,
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de niveau',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function update(UpdateNiveauRequest $request, $id)
{
    try {

        $niveau = Niveau::findOrFail($id);
        $niveau->nom = $request->nom;
        $niveau->nombre_enseignant = $request->nombre_enseignant;
        $niveau->nombre_classe = $request->nombre_classe;
        $niveau->nombre_eleve = $request->nombre_eleve;
        $niveau->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Niveau mis à jour avec succès',
            'data' => $niveau,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Niveau non trouvé',
            'error' => 'Aucun niveau ne correspond à cet ID',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour du niveau',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupérer le niveau par son ID
        $niveau = Niveau::findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Niveau trouvé avec succès',
            'data' => $niveau,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Niveau non trouvé',
            'error' => 'Aucun niveau ne correspond à cet ID',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération du niveau',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function index()
{
    try {
        // Récupérer tous les niveaux
        $niveaux = Niveau::all();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Niveaux récupérés avec succès',
            'data' => $niveaux,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des niveaux',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function destroy($id)
{
    try {
        // Récupérer le niveau par son ID
        $niveau = Niveau::findOrFail($id);

        // Supprimer le niveau
        $niveau->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Niveau supprimé avec succès',
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Niveau non trouvé',
            'error' => 'Aucun niveau ne correspond à cet ID',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du niveau',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
