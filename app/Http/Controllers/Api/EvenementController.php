<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evenement\CreateEvenementRequest;
use App\Http\Requests\Evenement\UpdateEvenementRequest;
use App\Models\Evenement;
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
            //if ($request->has('participant')) {
                // Extraire les IDs des participants
                //$participantIds = collect($request->participant)->pluck('id');

                // Attacher les participants à l'événement
                //$evenement->participants()->attach($participantIds);
            //}


            //if ($request->has('participant')) {
                //foreach ($request->participant as $participant) {
                    // Vérifier si l'élément a 'id' (utilisateur)
                    //if (isset($participant['id'])) {
                        // Attacher l'utilisateur à l'événement
                        //$evenement->participants()->attach($participant['id']);
                    //}

                    // Vérifier si l'élément a 'classe_id' (classe)
                    //if (isset($participant['classe_id'])) {
                        // Attacher la classe à l'événement
                        //$evenement->classes()->attach($participant['classe_id']);
                    //}
                //}
            //}
            if ($request->has('participant')) {
                foreach ($request->participant as $participant) {
                    // Vérifier si 'id' (utilisateur) est défini
                    if (isset($participant['id'])) {
                        $evenement->participants()->attach($participant['id'], [
                            'classe_id' => $participant['classe_id'] ?? null, // Ajouter classe_id si fourni
                        ]);
                    }
                }
            }

        // Réponse en cas de succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'événement a été ajouter avec succès.',
            'data' => $evenement,
            'participants_attaches' => $evenement->participants
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
