<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evenement\CreateEvenementRequest;
use App\Http\Requests\Evenement\UpdateEvenementRequest;
use App\Models\Evenement;
use App\Models\Enseignant;
use App\Models\Apprenant;
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
            if ($request->lieu == 'Salle') {
                $evenement->salle_id = $request->salle_id ?? null;
            }

            // Enregistrer le lieu_exterieur si le lieu est "Exterieur"
            if ($request->lieu == 'Exterieur') {
                $evenement->lieu_exterieur = $request->lieu_exterieur ?? null;
            }

            // Enregistrer le lien_evenement si le lieu est "En ligne"
            if ($request->lieu == 'En ligne') {
                $evenement->lien_evenement = $request->lien_evenement ?? null;
            }
            $evenement ->recurrence = $request->recurrence ?? null;
            $evenement ->ressource = $request->ressource ?? null;
            $evenement ->type_evenement = $request->type_evenement ?? null;
            $evenement ->responsable_id = $request->responsable_id ?? null;
            $evenement ->save();
            if ($request->has('participant')) {
                foreach ($request->participant as $participant) {
                    // Cas 1 : Si 'apprenant_id' est spécifié
                    if (isset($participant['apprenant_id'])) {
                        // Trouver l'apprenant par son ID
                        $apprenant = Apprenant::find($participant['apprenant_id']);
                        if ($apprenant) {
                            // On récupère l'ID de l'utilisateur associé à l'apprenant
                            $user_id = $apprenant->user_id; // user_id de l'apprenant dans la table 'users'

                            // Attacher l'apprenant avec le bon user_id dans la table pivot
                            $evenement->participants()->attach($apprenant->id, [
                                'user_id' => $user_id,  // Utilise l'user_id de l'apprenant trouvé
                                'classe_id' => null,  // classe_id peut rester null
                            ]);
                        } else {
                            // L'apprenant n'existe pas
                            throw new Exception('L\'apprenant sélectionné n\'existe pas.');
                        }
                    }

                    // Cas 2 : Si 'enseignant_id' est spécifié
                    elseif (isset($participant['enseignant_id'])) {
                        // Trouver l'enseignant par son ID
                        $enseignant = Enseignant::find($participant['enseignant_id']);
                        if ($enseignant) {
                            // On récupère l'ID de l'utilisateur associé à l'enseignant
                            $user_id = $enseignant->user_id; // user_id de l'enseignant dans la table 'users'

                            // Attacher l'enseignant avec le bon user_id dans la table pivot
                            $evenement->participants()->attach($enseignant->id, [
                                'user_id' => $user_id,  // Utilise l'user_id de l'enseignant trouvé
                                'classe_id' => null,  // classe_id peut rester null
                            ]);
                        } else {
                            // L'enseignant n'existe pas
                            throw new Exception('L\'enseignant sélectionné n\'existe pas.');
                        }
                    }

                    // Cas 3 : Si 'classe_id' est spécifié
                    elseif (isset($participant['classe_id'])) {
                        // Attacher la classe avec 'user_id' = null et 'classe_id' comme spécifié
                        $evenement->participants()->attach($participant['classe_id'], [
                            'user_id' => null,    // 'user_id' est null pour la classe
                            'classe_id' => $participant['classe_id'],  // utilise la classe_id spécifiée
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
        if ($request->lieu == 'Salle') {
            $evenement->salle_id = $request->salle_id ?? null;
        }

        // Enregistrer le lieu_exterieur si le lieu est "Exterieur"
        if ($request->lieu == 'Exterieur') {
            $evenement->lieu_exterieur = $request->lieu_exterieur ?? null;
        }

        // Enregistrer le lien_evenement si le lieu est "En ligne"
        if ($request->lieu == 'En ligne') {
            $evenement->lien_evenement = $request->lien_evenement ?? null;
        }
        $evenement->recurrence = $request->recurrence ?? null;
        $evenement->ressource = $request->ressource ?? null;
        $evenement->type_evenement = $request->type_evenement ?? null;
        $evenement->responsable_id = $request->responsable_id ?? null;
        $evenement->save();

        // Vérifier s'il y a des participants à ajouter ou modifier
         if ($request->has('participant')) {
            // Supprimer tous les participants existants pour cet événement avant de les ajouter/modifier
            $evenement->participants()->detach();

            // Ajouter ou mettre à jour les participants dans la table pivot
            foreach ($request->participant as $participant) {
                // Cas 1 : Si 'classe_id' est spécifié
                if (isset($participant['classe_id'])) {
                    $evenement->participants()->attach($participant['classe_id'], [
                        'user_id' => null,
                        'classe_id' => $participant['classe_id'],
                        'evenement_id' => $evenement->id, // Assurer que l'événement_id est bien stocké
                    ]);
                }

                // Cas 2 : Si 'enseignant_id' est spécifié
                elseif (isset($participant['enseignant_id'])) {
                    $evenement->participants()->attach($participant['enseignant_id'], [
                        'user_id' => $participant['enseignant_id'],
                        'classe_id' => null,
                        'evenement_id' => $evenement->id, // Assurer que l'événement_id est bien stocké
                    ]);
                }

                // Cas 3 : Si 'apprenant' est spécifié
                elseif (isset($participant['apprenant'])) {
                    $evenement->participants()->attach($participant['apprenant'], [
                        'user_id' => $participant['apprenant'],
                        'classe_id' => null,
                        'evenement_id' => $evenement->id, // Assurer que l'événement_id est bien stocké
                    ]);
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
        // Recherche de l'événement par ID avec les informations des participants et la table pivot
        $evenement = Evenement::with('participants.classe') // Charger les participants
            ->findOrFail($id);

        // Préparer la réponse
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
            'participants' => $evenement->participants->map(function ($participant) {
                return [
                    'user_id' => $participant->pivot->user_id,  // Récupérer user_id de la table pivot
                    'classe_id' => $participant->pivot->classe_id,  // Récupérer classe_id de la table pivot
                    'id' => $participant->id,
                    'nom' => $participant->nom ?? null,  // Nom de l'utilisateur
                    'prenom' => $participant->prenom ?? null,
                    'email' => $participant->email ?? null,
                    'adresse' => $participant->adresse ?? null,
                    'etat' => $participant->etat ?? null,
                    'role_nom' => $participant->role_nom ?? null,
                    'nom' => $participant->classe->nom ?? null,
                    'niveau_classe' => $participant->classe->niveau_classe ?? null,  // Récupérer niveau_classe depuis la relation classe
                    'niveau_education' => $participant->classe->niveau_education ?? null,  // Récupérer le rôle
                ];
            }),
        ];

        // Retourner la réponse en JSON
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
