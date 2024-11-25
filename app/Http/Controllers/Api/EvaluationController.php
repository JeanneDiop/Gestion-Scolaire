<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evaluation\CreateEvaluationRequest;
use App\Http\Requests\Evaluation\UpdateEvaluationRequest;
use App\Models\Evaluation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Note;
use App\Models\Historique;
use Carbon\Carbon;


class EvaluationController extends Controller
{
    public function stores(CreateEvaluationRequest $request)
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
            Historique::create([
                'action' => 'create',  // Action 'update' pour la modification
                'message' => 'Evaluation ajouté : ' . $evaluation->nom,
                'user_id' => auth()->id(), // ID de l'utilisateur authentifié
                'evaluation_id' => $evaluation->id,  // ID de la salle modifiée
                'created_at' => Carbon::now(),
            ]);

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
    public function store(CreateEvaluationRequest $request)
    {
        try {
            // Récupération des données validées
            $validatedData = $request->validated();
            $apprenants = $validatedData['apprenant_id']; // Tableau d'IDs d'apprenants

            // Création de l'évaluation
            $evaluation = new Evaluation();
            $evaluation->nom_evaluation = $validatedData['nom_evaluation'];
            $evaluation->niveau_education = $validatedData['niveau_education'];
            $evaluation->categorie = $validatedData['categorie'];
            $evaluation->type_evaluation = $validatedData['type_evaluation'];
            $evaluation->date_evaluation = $validatedData['date_evaluation'];
            $evaluation->cours_id = $validatedData['cours_id'];
            $evaluation->save();

            // Ajouter les relations dans la table pivot
            foreach ($apprenants as $apprenantId) {
                // Récupérer la classe (peut être null)
                $classeId = $validatedData['classe_id'] ?? null; // Utilisation de null si 'classe_id' n'est pas défini

                // Si 'apprenant_id' est fourni, l'attacher à l'évaluation
                if ($apprenantId !== null) {
                    // Si 'classe_id' est fourni ou nul, l'attacher dans la table pivot
                    $evaluation->apprenants()->attach($apprenantId, ['classe_id' => $classeId]);
                }
            }

            // Enregistrement dans l'historique
            Historique::create([
                'action' => 'create',
                'message' => 'Évaluation ajoutée : ' . $evaluation->nom_evaluation,
                'user_id' => auth()->id(),
                'evaluation_id' => $evaluation->id,
                'created_at' => Carbon::now(),
            ]);

            // Réponse avec l'évaluation créée
            return response()->json([
                'status_code' => 200,
                'status_message' => 'L\'évaluation a été ajoutée avec succès.',
                'data' => $evaluation,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de l\'évaluation.',
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
        Historique::create([
            'action' => 'update',
            'message' => 'Evaluation modifiée : ' . $evaluation->nom,
            'user_id' => auth()->id(),
            'evaluation_id' => $evaluation->id,
            'created_at' => Carbon::now(),
        ]);

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
        $evaluation = Evaluation::with([
            'apprenant.user',
            'apprenant.classe.salle',
            'cours.enseignant.user'
        ])->findOrFail($id);

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

        $evaluations = Evaluation::with([
            'apprenant.user',
            'apprenant.classe.salle',
            'cours.enseignant.user'
        ])->get();


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

        $evaluation = Evaluation::findOrFail($id);
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
