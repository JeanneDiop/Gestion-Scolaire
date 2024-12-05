<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Salle;
use App\Models\Cours;
use App\Models\Programme;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Historique;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Classe\CreateClasseRequest;
use App\Http\Requests\Classe\EditClasseRequest;

class ClasseController extends Controller
{



    public function updateClasse(EditClasseRequest $request, $id)
{
    try {
        // Rechercher la classe par ID
        $classe = Classe::findOrFail($id);

        // Mettre à jour les attributs de la classe
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->niveau_education = $request->niveau_education;
        $classe->salle_id = $request->salle_id;
        $classe->update();

        // Récupérer les programmes qui correspondent au niveau d'éducation et au niveau de classe mis à jour
        $query = Programme::where('niveau_education', $classe->niveau_education)
            ->where('niveau_classe', $classe->niveau_classe);

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $programmes = $query->get();
        if ($programmes->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Le programme que vous avez choisi n\'existe pas dans la base de données.',
            ], 404);
        }

        // Associer la classe à chaque programme
        foreach ($programmes as $programme) {
            if ($programme->niveau_classe === $classe->niveau_classe) {
                $programme->classe_id = $classe->id;
                $programme->save(); // Utilisez save() pour enregistrer chaque programme modifié
            }
        }

        // Retourner la classe mise à jour et les programmes correspondants (par exemple en JSON)
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe mise à jour avec succès',
            'classe' => $classe,
            'programmes' => $programmes
        ], 200);

    } catch (\Exception $e) {
        // En cas d'erreur, retourner une réponse JSON avec l'erreur
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la classe',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function updateClasses(EditClasseRequest $request, $id)
{
    try {
        // Récupérer la classe par son identifiant
        $classe = Classe::find($id);

        // Vérifier si la classe existe
        if (!$classe) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Classe non trouvée'
            ], 404);
        }

        // Vérifier l'existence d'un programme correspondant au niveau d'éducation et de classe
        $query = Programme::where('niveau_education', $request->niveau_education)
            ->where('niveau_classe', $request->niveau_classe);

        if ($request->filled('source')) {
            $query->where(function($q) use ($request) {
                $q->where('source', 'manuel')
                  ->orWhere('source', 'import_excel');
            });
        }

        // Récupérer un programme qui correspond aux critères
        $programme = $query->first();

        // Si aucun programme ne correspond, retourner une erreur


        // Mettre à jour les attributs de la classe
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->niveau_education = $request->niveau_education;
        $classe->salle_id = $request->salle_id;
        $classe->save();
        Historique::create([
            'action' => 'update',
            'message' => 'Classe modifiée : ' . $classe->nom,
            'user_id' => auth()->id(),
            'classe_id' => $classe->id,
            'created_at' => Carbon::now(),
        ]);

        // Associer la classe au programme correspondant
        if (!is_null($programme) && $programme->niveau_classe === $classe->niveau_classe) {
            $programme->classe_id = $classe->id;
            $programme->save();
        } else {

            $programme = [];
        }

        // Retourner la classe mise à jour et le programme correspondant
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe mise à jour avec succès',
            'classe' => $classe,
            'programme' => $programme
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function showClasse($id)
{
    try {
        // Chargement de la classe avec les relations nécessaires
        $classe = Classe::with([
            'salle',
            'programmes',
            'apprenants.user',
            'classeAssociations.apprenant.user',
            'classeAssociations.cours',
            'classeAssociations.enseignant.user'
        ])->findOrFail($id);

        // Récupération des programmes importés via Excel
        $programmesImportExcel = Programme::where('source', 'import_excel')
        ->where('niveau_classe', $classe->niveau_classe)
        ->where('niveau_education', $classe->niveau_education)

            ->get()
            ->map(function ($programme) {
                return [
                    'id' => $programme->id,
                    'nom' => $programme->nom,
                    'niveau_classe' => $programme->niveau_classe,
                    'niveau_education' => $programme->niveau_education,
                    'matiere' => $programme->matiere,
                    'categorie' => $programme->categorie,
                    'importer_programme' => $programme->importer_programme,
                    'exporter_programme' => $programme->exporter_programme,
                    'competences_essentielles' => $programme->competences_essentielles,
                    'lecons' => $programme->lecons,
                    'volume_horaire' => $programme->volume_horaire,
                    'duree_seance' => $programme->duree_seance,
                    'mode_evaluation' => $programme->mode_evaluation,
                    'bareme' => $programme->bareme,
                    'file_name' => $programme->file_name,
                ];
            });

        // Récupération des programmes manuels
        $programmesManuels = Programme::where('source', 'manuel')
        ->where('niveau_classe', $classe->niveau_classe)
            ->where('niveau_education', $classe->niveau_education)
            ->get()
            ->map(function ($programmeManuel) {
                return [
                    'id' => $programmeManuel->id,
                    'nom' => $programmeManuel->nom,
                    'niveau_classe' => $programmeManuel->niveau_classe,
                    'niveau_education' => $programmeManuel->niveau_education,
                    'cycle' => $programmeManuel->cycle,
                    'annee_scolaire' => $programmeManuel->annee_scolaire,
                    'langue_enseignee' => $programmeManuel->langue_enseignee,
            'cours' => $programmeManuel->cours->map(function ($cours) {
                        return [
                             'id' => $cours->id,
                            'nom' => $cours->nom,
                            'description' => $cours->description,
                            'niveau_education' => $cours->niveau_education,
                            'niveau_classe' => $cours->niveau_classe,
                            'heure_allouee' => $cours->heure_allouee,
                            'etat' => $cours->etat,
                            'credits' => $cours->credits,
                            'coefficient' => $cours->coefficient,
                            'semestre' => $cours->semestre,
                            'objectif_generaux' => $cours->objectif_generaux,
                            'objectif_specifiques' => $cours->objectif_specifiques,
                           'enseignant' => $cours->enseignant ? [
    'id' => $cours->enseignant->id,
    'nom' => $cours->enseignant->user->nom ?? null,
    'prenom' => $cours->enseignant->user->prenom ?? null,
    'specialite' => $cours->enseignant->user->specialite ?? null,
    'telephone' => $cours->enseignant->user->telephone ?? null,
    'email' => $cours->enseignant->user->email ?? null,
] : null,
                            'categories' => $cours->categories->map(function ($categorie) {
                                return [
                                    'id' => $categorie->id,
                                    'nom' => $categorie->nom,
                                    'volume_horaire' => $categorie->volume_horaire,
                                    'duree_seance' => $categorie->duree_seance,
                                    'mode_evaluation' => $categorie->mode_evaluation,
                                    'frequence_evaluation' => $categorie->frequence_evaluation,
                                    'lecons' => $categorie->lecons,
                                    'type_exercices' => $categorie->type_exercices,
                                    'heure_debut' => $categorie->heure_debut,
                                    'heure_fin' => $categorie->heure_fin,
                                    'bareme' => $categorie->bareme,
                                    'competences' => $categorie->competences->map(function ($competence) {
                                        return [
                                            'id' => $competence->id,
                                            'nom' => $competence->nom,
                                            'description' => $competence->description,
                                        ];
                                    }),
                                ];
                            })
                        ];
                    }),
                ];
            });
        // Préparer la structure des données pour la réponse
        $classeData = [
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
            'programmes_import_excel' => $programmesImportExcel,
            'programmes_manuels' => $programmesManuels,
            'apprenants' => $classe->apprenants->map(function ($apprenant) {
                return [
                    'id' => $apprenant->id,
                    'lieu_naissance' => $apprenant->lieu_naissance,
                    'date_naissance' => $apprenant->date_naissance,
                    'niveau_education' => $apprenant->niveau_education,
                    'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                    'numero_CNI' => $apprenant->numero_CNI,
                    'image' => $apprenant->image,
                    'user' => $apprenant->user ? [
                        'id' => $apprenant->user->id,
                        'nom' => $apprenant->user->nom,
                        'prenom' => $apprenant->user->prenom,
                        'telephone' => $apprenant->user->telephone,
                        'email' => $apprenant->user->email,
                        'etat' => $apprenant->user->etat,
                        'genre' => $apprenant->user->genre,
                        'adresse' => $apprenant->user->adresse,
                    ] : null,
                ];
            }),
            'associations' => $classe->classeAssociations->map(function ($association) {
                return [
                    'apprenant' => $association->apprenant ? [
                        'id' => $association->apprenant->id,
                        'nom' => $association->apprenant->user->nom ?? null,
                        'prenom' => $association->apprenant->user->prenom ?? null,
                    ] : null,
                    'cours' => $association->cours ? [
                        'id' => $association->cours->id,
                        'nom' => $association->cours->nom,
                    ] : null,
                    'enseignant' => $association->enseignant ? [
                        'id' => $association->enseignant->id,
                        'nom' => $association->enseignant->user->nom ?? null,
                        'prenom' => $association->enseignant->user->prenom ?? null,
                        'specialite' => $association->enseignant->user->specialite ?? null,
                    ] : null,
                ];
            }),
        ];

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la classe récupérés avec succès',
            'data' => $classeData,
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Classe non trouvée',
            'error' => 'La classe avec l\'ID spécifié n\'existe pas.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des détails de la classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function indexClasse(Request $request)
{
    try {

        $classes = Classe::with([
            'salle',
            'programmes',
            'apprenants.user',
            'classeAssociations.apprenant.user',
            'classeAssociations.cours',
            'classeAssociations.enseignant.user'
        ])->get();

        // Parcours de chaque classe pour obtenir les détails et les programmes associés
        $classesData = $classes->map(function ($classe) {
            $programmesManuels = [];
            // Récupération des programmes importés par Excel pour la classe actuelle
            $programmesImportExcel = Programme::where('source', 'import_excel')
        ->where('niveau_classe', $classe->niveau_classe)
        ->where('niveau_education', $classe->niveau_education)
        ->get()
        ->map(function ($programme) {
            return [
                'id' => $programme->id,
                'nom' => $programme->nom,
                'niveau_classe' => $programme->niveau_classe,
                'niveau_education' => $programme->niveau_education,
                'matiere' => $programme->matiere,
                'categorie' => $programme->categorie,
                'importer_programme' => $programme->importer_programme,
                'exporter_programme' => $programme->exporter_programme,
                'competences_essentielles' => $programme->competences_essentielles,
                'lecons' => $programme->lecons,
                'volume_horaire' => $programme->volume_horaire,
                'duree_seance' => $programme->duree_seance,
                'mode_evaluation' => $programme->mode_evaluation,
                'bareme' => $programme->bareme,
                'file_name' => $programme->file_name,
            ];
        });

            // Récupération des programmes manuels pour la classe actuelle
            $programmeManuel = Programme::where('source', 'manuel')
        ->where('niveau_classe', $classe->niveau_classe)
        ->where('niveau_education', $classe->niveau_education)
        ->first(); // Récupérer seulement le premier programme correspondant

    if ($programmeManuel) {
        $programmesManuels[] = [
            'id' => $programmeManuel->id,
            'nom' => $programmeManuel->nom,
            'niveau_classe' => $programmeManuel->niveau_classe,
            'niveau_education' => $programmeManuel->niveau_education,
            'cycle' => $programmeManuel->cycle,
            'annee_scolaire' => $programmeManuel->annee_scolaire,
            'langue_enseignee' => $programmeManuel->langue_enseignee,
            'cours' => $programmeManuel->cours->map(function ($cours) {
                return [
                    'id' => $cours->id,
                    'nom' => $cours->nom,
                    'description' => $cours->description,
                    'niveau_education' => $cours->niveau_education,
                    'niveau_classe' => $cours->niveau_classe,
                    'heure_allouee' => $cours->heure_allouee,
                    'etat' => $cours->etat,
                    'credits' => $cours->credits,
                    'coefficient' => $cours->coefficient,
                    'semestre' => $cours->semestre,
                    'objectif_generaux' => $cours->objectif_generaux,
                    'objectif_specifiques' => $cours->objectif_specifiques,
                    'enseignant' => $cours->enseignant ? [
                        'id' => $cours->enseignant->id,
                        'nom' => $cours->enseignant->user->nom ?? null,
                        'prenom' => $cours->enseignant->user->prenom ?? null,
                        'specialite' => $cours->enseignant->user->specialite ?? null,
                        'telephone' => $cours->enseignant->user->telephone ?? null,
                        'email' => $cours->enseignant->user->email ?? null,
                    ] : null,
                    'categories' => $cours->categories->map(function ($categorie) {
                        return [
                            'id' => $categorie->id,
                            'nom' => $categorie->nom,
                            'volume_horaire' => $categorie->volume_horaire,
                            'duree_seance' => $categorie->duree_seance,
                            'mode_evaluation' => $categorie->mode_evaluation,
                            'frequence_evaluation' => $categorie->frequence_evaluation,
                            'lecons' => $categorie->lecons,
                            'type_exercices' => $categorie->type_exercices,
                            'heure_debut' => $categorie->heure_debut,
                            'heure_fin' => $categorie->heure_fin,
                            'bareme' => $categorie->bareme,
                            'competences' => $categorie->competences->map(function ($competence) {
                                return [
                                    'id' => $competence->id,
                                    'nom' => $competence->nom,
                                    'description' => $competence->description,
                                ];
                            }),
                        ];
                    })
                ];
            }),
        ];
    }

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
                'programmes_import_excel' => $programmesImportExcel,
                'programmes_manuels' => $programmesManuels,
                'apprenants' => $classe->apprenants->map(function ($apprenant) {
                    return [
                        'id' => $apprenant->id,
                        'lieu_naissance' => $apprenant->lieu_naissance,
                        'date_naissance' => $apprenant->date_naissance,
                        'niveau_education' => $apprenant->niveau_education,
                        'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                        'numero_CNI' => $apprenant->numero_CNI,
                        'statut_marital' => $apprenant->statut_marital,
                        'image' => $apprenant->image,
                        'user' => $apprenant->user ? [
                            'id' => $apprenant->user->id,
                            'nom' => $apprenant->user->nom,
                            'prenom' => $apprenant->user->prenom,
                            'telephone' => $apprenant->user->telephone,
                            'email' => $apprenant->user->email,
                            'etat' => $apprenant->user->etat,
                            'genre' => $apprenant->user->genre,
                            'adresse' => $apprenant->user->adresse,
                        ] : null,
                    ];
                }),
                'associations' => $classe->classeAssociations->map(function ($association) {
                    return [
                        'apprenant' => $association->apprenant ? [
                            'id' => $association->apprenant->id,
                            'nom' => $association->apprenant->user->nom ?? null,
                            'prenom' => $association->apprenant->user->prenom ?? null,
                        ] : null,
                        'cours' => $association->cours ? [
                            'id' => $association->cours->id,
                            'nom' => $association->cours->nom,
                        ] : null,
                        'enseignant' => $association->enseignant ? [
                            'id' => $association->enseignant->id,
                            'nom' => $association->enseignant->user->nom ?? null,
                            'prenom' => $association->enseignant->user->prenom ?? null,
                            'specialite' => $association->enseignant->specialite ?? null,
                        ] : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des classes récupérées avec succès',
            'data' => $classesData,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des classes',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function showNotes($classeId)
{
    // Récupérer la classe avec les apprenants, leurs évaluations et les notes associées
    $classe = Classe::with(['apprenants.evaluations.cours', 'apprenants.evaluations.notes'])
        ->where('id', $classeId)
        ->first();

    // Vérifiez si la classe existe
    if (!$classe) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Classe non trouvée.'
        ]);
    }

    $notes = [];

    // Parcourez les apprenants et leurs évaluations
    foreach ($classe->apprenants as $apprenant) {
        // Initialiser un tableau pour les informations de l'apprenant
        $apprenantData = [
            'id' => $apprenant->id,
                        'nom' => $apprenant->user->nom ?? null,
                        'prenom' => $apprenant->user->prenom ?? null,
                        'telephone' => $apprenant->user->telephone ?? null,
                        'email' => $apprenant->user->email ?? null,
                        'adresse' => $apprenant->user->adresse ?? null,
                        'genre' => $apprenant->user->genre ?? null,
                        'etat' => $apprenant->user->etat ?? null,
                        'lieu_naissance' => $apprenant->lieu_naissance,
                        'date_naissance' => $apprenant->date_naissance,
                        'numero_CNI' => $apprenant->numero_CNI,
                        'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                        'niveau_education' => $apprenant->niveau_education,
                        'statut_marital' => $apprenant->statut_marital,
        ];

        foreach ($apprenant->evaluations as $evaluation) {
            // Vérifie si l'évaluation a des notes
            foreach ($evaluation->notes as $note) {
                $apprenantData['evaluations'][] = [
                    'cours' => [
                        'nom' => $evaluation->cours->nom,
                    ],
                    'note' => $note->note, // Récupérer la note de l'évaluation
                    'evaluation' => [
                        'id' => $evaluation->id,
                        'nom_evaluation' => $evaluation->nom_evaluation,
                        'date_evaluation' => $evaluation->date_evaluation,
                        'type_evaluation' => $evaluation->type_evaluation,
                    ]
                ];
            }
        }

        // Ajouter les données de l'apprenant au tableau des notes
        $notes[] = $apprenantData;
    }

    return response()->json([
        'status_code' => 200,
        'status_message' => 'Notes récupérées avec succès.',
        'data' => $notes
    ]);
}

public function ajouterClasse(CreateClasseRequest $request)
{
    try {
        // Vérifier l'existence d'un programme correspondant au niveau d'éducation et de classe
        $query = Programme::where('niveau_education', $request->niveau_education)
            ->where('niveau_classe', $request->niveau_classe);

        if ($request->filled('source')) {
            $query->where(function($q) use ($request) {
                $q->where('source', 'manuel')
                  ->orWhere('source', 'import_excel');
            });
        }

        // Récupérer un seul programme qui correspond aux critères
        $programme = $query->first();



        // Créer une nouvelle classe
        $classe = new Classe();
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->niveau_education = $request->niveau_education;
        $classe->salle_id = $request->salle_id;
        $classe->save();
        Historique::create([
            'action' => 'create',  // Action 'update' pour la modification
            'message' => 'Classe ajouté : ' . $classe->nom,
            'user_id' => auth()->id(), // ID de l'utilisateur authentifié
            'classe_id' => $classe->id,  // ID de la salle modifiée
            'created_at' => Carbon::now(),
        ]);

        // Associer la classe au programme correspondant
        //if ($programme->niveau_classe === $classe->niveau_classe) {
           // $programme->classe_id = $classe->id;
            //$programme->save();
        //}
        if (!is_null($programme) && $programme->niveau_classe === $classe->niveau_classe) {
            $programme->classe_id = $classe->id;
            $programme->save();
        } else {

            $programme = [];
        }

        // Retourner la classe et le programme correspondant (par exemple en JSON)
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe ajoutée avec succès',
            'classe' => $classe,
            'programme' => $programme // Utilisation de $programme ici
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function destroy($id)
{
    try {
        // Récupérer la classe par son identifiant
        $classe = Classe::find($id);

        // Vérifier si la classe existe
        if (!$classe) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Classe non trouvée'
            ], 404);
        }

        // Supprimer la classe
        $classe->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe supprimée avec succès'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function getClassPresenceDetails($classeId)
{
    // Récupérer la classe avec ses apprenants (directement dans la classe) et les apprenants via les associations
    $classe = Classe::with([
        'apprenants.presences.cours',
        'classeAssociations.apprenant.presences.cours',
        'classeAssociations.cours',
        'classeAssociations.enseignant'
    ])->find($classeId);

    // Vérifier si la classe existe
    if (!$classe) {
        return response()->json([
            'message' => "Aucune classe trouvée avec l'ID {$classeId}."
        ], 404);
    }

    // Initialiser un tableau pour stocker les détails
    $classPresenceDetails = [];
    $apprenantIds = [];  // Tableau pour suivre les IDs des apprenants déjà traités

    // Fonction pour ajouter les détails d'un apprenant et ses présences
    $addApprenantDetails = function ($apprenant) use (&$classPresenceDetails, &$apprenantIds) {
        // Si l'apprenant n'a pas déjà été ajouté, on l'ajoute
        if (!in_array($apprenant->id, $apprenantIds)) {
            $apprenantIds[] = $apprenant->id;  // Ajouter l'ID de l'apprenant dans le tableau
            $presenceDetails = [];

            // Vérifier si l'apprenant a des enregistrements de présence
            if ($apprenant->presences) {
                foreach ($apprenant->presences as $presence) {
                    $statut = ucfirst(strtolower($presence->statut)); // Capitaliser le statut

                    // Ajouter les informations selon le statut
                    if ($statut === 'Absent') {
                        $presenceDetails[] = [
                            'statut' => $statut,
                            'date_absent' => $presence->date_absent,
                            'raison_absence' => $presence->raison_absence,
                            'cours' => $presence->cours ? [
                                'id' => $presence->cours->id,
                                'nom' => $presence->cours->nom,
                            ] : null,
                        ];
                    } elseif ($statut === 'Present') {
                        $presenceDetails[] = [
                            'statut' => $statut,
                            'date_present' => $presence->date_present,
                            'cours' => $presence->cours ? [
                                'id' => $presence->cours->id,
                                'nom' => $presence->cours->nom,
                            ] : null,
                        ];
                    } elseif ($statut === 'Retard') {
                        $presenceDetails[] = [
                            'statut' => $statut,
                            'heure_arrivee' => $presence->heure_arrivee,
                            'duree_retard' => $presence->duree_retard,
                            'cours' => $presence->cours ? [
                                'id' => $presence->cours->id,
                                'nom' => $presence->cours->nom,
                            ] : null,
                        ];
                    }
                }
            }

            // Ajouter les détails de l'apprenant et ses présences
            $classPresenceDetails[] = [
                'apprenant' => [
                    'id' => $apprenant->id,
                    'nom' => $apprenant->user->nom,
                    'prenom' => $apprenant->user->prenom,
                    'telephone' => $apprenant->user->telephone,
                    'email' => $apprenant->user->email,
                    'adresse' => $apprenant->user->adresse,
                    'genre' => $apprenant->user->genre,
                    'etat' => $apprenant->user->etat,
                    'lieu_naissance' => $apprenant->lieu_naissance,
                    'date_naissance' => $apprenant->date_naissance,
                    'numero_CNI' => $apprenant->numero_CNI,
                    'numero_identification_eleve' => $apprenant->numero_identification_eleve,
                    'niveau_education' => $apprenant->niveau_education,
                ],
                'presences' => $presenceDetails,
            ];
        }
    };

    // Boucler à travers les apprenants de la classe (directement dans la classe)
    foreach ($classe->apprenants as $apprenant) {
        $addApprenantDetails($apprenant);  // Ajouter les détails de l'apprenant
    }

    // Boucler à travers les apprenants dans les associations (si la classe a des associations)
    foreach ($classe->classeAssociations as $association) {
        $apprenant = $association->apprenant;
        if ($apprenant) {
            $addApprenantDetails($apprenant);  // Ajouter les détails de l'apprenant dans l'association
        }
    }
    $associationsDetails = $classe->classeAssociations->map(function ($association) {
        return [
            'apprenant' => $association->apprenant ? [
                'id' => $association->apprenant->id,
                'nom' => $association->apprenant->user->nom ?? null,
                'prenom' => $association->apprenant->user->prenom ?? null,
            ] : null,
            'cours' => $association->cours ? [
                'id' => $association->cours->id,
                'nom' => $association->cours->nom,
            ] : null,
            'enseignant' => $association->enseignant ? [
                'id' => $association->enseignant->id,
                'nom' => $association->enseignant->user->nom ?? null,
                'prenom' => $association->enseignant->user->prenom ?? null,
                'matiere_enseignée' => $association->enseignant->matiere_enseignée ?? null,
            ] : null,
        ];
    });

    // Retourner les détails de la classe et les présences
    return response()->json([
        'classe' => [
            'id' => $classe->id,
            'nom' => $classe->nom,
            'niveau_education' => $classe->niveau_education,
            'niveau_classe' => $classe->niveau_classe,
        ],
        'details' => $classPresenceDetails,
        'associations' => $associationsDetails,
    ]);
}
}
