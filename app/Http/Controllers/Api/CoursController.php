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
use App\Models\Historique;
use Carbon\Carbon;

class CoursController extends Controller
{
    public function store(CreateCoursRequest $request)
    {
        try {
            $cours = new Cours();
            $cours->nom = $request->nom;
            $cours->description = $request->description;
            $cours->niveau_education = $request->niveau_education;
            $cours->niveau_classe = $request->niveau_classe;
            $cours->heure_allouee = $request->heure_allouee;
            $cours->etat = $request->etat ?? 'encours';
            $cours->credits = $request->credits;
            $cours->duree_recommander_sceance=$request->duree_recommander_sceance ?? null;
            $cours->frequence_evaluation=$request->frequence_evaluation ?? null;
            $cours->type_evaluation=$request->type_evaluation ?? null;
            $cours->type_exercice=$request->type_exercice ?? null;
            $cours->coefficient=$request->coefficient ?? null;
            $cours->bareme=$request->bareme ?? null;
            $cours->categorie_cours=$request->categorie_cours ?? null;
            $cours->semestre=$request->semestre ?? null;
            $cours->leçons=$request->leçons ?? null;
            $cours->enseignant_id = $request->enseignant_id;
            $cours->save();
            Historique::create([
                'action' => 'create',  // Action 'update' pour la modification
                'message' => 'Cours ajouté : ' . $cours->nom,
                'user_id' => auth()->id(), // ID de l'utilisateur authentifié
                'cours_id' => $cours->id,  // ID de la salle modifiée
                'created_at' => Carbon::now(),
            ]);

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
            'evaluations.evaluationApprenants.apprenant.classe.salle',
            'classeAssociations.classe',
            'programme.classe', // Ajout de la classe liée à programme
            'categories.cours', // Assurez-vous que la relation 'cours' existe sur CategorieCours
            'categories.competences',
        ])->get();

