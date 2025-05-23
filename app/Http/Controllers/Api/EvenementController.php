<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Evenement\CreateEvenementRequest;
use App\Http\Requests\Evenement\UpdateEvenementRequest;
use App\Models\Evenement;
use App\Models\Enseignant;
use App\Models\Apprenant;
use App\Models\Classe;
use Carbon\Carbon;
use App\Models\Historique;
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
            Historique::create([
                'action' => 'create',
                'message' => 'Événement ajouté : ' . $evenement->titre,
                'user_id' => auth()->id(),
                'evenement_id' => $evenement->id,
                'created_at' => Carbon::now(),
            ]);
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

            $evenement->load([
                'responsable',
                'participants' => function ($query) {
                    $query->withPivot('classe_id');  // Inclure 'classe_id' dans le pivot
                },
                'classes'
            ]);

            // Structurer la réponse avec les informations de l'événement et des participants
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
                'responsable' => $evenement->responsable,  // Inclure les détails du responsable
                'participants' => [],  // Initialiser un tableau pour les participants
            ];

            // Ajouter les participants (utilisateurs) à la réponse
            foreach ($evenement->participants as $participant) {
                if ($participant->pivot->user_id) {
                    // Si c'est un utilisateur, on l'ajoute avec les détails
                    $response['participants'][] = [
                        'user_id' => $participant->pivot->user_id,
                        'classe_id' => null,  // Pas de classe pour cet utilisateur
                        'id' => $participant->id,
                        'nom' => $participant->nom,
                        'prenom' => $participant->prenom,
                        'adresse' => $participant->adresse,
                        'email' => $participant->email,
                        'etat' => $participant->etat,
                        'role_nom' => $participant->role_nom,
                    ];
                }
            }

            // Ajouter les classes à la réponse
            foreach ($evenement->classes as $classe) {
                $response['participants'][] = [
                    'user_id' => null,  // Pas d'utilisateur pour cette entrée
                    'classe_id' => $classe->id,
                    'nom' => $classe->nom,
                    'niveau_classe' => $classe->niveau_classe,
                    'niveau_education' => $classe->niveau_education,
                ];
            }
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
        // Trouver l'événement à mettre à jour
        $evenement = Evenement::findOrFail($id);

        // Mettre à jour les informations de l'événement
        $evenement->titre = $request->titre;
        $evenement->description = $request->description ?? null;
        $evenement->date_heure = $request->date_heure ?? null;
        $evenement->lieu = $request->lieu ?? null;

        // Mettre à jour le lieu spécifique si nécessaire
        if ($request->lieu == 'Salle') {
            $evenement->salle_id = $request->salle_id ?? null;
        }

        if ($request->lieu == 'Exterieur') {
            $evenement->lieu_exterieur = $request->lieu_exterieur ?? null;
        }

        if ($request->lieu == 'En ligne') {
            $evenement->lien_evenement = $request->lien_evenement ?? null;
        }

        // Mettre à jour les autres champs
        $evenement->recurrence = $request->recurrence ?? null;
        $evenement->ressource = $request->ressource ?? null;
        $evenement->type_evenement = $request->type_evenement ?? null;
        $evenement->responsable_id = $request->responsable_id ?? null;
        $evenement->save();
        Historique::create([
            'action' => 'update',
            'message' => 'Événement  mis à jour avec succés : ' . $evenement->titre,
            'user_id' => auth()->id(),
            'evenement_id' => $evenement->id,
            'created_at' => Carbon::now(),
        ]);

        // Mise à jour des participants
        if ($request->has('participant')) {
            // On commence par vider les participants existants
            $evenement->participants()->detach();

            foreach ($request->participant as $participant) {
                // Cas 1 : Si 'apprenant_id' est spécifié
                if (isset($participant['apprenant_id'])) {
                    $apprenant = Apprenant::find($participant['apprenant_id']);
                    if ($apprenant) {
                        $user_id = $apprenant->user_id;  // user_id de l'apprenant
                        $evenement->participants()->attach($apprenant->id, [
                            'user_id' => $user_id,
                            'classe_id' => null,  // classe_id peut rester null
                        ]);
                    } else {
                        throw new Exception('L\'apprenant sélectionné n\'existe pas.');
                    }
                }

                // Cas 2 : Si 'enseignant_id' est spécifié
                elseif (isset($participant['enseignant_id'])) {
                    $enseignant = Enseignant::find($participant['enseignant_id']);
                    if ($enseignant) {
                        $user_id = $enseignant->user_id;  // user_id de l'enseignant
                        $evenement->participants()->attach($enseignant->id, [
                            'user_id' => $user_id,
                            'classe_id' => null,  // classe_id peut rester null
                        ]);
                    } else {
                        throw new Exception('L\'enseignant sélectionné n\'existe pas.');
                    }
                }

                // Cas 3 : Si 'classe_id' est spécifié
                elseif (isset($participant['classe_id'])) {
                    $evenement->participants()->attach($participant['classe_id'], [
                        'user_id' => null,  // Pas d'utilisateur pour cette entrée
                        'classe_id' => $participant['classe_id'],  // Utilisation de la classe spécifiée
                    ]);
                }
            }
        }

        // Charger les relations nécessaires
        $evenement->load([
            'responsable',
            'participants' => function ($query) {
                $query->withPivot('classe_id');  // Inclure 'classe_id' dans le pivot
            },
            'classes'
        ]);

        // Structurer la réponse
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
            'responsable' => $evenement->responsable,
            'participants' => [],
        ];

        // Ajouter les participants à la réponse
        foreach ($evenement->participants as $participant) {
            if ($participant->pivot->user_id) {
                $response['participants'][] = [
                    'user_id' => $participant->pivot->user_id,
                    'classe_id' => null,
                    'id' => $participant->id,
                    'nom' => $participant->nom,
                    'prenom' => $participant->prenom,
                    'adresse' => $participant->adresse,
                    'email' => $participant->email,
                    'etat' => $participant->etat,
                    'role_nom' => $participant->role_nom,
                ];
            }
        }

        // Ajouter les classes à la réponse
        foreach ($evenement->classes as $classe) {
            $response['participants'][] = [
                'user_id' => null,
                'classe_id' => $classe->id,
                'nom' => $classe->nom,
                'niveau_classe' => $classe->niveau_classe,
                'niveau_education' => $classe->niveau_education,
            ];
        }

        // Réponse en cas de succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'événement a été mis à jour avec succès.',
            'data' => $response,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Événement non trouvé.',
        ], 404);
    } catch (Exception $e) {
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
        // Récupérer l'événement avec ses participants et les classes via la table pivot
        $evenement = Evenement::with(['participants' => function ($query) {
            $query->withPivot('classe_id');  // Inclure 'classe_id' dans le pivot
        }, 'classes'])->findOrFail($id);

        // Structurer la réponse avec les participants et leurs classes
        $response = [
            'id' => $evenement->id,
            'titre' => $evenement->titre,
            'description' => $evenement->description,
            'date_heure' => $evenement->date_heure,
            'lieu' => $evenement->lieu,
            'recurrence' => $evenement->recurrence,
            'ressource' => $evenement->ressource,
            'type_evenement' => $evenement->type_evenement,
            'salle_id' => $evenement->salle_id,
            'lieu_exterieur' => $evenement->lieu_exterieur,
            'lien_evenement' => $evenement->lien_evenement,
            'responsable_id' => $evenement->responsable_id,
            'responsable' => $evenement->responsable,
            'participants' => [],
        ];

        // Ajouter les utilisateurs à la réponse
        foreach ($evenement->participants as $participant) {
            if ($participant->pivot->user_id) {
                // Si c'est un utilisateur, on l'ajoute avec les détails
                $response['participants'][] = [
                    'user_id' => $participant->pivot->user_id,
                    'classe_id' => null,  // Pas de classe pour cet utilisateur
                    'id' => $participant->id,
                    'nom' => $participant->nom,
                    'prenom' => $participant->prenom,
                    'adresse' => $participant->adresse,
                    'email' => $participant->email,
                    'etat' => $participant->etat,
                    'role_nom' => $participant->role_nom,
                ];
            }
        }

        // Ajouter les classes à la réponse
        foreach ($evenement->classes as $classe) {
            $response['participants'][] = [
                'user_id' => null,  // Pas d'utilisateur pour cette entrée
                'classe_id' => $classe->id,
                'nom' => $classe->nom,
                'niveau_classe' => $classe->niveau_classe,
                'niveau_education' => $classe->niveau_education,
            ];
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de l\'événement récupérés avec succès.',
            'data' => $response,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Événement non trouvé.',
        ]);
    } catch (Exception $e) {
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
        // Récupérer tous les événements avec leurs participants et leurs classes via la table pivot
        $evenements = Evenement::with(['participants' => function ($query) {
            $query->withPivot('classe_id');  // Inclure 'classe_id' dans le pivot
        }, 'classes'])->get();

        // Structurer la réponse pour chaque événement
        $response = $evenements->map(function ($evenement) {
            $eventData = [
                'id' => $evenement->id,
                'titre' => $evenement->titre,
                'description' => $evenement->description,
                'date_heure' => $evenement->date_heure,
                'lieu' => $evenement->lieu,
                'recurrence' => $evenement->recurrence,
                'ressource' => $evenement->ressource,
                'type_evenement' => $evenement->type_evenement,
                'salle_id' => $evenement->salle_id,
                'lieu_exterieur' => $evenement->lieu_exterieur,
                'lien_evenement' => $evenement->lien_evenement,
                'responsable_id' => $evenement->responsable_id,
                'responsable' => $evenement->responsable,  // Inclure les détails du responsable
                'participants' => [],
            ];

            // Ajouter les utilisateurs à la réponse
            foreach ($evenement->participants as $participant) {
                if ($participant->pivot->user_id) {
                    // Si c'est un utilisateur, on l'ajoute avec les détails
                    $eventData['participants'][] = [
                        'user_id' => $participant->pivot->user_id,
                        'classe_id' => null,  // Pas de classe pour cet utilisateur
                        'id' => $participant->id,
                        'nom' => $participant->nom,
                        'prenom' => $participant->prenom,
                        'adresse' => $participant->adresse,
                        'email' => $participant->email,
                        'etat' => $participant->etat,
                        'role_nom' => $participant->role_nom,
                    ];
                }
            }

            // Ajouter les classes à la réponse
            foreach ($evenement->classes as $classe) {
                $eventData['participants'][] = [
                    'user_id' => null,  // Pas d'utilisateur pour cette entrée
                    'classe_id' => $classe->id,
                    'nom' => $classe->nom,
                    'niveau_classe' => $classe->niveau_classe,
                    'niveau_education' => $classe->niveau_education,
                ];
            }

            return $eventData;
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des événements récupérée avec succès.',
            'data' => $response,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des événements.',
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
