<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\ProgrammeClasse;
use App\Models\Cours;
use App\Models\Competence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseRequest;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseRequest;
class ProgrammeClasseController extends Controller
{
    public function store(CreateProgrammeClasseRequest $request)
    {
        try {
            $programme = new ProgrammeClasse();
            $programme->nom = $request->nom;
            $programme->niveau_education = $request->niveau_education;
            $programme->description= $request->description;
            $programme->periode= $request->periode;
            $programme->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'ProgrammeClasse a été ajoutée',
                'data' => $programme,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la programmeclasse',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(UpdateProgrammeClasseRequest $request, $id)
{
    try {
        // Trouver le programme de classe par son ID
        $programme = ProgrammeClasse::findOrFail($id);
        $programme->nom = $request->nom;
        $programme->niveau_education = $request->niveau_education;
        $programme->description = $request->description;
        $programme->periode = $request->periode;
        $programme->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse a été mise à jour',
            'data' => $programme,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'ProgrammeClasse non trouvée',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la programmeclasse',
            'error' => $e->getMessage(),
        ]);
    }
}


public function storeProgrammeCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Création du ProgrammeClasse
        $programme_classe = new ProgrammeClasse();
        $programme_classe->nom = $request->nom;
        $programme_classe->niveau_education = $request->niveau_education;
        $programme_classe->niveau_classe = $request->niveau_classe;
        $programme_classe->cycle = $request->cycle;
        $programme_classe->annee_scolaire = $request->annee_scolaire;
        $programme_classe->langue_enseignee = $request->langue_enseignee;
        $programme_classe->objectif_generaux = $request->objectif_generaux;
        $programme_classe->objectif_specifiques = $request->objectif_specifiques;
        $programme_classe->bareme = $request->bareme;
        $programme_classe->frequence_evaluation = $request->frequence_evaluation;
        $programme_classe->type_evaluation = $request->type_evaluation ;
        $programme_classe->importer_programme = $request->importer_programme ;
        $programme_classe->exporter_programme = $request->exporter_programme;
        $programme_classe->save();