        $result = $cours->map(function ($cours) {
            return [
                'id' => $cours->id,
                'nom' => $cours->nom,
                'description' => $cours->description,
                'niveau_education' => $cours->niveau_education,
                'niveau_classe' => $cours->niveau_classe,
                'heure_allouee' => $cours->heure_allouee,
                'etat' => $cours->etat,
                'credits' => $cours->credits,
                'semestre' => $cours->semestre,
                'objectif_generaux' => $cours->objectif_generaux,
                'objectif_specifiques' => $cours->objectif_specifiques,
                'programme' => $cours->programme ? [
                    'id' => $cours->programme->id,
                    'nom' => $cours->programme->nom,
                    'niveau_education' => $cours->programme->niveau_education,
                    'cycle' => $cours->programme->cycle,
                    'anne_scolaire' => $cours->programme->annee_scolaire,
                    'langue_enseignee' => $cours->programme->langue_enseignee,
                    'classe' => $cours->programme->classe ? [
                        'id' => $cours->programme->classe->id,
                        'nom' => $cours->programme->classe->nom,
                        'niveau_classe' => $cours->programme->classe->niveau_classe,
                        'niveau_education' => $cours->programme->classe->niveau_education,
                    ] : null,
                ] : null,
                'enseignant' => $cours->enseignant && $cours->enseignant->user ? [
                    'id' => $cours->enseignant->user->id,
                    'nom' => $cours->enseignant->user->nom,
                    'prenom' => $cours->enseignant->user->prenom,
                    'specialite' => $cours->enseignant->specialite,
                ] : null,
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
                'evaluations' => $cours->evaluations->map(function ($evaluation) {
                    return [
                        'id' => $evaluation->id,
                        'nom_evaluation' => $evaluation->nom_evaluation,
                        'date_evaluation' => $evaluation->date_evaluation,
                        'type_evaluation' => $evaluation->type_evaluation,
                        'evaluation_apprenants' => $evaluation->evaluationApprenants->map(function ($evaluationApprenant) {
                            return [

                                'classe_id' => $evaluationApprenant->classe_id,
                                'evaluation_id' => $evaluationApprenant->evaluation_id,
                                'apprenant_id' => $evaluationApprenant->apprenant_id,
                                'apprenant' => $evaluationApprenant->apprenant ? [
                                    'id' => $evaluationApprenant->apprenant->id,
                                    'nom' => $evaluationApprenant->apprenant->user->nom ?? null,
                                    'prenom' => $evaluationApprenant->apprenant->user->prenom ?? null,
                                    'email' => $evaluationApprenant->apprenant->user->email ?? null,
                                    'telephone' => $evaluationApprenant->apprenant->user->telephone ?? null,
                                    'date_naissance'=> $evaluationApprenant->apprenant->date_naissance ?? null ,
                                    'lieu_naissance'=> $evaluationApprenant->apprenant->lieu_naissance ?? null,
                                    'numero_CNI'=> $evaluationApprenant->apprenant->numero_CNI ?? null,
                                    'numero_identification_eleve'=> $evaluationApprenant->apprenant->numero_identification_eleve ?? null,
                                    'niveau_education'=> $evaluationApprenant->apprenant->niveau_education ?? null,
                                   'classe' => $evaluationApprenant->apprenant->classe ? [
                        'id' => $evaluationApprenant->apprenant->classe->id,
                        'nom' => $evaluationApprenant->apprenant->classe->nom,
                        'niveau_classe' => $evaluationApprenant->apprenant->classe->niveau_classe,
                        'niveau_education' => $evaluationApprenant->apprenant->classe->niveau_education,
                        'salle' => $evaluationApprenant->apprenant->classe->salle ? [
                            'id' => $evaluationApprenant->apprenant->classe->salle->id,
                            'nom' => $evaluationApprenant->apprenant->classe->salle->nom,
                            'capacity' => $evaluationApprenant->apprenant->classe->salle->capacity,
                            'type' => $evaluationApprenant->apprenant->classe->salle->type,
                        ] : null,
                    ] : null,
                                ] : null,
                            ];
                        }),
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

            'evaluations.evaluationApprenants.apprenant.classe.salle',
            'classeAssociations.classe',
            'programme.classe',
            'categories.cours',
            'categories.competences',
        ])->findOrFail($id);
        $coursData = [
            'id' => $cours->id,
                'nom' => $cours->nom,
                'description' => $cours->description,
                'niveau_education' => $cours->niveau_education,
                'niveau_classe' => $cours->niveau_classe,
                'heure_allouee' => $cours->heure_allouee,
                'etat' => $cours->etat,
                'credits' => $cours->credits,
                'semestre' => $cours->semestre,
                'objectif_generaux' => $cours->objectif_generaux,
                'objectif_specifiques' => $cours->objectif_specifiques,
             'programme' => $cours->programme  ? [
                'id' => $cours->programme->id,
                'nom' => $cours->programme->nom,
                'niveau_education' => $cours->programme->niveau_education,
                'cycle' => $cours->programme->cycle,
                'anne_scolaire' => $cours->programme->annee_scolaire,
                'langue_enseignee' => $cours->programme->langue_enseignee,
                'classe' => $cours->programme->classe ? [
                    'id' => $cours->programme->classe->id,
                    'nom' => $cours->programme->classe->nom,
                    'niveau_classe' => $cours->programme->classe->niveau_classe,
                    'niveau_education' => $cours->programme->classe->niveau_education,
                ] : null,
            ] : null,
          'enseignant' => $cours->enseignant && $cours->enseignant->user ? [
    'id' => $cours->enseignant->user->id,
    'nom' => $cours->enseignant->user->nom,
    'prenom' => $cours->enseignant->user->prenom,
    'specialite' => $cours->enseignant->specialite,
] : null,
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
               'evaluations' => $cours->evaluations->map(function ($evaluation) {
    return [
        'id' => $evaluation->id,
        'nom_evaluation' => $evaluation->nom_evaluation,
        'date_evaluation' => $evaluation->date_evaluation,
        'type_evaluation' => $evaluation->type_evaluation,
        'evaluation_apprenants' => $evaluation->evaluationApprenants->map(function ($evaluationApprenant) {
            return [

                'classe_id_id' => $evaluationApprenant->classe_id,
                                'evaluation_id' => $evaluationApprenant->evaluation_id,
                                'apprenant_id' => $evaluationApprenant->apprenant_id,
                                'apprenant' => $evaluationApprenant->apprenant ? [
                                    'id' => $evaluationApprenant->apprenant->id,
                                    'nom' => $evaluationApprenant->apprenant->user->nom ?? null,
                                    'prenom' => $evaluationApprenant->apprenant->user->prenom ?? null,
                                    'email' => $evaluationApprenant->apprenant->user->email ?? null,
                                    'telephone' => $evaluationApprenant->apprenant->user->telephone ?? null,
                                    'date_naissance'=> $evaluationApprenant->apprenant->date_naissance ?? null ,
                                    'lieu_naissance'=> $evaluationApprenant->apprenant->lieu_naissance ?? null,
                                    'numero_CNI'=> $evaluationApprenant->apprenant->numero_CNI ?? null,
                                    'numero_identification_eleve'=> $evaluationApprenant->apprenant->numero_identification_eleve ?? null,
                                    'niveau_education'=> $evaluationApprenant->apprenant->niveau_education ?? null,
                                    'classe' => $evaluationApprenant->apprenant->classe ? [
                        'id' => $evaluationApprenant->apprenant->classe->id,
                        'nom' => $evaluationApprenant->apprenant->classe->nom,
                        'niveau_classe' => $evaluationApprenant->apprenant->classe->niveau_classe,
                        'niveau_education' => $evaluationApprenant->apprenant->classe->niveau_education,
                        'salle' => $evaluationApprenant->apprenant->classe->salle ? [
                            'id' => $evaluationApprenant->apprenant->classe->salle->id,
                            'nom' => $evaluationApprenant->apprenant->classe->salle->nom,
                            'capacity' => $evaluationApprenant->apprenant->classe->salle->capacity,
                            'type' => $evaluationApprenant->apprenant->classe->salle->type,
                        ] : null,
                    ] : null,
                ] : null,
            ];
        }),
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
