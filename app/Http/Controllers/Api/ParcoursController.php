<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Parcours;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Parcours\CreateParcoursRequest;
use App\Http\Requests\Parcours\UpdateParcoursRequest;
class ParcoursController extends Controller
{
    public function store(CreateParcoursRequest $request)
    {
        try {
            $parcours = new Parcours();
            $parcours ->nom = $request->nom;
            $parcours ->description = $request->description;
            $parcours ->credits = $request->credits;
            $parcours ->date_creation = $request->date_creation;
            $parcours ->date_modification = $request->date_modification;
            $parcours ->apprenant_id = $request->apprenant_id;
            $parcours ->programme_id = $request->programme_id;
            $parcours ->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'parcours a été ajoutée',
                'data' => $parcours ,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du parcours',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(UpdateParcoursRequest $request, $id)
{
    try {
        $parcours = Parcours::findOrFail($id);

        $parcours->nom = $request->nom;
        $parcours->description = $request->description;
        $parcours->credits = $request->credits;
        $parcours->date_modification = now();
        $parcours->apprenant_id = $request->apprenant_id;
        $parcours->programme_id = $request->programme_id;
        $parcours->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Parcours mis à jour avec succès',
            'data' => $parcours,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour du parcours',
            'error' => $e->getMessage(),
        ]);
    }
}
public function show($id)
{
    try {
        $parcours = Parcours::with([
            'apprenant.user',
            'apprenant.classe.salle', // Relation pour récupérer la salle à partir de la classe
            'programme.cours.enseignant.user' // Relation pour récupérer l'enseignant et son utilisateur
        ])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails du parcours récupérés avec succès',
            'data' => $parcours,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des détails du parcours',
            'error' => $e->getMessage(),
        ]);
    }
}

public function index()
{
    try {
        // Récupérer tous les parcours avec les informations associées
        $parcours = Parcours::with([
            'apprenant.user',                   // Récupérer l'utilisateur associé à l'apprenant
            'apprenant.classe.salle',           // Récupérer la salle associée à la classe de l'apprenant
            'programme.cours.enseignant.user'   // Récupérer l'utilisateur associé à l'enseignant
        ])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Tous les parcours récupérés avec succès',
            'data' => $parcours,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des parcours',
            'error' => $e->getMessage(),
        ]);
    }
}


public function destroy($id)
{
    try {
        // Récupérer le parcours avec les relations apprenant et programme
        $parcours = Parcours::findOrFail($id);

        // Supprimer le parcours
        $parcours->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Parcours supprimé avec succès',
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du parcours',
            'error' => $e->getMessage(),
        ]);
    }
}

}
