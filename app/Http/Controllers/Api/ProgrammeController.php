<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Historique;
use Carbon\Carbon;
use App\Models\Programme;
use App\Models\Cours;
use App\Models\Competence;
use App\Models\CategorieCours;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseRequest;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseRequest;
use Monolog\Handler\NullHandler;

class ProgrammeController extends Controller
{


    public function updateProgrammeCours(UpdateProgrammeClasseCoursRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            // Récupération du programme à mettre à jour
            $programme = Programme::findOrFail($id);
            $programme->nom = $request->nom ?? $programme->nom ?? null;
            $programme->niveau_education = $request->niveau_education ?? $programme->niveau_education ?? null;
            $programme->niveau_classe = $request->niveau_classe ?? $programme->niveau_classe ?? null;
            $programme->cycle = $request->cycle ?? $programme->cycle ?? null;
            $programme->annee_scolaire = $request->annee_scolaire ?? $programme->annee_scolaire ?? null;
            $programme->langue_enseignee = $request->langue_enseignee ?? $programme->langue_enseignee ?? null;
            $programme->classe_id = $request->classe_id ?? $programme->classe_id ?? null;
            $programme->save();

            // Historique de la mise à jour du programme
            Historique::create([
                'action' => 'update',
                'message' => 'Programme modifié : ' . $programme->nom,
                'user_id' => auth()->id(),
                'programme_id' => $programme->id,
                'created_at' => Carbon::now(),
            ]);

            // Mise à jour des cours
            foreach ($request->cours as $coursData) {
                $cours = isset($coursData['id']) ? Cours::find($coursData['id']) : null;

                if ($cours) {
                    if ($coursData['niveau_education'] !== $programme->niveau_education) {
                        return response()->json([
                            'status_code' => 400,
                            'status_message' => 'Le niveau d\'éducation du cours ne correspond pas au niveau d\'éducation du programme.',
                        ], 400);
                    }

                    if ($coursData['niveau_classe'] !== $programme->niveau_classe) {
                        return response()->json([
                            'status_code' => 400,
                            'status_message' => 'Le niveau de classe du cours ne correspond pas au niveau de classe du programme.',
                        ], 400);
                    }// Si le cours existe, on le met à jour
                    $cours->nom = $coursData['nom'] ?? $cours->nom ?? null;
                    $cours->description = $coursData['description'] ?? $cours->description ?? null;
                    $cours->niveau_education = $coursData['niveau_education'] ?? $cours->niveau_education ?? null;
                    $cours->niveau_classe = $coursData['niveau_classe'] ?? $cours->niveau_classe ?? null;
                    $cours->heure_allouee = $coursData['heure_allouee'] ?? $cours->heure_allouee ?? null;
                    $cours->etat = $coursData['etat'] ?? $cours->etat ?? null;
                    $cours->credits = $coursData['credits'] ?? $cours->credits ?? null;
                    $cours->coefficient = $coursData['coefficient'] ?? $cours->coefficient ?? null;
                    $cours->semestre = $coursData['semestre'] ?? $cours->semestre ?? null;
                    $cours->enseignant_id = $coursData['enseignant_id'] ?? $cours->enseignant_id ?? null;
                    $cours->objectif_generaux = $coursData['objectif_generaux'] ?? $cours->objectif_generaux ?? null;
                    $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? $cours->objectif_specifiques ?? null;
                    $cours->programme_id = $programme->id;
                    $cours->save();

                    // Historique de la mise à jour du cours
                    Historique::create([
                        'action' => 'update',
                        'message' => 'Cours modifié : ' . $cours->nom,
                        'user_id' => auth()->id(),
                        'cours_id' => $cours->id,
                        'created_at' => Carbon::now(),
                    ]);

                    // Mise à jour des catégories (compétences)
                    if (isset($coursData['categories'])) {
                        foreach ($coursData['categories'] as $categorieData) {
                            $categorie = isset($categorieData['id']) ? CategorieCours::find($categorieData['id']) : null;

                            if ($categorie) { // Si la catégorie existe, on la met à jour
                                $bareme = $categorieData['bareme'] ?? $categorie->bareme;
                                $this->validateBareme($coursData['niveau_education'], $bareme);

                                $categorie->nom = $categorieData['nom'] ?? $categorie->nom ?? null;
                                $categorie->volume_horaire = $categorieData['volume_horaire'] ?? $categorie->volume_horaire ?? null;
                                $categorie->type_exercices = $categorieData['type_exercices'] ?? $categorie->type_exercices ?? null;
                                $categorie->leçons = $categorieData['leçons'] ?? $categorie->leçons ?? null;
                                $categorie->duree_seance = $categorieData['duree_seance'] ?? $categorie->duree_seance ?? null;
                                $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? $categorie->mode_evaluation ?? null;
                                $categorie->heure_debut = $categorieData['heure_debut'] ?? $categorie->heure_debut ?? null;
                                $categorie->heure_fin = $categorieData['heure_fin'] ?? $categorie->heure_fin ?? null;
                                $categorie->frequence_evaluation = $categorieData['frequence_evaluation'] ?? $categorie->frequence_evaluation ?? null;
                                $categorie->bareme = $bareme ?? null;
                                $categorie->save();

                                // Historique de la mise à jour de la catégorie
                                Historique::create([
                                    'action' => 'update',
                                    'message' => 'Catégorie modifiée : ' . $categorie->nom,
                                    'user_id' => auth()->id(),
                                    'categorie_id' => $categorie->id,
                                    'created_at' => Carbon::now(),
                                ]);

                                // Mise à jour des compétences spécifiques
                                if (isset($categorieData['competences'])) {
                                    foreach ($categorieData['competences'] as $competenceData) {
                                        $competence = isset($competenceData['id']) ? Competence::find($competenceData['id']) : null;

                                        if ($competence) { // Si la compétence existe, on la met à jour
                                            $competence->nom = $competenceData['nom'] ?? $competence->nom ?? null;
                                            $competence->description = $competenceData['description'] ?? $competence->description ?? null;
                                            $competence->categorie_cours_id = $categorie->id;
                                            $competence->save();

                                            // Historique de la mise à jour de la compétence
                                            Historique::create([
                                                'action' => 'update',
                                                'message' => 'Compétence modifiée : ' . $competence->nom,
                                                'user_id' => auth()->id(),
                                                'competence_id' => $competence->id,
                                                'created_at' => Carbon::now(),
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Validation de la transaction
            DB::commit();

            // Charger les relations pour la réponse
            $programme = $programme->fresh()->load(['cours.categories.competences']);
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Programme, cours et compétences ont été mis à jour avec succès.',
                'data' => [
                    'programme' => $programme,
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la mise à jour du programme, des cours et des compétences.',
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


public function storesProgrammeCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Création du Programme
        $programme = new Programme();
        $programme->nom = $request->nom ?? null;
        $programme->niveau_education = $request->niveau_education ?? null;
        $programme->niveau_classe = $request->niveau_classe ?? null;
        $programme->cycle = $request->cycle ?? null;
        $programme->annee_scolaire = $request->annee_scolaire ?? null;
        $programme->langue_enseignee = $request->langue_enseignee ?? null;
        $programme->classe_id = $request->classe_id ?? null;
        $programme->save();
        Historique::create([
            'action' => 'create',
            'message' => 'Programme ajoutée : ' . $programme->nom,
            'user_id' => auth()->id(),
            'programme_id' => $programme->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
            'created_at' => Carbon::now(),
        ]);
        // Boucle pour ajouter chaque cours et ses compétences
        foreach ($request->cours as $coursData) {
            // Vérification du niveau d'éducation et du niveau de classe
            if ($coursData['niveau_education'] !== $programme->niveau_education) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Le niveau d\'éducation du cours ne correspond pas au niveau d\'éducation du programme.',
                ], 400);
            }

            if ($coursData['niveau_classe'] !== $programme->niveau_classe) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Le niveau de classe du cours ne correspond pas au niveau de classe du programme.',
                ], 400);
            }
            // Création du cours et lien avec le programme
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $coursData['niveau_education'] ?? null;
            $cours->niveau_classe = $coursData['niveau_classe'] ?? null;
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
            Historique::create([
                'action' => 'create',
                'message' => 'Cours ajoutée : ' . $cours->nom,
                'user_id' => auth()->id(),
                'cours_id' => $cours->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                'created_at' => Carbon::now(),
            ]);
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
                    Historique::create([
                        'action' => 'create',
                        'message' => 'Categorie ajoutée : ' . $categorie->nom,
                        'user_id' => auth()->id(),
                        'categorie_id' => $categorie->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                        'created_at' => Carbon::now(),
                    ]);
                    // Ajouter les compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            $competence = new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_cours_id = $categorie->id; // Associer la compétence à la catégorie
                            $competence->save();
                            Historique::create([
                                'action' => 'create',
                                'message' => 'Competence ajoutée : ' . $competence->nom,
                                'user_id' => auth()->id(),
                                'competence_id' => $competence->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                                'created_at' => Carbon::now(),
                            ]);
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
private function validateBaremes($niveauEducation, $bareme)
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


public function storeProgrammeCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Création du Programme
        $programme = new Programme();
        $programme->nom = $request->nom ?? null;
        $programme->niveau_education = $request->niveau_education ?? null;
        $programme->niveau_classe = $request->niveau_classe ?? null;
        $programme->cycle = $request->cycle ?? null;
        $programme->annee_scolaire = $request->annee_scolaire ?? null;
        $programme->langue_enseignee = $request->langue_enseignee ?? null;
        $programme->classe_id = $request->classe_id ?? null;
        $programme->save();
        Historique::create([
            'action' => 'create',
            'message' => 'Programme ajoutée : ' . $programme->nom,
            'user_id' => auth()->id(),
            'programme_id' => $programme->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
            'created_at' => Carbon::now(),
        ]);
        // Boucle pour ajouter chaque cours et ses compétences
        foreach ($request->cours as $coursData) {
            // Vérification du niveau d'éducation et du niveau de classe
            if (isset($coursData['niveau_education']) && $coursData['niveau_education'] !== $programme->niveau_education) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Le niveau d\'éducation du cours ne correspond pas au niveau d\'éducation du programme.',
                ], 400);
            }

            if (isset($coursData['niveau_classe']) && $coursData['niveau_classe'] !== $programme->niveau_classe) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'Le niveau de classe du cours ne correspond pas au niveau de classe du programme.',
                ], 400);
            }

            // Création du cours
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $programme->niveau_education;
            $cours->niveau_classe = $programme->niveau_classe;
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

            Historique::create([
                'action' => 'create',
                'message' => 'Cours ajoutée : ' . $cours->nom,
                'user_id' => auth()->id(),
                'cours_id' => $cours->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                'created_at' => Carbon::now(),
            ]);
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
                    Historique::create([
                        'action' => 'create',
                        'message' => 'Categorie ajoutée : ' . $categorie->nom,
                        'user_id' => auth()->id(),
                        'categorie_id' => $categorie->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                        'created_at' => Carbon::now(),
                    ]);
                    // Ajouter les compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            $competence = new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_cours_id = $categorie->id; // Associer la compétence à la catégorie
                            $competence->save();
                            Historique::create([
                                'action' => 'create',
                                'message' => 'Competence ajoutée : ' . $competence->nom,
                                'user_id' => auth()->id(),
                                'competence_id' => $competence->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                                'created_at' => Carbon::now(),
                            ]);
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


public function RecupererProgrammes($niveau_classe, $niveau_education)
{
    try {
        // Récupérer les programmes selon les paramètres niveau_classe et niveau_education
        $programmes = Programme::where('niveau_classe', $niveau_classe)
            ->where('niveau_education', $niveau_education)
            ->where('source', 'import_excel') // Filtrer uniquement les programmes importés via Excel
            ->get();

        // Vérifier si aucun programme n'a été trouvé
        if ($programmes->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Aucun programme trouvé pour ces critères.',
            ], 404);
        }

        // Retourner les programmes trouvés
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programmes récupérés avec succès.',
            'data' => $programmes,
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function updateProgramme(Request $request, $niveau_classe, $niveau_education,$id)
{
    try {
        // Validation des données de la requête
        $validatedData = $request->validate([

            'matiere' => 'nullable|string|max:255',
            'categorie' => 'nullable|string|max:255',
            'competences_essentielles' => 'nullable|string',
            'leçons' => 'nullable|string',
            'type_exercices' => 'nullable|string',
            'volume_horaire' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
            'duree_seance' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
            'heure_debut' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
            'heure_fin' => 'nullable|regex:/^([0-9]+h)?([0-9]+min)?$/',
            'mode_evaluation' => 'nullable|string|max:255',
            'bareme' => 'nullable|string|max:255',
            'file_name' => 'nullable|string|max:255',
        ]);

        $validatedData = $request->only([
            'matiere',
            'categorie',
            'competences_essentielles',
            'leçons',
            'type_exercices',
            'volume_horaire',
            'duree_seance',
            'mode_evaluation',
            'bareme',
            'source',
            'heure_debut',
            'heure_fin',
            'niveau_education',
            'niveau_classe',
            'file_name',
        ]);
        // Trouver le programme correspondant aux critères
        $programme = Programme::where('id', $id)
            ->where('niveau_classe', $niveau_classe)
            ->where('niveau_education', $niveau_education)
            ->first();

        // Vérifier si le programme existe
        if (!$programme) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Programme non trouvé.',
            ], 404);
        }

        // Mise à jour des données
        $updated = $programme->update($validatedData);

        // Vérifier si la mise à jour a eu lieu
        if (!$updated) {
            return response()->json([
                'status_code' => 400,
                'status_message' => 'Aucune modification effectuée.',
            ], 400);
        }

        // Retourner une réponse de succès avec les données mises à jour
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme mis à jour avec succès.',
            'data' => $programme,  // Retourne les données mises à jour
        ], 200);

    } catch (Exception $e) {
        // Gérer les erreurs générales
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}








