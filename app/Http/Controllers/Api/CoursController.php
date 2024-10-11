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
            $cours->duree = $request->duree;
            $cours->etat = $request->etat ?? 'encours';
            $cours->credits = $request->credits;
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
                'evaluations.apprenant.classe.salle'
            ])->get();

            $result = $cours->map(function ($course) {
                return [
                    'id' => $course->id,
                    'nom' => $course->nom,
                    'description' => $course->description,
                    'duree' => $course->duree,
                    'enseignant' => [
                        'id' => $course->enseignant->user->id,
                        'nom' => $course->enseignant->user->nom,
                        'prenom' => $course->enseignant->user->prenom,
                        'specialite' => $course->enseignant->specialite,
                    ],
                    'evaluations' => $course->evaluations->map(function ($evaluation) {
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
            'evaluations.apprenant.user', // Ici, on charge l'utilisateur de l'apprenant
            'classeAssociations.classe' // Charger les classes associées
        ])->findOrFail($id);

        $result = [
            'id' => $cours->id,
            'nom' => $cours->nom,
            'description' => $cours->description,
            'duree' => $cours->duree,
            'enseignant' => [
                'id' => $cours->enseignant->user->id,
                'nom' => $cours->enseignant->user->nom,
                'prenom' => $cours->enseignant->user->prenom,
                'specialite' => $cours->enseignant->specialite,
            ],
            'evaluations' => $cours->evaluations->map(function ($evaluation) {
                $apprenant = $evaluation->apprenant;
                $classe = $apprenant->classe; // Utiliser la relation classe de l'apprenant
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
                                'nom_salle' => $salle->nom
                            ] : null
                        ]
                    ]
                ];
            }),
            'classes_associées' => $cours->classeAssociations->map(function ($association) {
                return [
                    'classe_id' => $association->classe_id, // Afficher le classe_id
                    'nom_classe' => $association->classe ? $association->classe->nom : null,
                ];
            }),
        ];

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le cours a été récupéré avec succès.',
            'data' => $result,
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Cours non trouvé.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération du cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

      public function destroy($id)
{
    DB::beginTransaction();

    try {
        // Récupération du cours à supprimer
        $cours = Cours::findOrFail($id);

        // Vérification si le cours a un enseignant associé
        if ($cours->enseignant_id) {
            // Mettre à null l'enseignant associé avant la suppression du cours
            $cours->enseignant_id = null;
            $cours->save();
        }

        // Suppression du cours
        $cours->delete();

        DB::commit(); // Valide la transaction

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le cours a été supprimé avec succès.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur est survenue lors de la suppression du cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
