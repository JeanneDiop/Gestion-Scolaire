<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use App\Models\Historique;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\DemandeMaintenance;
use App\Http\Requests\Maintenance\CreateMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateSuiviRequest;
class DemandeMaintenanceController extends Controller
{
    public function store(CreateMaintenanceRequest $request)
    {
        try {
            $maintenance = new DemandeMaintenance();
            $maintenance ->description = $request->description ?? null;
           $maintenance->status = $request->status ?? 'en_attente';
            $maintenance ->niveau_priorite = $request->niveau_priorite ?? 'moyen';
            $maintenance ->emplacement = $request->emplacement ?? null;
            $dateDemande = $request->date_demande ? Carbon::parse($request->date_demande) : Carbon::today();

            // On applique la validation que la date soit aujourd'hui ou dans le futur
            if ($dateDemande < Carbon::today()) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'La date de demande doit être aujourd\'hui ou dans le futur.',
                ], 400);
            }

            // Affectation de la date de demande
            $maintenance->date_demande = $dateDemande;
            $maintenance ->demandeur_id = $request->demandeur_id ?? null;
            $maintenance ->personnel_id = $request->personnel_id ?? null;
            $maintenance ->save();
            Historique::create([
                'action' => 'create',
                'message' => 'maintenance  ajouté : ' . $maintenance->description,
                'user_id' => auth()->id(),
                'maintenance _id' => $maintenance->id,
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'demandemaintenance a été ajoutée',
                'data' =>   $maintenance ,
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la demande de maintenance  ',
                'error' => $e->getMessage(),
            ],500);
        }
    }


    public function update(UpdateMaintenanceRequest $request, $id)
{
    try {
        // Récupérer la demande de maintenance existante
        $maintenance = DemandeMaintenance::findOrFail($id);

        // Mise à jour des champs
        $maintenance ->description = $request->description ?? null;
        $maintenance->status = $request->status ?? 'en_attente';
        $maintenance ->niveau_priorite = $request->niveau_priorite ?? 'moyen';
        $maintenance ->emplacement = $request->emplacement ?? null;
        $dateDemande = $request->date_demande ? Carbon::parse($request->date_demande) : Carbon::today();

        // On applique la validation que la date soit aujourd'hui ou dans le futur
        if ($dateDemande < Carbon::today()) {
            return response()->json([
                'status_code' => 400,
                'status_message' => 'La date de demande doit être aujourd\'hui ou dans le futur.',
            ], 400);
        }

        // Affectation de la date de demande
        $maintenance->date_demande = $dateDemande;
        $maintenance ->demandeur_id = $request->demandeur_id ?? null;
        $maintenance ->personnel_id = $request->personnel_id ?? null;

        // Sauvegarde des modifications
        $maintenance->update();
        Historique::create([
            'action' => 'update',
            'message' => 'demande de maintenance   mis à jour avec succés : ' . $maintenance->description,
            'user_id' => auth()->id(),
            'maintenance_id' => $maintenance->id,
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Demande de maintenance mise à jour avec succès',
            'data' => $maintenance,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Demande de maintenance introuvable',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la demande de maintenance',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Recherche de la demande de maintenance par son ID avec les relations
        $maintenance = DemandeMaintenance::with(['demandeur', 'personnel'])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Demande de maintenance récupérée avec succès',
            'data' => $maintenance,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Demande de maintenance introuvable',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la demande de maintenance',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function index()
{
    try {
        // Récupération de toutes les demandes de maintenance avec les relations
        $maintenances = DemandeMaintenance::with(['demandeur', 'personnel'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des demandes de maintenance récupérée avec succès',
            'data' => $maintenances,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des demandes de maintenance',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Recherche de la demande de maintenance par son ID
        $maintenance = DemandeMaintenance::findOrFail($id);

        // Suppression de la demande de maintenance
        $maintenance->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Demande de maintenance supprimée avec succès',
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Demande de maintenance introuvable',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la demande de maintenance',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function suivi(UpdateSuiviRequest $request, $id)
{
    try {
        // Récupérer la demande de maintenance existante
        $maintenance = DemandeMaintenance::findOrFail($id);

        // Mise à jour des champs de suivi
        $maintenance->status = $request->status ?? $maintenance->status; // Si le status est passé, on le met à jour
        $dateResolution = $request->date_resolution ? Carbon::parse($request->date_resolution) : Carbon::today();

            // On applique la validation que la date soit aujourd'hui ou dans le futur
            if ($dateResolution < Carbon::today()) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'La date de resolution doit être aujourd\'hui ou dans le futur.',
                ], 400);
            }

            // Affectation de la date de demande
            $maintenance->date_resolution = $dateResolution; // Si une date de résolution est passée, on l'utilise, sinon on met la date d'aujourd'hui
        $maintenance->commentaire = $request->commentaire ?? null; // Si un commentaire est passé, on l'ajoute

        // Sauvegarde des modifications
        $maintenance->save();

        // Enregistrement de l'historique
        Historique::create([
            'action' => 'suivi',
            'message' => 'Suivi de la maintenance mis à jour : ' . $maintenance->description,
            'user_id' => auth()->id(),
            'maintenance_id' => $maintenance->id,
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Suivi de la demande de maintenance mis à jour avec succès',
            'data' => $maintenance,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Demande de maintenance introuvable',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour du suivi de la demande de maintenance',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
