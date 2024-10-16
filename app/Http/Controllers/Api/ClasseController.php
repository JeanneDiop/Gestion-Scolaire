<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Salle;
use App\Models\Cours;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Classe\CreateClasseRequest;
use App\Http\Requests\Classe\UpdateClasseCoursRequest;
use App\Http\Requests\Classe\CreateClasseCoursRequest;
use App\Http\Requests\Classe\EditClasseRequest;

class ClasseController extends Controller
{
    public function storeClasse(CreateClasseRequest $request)
    {
        try {
            $classe = new Classe();
            $classe->nom = $request->nom;
            $classe->niveau_classe = $request->niveau_classe; // Corrigez l'orthographe de 'niveau_classe
            $classe->salle_id = $request->salle_id;
            $classe->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Classe a été ajoutée',
                'data' => $classe,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function storeClasseCours(CreateClasseCoursRequest $request)
{
    try {

        DB::beginTransaction();


        $classe = new Classe();
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->salle_id = $request->salle_id;
        $classe->save();

        foreach ($request->cours as $coursData) {
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'];
            $cours->niveau_education = $request->niveau_education;
            $cours->heure_allouée = $coursData['heure_allouée'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'];
            $cours->coefficient = $coursData['coefficient'];
            $cours->enseignant_id = $coursData['enseignant_id'];
            $cours->classe_id = $classe->id;
            $cours->save();
        }

        DB::commit();

        // Charger les cours après la sauvegarde pour éviter les doublons
        $classe->load('cours');

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe et cours ont été ajoutés avec succès',
            'data' => [
                'classe' => [
                    'id' => $classe->id,
                    'nom' => $classe->nom,
                    'niveau_classe' => $classe->niveau_classe,
                    'salle_id' => $classe->salle_id,
                    'cours' => $classe->cours,
                ],
            ],
        ]);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe et des cours',
            'error' => $e->getMessage(),
        ]);
    }
}
public function updateClasseCours(UpdateClasseCoursRequest $request, $id)
{
    try {

        $classe = Classe::findOrFail($id);

        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->salle_id = $request->salle_id;
        $classe->update();


        $updatedCours = [];
        foreach ($request->cours as $coursData) {
            if (!isset($coursData['id'])) {
                return response()->json([
                    'status_code' => 400,
                    'status_message' => 'L\'ID du cours est manquant',
                ], 400);
            }

            $cours = Cours::findOrFail($coursData['id']);
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'];
            $cours->niveau_education = $request->niveau_classe;
            $cours->heure_allouée = $coursData['heure_allouée'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'];
            $cours->coefficient = $coursData['coefficient'];
            $cours->enseignant_id = $coursData['enseignant_id'];
            $cours->update();

            $updatedCours[] = $cours;
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe et cours ont été mis à jour avec succès',
            'data' => [
                'classe' => $classe,
                'cours' => $updatedCours,
            ],
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Classe ou cours non trouvé',
            'error' => $e->getMessage(),
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la classe et des cours',
            'error' => $e->getMessage(),
        ]);
    }
}
public function indexClasse()
{
    try {
        $classes = Classe::with(['salle', 'cours.enseignant.user', 'apprenants.user', 'classeAssociations.apprenant', 'classeAssociations.cours', 'classeAssociations.enseignant.user'])->get();

        // Structurer les données de chaque classe
        $classeData = $classes->map(function ($classe) {
            return [
                'id' => $classe->id,
                'nom' => $classe->nom,
                'niveau_classe' => $classe->niveau_classe,
                'salle' => $classe->salle ? [
                    'id' => $classe->salle->id,
                    'nom' => $classe->salle->nom,
                    'capacity' => $classe->salle->capacity,
                    'type' => $classe->salle->type,
                ] : null,
                'cours' => $classe->cours->map(function ($cours) {
                    return [
                        'id' => $cours->id,
                        'nom' => $cours->nom,
                        'niveau_education' => $cours->niveau_education,
                        'description' => $cours->description,
                        'heure_allouée' => $cours->heure_allouée,
                        'credits' => $cours->credits,
                        'etat' => $cours->etat,
                        'enseignant' => $cours->enseignant ? [
                            'id' => $cours->enseignant->id,
                            'nom' => $cours->enseignant->user->nom,
                            'prenom' => $cours->enseignant->user->prenom,
                            'specialite' => $cours->enseignant->specialite,
                        ] : null,
                    ];
                }),
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
                            'nom' => $association->apprenant->user->nom,
                            'prenom' => $association->apprenant->user->prenom,
                        ] : null,
                        'cours' => $association->cours ? [
                            'id' => $association->cours->id,
                            'nom' => $association->cours->nom,
                        ] : null,
                        'enseignant' => $association->enseignant ? [
                            'id' => $association->enseignant->id,
                            'nom' => $association->enseignant->user->nom,
                            'prenom' => $association->enseignant->user->prenom,
                            'specialite' => $association->enseignant->specialite,
                        ] : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Toutes les classes ont été récupérées',
            'data' => $classeData,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des classes',
            'error' => $e->getMessage(),
        ]);
    }
}


