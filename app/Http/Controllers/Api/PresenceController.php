<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Historique;
use Carbon\Carbon;
use App\Http\Requests\Presence\CreatePresenceRequest;
use App\Http\Requests\Presence\UpdatePresenceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class PresenceController extends Controller
{
    public function storePresence(CreatePresenceRequest $request)
    {
        try {
            // Démarrer la transaction
            DB::beginTransaction();

            // Log des données de la requête reçue
            Log::info('Données reçues pour la présence/absence:', $request->all());

            // Créer une nouvelle instance de PresenceAbsence
            $presenceAbsence = new Presence();
            $presenceAbsence->type_utilisateur = $request->type_utilisateur;
            $presenceAbsence->statut = $request->statut;
            $presenceAbsence->cours_id = $request->cours_id;

            // Traitement en fonction du statut
            if ($presenceAbsence->statut === 'present') {
                $presenceAbsence->date_present = $request->date_present ?? null;
            } elseif ($presenceAbsence->statut === 'retard') {
                $presenceAbsence->heure_arrivee = $request->heure_arrivee ?? null;
                $presenceAbsence->duree_retard = $request->duree_retard ?? null;
            } elseif ($presenceAbsence->statut === 'absent') {
                $presenceAbsence->date_absent = $request->date_absent ?? null;
                $presenceAbsence->raison_absence = $request->raison_absence ?? null;
            } else {
                // Log d'un statut invalide
                Log::warning('Statut non valide reçu:', ['statut' => $request->statut]);

                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Statut non valide.',
                ], 400);
            }

            // Assignation de l'ID de l'utilisateur en fonction du type
            if ($presenceAbsence->type_utilisateur === 'apprenant') {
                $presenceAbsence->apprenant_id = $request->apprenant_id ?? null;
            } elseif ($presenceAbsence->type_utilisateur === 'enseignant') {
                $presenceAbsence->enseignant_id = $request->enseignant_id ?? null;
            }

            // Sauvegarder l'objet PresenceAbsence dans la base de données
            $presenceAbsence->save();
            Historique::create([
                'action' => 'create',  // Action 'update' pour la modification
                'message' => 'Presence ajouté : ' . $presenceAbsence->nom,
                'user_id' => auth()->id(), // ID de l'utilisateur authentifié
                'presence_id' => $presenceAbsence->id,  // ID de la salle modifiée
                'created_at' => Carbon::now(),
            ]);

            // Log après l'enregistrement réussi
            Log::info('Présence/Absence enregistrée avec succès', ['presenceAbsence' => $presenceAbsence]);

            // Commit de la transaction
            DB::commit();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'La présence/absence a été enregistrée avec succès.',
                'data' => $presenceAbsence,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log des erreurs de validation
            DB::rollBack();
            Log::error('Erreur de validation', ['errors' => $e->validator->errors()]);

            return response()->json([
                'status_code' => 422,
                'status_message' => 'Erreur de validation.',
                'errors' => $e->validator->errors()->toArray(),
            ], 422);
        } catch (\Exception $e) {
            // Log de l'exception
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement de la présence/absence', ['error' => $e->getMessage()]);

            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement de la présence/absence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function updatePresence(UpdatePresenceRequest $request, $id)
{
    try {
        // Démarrer la transaction
        DB::beginTransaction();

        // Récupérer l'enregistrement de présence/absence existant
        $presenceAbsence = Presence::findOrFail($id);

        // Log des données de la requête reçue
        Log::info('Données reçues pour la mise à jour de la présence/absence:', $request->all());

        // Mettre à jour les champs généraux
        $presenceAbsence->type_utilisateur = $request->type_utilisateur;
        $presenceAbsence->statut = $request->statut;
        $presenceAbsence->cours_id = $request->cours_id;

        // Mise à jour des champs en fonction du statut
        if ($presenceAbsence->statut === 'present') {
            $presenceAbsence->date_present = $request->date_present ?? null;
        } elseif ($presenceAbsence->statut === 'retard') {
            $presenceAbsence->heure_arrivee = $request->heure_arrivee ?? null;
            $presenceAbsence->duree_retard = $request->duree_retard ?? null;
        } elseif ($presenceAbsence->statut === 'absent') {
            $presenceAbsence->date_absent = $request->date_absent ?? null;
            $presenceAbsence->raison_absence = $request->raison_absence ?? null;
        } else {
            // Log d'un statut invalide
            Log::warning('Statut non valide reçu pour la mise à jour:', ['statut' => $request->statut]);

            return response()->json([
                'status_code' => 400,
                'status_message' => 'Statut non valide.',
            ], 400);
        }

        // Mise à jour de l'ID de l'utilisateur en fonction du type
        if ($presenceAbsence->type_utilisateur === 'apprenant') {
            $presenceAbsence->apprenant_id = $request->apprenant_id ?? null;
            $presenceAbsence->enseignant_id = null; // Réinitialiser le champ non pertinent
        } elseif ($presenceAbsence->type_utilisateur === 'enseignant') {
            $presenceAbsence->enseignant_id = $request->enseignant_id ?? null;
            $presenceAbsence->apprenant_id = null;
        }

        // Sauvegarder les modifications dans la base de données
        $presenceAbsence->save();
        Historique::create([
            'action' => 'update',
            'message' => 'Presence modifiée : ' . $presenceAbsence->nom,
            'user_id' => auth()->id(),
            'presence_id' => $presenceAbsence->id,
            'created_at' => Carbon::now(),
        ]);
        // Log après la mise à jour réussie
        Log::info('Présence/Absence mise à jour avec succès', ['presenceAbsence' => $presenceAbsence]);

        // Commit de la transaction
        DB::commit();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'La présence/absence a été mise à jour avec succès.',
            'data' => $presenceAbsence,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log des erreurs de validation
        DB::rollBack();
        Log::error('Erreur de validation lors de la mise à jour', ['errors' => $e->validator->errors()]);

        return response()->json([
            'status_code' => 422,
            'status_message' => 'Erreur de validation.',
            'errors' => $e->validator->errors()->toArray(),
        ], 422);
    } catch (\Exception $e) {
        // Log de l'exception
        DB::rollBack();
        Log::error('Erreur lors de la mise à jour de la présence/absence', ['error' => $e->getMessage()]);

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la mise à jour de la présence/absence.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function index()
{
    try {
        // Récupérer toutes les présences/absences
        $presencesAbsences = Presence::all();

        // Retourner une réponse JSON avec les données récupérées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des présences/absences récupérée avec succès.',
            'data' => $presencesAbsences,
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des présences/absences.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function indexPresent()
{
    try {
        // Récupérer toutes les présences avec le statut "present"
        $presences = Presence::where('statut', 'present')->get();

        // Retourner une réponse JSON avec les données récupérées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des présences récupérées avec succès pour le statut "present".',
            'data' => $presences,
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des présences pour le statut "present".',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function indexAbsent()
{
    try {
        // Récupérer toutes les présences avec le statut "present"
        $presences = Presence::where('statut', 'absent')->get();

        // Retourner une réponse JSON avec les données récupérées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des présences récupérées avec succès pour le statut "absent".',
            'data' => $presences,
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des présences pour le statut "absent".',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function show($id)
{
    try {
        // Récupérer la présence/absence par son ID
        $presenceAbsence = Presence::find($id);

        // Vérifier si la présence/absence existe
        if (!$presenceAbsence) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Présence/absence non trouvée.',
            ], 404);
        }

        // Retourner une réponse JSON avec les données de la présence/absence
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la présence/absence récupérés avec succès.',
            'data' => $presenceAbsence,
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des détails de la présence/absence.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function showpresent($id)
{
    try {
        // Récupérer la présence par son ID avec le statut "present"
        $presenceAbsence = Presence::where('id', $id)->where('statut', 'present')->first();

        // Vérifier si la présence avec le statut "present" existe
        if (!$presenceAbsence) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Présence avec le statut "present" non trouvée.',
            ], 404);
        }

        // Retourner une réponse JSON avec les données de la présence
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la présence avec le statut "present" récupérés avec succès.',
            'data' => $presenceAbsence,
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des détails de la présence avec le statut "present".',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showabsent($id)
{
    try {
        // Récupérer l'absence par son ID avec le statut "absent"
        $presenceAbsence = Presence::where('id', $id)->where('statut', 'absent')->first();

        // Vérifier si l'absence avec le statut "absent" existe
        if (!$presenceAbsence) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Absence avec le statut "absent" non trouvée.',
            ], 404);
        }

        // Retourner une réponse JSON avec les données de l'absence
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de l\'absence avec le statut "absent" récupérés avec succès.',
            'data' => $presenceAbsence,
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des détails de l\'absence avec le statut "absent".',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Récupérer la présence/absence par son ID
        $presenceAbsence = Presence::find($id);

        // Vérifier si la présence/absence existe
        if (!$presenceAbsence) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Présence/absence non trouvée.',
            ], 404);
        }

        // Supprimer la présence/absence
        $presenceAbsence->delete();

        // Retourner une réponse JSON confirmant la suppression
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Présence/absence supprimée avec succès.',
        ], 200);

    } catch (\Exception $e) {
        // Gestion de l'exception
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la suppression de la présence/absence.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
