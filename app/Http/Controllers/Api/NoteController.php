<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Note\CreateNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Historique;
use Carbon\Carbon;

use App\Models\Apprenant;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class NoteController extends Controller
{
    public function store(CreateNoteRequest $request)
    {
        try {
            $note = new Note();
            $note->note = $request->note;
            $note->type_note = $request->type_note;
            $note->date_note = $request->date_note;
            $note->evaluation_apprenant_id = $request->evaluation_apprenant_id;
            $note->save();
            Historique::create([
                'action' => 'create',  // Action 'update' pour la modification
                'message' => 'Note ajouté : ' . $note->nom,
                'user_id' => auth()->id(), // ID de l'utilisateur authentifié
                'note_id' => $note->id,  // ID de la salle modifiée
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'note a été ajoutée',
                'data' =>  $note,
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du note',
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function update(UpdateNoteRequest $request, $id)
{
    try {
        // Récupérer la note par son ID
        $note = Note::findOrFail($id);

        // Mettre à jour les attributs de la note
        $note->note = $request->note;
        $note->type_note = $request->type_note;
        $note->date_note = $request->date_note;
         $note->evaluation_id = $request->evaluation_id;

        // Sauvegarder les modifications
        $note->update();
        Historique::create([
            'action' => 'update',
            'message' => 'Note modifiée : ' . $note->nom,
            'user_id' => auth()->id(),
            'note_id' => $note->id,
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'La note a été mise à jour avec succès.',
            'data' => $note,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Note non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la note.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        $note = Note::with('evaluationApprenant.evaluation.cours.enseignant.user', 'evaluationApprenant.apprenant.user')->find($id);

        if (!$note) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Note introuvable',
            ], 404);
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Note récupérée avec succès',
            'data' => $note,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la note',
            'error' => $e->getMessage(),
        ], 500);
    }
}



public function index()
{
    try {
        // Charger les données avec la relation evaluationApprenant et les autres relations
        $notes = Note::with([
                'evaluationApprenant.evaluation.cours.enseignant.user',
                'evaluationApprenant.apprenant.user'
            ])
            ->get()
            ->groupBy(function ($note) {
                return $note->evaluationApprenant && $note->evaluationApprenant->apprenant ? $note->evaluationApprenant->apprenant->id : null;
            });

        // Formater les résultats pour éviter la répétition des données de l'apprenant
        $result = [];

        foreach ($notes as $apprenantId => $apprenantNotes) {
            $apprenant = $apprenantNotes->first()->evaluationApprenant->apprenant ?? null;

            if (!$apprenant) {
                continue; // S'assurer que l'apprenant existe
            }

            // Récupérer les notes pour cet apprenant
            $notesArray = $apprenantNotes->map(function ($note) {
                // Récupérer l'information de la relation evaluationApprenant
                $evaluationApprenant = $note->evaluationApprenant;

                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'type_note' => $note->type_note,
                    'date_note' => $note->date_note,
                    'evaluation_apprenant_id' => $evaluationApprenant ? $evaluationApprenant->id : 'non spécifié',
                    'evaluation' => [
                        'id' => $note->evaluation ? $note->evaluation->id : 'non spécifié',
                        'nom_evaluation' => $note->evaluation ? $note->evaluation->nom_evaluation : 'non spécifié',
                        'date_evaluation' => $note->evaluation ? $note->evaluation->date_evaluation : 'non spécifié',
                        'type_evaluation' => $note->evaluation ? $note->evaluation->type_evaluation : 'non spécifié',
                        'cours' => [
                            'id' => $note->evaluation && $note->evaluation->cours ? $note->evaluation->cours->id : 'non spécifié',
                            'nom' => $note->evaluation && $note->evaluation->cours ? $note->evaluation->cours->nom : 'non spécifié',
                            'enseignant' => [
                                'id' => $note->evaluation && $note->evaluation->cours && $note->evaluation->cours->enseignant ? $note->evaluation->cours->enseignant->id : 'non spécifié',
                                'nom' => $note->evaluation && $note->evaluation->cours && $note->evaluation->cours->enseignant ? $note->evaluation->cours->enseignant->user->nom : 'non spécifié',
                                'specialite' => $note->evaluation && $note->evaluation->cours && $note->evaluation->cours->enseignant ? $note->evaluation->cours->enseignant->specialite : 'non spécifié',
                            ]
                        ]
                    ],
                ];
            });

            // Ajouter l'apprenant et ses notes au résultat
            $result[] = [
                'apprenant' => [
                    'id' => $apprenant->id,
                    'nom' => $apprenant->user->nom ?? null,
                    'prenom' => $apprenant->user->prenom ?? null,
                    'telephone' => $apprenant->user->telephone ?? null,
                    'email' => $apprenant->user->email ?? null,
                    'adresse' => $apprenant->user->adresse ?? null,
                    'genre' => $apprenant->user->genre ?? null,
                    'etat' => $apprenant->user->etat ?? null,
                    'lieu_naissance' => $apprenant->lieu_naissance ?? null,
                    'date_naissance' => $apprenant->date_naissance ?? null,
				     'numero_CNI'=> $apprenant->numero_CNI,
				    'numero_identification_eleve'=>$apprenant->numero_identification_eleve,
				    'niveau_education'=>$apprenant->niveau_education,
				    'nationalité'=>$apprenant->nationalité,
				    'regime_paiement'=>$apprenant->regime_paiement,
				   'reduction_bourse'=>$apprenant->reduction_bourse,
				   'statut_paiement_actuel'=>$apprenant->statut_paiement_actuel,
				   'references_factures'=>$apprenant->references_factures,
				   'conditions_medicales'=>$apprenant->conditions_medicales,
                ],
                'notes' => $notesArray,
            ];
        }

        return response()->json($result);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur est survenue lors de la récupération des notes.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
//la fonction qui nous permet d'afficher tous les notes des apprenants d'une classe
public function showNotesByClasse($classeId)
{
    try {
        // Récupérer les apprenants de la classe
        $apprenants = Apprenant::with(['notes.evaluationApprenant.evaluation.cours.enseignant.user'])
            ->where('classe_id', $classeId)
            ->get();

        // Si aucun apprenant n'a été trouvé
        if ($apprenants->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Aucun apprenant trouvé pour cette classe.'
            ], 404);
        }

        // Traiter chaque apprenant et leurs notes
        $result = $apprenants->map(function ($apprenant) {
            return $apprenant->notes->map(function ($note) use ($apprenant) {
                return [
                    'apprenant' => [
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
                        'numero_identification_eleve' => $apprenant->numero_identification_eleve,
                        'niveau_education' => $apprenant->niveau_education,
                    ],
                    'note' => [
                        'note_value' => $note->note,
                        'cours' => [
                            'nom' => $note->evaluationApprenant->evaluation->cours->nom ?? null,
                            'enseignant' => $note->evaluationApprenant->evaluation->cours->enseignant->user->nom ?? null,
                        ],
                        'evaluation' => [
                            'id' => $note->evaluationApprenant->evaluation->id ?? null,
                            'nom_evaluation' => $note->evaluationApprenant->evaluation->nom_evaluation ?? null,
                            'date_evaluation' => $note->evaluationApprenant->evaluation->date_evaluation ?? null,
                            'type_evaluation' => $note->evaluationApprenant->evaluation->type_evaluation ?? null,
                        ]
                    ]
                ];
            });
        })->flatten(1); // Aplatir la collection pour éviter des sous-collections imbriquées

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Notes de la classe récupérées avec succès.',
            'data' => $result
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des notes de la classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function destroy($id)
{
    try {
        // Récupérer la note par son ID
        $note = Note::findOrFail($id);

        // Supprimer la note
        $note->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Note supprimée avec succès',
        ],200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la note',
            'error' => $e->getMessage(),
        ],500);
    }
}

}