public function showClasse($id)
{
    try {
        // Récupérer la classe avec ses relations
        $classe = Classe::with([
            'salle',
            'cours.enseignant.user',
            'apprenants.user',
            'classeAssociations.apprenant',
            'classeAssociations.cours',
            'classeAssociations.enseignant.user'
        ])->findOrFail($id);

        // Structurer les données de la classe
        $classeData = [
            'id' => $classe->id,
            'nom' => $classe->nom,
            'niveau_classe' => $classe->niveau_classe,
            'salle' => $classe->salle ? [
                'id' => $classe->salle->id,
                'nom' => $classe->salle->nom,
                'capacity' => $classe->salle->capacity,
                'type' => $classe->salle->type,
            ] : null,
            // Récupérer tous les cours associés à la classe
            'cours' => $classe->cours->map(function ($cours) {
                return [
                    'id' => $cours->id,
                    'nom' => $cours->nom,
                    'niveau_education' => $cours->niveau_education,
                    'description' => $cours->description,
                    'heure_allouée' => $cours->heure_allouée,
                    'credits' => $cours->credits,
                    'etat' => $cours->etat,
                    'enseignant' => $cours->enseignant ? [
                        'id' => $cours->enseignant->id,
                        'nom' => $cours->enseignant->user->nom,
                        'prenom' => $cours->enseignant->user->prenom,
                        'specialite' => $cours->enseignant->specialite,
                    ] : null,
                ];
            }),
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
                        'nom' => $association->apprenant->user->nom,
                        'prenom' => $association->apprenant->user->prenom,
                    ] : null,
                    'cours' => $association->cours ? [
                        'id' => $association->cours->id,
                        'nom' => $association->cours->nom,
                    ] : null,
                    'enseignant' => $association->enseignant ? [
                        'id' => $association->enseignant->id,
                        'nom' => $association->enseignant->user->nom,
                        'prenom' => $association->enseignant->user->prenom,
                        'specialite' => $association->enseignant->user->specialite,
                    ] : null,
                ];
            }),
        ];

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la classe récupérés avec succès',
            'data' => $classeData, // Retourner les données structurées
        ]);
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
        ]);
    }
}


    public function updateClasse(EditClasseRequest $request, $id)
    {
    DB::beginTransaction();

    try {

        $classe = Classe::findOrFail($id);
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->salle_id = $request->salle_id;
        $classe->update();

        // Récupération des données de l'enseignant et de la salle

        $salle = Salle::find($request->salle_id); // Assurez-vous d'importer le modèle Salle

        DB::commit(); // Valide la transaction

        return response()->json([
            'status_code' => 200,
            'status_message' => 'La classe a été modifiée avec succès',
            'data' => $classe,
            'salle' => $salle
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur est survenue lors de la mise à jour de la classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function destroy($id)
{
    try {
        // Récupérer la classe à supprimer
        $classe = Classe::findOrFail($id);

        // Supprimer la classe
        $classe->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe supprimée avec succès',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'vous ne pouvez pas supprimer la classe parceque ya des apprenants qui sont associés' ,
            'error' => $e->getMessage(),
        ],500);
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
