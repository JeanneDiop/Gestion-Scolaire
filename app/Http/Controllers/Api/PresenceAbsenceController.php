<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PresenceAbsence;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\PresenceAbsence\CreatePresenceAbsenceRequest;
use App\Http\Requests\PresenceAbsence\UpdatePresenceAbsenceRequest;
class PresenceAbsenceController extends Controller
{
    public function store(CreatePresenceAbsenceRequest $request)
    {
        {
            try {
              $presenceabsence = new PresenceAbsence();
              $presenceabsence->present= $request->present;
              $presenceabsence->absent= $request->absent;
              $presenceabsence->date_present= $request->date_present;
              $presenceabsence->date_absent= $request->date_absent;
              $presenceabsence->raison_absence= $request->raison_absence;
              $presenceabsence->cours_id= $request->cours_id;
              $presenceabsence->apprenant_id= $request->apprenant_id;
              $presenceabsence->save();
              return response()->json([
                'status_code' => 200,
                'status_message' => 'presenceabsence a été ajouté',
                'data' => $presenceabsence,
              ],200);
            }  catch (Exception $e) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Erreur interne du serveur',
                    'error' => $e->getMessage(),
                ], 500);
          }
        }
    }
    public function update(UpdatePresenceAbsenceRequest $request, $id)
{
    try {

        $presenceabsence = PresenceAbsence::findOrFail($id);

        // Mise à jour des attributs
        $presenceabsence->present = $request->present;
        $presenceabsence->absent = $request->absent;
        $presenceabsence->date_present = $request->date_present;
        $presenceabsence->date_absent = $request->date_absent;
        $presenceabsence->raison_absence = $request->raison_absence;
        $presenceabsence->cours_id = $request->cours_id;
        $presenceabsence->apprenant_id = $request->apprenant_id;

        // Sauvegarde des modifications
        $presenceabsence->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'presenceabsence a été mis à jour',
            'data' => $presenceabsence,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'presenceabsence non trouvé',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function indexabsent()
{
    try {
        // Récupérer les enregistrements d'absences avec les relations nécessaires
        $absences = PresenceAbsence::with([
            'cours.enseignant.user',        // Récupère les informations du cours, de l'enseignant et de son utilisateur
            'apprenant.user',               // Récupère les informations de l'apprenant et de son utilisateur
            'apprenant.tuteur.user',        // Récupère les informations du tuteur de l'apprenant et de son utilisateur
            'apprenant.classe.salle'        // Récupère les informations de la classe de l'apprenant et de la salle associée
        ])
        ->where('absent', 'oui') // Filtrer uniquement les enregistrements d'absence
        ->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des absences récupérée avec succès.',
            'data' => $absences,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'ya pas  des apprenants absents.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function indexpresent()
{
    try {
        // Récupérer les enregistrements de présence avec les relations nécessaires
        $presences = PresenceAbsence::with([
            'cours.enseignant.user',        // Récupère les informations du cours, de l'enseignant et de son utilisateur
            'apprenant.user',               // Récupère les informations de l'apprenant et de son utilisateur
            'apprenant.tuteur.user',        // Récupère les informations du tuteur de l'apprenant et de son utilisateur
            'apprenant.classe.salle'   // Récupère les informations de l'apprenant et de son utilisateur
        ])
        ->where('present', 'oui') // Filtrer uniquement les enregistrements de présence
        ->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des apprenants présents récupérée avec succès.',
            'data' => $presences,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'ya pas d\'apprenants presents dans notre base.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showabsent($id)
{
    try {
        // Récupérer l'enregistrement d'absence par son ID, avec les relations nécessaires
        $absence = PresenceAbsence::with([
            'cours.enseignant.user',        // Récupère les informations du cours, de l'enseignant et de son utilisateur
            'apprenant.user',               // Récupère les informations de l'apprenant et de son utilisateur
            'apprenant.tuteur.user',        // Récupère les informations du tuteur de l'apprenant et de son utilisateur
            'apprenant.classe.salle'        // Récupère les informations de la classe de l'apprenant et de la salle associée
        ])
        ->where('absent', 'oui')  // Filtrer uniquement les absences
        ->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de l\'absence récupérés avec succès.',
            'data' => $absence,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Absence non trouvée.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur lors de la récupération des détails de l\'absence.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showpresent($id)
{
    try {
        // Récupérer l'enregistrement de présence par son ID, avec les relations nécessaires
        $presence = PresenceAbsence::with([
            'cours.enseignant.user',        // Récupère les informations du cours, de l'enseignant et de son utilisateur
            'apprenant.user',               // Récupère les informations de l'apprenant et de son utilisateur
            'apprenant.tuteur.user',        // Récupère les informations du tuteur de l'apprenant et de son utilisateur
            'apprenant.classe.salle'        // Récupère les informations de la classe de l'apprenant et de la salle associée
        ])
        ->where('absent', 'non')  // Filtrer uniquement les présences
        ->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la présence récupérés avec succès.',
            'data' => $presence,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Présence non trouvée.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur lors de la récupération des détails de la présence.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function index()
{
    try {
        // Récupérer tous les enregistrements de présence/absence avec les relations nécessaires
        $presencesAbsences = PresenceAbsence::with([
            'cours.enseignant.user',       // Récupère les informations du cours, de l'enseignant et de son utilisateur
            'apprenant.user',              // Récupère les informations de l'apprenant et de son utilisateur
            'apprenant.tuteur.user',       // Récupère les informations du tuteur de l'apprenant et de son utilisateur
            'apprenant.classe.salle'       // Récupère les informations de la classe de l'apprenant et de la salle associée
        ])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des présences et absences récupérée avec succès.',
            'data' => $presencesAbsences,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur lors de la récupération des présences et absences.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {

        $presenceabsence = PresenceAbsence::findOrFail($id);
        $presenceabsence->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Présence/absence supprimée avec succès.',
        ], 200);
    } catch (ModelNotFoundException $e) {

        return response()->json([
            'status_code' => 404,
            'status_message' => 'Présence/absence non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la suppression de la présence/absence.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
