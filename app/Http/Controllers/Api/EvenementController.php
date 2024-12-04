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
                    // Cas où un apprenant est défini
                    if (isset($participant['apprenant_id'])) {
                        $evenement->participants()->attach($participant['apprenant_id'], [
                            'classe_id' => $participant['classe_id'] ?? null,
                        ]);
                    }

                    // Cas où un enseignant est défini
                    if (isset($participant['enseignant_id'])) {
                        $evenement->participants()->attach($participant['enseignant_id'], [
                            'classe_id' => $participant['classe_id'] ?? null,
                        ]);
                    }

                    // Cas où une classe est définie
                    if (isset($participant['classe_id'])) {
                        // Traitez ici la logique pour les classes, comme attacher tous les membres de la classe
                        $classe = Classe::with('apprenants')->find($participant['classe_id']);
                        if ($classe) {
                            foreach ($classe->apprenants as $apprenant) {
                                $evenement->participants()->attach($apprenant->id, [
                                    'classe_id' => $participant['classe_id'],
                                ]);
                            }
                        }
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
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'événement.',
            'error' => $e->getMessage(),
        ]);
    }
}

public function update(UpdateEvenementRequest $request, $id)
{
    try {
        // Récupérer l'événement à mettre à jour
        $evenement = Evenement::findOrFail($id);

        // Mettre à jour les champs de l'événement
        $evenement->titre = $request->titre ?? $evenement->titre;
        $evenement->description = $request->description ?? $evenement->description;
        $evenement->date_heure = $request->date_heure ?? $evenement->date_heure;
        $evenement->lieu = $request->lieu ?? $evenement->lieu;
        $evenement->recurrence = $request->recurrence ?? $evenement->recurrence;
        $evenement->ressource = $request->ressource ?? $evenement->ressource;
        $evenement->type_evenement = $request->type_evenement ?? $evenement->type_evenement;
        $evenement->responsable_id = $request->responsable_id ?? $evenement->responsable_id;
        $evenement->save();

        // Gérer les participants
        if ($request->has('participant')) {
            foreach ($request->participant as $participant) {
                // Si le participant a un `classe_id`, on doit mettre `user_id` à null
                if (isset($participant['classe_id'])) {
                    // Si la classe est définie, on attache tous les apprenants de cette classe
                    $classe = Classe::with('apprenants')->find($participant['classe_id']);
                    if ($classe) {
                        foreach ($classe->apprenants as $apprenant) {
                            // Attacher chaque apprenant de la classe à l'événement avec `user_id` null
                            $evenement->participants()->attach($apprenant->id, [
                                'classe_id' => $participant['classe_id'],  // Lier à la classe
                            ]);
                        }
                    }
                } else {
                    // Cas où un apprenant ou un enseignant est défini (avec `user_id`)
                    if (isset($participant['apprenant_id']) || isset($participant['enseignant_id'])) {
                        $user_id = $participant['apprenant_id'] ?? $participant['enseignant_id'];
                        $classe_id = $participant['classe_id'] ?? null;

                        // Attacher le participant avec `user_id` et `classe_id` s'il est défini
                        $evenement->participants()->attach($user_id, [
                            'classe_id' => $classe_id,  // Attacher aussi la classe si définie
                        ]);
                    }
                }
            }
        }

        // Charger la relation responsable
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
        ], 404);
    } catch (Exception $e) {
        // Réponse en cas d'erreur générale
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'événement.',
            'error' => $e->getMessage(),
        ], 500);
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
