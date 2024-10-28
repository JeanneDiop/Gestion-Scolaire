<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cours;
use App\Models\Enseignant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Cours\CreateCoursRequest;
use App\Http\Requests\Cours\updateCoursRequest;

class CoursController extends Controller
{
    public function store(CreateCoursRequest $request)
    {
        try {
            $cours = new Cours();
            $cours->nom = $request->nom;
            $cours->description = $request->description;
            $cours->niveau_education = $request->niveau_education;
            $cours->heure_allouée = $request->heure_allouée;
            $cours->etat = $request->etat ?? 'encours';
            $cours->credits = $request->credits;
            $cours->coefficient = $request->coefficient;
            $cours->enseignant_id = $request->enseignant_id;
            $cours->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Cours a été ajoutée',
                'data' => $cours,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du cours',
                'error' => $e->getMessage(),
            ]);
        }
    }

public function index()
{
    try {
        $cours = Cours::with([
            'enseignant.user',
            'evaluations.apprenant.user',
            'classeAssociations.classe',
            'programmeclasse'
        ])->get();

        $result = $cours->map(function ($cours) {
            return [
                'id' => $cours->id,
                'nom' => $cours->nom,
                'description' => $cours->description,
                'duree' => $cours->duree,
                'programme_classe' => $cours->programmeClasse ? [
                    'id' => $cours->programmeClasse->id,
                    'nom' => $cours->programmeClasse->nom,
                    'description' => $cours->programmeClasse->description,
                    'niveau_classe' => $cours->programmeClasse->niveau_classe,
                    'niveau_education' => $cours->programmeClasse->niveau_education,
                    'periode' => $cours->programmeClasse->periode,
                ] : null,
                'enseignant' => [
                    'id' => $cours->enseignant->user->id,
                    'nom' => $cours->enseignant->user->nom,
                    'prenom' => $cours->enseignant->user->prenom,
                    'specialite' => $cours->enseignant->specialite,
                ],
                'evaluations' => $cours->evaluations->map(function ($evaluation) {
                    $apprenant = $evaluation->apprenant;
                    $classe = $apprenant->classe; 
                    $salle = $classe ? $classe->salle : null;

                    return [
                        'id' => $evaluation->id,
                        'nom_evaluation' => $evaluation->nom_evaluation,
                        'date_evaluation' => $evaluation->date_evaluation,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'apprenant' => [
                            'id' => $apprenant->user->id,
                            'nom' => $apprenant->user->nom,
                            'prenom' => $apprenant->user->prenom,
                            'classe' => [
                                'id' => $classe ? $classe->id : null,
                                'nom_classe' => $classe ? $classe->nom : null,
                                'salle' => $salle ? [
                                    'id' => $salle->id,
                                    'nom_salle' => $salle->nom,
                                ] : null
                            ]
                        ]
                    ];
                }),
                'classes_associées' => $cours->classeAssociations->map(function ($association) {
                    return [
                        'classe_id' => $association->classe_id,
                        'niveau_classe' => $association->classe ? $association->classe->niveau_classe : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Tous les cours ont été récupérés avec succès.',
            'data' => $result,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        $cours = Cours::with([
            'enseignant.user',
            'evaluations.apprenant.user',
            'classeAssociations.classe',
            'programmeclasse'
        ])->findOrFail($id);
        $coursData = [
            'id' => $cours->id,
            'nom' => $cours->nom,
            'description' => $cours->description,
            'duree' => $cours->duree,
            'programme_classe' => $cours->programmeClasse ? [
                'id' => $cours->programmeClasse->id,
                    'nom' => $cours->programmeClasse->nom,
                    'description' => $cours->programmeClasse->description,
                    'niveau_classe' => $cours->programmeClasse->niveau_classe,
                    'niveau_education' => $cours->programmeClasse->niveau_education,
                    'periode' => $cours->programmeClasse->periode,
            ] : null,
            'enseignant' => [
                'id' => $cours->enseignant->user->id,
                'nom' => $cours->enseignant->user->nom,
                'prenom' => $cours->enseignant->user->prenom,
                'specialite' => $cours->enseignant->specialite,
            ],
            'evaluations' => $cours->evaluations->map(function ($evaluation) {
                $apprenant = $evaluation->apprenant;

                return [
                    'id' => $evaluation->id,
                    'nom_evaluation' => $evaluation->nom_evaluation,
                    'date_evaluation' => $evaluation->date_evaluation,
                    'type_evaluation' => $evaluation->type_evaluation,
                    'apprenant' => [
                        'id' => $apprenant->user->id,
                        'nom' => $apprenant->user->nom,
                        'prenom' => $apprenant->user->prenom,
                        'classe' => $apprenant->classe ? [
                            'id' => $apprenant->classe->id,
                            'nom_classe' => $apprenant->classe->nom,
                        ] : null
                    ]
                ];
            }),
            'classes_associées' => $cours->classeAssociations->map(function ($association) {
                return [
                    'classe_id' => $association->classe_id,
                    'niveau_classe' => $association->classe ? $association->classe->niveau_classe : null,
                ];
            }),
        ];

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails du cours récupérés avec succès.',
            'data' => $coursData,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Cours non trouvé',
            'error' => 'Le cours avec l\'ID spécifié n\'existe pas.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des détails du cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function destroy($id)
{
    try {
        $cours = Cours::findOrFail($id);

        $cours->delete();
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Cours supprimé avec succès.',
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Cours non trouvé',
            'error' => 'Le cours avec l\'ID spécifié n\'existe pas.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
