<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evenement\CreateEvenementRequest;
use App\Http\Requests\Evenement\UpdateEvenementRequest;
use App\Models\Evenement;
use App\Models\Classe;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
class EvenementController extends Controller
{
    public function store(CreateEvenementRequest $request)
    {
        try {
            $evenement = new Evenement();
            $evenement ->titre = $request->titre;
            $evenement ->description= $request->description ?? null;
            $evenement ->date_heure = $request->date_heure ?? null;
            $evenement ->lieu = $request->lieu ?? null;
            $evenement ->recurrence = $request->recurrence ?? null;
            $evenement ->ressource = $request->ressource ?? null;
            $evenement ->type_evenement = $request->type_evenement ?? null;
            $evenement ->responsable_id = $request->responsable_id ?? null;
            $evenement ->save();
            if ($request->has('participant')) {
                foreach ($request->participant as $participant) {

                    // Cas 1 : Si 'apprenant_id' est spécifié
                    if (isset($participant['apprenant_id'])) {
                        // Attacher l'apprenant avec 'user_id' et 'classe_id' = null
                        $evenement->participants()->attach($participant['apprenant_id'], [
                            'user_id' => $participant['apprenant_id'],  // 'user_id' est l'id de l'apprenant
                            'classe_id' => null,  // 'classe_id' est null
                        ]);
                    }

                    // Cas 2 : Si 'enseignant_id' est spécifié
                    elseif (isset($participant['enseignant_id'])) {
                        // Attacher l'enseignant avec 'user_id' et 'classe_id' = null
                        $evenement->participants()->attach($participant['enseignant_id'], [
                            'user_id' => $participant['enseignant_id'],  // 'user_id' est l'id de l'enseignant
                            'classe_id' => null,  // 'classe_id' est null
                        ]);
                    }

                    // Cas 3 : Si 'classe_id' est spécifié
                    elseif (isset($participant['classe_id'])) {
                        // Attacher la classe avec 'user_id' = null et 'classe_id' comme spécifié
                        $evenement->participants()->attach($participant['classe_id'], [
                            'user_id' => null,    // 'user_id' est null pour la classe
                            'classe_id' => $participant['classe_id'],  // 'classe_id' est la valeur spécifiée
                        ]);
                    }
                }
            }

            $evenement->load('responsable');
        // Réponse en cas de succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'événement a été ajouter avec succès.',
            'data' => $evenement,

        ],200);
    } catch (ModelNotFoundException $e) {
        // Réponse si l'événement n'est pas trouvé
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Événement non trouvé.',
        ]);
    } catch (Exception $e) {
        // Réponse en cas d'erreur générale
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de l\'événement.',
            'error' => $e->getMessage(),
        ]);
    }
}
public function update(UpdateEvenementRequest $request, $id)
{
    try {
        // Récupérer l'événement à mettre à jour
        $evenement = Evenement::findOrFail($id);

        // Mettre à jour les informations de l'événement
        $evenement->titre = $request->titre;
        $evenement->description = $request->description ?? null;
        $evenement->date_heure = $request->date_heure ?? null;
        $evenement->lieu = $request->lieu ?? null;
        $evenement->recurrence = $request->recurrence ?? null;
        $evenement->ressource = $request->ressource ?? null;
        $evenement->type_evenement = $request->type_evenement ?? null;
        $evenement->responsable_id = $request->responsable_id ?? null;
        $evenement->save();

        // Vérifier s'il y a des participants à ajouter ou modifier
        if ($request->has('participant')) {
            foreach ($request->participant as $participant) {

                // Cas 1 : Si 'apprenant_id' est spécifié
                if (isset($participant['apprenant_id'])) {
                    // Attacher ou mettre à jour l'apprenant
                    $evenement->participants()->syncWithoutDetaching([
                        $participant['apprenant_id'] => [
                            'user_id' => $participant['apprenant_id'],
                            'classe_id' => null,
                        ]
                    ]);
                }

                // Cas 2 : Si 'enseignant_id' est spécifié
                elseif (isset($participant['enseignant_id'])) {
                    // Chercher si l'enseignant est déjà attaché à cet événement
                    $existingParticipant = $evenement->participants()->where('user_id', $participant['enseignant_id'])->first();

                    if ($existingParticipant) {
                        // Si l'enseignant existe déjà mais avec un 'classe_id' attaché, on met à jour pour avoir 'user_id' et 'classe_id' = null
                        $existingParticipant->update([
                            'user_id' => $participant['enseignant_id'],
                            'classe_id' => null,  // Remplacer classe_id par null
                        ]);
                    } else {
                        // Sinon, ajouter l'enseignant en tant que participant
                        $evenement->participants()->attach($participant['enseignant_id'], [
                            'user_id' => $participant['enseignant_id'],
                            'classe_id' => null,
                        ]);
                    }
                }

                // Cas 3 : Si 'classe_id' est spécifié
                elseif (isset($participant['classe_id'])) {
                    // Chercher si une classe est déjà attachée
                    $existingParticipant = $evenement->participants()->where('classe_id', $participant['classe_id'])->first();

                    if ($existingParticipant) {
                        // Si la classe existe déjà, on la met à jour en fonction de l'id de la classe
                        $existingParticipant->update([
                            'user_id' => null,
                            'classe_id' => $participant['classe_id'],
                        ]);
                    } else {
                        // Sinon, ajouter la classe en tant que participant
                        $evenement->participants()->attach($participant['classe_id'], [
                            'user_id' => null,
                            'classe_id' => $participant['classe_id'],
                        ]);
                    }
                }
            }
        }

        // Charger la relation responsable si nécessaire
        $evenement->load('responsable');

        // Réponse en cas de succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'événement a été mis à jour avec succès.',
            'data' => $evenement,
        ], 200);

    } catch (ModelNotFoundException $e) {
        // Réponse si l'événement n'est pas trouvé
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Événement non trouvé.',
        ]);
    } catch (Exception $e) {
        // Réponse en cas d'erreur générale
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'événement.',
            'error' => $e->getMessage(),
        ]);
    }
}


