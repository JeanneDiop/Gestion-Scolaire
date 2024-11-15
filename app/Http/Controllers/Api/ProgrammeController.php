<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Programme;
use App\Models\Cours;
use App\Models\Competence;
use App\Models\CategorieCours;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseRequest;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseRequest;
class ProgrammeController extends Controller
{
    

public function updateProgrammeCours(UpdateProgrammeClasseCoursRequest $request, $id)
{
    try {
        DB::beginTransaction();

        // Récupération du programme à mettre à jour
        $programme = Programme::findOrFail($id);
        $programme->nom = $request->nom;
        $programme->niveau_education = $request->niveau_education;
        $programme->niveau_classe = $request->niveau_classe;
        $programme->cycle = $request->cycle ?? null;
        $programme->annee_scolaire = $request->annee_scolaire;
        $programme->langue_enseignee = $request->langue_enseignee ?? null;
        $programme->classe_id = $request->classe_id ?? null;
        $programme->save();

        // Mise à jour ou ajout des cours et leurs catégories/compétences
        foreach ($request->cours as $coursData) {
            // Récupération ou création du cours
            $cours = isset($coursData['id']) ? Cours::find($coursData['id']) : new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $coursData['niveau_education'];
            $cours->niveau_classe = $coursData['niveau_classe'];
            $cours->heure_allouee = $coursData['heure_allouee'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'] ?? null;
            $cours->coefficient = $coursData['coefficient'] ?? null;
            $cours->semestre = $coursData['semestre'] ?? null;
            $cours->enseignant_id = $coursData['enseignant_id'] ?? null;
            $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
            $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
            $cours->programme_id = $programme->id;
            $cours->save();

            // Mise à jour ou ajout des catégories (compétences) à ce cours
            if (isset($coursData['categories'])) {
                foreach ($coursData['categories'] as $categorieData) {
                    // Validation du barème pour chaque niveau d'éducation
                    $bareme = $categorieData['bareme'] ?? null;
                    $this->validateBareme($coursData['niveau_education'], $bareme);

                    // Récupération ou création de la catégorie de cours
                    $categorie = isset($categorieData['id']) ? CategorieCours::find($categorieData['id']) : new CategorieCours();
                    $categorie->nom = $categorieData['nom'];
                    $categorie->cours_id = $cours->id;
                    $categorie->volume_horaire = $categorieData['volume_horaire'] ?? null;
                    $categorie->type_exercices = $categorieData['type_exercices'] ?? null;
                    $categorie->leçons = $categorieData['leçons'] ?? null;
                    $categorie->duree_seance = $categorieData['duree_seance'] ?? null;
                    $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? null;
                    $categorie->heure_debut = $categorieData['heure_debut'] ?? null;
                    $categorie->heure_fin = $categorieData['heure_fin'] ?? null;
                    $categorie->frequence_evaluation = $categorieData['frequence_evaluation'] ?? null;
                    $categorie->bareme = $bareme;
                    $categorie->save();

                    // Mise à jour ou ajout des compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            // Récupération ou création de la compétence
                            $competence = isset($competenceData['id']) ? Competence::find($competenceData['id']) : new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_cours_id = $categorie->id;
                            $competence->save();
                        }
                    }
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme = $programme->fresh()->load(['cours.categories.competences']);
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme, cours et compétences ont été mis à jour avec succès',
            'data' => [
                'programme' => $programme,
            ]
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour du programme, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupérer un programme de classe spécifique avec ses relations associées
        $programme = Programme::where('source', 'manuel')
            ->with([
                'classe', // Classe associée au programme
                'cours.programme', // Cours associés au programme
                'cours.categories', // Accéder aux catégories via le modèle Cours
            ])
            ->findOrFail($id); // Utilisation de findOrFail pour obtenir un 404 si non trouvé

        // Formatage des données pour une meilleure présentation
        $programmeData = [
            // Attributs spécifiques au modèle Programme
            'id' => $programme->id,
            'nom' => $programme->nom,
            'source' => $programme->source,
            'niveau_education' => $programme->niveau_education,
            'niveau_classe' => $programme->niveau_classe,
            'cycle' => $programme->cycle,
            'annee_scolaire' => $programme->annee_scolaire,
            'langue_enseignee' => $programme->langue_enseignee,
            'importer_programme' => $programme->importer_programme,
            'exporter_programme' => $programme->exporter_programme,

            // Classe associée au programme
            'classe' => $programme->classe ? [
                'id' => $programme->classe->id,
                'nom' => $programme->classe->nom,
                'niveau_education' => $programme->classe->niveau_education,
                'niveau_classe' => $programme->classe->niveau_classe,
                'salle_id' => $programme->classe->salle_id,
            ] : null,

            // Cours associés au programme
            'cours' => $programme->cours->map(function ($cours) {
                return [
                    'id' => $cours->id,
                    'nom' => $cours->nom,
                    'description' => $cours->description,
                    'niveau_education'  => $cours->niveau_education,
                    'niveau_classe' => $cours->niveau_classe,
                    'heure_allouee' => $cours->heure_allouee,
                    'etat' => $cours->etat,
                    'credits'  => $cours->credits,
                    'coefficient'  => $cours->coefficient,
                    'semestre'  => $cours->semestre,
                    'objectif_generaux'  => $cours->objectif_generaux,
                    'objectif_specifiques' => $cours->objectif_specifiques,
                    'enseignant' => $cours->enseignant ? [
                        'id' => $cours->enseignant->user->id,
                        'nom' => $cours->enseignant->user->nom,
                        'prenom' => $cours->enseignant->user->prenom,
                        'matiere_enseignée' => $cours->enseignant->matiere_enseignée,
                    ] : null,

                    // CategorieCours associés
                    'categorie_cours' => $cours->categories->map(function ($categorieCours) {
                        return [
                            'id' => $categorieCours->id,
                            'nom' => $categorieCours->nom,
                            'leçons' => $categorieCours->leçons,
                            'type_exercices' => $categorieCours->type_exercices,
                            'volume_horaire' => $categorieCours->volume_horaire,
                            'duree_seance' => $categorieCours->duree_seance,
                            'mode_evaluation' => $categorieCours->mode_evaluation,
                            'frequence_evaluation' => $categorieCours->frequence_evaluation,
                            'heure_debut' => $categorieCours->heure_debut,
                            'heure_fin' => $categorieCours->heure_fin,
                            'bareme' => $categorieCours->bareme,
                            'competences' => $categorieCours->competences->map(function ($competence) {
                                return [
                                    'id' => $competence->id,
                                    'nom' => $competence->nom,
                                    'description' => $competence->description,
                                ];
                            }),
                        ];
                    }),
                ];
            }),
        ];

        // Réponse JSON avec les données formatées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme de classe récupéré avec succès',
            'data' => $programmeData,
        ], 200);
    } catch (Exception $e) {
        // En cas d'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération du programme de classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}




public function index()
{
    try {
        // Récupérer tous les programmes de classe avec leurs relations associées
        // Filtrer par la source 'manuel'
        $programmeClasses = Programme::where('source', 'manuel')
            ->with([
                'classe', // Classe associée au programme
                'cours.programme', // Cours associés au programme
                'cours.categories', // Accéder aux catégories via le modèle Cours (pas directement via Programme)
            ])
            ->get();

        // Utilisation de map pour formater les données
        $programmeClassesData = $programmeClasses->map(function ($programme) {
            return [
                // Attributs spécifiques au modèle Programme
                'id' => $programme->id,
                'nom' => $programme->nom,
                'source' => $programme->source,
                'niveau_education' => $programme->niveau_education,
                'niveau_classe' => $programme->niveau_classe,
                'cycle' => $programme->cycle,
                'annee_scolaire' => $programme->annee_scolaire,
                'langue_enseignee' => $programme->langue_enseignee,
                'importer_programme' => $programme->importer_programme,
                'exporter_programme' => $programme->exporter_programme,

                // Classe associée au programme
                'classe' => $programme->classe ? [
                    'id' => $programme->classe->id,
                    'nom' => $programme->classe->nom,
                    'niveau_education' => $programme->classe->niveau_education,
                    'niveau_classe' => $programme->classe->niveau_classe,
                    'salle_id' => $programme->classe->salle_id,
                ] : null,

                // Cours associés au programme
                'cours' => $programme->cours->map(function ($cours) {
                    return [
                        'id' => $cours->id,
                        'nom' => $cours->nom,
                        'description' => $cours-> description,
                        'niveau_education'  => $cours-> niveau_education,
                        'niveau_classe' => $cours->niveau_classe,
                        'heure_allouee' => $cours->heure_allouee,
                        'etat' => $cours->etat,
                        'credits'  => $cours->credits,
                        'coefficient'  => $cours-> coefficient,
                        'semestre'  => $cours->semestre,
                       'objectif_generaux'  => $cours->objectif_generaux,
                       'objectif_specifiques' => $cours->objectif_specifiques,
                        'enseignant' => $cours->enseignant ? [
                            'id' => $cours->enseignant->user->id,
                            'nom' => $cours->enseignant->user->nom,
                            'prenom' => $cours->enseignant->user->prenom,
                            'matiere_enseignée' =>$cours->enseignant->matiere_enseignée,
                        ] : null,

                        // CategorieCours associés (utilisation de categories)
                        'categorie_cours' => $cours->categories->map(function ($categorieCours) {
                            return [
                                'id' => $categorieCours->id,
                                'nom' => $categorieCours->nom,
                                'leçons'=> $categorieCours->leçons,
                                'type_exercices'=> $categorieCours->type_exercices,
                                'volume_horaire' => $categorieCours->volume_horaire,
                                'duree_seance' => $categorieCours->duree_seance,
                                'mode_evaluation'=> $categorieCours->mode_evaluation,
                                'frequence_evaluation'=> $categorieCours->frequence_evaluation,
                                'heure_debut' =>$categorieCours->heure_debut,
                                'heure_fin'=> $categorieCours->heure_fin,
                                'bareme'=> $categorieCours->bareme,
                                'competences' => $categorieCours->competences->map(function ($competence) {
                                    return [
                                        'id' => $competence->id,
                                        'nom' => $competence->nom,
                                        'description' => $competence->description,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
            ];
        });

        // Réponse JSON avec les données formatées
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des programmes de classe récupérée avec succès',
            'data' => $programmeClassesData,
        ], 200);
    } catch (Exception $e) {
        // En cas d'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des programmes de classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function storeProgrammeCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Création du Programme
        $programme = new Programme();
        $programme->nom = $request->nom;
        $programme->niveau_education = $request->niveau_education;
        $programme->niveau_classe = $request->niveau_classe;
        $programme->cycle = $request->cycle ?? null;
        $programme->annee_scolaire = $request->annee_scolaire;
        $programme->langue_enseignee = $request->langue_enseignee ?? null;
        $programme->classe_id = $request->classe_id ?? null;
        $programme->save();

        // Boucle pour ajouter chaque cours et ses compétences
        foreach ($request->cours as $coursData) {
            // Création du cours et lien avec le programme
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $coursData['niveau_education'];
            $cours->niveau_classe = $coursData['niveau_classe'];
            $cours->heure_allouee = $coursData['heure_allouee'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'] ?? null;
            $cours->coefficient = $coursData['coefficient'] ?? null;
            $cours->semestre = $coursData['semestre'] ?? null;
            $cours->enseignant_id = $coursData['enseignant_id'] ?? null;
            $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
            $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
            $cours->programme_id = $programme->id; // Associer le cours au programme
            $cours->save();

            // Validation et ajout des catégories (compétences) à ce cours
            if (isset($coursData['categories'])) {
                foreach ($coursData['categories'] as $categorieData) {
                    // Validation du barème pour chaque niveau d'éducation
                    $bareme = $categorieData['bareme'] ?? null;
                    $this->validateBareme($coursData['niveau_education'], $bareme);

                    // Création de la catégorie de cours et lien avec le cours
                    $categorie = new CategorieCours();
                    $categorie->nom = $categorieData['nom'];
                    $categorie->cours_id = $cours->id; // Associer la catégorie au cours
                    $categorie->volume_horaire = $categorieData['volume_horaire'] ?? null;
                    $categorie->type_exercices = $categorieData['type_exercices'] ?? null;
                    $categorie->leçons = $categorieData['leçons'] ?? null;
                    $categorie->duree_seance = $categorieData['duree_seance'] ?? null;
                    $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? null;
                    $categorie->heure_debut = $categorieData['heure_debut'] ?? null;
                    $categorie->heure_fin = $categorieData['heure_fin'] ?? null;
                    $categorie->frequence_evaluation = $categorieData['frequence_evaluation'] ?? null;
                    $categorie->bareme = $bareme; // Associer le barème à la catégorie
                    $categorie->save();

                    // Ajouter les compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            $competence = new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_cours_id = $categorie->id; // Associer la compétence à la catégorie
                            $competence->save();
                        }
                    }
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme = Programme::with(['cours.categories.competences'])->find($programme->id);
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme, cours et compétences ont été ajoutés avec succès',
            'data' => [
                'programme' => $programme,
            ]
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du programme, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// Fonction de validation des barèmes
private function validateBareme($niveauEducation, $bareme)
{
    if ($niveauEducation === 'maternelle' && !in_array($bareme, ['Acquis', 'En Progression'])) {
        return response()->json([
            'status_code' => 400,
            'status_message' => 'Pour le niveau "maternelle", le barème doit être "Acquis" ou "En Progression".',
        ], 400);
    }

    if ($niveauEducation === 'primaire' && !preg_match('/^(10|[0-9])\/10$/', $bareme)) {
        return response()->json([
            'status_code' => 400,
            'status_message' => 'Pour le niveau "primaire", le barème doit être au format "X/10".',
        ], 400);
    }

    if ($niveauEducation === 'secondaire' && !preg_match('/^(20|[1-9]?[0-9])\/20$/', $bareme)) {
        return response()->json([
            'status_code' => 400,
            'status_message' => 'Pour le niveau "secondaire", le barème doit être au format "X/20".',
        ], 400);
    }
}

public function listerprogrammesexcel()
{
    try {
        // Récupérer uniquement les programmes importés via Excel
        $programmeClasses = Programme::where('source', 'import_excel')->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des programmes importés via Excel récupérée avec succès',
            'data' => $programmeClasses,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des programmes importés via Excel',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}








