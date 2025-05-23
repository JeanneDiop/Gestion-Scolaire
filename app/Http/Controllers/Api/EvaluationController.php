<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evaluation\CreateEvaluationRequest;
use App\Http\Requests\Evaluation\UpdateEvaluationRequest;
use App\Models\Evaluation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Note;
use App\Models\Historique;
use App\Models\Apprenant;
use Carbon\Carbon;


class EvaluationController extends Controller
{
    public function store(CreateEvaluationRequest $request)
    {
        try {
            // Récupération des données validées
            $validatedData = $request->validated();


            // Création de l'évaluation
            $evaluation = new Evaluation();
            $evaluation->nom_evaluation = $validatedData['nom_evaluation'];
            $evaluation->niveau_education = $validatedData['niveau_education'];
            $evaluation->categorie = $validatedData['categorie'] ?? null;
            $evaluation->type_evaluation = $validatedData['type_evaluation'] ?? null;
            $evaluation->date_evaluation = $validatedData['date_evaluation'];
            $evaluation->cours_id = $validatedData['cours_id'];
            $evaluation->save();

            if ($request->has('classe_id') && $request->has('apprenant_id')) {
                $evaluation->classes()->attach($request->classe_id);
                $evaluation->apprenants()->attach($request->apprenant_id);
            }
            // Cas 2: Évaluation de la classe seulement
            elseif ($request->has('classe_id') && !$request->has('apprenant_id')) {
                $evaluation->classes()->attach($request->classe_id);
            }
            // Cas 3: Évaluation des apprenants seulement
            elseif (!$request->has('classe_id') && $request->has('apprenant_id')) {
                $evaluation->apprenants()->attach($request->apprenant_id);
            }

            $evaluation->load('classes', 'apprenants');
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
            // Récupération des données validées
            $validatedData = $request->validated();

            // Récupération de l'évaluation existante
            $evaluation = Evaluation::findOrFail($id);

            // Mise à jour des données de l'évaluation
            $evaluation->nom_evaluation = $validatedData['nom_evaluation'];
            $evaluation->niveau_education = $validatedData['niveau_education'];
            $evaluation->categorie = $validatedData['categorie'] ?? null;
            $evaluation->type_evaluation = $validatedData['type_evaluation'] ?? null;
            $evaluation->date_evaluation = $validatedData['date_evaluation'];
            $evaluation->cours_id = $validatedData['cours_id'];
            $evaluation->update();

            // Vérification et mise à jour des relations avec les classes et les apprenants
            $classeIds = $request->input('classe_id', []); // Utiliser un tableau vide par défaut
            $apprenantIds = $request->input('apprenant_id', []); // Utiliser un tableau vide par défaut

            // Mise à jour des relations dans la table pivot
            if (!empty($classeIds)) {
                $evaluation->classes()->sync($classeIds);
            }

            if (!empty($apprenantIds)) {
                $evaluation->apprenants()->sync($apprenantIds);
            }

            // Charger les relations avec les données pivot
            $evaluation->load('classes', 'apprenants');

            // Enregistrement dans l'historique
            Historique::create([
                'action' => 'update',
                'message' => 'Évaluation mise à jour : ' . $evaluation->nom_evaluation,
                'user_id' => auth()->id(),
                'evaluation_id' => $evaluation->id,
                'created_at' => Carbon::now(),
            ]);

            // Réponse avec l'évaluation mise à jour
            return response()->json([
                'status_code' => 200,
                'status_message' => 'L\'évaluation a été mise à jour avec succès.',
                'data' => $evaluation,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'évaluation.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function show($id)
{
    try {
        // Récupération d'une évaluation spécifique avec toutes les relations nécessaires
        $evaluation = Evaluation::with([
            'apprenants.user',            // Charger les apprenants et leurs informations utilisateur
            'apprenants.classe.salle',    // Charger la classe et la salle associée pour chaque apprenant
            'cours.enseignant.user',      // Charger le cours et l'enseignant avec ses informations utilisateur
            'classes.salle',              // Charger directement les classes associées et les salles
        ])->findOrFail($id);

        // Réponse avec les données de l'évaluation récupérée
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
            // Récupération des évaluations avec toutes les relations nécessaires
            $evaluations = Evaluation::with([
                'apprenants.user',
                'apprenants.classe.salle',
                'cours.enseignant.user',
                'classes.salle',
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
