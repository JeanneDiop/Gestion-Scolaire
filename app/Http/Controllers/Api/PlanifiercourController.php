<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Planifiercour;
use App\Http\Requests\Planifiercour\CreatePlanifiercourRequest;
use App\Http\Requests\Planifiercour\UpdatePlanifiercourRequest;
use Exception;
class PlanifiercourController extends Controller
{
    public function store(CreatePlanifiercourRequest $request)
    {
        {
            try {
              $planifiercour = new Planifiercour();
              $planifiercour->date_cours= $request->date_cours;
              $planifiercour->heure_debut= $request->heure_debut;
              $planifiercour->heure_fin= $request->heure_fin;
              $planifiercour->jour_semaine= $request->jour_semaine;
              $planifiercour->duree= $request->duree;
              $planifiercour->statut= $request->statut;
              $planifiercour->annee_scolaire = $request->annee_scolaire;
              $planifiercour->semestre = $request->semestre;
              $planifiercour->cours_id = $request->cours_id;
              $planifiercour->classe_id = $request->classe_id;
              $planifiercour->save();
              return response()->json([
                'status_code' => 200,
                'status_message' => 'planifiercour a été ajouté',
                'data' => $planifiercour,
              ],200);
            }  catch (Exception $e) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Erreur interne du serveur',
                    'error' => $e->getMessage(),
                ], 500);
          }
        }
    }

    public function update(UpdatePlanifiercourRequest $request, $id)
{
    try {
        // Récupération du cours à mettre à jour
        $planifiercour = Planifiercour::findOrFail($id);

        // Mise à jour des champs
        $planifiercour->date_cours = $request->date_cours;
        $planifiercour->heure_debut = $request->heure_debut;
        $planifiercour->heure_fin = $request->heure_fin;
        $planifiercour->jour_semaine = $request->jour_semaine;
        $planifiercour->duree = $request->duree;
        $planifiercour->statut = $request->statut;
        $planifiercour->annee_scolaire = $request->annee_scolaire;
        $planifiercour->semestre = $request->semestre;
        $planifiercour->cours_id = $request->cours_id;
        $planifiercour->classe_id = $request->classe_id;

        // Enregistrement des modifications
        $planifiercour->save();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le cours planifié a été mis à jour avec succès.',
            'data' => $planifiercour,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur lors de la mise à jour.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function show($id)
{
    try {
        // Récupération du planifier de cours par ID avec les relations
        $planifiercour = Planifiercour::with(['classe.salle', 'cours.enseignant.user'])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Planification de cours récupérée avec succès.',
            'data' => $planifiercour,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Planification de cours non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    }
}

public function index()
{
    try {

        $planifiercours = Planifiercour::with(['classe.salle', 'cours.enseignant.user'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des planifications de cours récupérée avec succès.',
            'data' => $planifiercours,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des planifications de cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {

        $planifiercour = Planifiercour::findOrFail($id);

        $planifiercour->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le cours planifié a été supprimé avec succès.',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du cours planifié.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
