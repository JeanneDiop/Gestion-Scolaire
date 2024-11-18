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

        // Associer la classe à chaque programme
        if ($programmes->niveau_classe === $classe->niveau_classe) {
            $programmes->classe_id = $classe->id;
            $programmes->update();
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
        if (is_null($programme)) {
            return response()->json([
                'status_code' => 400,
                'status_message' => 'Aucun programme ne correspond au niveau d\'éducation et de classe spécifiés.',
            ], 400);
        }

        // Mettre à jour les attributs de la classe
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->niveau_education = $request->niveau_education;
        $classe->salle_id = $request->salle_id;
        $classe->save();

        // Associer la classe au programme correspondant
        if ($programme->niveau_classe === $classe->niveau_classe) {
            $programme->classe_id = $classe->id;
            $programme->save();
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
        $programmeManuel = Programme::where('source', 'manuel')
        ->where('niveau_classe', $classe->niveau_classe)
        ->where('niveau_education', $classe->niveau_education)
        ->whereNotIn('id', $classe->programmes->pluck('id'))
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
        ];
    }

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
            // Initialiser la variable $programmesManuels pour éviter l'erreur "Undefined variable"
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
                ->whereNotIn('id', $classe->programmes->pluck('id'))
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
                            'specialite' => $association->enseignant->user->specialite ?? null,
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

        // Si aucun programme ne correspond, retourner une erreur
        if (is_null($programme)) {
            return response()->json([
                'status_code' => 400,
                'status_message' => 'Aucun programme ne correspond au niveau d\'éducation et de classe spécifiés.',
            ], 400);
        }

        // Créer une nouvelle classe
        $classe = new Classe();
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->niveau_education = $request->niveau_education;
        $classe->salle_id = $request->salle_id;
        $classe->save();

        // Associer la classe au programme correspondant
        if ($programme->niveau_classe === $classe->niveau_classe) {
            $programme->classe_id = $classe->id;
            $programme->save();
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
}
