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
    public function storeClasse(CreateClasseRequest $request)
    {
        try {

            $programmes = Programme::all();
            $classe = new Classe();
            $classe->nom = $request->nom;
            $classe->niveau_classe = $request->niveau_classe;
            $classe->niveau_education = $request->niveau_education;
            $classe->salle_id = $request->salle_id;

            if ($request->has('programme_id')) {
                $classe->programme_id = $request->programme_id;
                $classe->save();
                $matieres = Cours::where('programme_id', $classe->programme_id)->get();

                if ($matieres->isEmpty()) {
                    $matieres = [];
                }
            } else {
                $matieres = [];
            }

            $programmesWithMatieres = [];
            foreach ($programmes as $programme) {
                $programmeMatieres = Cours::where('programme_id', $programme->id)->get();
                $programmesWithMatieres[] = [
                    'programme' => $programme,
                    'matieres' => $programmeMatieres,
                ];
            }

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Classe a été ajoutée',
                'data' => [
                    'classe' => $classe,
                    'programmes_with_matieres' => $programmesWithMatieres,

                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function updateClasse(EditClasseRequest $request, $id)
    {
        try {

            $classe = Classe::findOrFail($id);
            $classe->nom = $request->nom;
            $classe->niveau_classe = $request->niveau_classe;
            $classe->niveau_education = $request->niveau_education;
            $classe->salle_id = $request->salle_id;
            if ($request->has('programme_classe_id')) {
                $classe->programme_classe_id = $request->programme_classe_id;
                $classe->update();
                $matieres = Cours::where('programme_classe_id', $classe->programme_classe_id)->get();

                if ($matieres->isEmpty()) {
                    $matieres = [];
                }
            } else {
                $matieres = [];
            }

            // Récupérer tous les programmes disponibles et leurs matières
            $programmes = Programme::all();
            $programmesWithMatieres = [];
            foreach ($programmes as $programme) {
                $programmeMatieres = Cours::where('programme_id', $programme->id)->get();
                $programmesWithMatieres[] = [
                    'programme' => $programme,
                    'matieres' => $programmeMatieres,
                ];
            }

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Classe mise à jour avec succès',
                'data' => [
                    'classe' => $classe,
                    'programmes_with_matieres' => $programmesWithMatieres,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la classe',
                'error' => $e->getMessage(),
            ]);
        }
    }


public function showClasse($id)
{
    try {
        $classe = Classe::with([
            'salle',
            'programmeclasse.cours',
            'apprenants.user',
            'classeAssociations.apprenant.user',
            'classeAssociations.cours',
            'classeAssociations.enseignant.user'
        ])->findOrFail($id);
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

            'programme_classe' => $classe->programmeClasse ? [
                'id' => $classe->programmeClasse->id,
                'nom' => $classe->programmeClasse->nom,
                'description' => $classe->programmeClasse->description,
                'niveau_classe' => $classe->programmeClasse->niveau_classe,
                'niveau_education' => $classe->programmeClasse->niveau_education,
                'periode' => $classe->programmeClasse->periode,
                'cours' => $classe->programmeClasse->cours->map(function ($cours) {
                    return [
                        'id' => $cours->id,
                        'nom' => $cours->nom,
                        'description' => $cours->description,
                        'niveau_education' => $cours->niveau_education,
                        'periode' => $cours->periode,
                        'etat' => $cours->etat,
                        'credits' => $cours->credits,
                        'coefficient' => $cours->coefficient,
                        'semestre' => $cours->semestre,
                    ];
                }),
            ] : null,

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
            'data' => $classeData, // Retourner les données structurées
        ],200);
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
        ],500);
    }
}


public function indexClasse(Request $request)
{
    try {

        $classes = Classe::with([
            'salle',
            'programmeclasse.cours',
            'apprenants.user',
            'classeAssociations.apprenant.user',
            'classeAssociations.cours',
            'classeAssociations.enseignant.user'
        ])->get();


        $classesData = $classes->map(function ($classe) {
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

                'programme_classe' => $classe->programmeclasse ? [
                    'id' => $classe->programmeclasse->id,
                    'nom' => $classe->programmeclasse->nom,
                    'description' => $classe->programmeclasse->description,
                    'niveau_classe' => $classe->programmeclasse->niveau_classe,
                    'niveau_education' => $classe->programmeclasse->niveau_education,
                    'periode' => $classe->programmeclasse->periode,
                    'cours' => $classe->programmeclasse->cours->map(function ($cours) {
                        return [
                            'id' => $cours->id,
                            'nom' => $cours->nom,
                            'description' => $cours->description,
                            'niveau_education' => $cours->niveau_education,
                            'periode' => $cours->periode,
                            'etat' => $cours->etat,
                            'credits' => $cours->credits,
                            'coefficient' => $cours->coefficient,
                            'semestre' => $cours->semestre,
                        ];
                    }),
                ] : null,

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



}
