<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Planifiercour;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Planifiercour\CreatePlanifiercourRequest;
use App\Http\Requests\Planifiercour\UpdatePlanifiercourRequest;
use Exception;
class PlanifiercourController extends Controller
{
    public function store(CreatePlanifiercourRequest $request)
    {
        {
            try {
              $planifiercour = new Planifiercour();
              $planifiercour->date_cours= $request->date_cours;
              $planifiercour->heure_debut= $request->heure_debut;
              $planifiercour->heure_fin= $request->heure_fin;
              $planifiercour->jour_semaine= $request->jour_semaine;
              $planifiercour->duree= $request->duree;
              $planifiercour->statut= $request->statut ?? 'prévu';
              $planifiercour->annee_scolaire = $request->annee_scolaire;
              $planifiercour->semestre = $request->semestre;
              $planifiercour->cours_id = $request->cours_id;
              $planifiercour->classe_id = $request->classe_id;
              $planifiercour->save();
              return response()->json([
                'status_code' => 200,
                'status_message' => 'planifiercour a été ajouté',
                'data' => $planifiercour,
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

    public function update(UpdatePlanifiercourRequest $request, $id)
{
    try {
       
        $planifiercour = Planifiercour::findOrFail($id);


        $planifiercour->date_cours = $request->date_cours;
        $planifiercour->heure_debut = $request->heure_debut;
        $planifiercour->heure_fin = $request->heure_fin;
        $planifiercour->jour_semaine = $request->jour_semaine;
        $planifiercour->duree = $request->duree;
        $planifiercour->statut= $request->statut ?? 'prévu';
        $planifiercour->annee_scolaire = $request->annee_scolaire;
        $planifiercour->semestre = $request->semestre;
        $planifiercour->cours_id = $request->cours_id;
        $planifiercour->classe_id = $request->classe_id;

        // Enregistrement des modifications
        $planifiercour->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le cours planifié a été mis à jour avec succès.',
            'data' => $planifiercour,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur lors de la mise à jour.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function show($id)
{
    try {
        // Récupération de la planification de cours avec les relations nécessaires
        $planifiercour = Planifiercour::with([
            'cours.enseignant.user',
            'classe.salle' // Accès à la classe et salle via planifiercour
        ])->findOrFail($id);

        $planifiercourData = [
            'id' => $planifiercour->id,
            'date_cours' => $planifiercour->date_cours,
            'heure_debut' => $planifiercour->heure_debut,
            'heure_fin' => $planifiercour->heure_fin,
            'jour_semaine' => $planifiercour->jour_semaine,
            'duree' => $planifiercour->duree,
            'statut' => $planifiercour->statut,
            'annee_scolaire' => $planifiercour->annee_scolaire,
            'semestre' => $planifiercour->semestre,

            // Informations du cours
            'cours' => $planifiercour->cours ? [
                'id' => $planifiercour->cours->id,
                'nom' => $planifiercour->cours->nom,
                'description' => $planifiercour->cours->description,
                'niveau_education' => $planifiercour->cours->niveau_education,
                'type' => $planifiercour->cours->type,
                'duree' => $planifiercour->cours->duree,
                'credits' => $planifiercour->cours->credits,

                // Informations de l'enseignant
                'enseignant' => $planifiercour->cours->enseignant ? [
                    'id' => $planifiercour->cours->enseignant->id,
                    'specialite' => $planifiercour->cours->enseignant->specialite,
                    'statut_marital' => $planifiercour->cours->enseignant->statut_marital,
                    'date_naissance' => $planifiercour->cours->enseignant->date_naissance,
                    'lieu_naissance' => $planifiercour->cours->enseignant->lieu_naissance,
                    'image' => $planifiercour->cours->enseignant->image,
                    'numero_CNI' => $planifiercour->cours->enseignant->numero_CNI,
                    'numero_securite_social' => $planifiercour->cours->enseignant->numero_securite_social,
                    'statut' => $planifiercour->cours->enseignant->statut,
                    'date_embauche' => $planifiercour->cours->enseignant->date_embauche,
                    'date_fin' => $planifiercour->cours->enseignant->date_fin,

                    // Informations de l'utilisateur associé à l'enseignant
                    'user' => $planifiercour->cours->enseignant->user ? [
                        'id' => $planifiercour->cours->enseignant->user->id,
                        'nom' => $planifiercour->cours->enseignant->user->nom,
                        'prenom' => $planifiercour->cours->enseignant->user->prenom,
                        'email' => $planifiercour->cours->enseignant->user->email,
                        'telephone' => $planifiercour->cours->enseignant->user->telephone,
                        'genre' => $planifiercour->cours->enseignant->user->genre,
                        'adresse' => $planifiercour->cours->enseignant->user->adresse,
                        'etat' => $planifiercour->cours->enseignant->user->etat,
                    ] : null,
                ] : null,
            ] : null,

            // Informations de la classe associée à la planification de cours
            'classe' => $planifiercour->classe ? [
                'id' => $planifiercour->classe->id,
                'nom' => $planifiercour->classe->nom,
                'niveau_classe' => $planifiercour->classe->niveau_classe,

                // Informations de la salle associée à la classe
                'salle' => $planifiercour->classe->salle ? [
                    'id' => $planifiercour->classe->salle->id,
                    'nom' => $planifiercour->classe->salle->nom,
                    'capacity' => $planifiercour->classe->salle->capacity,
                ] : null,
            ] : null,
        ];

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Planification de cours récupérée avec succès.',
            'data' => $planifiercourData,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Planification de cours non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    }
}





public function index()
{
    try {
        // Récupérer toutes les planifications de cours avec les relations nécessaires
        $planifications = Planifiercour::with([
            'cours.enseignant.user',        // Récupère l'utilisateur associé à l'enseignant du cours
            'classe.salle'                  // Récupère la salle associée à la classe
        ])->get();

        // Structurer les données à retourner
        $planificationsData = $planifications->map(function($planification) {
            return [
                'id' => $planification->id,
                'date_cours' => $planification->date_cours,
                'heure_debut' => $planification->heure_debut,
                'heure_fin' => $planification->heure_fin,
                'jour_semaine' => $planification->jour_semaine,
                'duree' => $planification->duree,
                'statut' => $planification->statut,
                'annee_scolaire' => $planification->annee_scolaire,
                'semestre' => $planification->semestre,

                // Informations du cours
                'cours' => $planification->cours ? [
                    'id' => $planification->cours->id,
                    'nom' => $planification->cours->nom,
                    'description' => $planification->cours->description,
                    'niveau_education' => $planification->cours->niveau_education,
                    'type' => $planification->cours->type,
                    'duree' => $planification->cours->duree,
                    'credits' => $planification->cours->credits,

                    // Informations de l'enseignant
                    'enseignant' => $planification->cours->enseignant ? [
                        'id' => $planification->cours->enseignant->id,
                        'specialite' => $planification->cours->enseignant->specialite,
                        'statut_marital' => $planification->cours->enseignant->statut_marital,
                        'date_naissance' => $planification->cours->enseignant->date_naissance,
                        'lieu_naissance' => $planification->cours->enseignant->lieu_naissance,
                        'image' => $planification->cours->enseignant->image,
                        'numero_CNI' => $planification->cours->enseignant->numero_CNI,
                        'numero_securite_social' => $planification->cours->enseignant->numero_securite_social,
                        'statut' => $planification->cours->enseignant->statut,
                        'date_embauche' => $planification->cours->enseignant->date_embauche,
                        'date_fin' => $planification->cours->enseignant->date_fin,

                        // Informations de l'utilisateur associé à l'enseignant
                        'user' => $planification->cours->enseignant->user ? [
                            'id' => $planification->cours->enseignant->user->id,
                            'nom' => $planification->cours->enseignant->user->nom,
                            'prenom' => $planification->cours->enseignant->user->prenom,
                            'email' => $planification->cours->enseignant->user->email,
                            'telephone' => $planification->cours->enseignant->user->telephone,
                            'genre' => $planification->cours->enseignant->user->genre,
                            'adresse' => $planification->cours->enseignant->user->adresse,
                            'etat' => $planification->cours->enseignant->user->etat,
                        ] : null,
                    ] : null,
                ] : null,

                // Informations de la classe associée
                'classe' => $planification->classe ? [
                    'id' => $planification->classe->id,
                    'nom' => $planification->classe->nom,
                    'niveau_classe' => $planification->classe->niveau_classe,

                    // Informations de la salle associée
                    'salle' => $planification->classe->salle ? [
                        'id' => $planification->classe->salle->id,
                        'nom' => $planification->classe->salle->nom,
                        'capacity' => $planification->classe->salle->capacity,
                    ] : null,
                ] : null,
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des planifications de cours récupérée avec succès.',
            'data' => $planificationsData,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des planifications de cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Récupération de la planification de cours par ID
        $planifiercour = Planifiercour::findOrFail($id);

        // Suppression de la planification de cours
        $planifiercour->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Planification de cours supprimée avec succès.',
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Planification de cours non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la planification de cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
