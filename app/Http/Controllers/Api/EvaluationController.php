<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evaluation\CreateEvaluationRequest;
use App\Http\Requests\Evaluation\UpdateEvaluationRequest;
use App\Models\Evaluation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class EvaluationController extends Controller
{
    public function store(CreateEvaluationRequest $request)
    {
        try {
            $evaluation = new Evaluation();
            $evaluation->nom_evaluation = $request->nom_evaluation;
            $evaluation->niveau_education = $request->niveau_education;
            $evaluation->categorie = $request->categorie;
            $evaluation->type_evaluation = $request->type_evaluation;
            $evaluation->date_evaluation = $request->date_evaluation;
            $evaluation->apprenant_id = $request->apprenant_id;
            $evaluation->cours_id = $request->cours_id;
            $evaluation->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'evaluation a été ajoutée',
                'data' => $evaluation,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du evaluations',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function update(UpdateEvaluationRequest $request, $id)
{
    try {
        // Trouver l'évaluation par ID
        $evaluation = Evaluation::findOrFail($id);

        // Mettre à jour les champs de l'évaluation
        $evaluation->nom_evaluation = $request->nom_evaluation;
        $evaluation->niveau_education = $request->niveau_education;
        $evaluation->categorie = $request->categorie;
        $evaluation->type_evaluation = $request->type_evaluation;
        $evaluation->date_evaluation = $request->date_evaluation;
        $evaluation->apprenant_id = $request->apprenant_id;
        $evaluation->cours_id = $request->cours_id;
        $evaluation->save();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Évaluation a été mise à jour avec succès',
            'data' => $evaluation,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Évaluation non trouvée',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'évaluation',
            'error' => $e->getMessage(),
        ]);
    }
}

public function show($id)
{
    try {
        // Récupérer l'évaluation avec les relations apprenant, classe, salle, cours et enseignant
        $evaluation = Evaluation::with([
            'apprenant.user', // Récupérer l'apprenant et ses informations utilisateur
            'apprenant.classe.salle', // Récupérer la classe et la salle associée à l'apprenant
            'cours.enseignant.user' // Récupérer l'enseignant du cours et ses informations utilisateur
        ])->findOrFail($id);

        // Retourner les informations de l'évaluation
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de l\'évaluation récupérés avec succès',
            'data' => $evaluation,
        ]);
    } catch (\Exception $e) {
        // Gérer les erreurs
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des détails de l\'évaluation',
            'error' => $e->getMessage(),
        ]);
    }
}
public function index()
{
    try {
        // Récupérer toutes les évaluations avec les relations apprenant, classe, salle, cours, et enseignant
        $evaluations = Evaluation::with([
            'apprenant.user', // Récupérer les informations de l'apprenant et de l'utilisateur associé
            'apprenant.classe.salle', // Récupérer la classe et la salle associée à l'apprenant
            'cours.enseignant.user' // Récupérer le cours et l'enseignant du cours avec son utilisateur
        ])->get(); // Récupère toutes les évaluations

        // Retourner les informations des évaluations
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des évaluations récupérée avec succès',
            'data' => $evaluations,
        ]);
    } catch (\Exception $e) {
        // Gérer les erreurs
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des évaluations',
            'error' => $e->getMessage(),
        ]);
    }
}


public function destroy($id)
{
    try {
        // Trouver l'évaluation par son ID
        $evaluation = Evaluation::findOrFail($id);

        // Supprimer l'évaluation
        $evaluation->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Évaluation supprimée avec succès',
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Évaluation non trouvée',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de l\'évaluation',
            'error' => $e->getMessage(),
        ]);
    }
}

}