        // Boucle pour ajouter chaque cours et ses compétences
        foreach ($request->cours as $coursData) {
            // Création du cours
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $coursData['niveau_education'];
            $cours->niveau_classe = $coursData['niveau_classe'];
            $cours->heure_allouee = $coursData['heure_allouee'];
            $cours->dureee_recommander_sceance = $coursData['duree_recommander_sceance'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'] ?? null;
            $cours->coefficient = $coursData['coefficient'] ?? null;
            $cours->semestre = $coursData['semestre'] ?? null;
            $cours->categorie_cours = $coursData['categorie_cours'];
            $cours->enseignant_id = $coursData['enseignant_id'];
            $cours->programme_classe_id = $programme_classe->id;
            $cours->save();

            // Boucle pour ajouter les compétences spécifiques à ce cours
            if (isset($coursData['competences'])) {
                foreach ($coursData['competences'] as $competenceData) {
                    $competence = new Competence();
                    $competence->nom = $competenceData['nom'];
                    $competence->description = $competenceData['description'];
                    $competence->cours_id = $cours->id;
                    $competence->save();
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme_classe->load('cours.competences');

        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse, cours et compétences ont été ajoutés avec succès',
            'data' => [
                'programmeclasse' => [
                    'id' => $programme_classe->id,
                    'nom' => $programme_classe->nom,
                    'niveau_classe' => $programme_classe->niveau_classe,
                    'niveau_education' => $programme_classe->niveau_education,
                    'description' => $programme_classe->description,
                    'periode' => $programme_classe->periode,
                    'cours' => $programme_classe->cours,
                ],
            ],
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function updateProgrammeCours($id, UpdateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Récupération du programme de classe à mettre à jour
        $programme_classe = ProgrammeClasse::findOrFail($id);
        $programme_classe->nom = $request->nom;
        $programme_classe->niveau_education = $request->niveau_education;
        $programme_classe->niveau_classe = $request->niveau_classe;
        $programme_classe->description = $request->description;
        $programme_classe->periode = $request->periode;
        $programme_classe->update();

        foreach ($request->cours as $coursData) {

            if (isset($coursData['id'])) {
                $cours = Cours::find($coursData['id']);
                if ($cours) {
                    $cours->nom = $coursData['nom'];
                    $cours->description = $coursData['description'] ?? null;
                    $cours->niveau_education = $coursData['niveau_education'];
                    $cours->heure_allouée = $coursData['heure_allouée'];
                    $cours->etat = $coursData['etat'] ?? 'encours';
                    $cours->credits = $coursData['credits'] ?? null;
                    $cours->coefficient = $coursData['coefficient'] ?? null;
                    $cours->semestre = $coursData['semestre'];
                    $cours->enseignant_id = $coursData['enseignant_id'];
                    $cours->programme_classe_id = $programme_classe->id;
                    $cours->update();
                }
            } else {
                // Si le cours n'a pas d'ID, on peut le créer
                $cours = new Cours();
                $cours->nom = $coursData['nom'];
                $cours->description = $coursData['description'] ?? null;
                $cours->niveau_education = $coursData['niveau_education'];
                $cours->heure_allouée = $coursData['heure_allouée'];
                $cours->etat = $coursData['etat'] ?? 'encours';
                $cours->credits = $coursData['credits'] ?? null;
                $cours->coefficient = $coursData['coefficient'] ?? null;
                $cours->semestre = $coursData['semestre'];
                $cours->enseignant_id = $coursData['enseignant_id'];
                $cours->programme_classe_id = $programme_classe->id;
                $cours->save();
            }
        }

        DB::commit();
        $programme_classe->load('cours');

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le programme de classe et les cours ont été mis à jour avec succès.',
            'data' => [
                'programmeclasse' => [
                    'id' => $programme_classe->id,
                    'nom' => $programme_classe->nom,
                    'niveau_classe' => $programme_classe->niveau_classe,
                    'niveau_education' => $programme_classe->niveau_education,
                    'description' => $programme_classe->description,
                    'periode' => $programme_classe->periode,
                    'cours' => $programme_classe->cours,
                ],
            ],
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la classe et des cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        $programme_classe = ProgrammeClasse::with([
            'classes.salle',
        ])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails du programme de classe récupérés avec succès.',
            'data' => [
                'programmeclasse' => [
                    'id' => $programme_classe->id,
                    'nom' => $programme_classe->nom,
                    'niveau_classe' => $programme_classe->niveau_classe,
                    'niveau_education' => $programme_classe->niveau_education,
                    'description' => $programme_classe->description,
                    'periode' => $programme_classe->periode,
                    'cours' => $programme_classe->cours ? $programme_classe->cours->map(function($cours) {
                        return [
                            'id' => $cours->id,
                            'nom' => $cours->nom,
                            'description' => $cours->description,
                            'niveau_education' => $cours->niveau_education,
                            'heure_allouée' => $cours->heure_allouée,
                            'etat' => $cours->etat,
                            'credits' => $cours->credits,
                            'coefficient' => $cours->coefficient,
                            'semestre' => $cours->semestre,
                        ];
                    }) : [],
                    'classes' => $programme_classe->classes ? $programme_classe->classes->map(function ($classe) {
                        return [
                            'id' => $classe->id,
                            'nom' => $classe->nom,
                            'niveau_classe' => $classe->niveau_classe,
                            'niveau_education' => $classe->niveau_education,
                            'salle' => $classe->salle ? [
                                'id' => $classe->salle->id,
                                'nom' => $classe->salle->nom,
                                'capacity' => $classe->salle->capacity,
                                'type' => $classe->salle->type,
                            ] : null,
                        ];
                    }) : [],
                ],
            ],
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération du programme de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function index()
{
    try {
        $programmes_classe = ProgrammeClasse::with([
            'cours',
            'classes.salle',
        ])->get();
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des programmes de classe récupérée avec succès.',
            'data' => $programmes_classe->map(function($programme_classe) {
                return [
                    'id' => $programme_classe->id,
                    'nom' => $programme_classe->nom,
                    'niveau_classe' => $programme_classe->niveau_classe,
                    'niveau_education' => $programme_classe->niveau_education,
                    'description' => $programme_classe->description,
                    'periode' => $programme_classe->periode,
                    'cours' => $programme_classe->cours ? $programme_classe->cours->map(function($cours) {
                        return [
                            'id' => $cours->id,
                            'nom' => $cours->nom,
                            'description' => $cours->description,
                            'niveau_education' => $cours->niveau_education,
                            'heure_allouée' => $cours->heure_allouée,
                            'etat' => $cours->etat,
                            'credits' => $cours->credits,
                            'coefficient' => $cours->coefficient,
                            'semestre' => $cours->semestre,
                        ];
                    }) : [],
                    'classes' => $programme_classe->classes ? $programme_classe->classes->map(function ($classe) {
                        return [
                            'id' => $classe->id,
                            'nom' => $classe->nom,
                            'niveau_classe' => $classe->niveau_classe,
                            'niveau_education' => $classe->niveau_education,
                            'salle' => $classe->salle ? [
                                'id' => $classe->salle->id,
                                'nom' => $classe->salle->nom,
                                'capacity' => $classe->salle->capacity,
                                'type' => $classe->salle->type,
                            ] : null,
                        ];
                    }) : [],  // Si la relation 'classes' est null, retourner un tableau vide
                ];
            }),
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la liste des programmes de classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        $programme = ProgrammeClasse::findOrFail($id);
        $programme->delete();
        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse supprimé avec succès',
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'ProgrammeClasse introuvable',
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du ProgrammeClasse',
            'error' => $e->getMessage(),
        ]);
    }
}
}
