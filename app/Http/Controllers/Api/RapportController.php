<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Rapport\CreateRapportRequest;
use App\Http\Requests\Rapport\UpdateRapportRequest;
use App\Models\Rapport;

class RapportController extends Controller
{
    public function storeRapport(CreateRapportRequest $request)
    {
        try {
            // Démarrer la transaction
            DB::beginTransaction();
    
            // Créer une nouvelle instance de Rapport
            $rapport = new Rapport();
            $rapport->nom_rapport = $request->nom_rapport ?? null;
            $rapport->type_utilisateur = $request->type_utilisateur ?? null;
            $rapport->date_commentaire = $request->date_commentaire ?? null;
    
            // Vérification du type d'utilisateur et affectation des commentaires
            if ($request->type_utilisateur === 'apprenant') {
                if ($request->has('commentaire_apprenant') && $request->has('apprenant_id')) {
                    $rapport->commentaire_apprenant = $request->commentaire_apprenant;
                    $rapport->apprenant_id = $request->apprenant_id; // Assigner l'ID de l'apprenant
                    $rapport->commentaire_enseignant = null;
                    $rapport->enseignant_id = null;
                } else {
                    // Si le commentaire ou l'ID de l'apprenant est manquant
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Le commentaire de l\'apprenant et son ID sont requis.',
                    ], 400);
                }
            } elseif ($request->type_utilisateur === 'enseignant') {
                if ($request->has('commentaire_enseignant') && $request->has('enseignant_id')) {
                    $rapport->commentaire_enseignant = $request->commentaire_enseignant;
                    $rapport->enseignant_id = $request->enseignant_id; // Assigner l'ID de l'enseignant
                    $rapport->commentaire_apprenant = null;
                    $rapport->apprenant_id = null;
                } else {
                    // Si le commentaire ou l'ID de l'enseignant est manquant
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Le commentaire de l\'enseignant et son ID sont requis.',
                    ], 400);
                }
            } else {
                // Si le type d'utilisateur est invalide
                DB::rollBack();
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Le type d\'utilisateur est invalide.',
                ], 400);
            }
    
            // Enregistrer le rapport
            $rapport->save();
    
            // Commit de la transaction
            DB::commit();
    
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Le rapport a été enregistré avec succès.',
                'data' => $rapport,
            ], 200);
    
        } catch (\Exception $e) {
            // Rollback si une exception survient
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    

    public function updateRapport(UpdateRapportRequest $request, $id)
    {
        try {
            // Démarrer la transaction
            DB::beginTransaction();
    
            // Récupérer le rapport à mettre à jour
            $rapport = Rapport::find($id);
    
            if (!$rapport) {
                // Si le rapport n'existe pas
                return response()->json([
                    'status_code' => 404,
                    'status_message' => 'Le rapport avec l\'ID spécifié n\'a pas été trouvé.',
                ], 404);
            }
    
            // Mettre à jour les champs communs
            $rapport->nom_rapport = $request->nom_rapport ?? $rapport->nom_rapport;
            $rapport->type_utilisateur = $request->type_utilisateur ?? $rapport->type_utilisateur;
            $rapport->date_commentaire = $request->date_commentaire ?? $rapport->date_commentaire;
    
            // Vérification et mise à jour selon le type d'utilisateur
            if ($request->type_utilisateur === 'apprenant') {
                if ($request->has('commentaire_apprenant') && $request->has('apprenant_id')) {
                    $rapport->commentaire_apprenant = $request->commentaire_apprenant;
                    $rapport->apprenant_id = $request->apprenant_id;
                    $rapport->commentaire_enseignant = null;
                    $rapport->enseignant_id = null;
                } else {
                    // Si le commentaire ou l'ID de l'apprenant est manquant
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Le commentaire de l\'apprenant et son ID sont requis.',
                    ], 400);
                }
            } elseif ($request->type_utilisateur === 'enseignant') {
                if ($request->has('commentaire_enseignant') && $request->has('enseignant_id')) {
                    $rapport->commentaire_enseignant = $request->commentaire_enseignant;
                    $rapport->enseignant_id = $request->enseignant_id;
                    $rapport->commentaire_apprenant = null;
                    $rapport->apprenant_id = null;
                } else {
                    // Si le commentaire ou l'ID de l'enseignant est manquant
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Le commentaire de l\'enseignant et son ID sont requis.',
                    ], 400);
                }
            } else {
                // Si le type d'utilisateur est invalide
                DB::rollBack();
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Le type d\'utilisateur est invalide.',
                ], 400);
            }
    
            // Enregistrer les modifications
            $rapport->save();
    
            // Commit de la transaction
            DB::commit();
    
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Le rapport a été mis à jour avec succès.',
                'data' => $rapport,
            ], 200);
    
        } catch (\Exception $e) {
            // Rollback si une exception survient
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

public function showRapport($id)
{
    try {
        // Trouver le rapport par ID et charger les relations enseignant et apprenant
        $rapport = Rapport::with(['enseignant', 'apprenant'])->findOrFail($id);
        
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le rapport a été récupéré avec succès.',
            'data' => $rapport,
        ], 200);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Le rapport avec cet ID n\'existe pas.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function index()
{
    try {
        // Récupérer tous les rapports avec leurs relations enseignant et apprenant
        $rapports = Rapport::with(['enseignant', 'apprenant'])->get();
        
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Les rapports ont été récupérés avec succès.',
            'data' => $rapports,
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

          
}