public function show($id)
{
    try {
        // Recherche de l'événement par ID avec les informations des participants
        $evenement = Evenement::with('participants', 'responsable:id,nom,prenom,telephone,email,role_nom')
        ->findOrFail($id);
        $response = [
            'id' => $evenement->id,
            'titre' => $evenement->titre,
            'description' => $evenement->description,
            'date_heure' => $evenement->date_heure,
            'lieu' => $evenement->lieu,
            'recurrence' => $evenement->recurrence,
            'ressource' => $evenement->ressource,
            'type_evenement' => $evenement->type_evenement,
            'responsable_id' => $evenement->responsable_id,
            'responsable' => $evenement->responsable, // Inclure les détails du responsable
            'participants' => $evenement->participants,
        ];
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de l\'événement récupérés avec succès.',
            'data' => $response,
        ]);
    } catch (ModelNotFoundException $e) {
        // Réponse si l'événement n'est pas trouvé
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Événement non trouvé.',
        ]);
    } catch (Exception $e) {
        // Réponse en cas d'erreur générale
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des détails de l\'événement.',
            'error' => $e->getMessage(),
        ]);
    }
}
public function index()
{
    try {
        // Récupération de tous les événements avec leurs participants
        $evenements = Evenement::with('participants', 'responsable:id,nom,prenom,telephone,email,role_nom')->get();

        // Formatage de chaque événement dans un tableau
        $response = $evenements->map(function ($evenement) {
            return [
                'id' => $evenement->id,
                'titre' => $evenement->titre,
                'description' => $evenement->description,
                'date_heure' => $evenement->date_heure,
                'lieu' => $evenement->lieu,
                'recurrence' => $evenement->recurrence,
                'ressource' => $evenement->ressource,
                'type_evenement' => $evenement->type_evenement,
                'responsable_id' => $evenement->responsable_id,
                'responsable' => $evenement->responsable, // Inclure les détails du responsable
                'participants' => $evenement->participants,
            ];
        });

        // Réponse en cas de succès avec la liste des événements et leurs participants
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des événements récupérée avec succès.',
            'data' => $response,
        ]);
    } catch (Exception $e) {
        // Réponse en cas d'erreur générale
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la liste des événements.',
            'error' => $e->getMessage(),
        ]);
    }
}

public function destroy($id)
{
    try {
        // Recherche de l'événement par ID
        $evenement = Evenement::findOrFail($id);

        // Suppression de l'événement
        $evenement->delete();

        // Réponse en cas de succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'événement a été supprimé avec succès.',
        ]);
    } catch (ModelNotFoundException $e) {
        // Réponse si l'événement n'est pas trouvé
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Événement non trouvé.',
        ]);
    } catch (Exception $e) {
        // Réponse en cas d'erreur générale
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de l\'événement.',
            'error' => $e->getMessage(),
        ]);
    }
}

}
