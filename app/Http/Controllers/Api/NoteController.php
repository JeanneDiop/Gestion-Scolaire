<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Note\CreateNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Historique;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        // Récupérer toutes les notes avec les relations associées
        $notes = Note::with('evaluationApprenant.evaluation.cours.enseignant.user', 'evaluationApprenant.apprenant.user')->get();

        // Vérifier si des notes ont été récupérées
        if ($notes->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Aucune note trouvée',
            ], 404);
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Notes récupérées avec succès',
            'data' => $notes,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des notes',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showNotesByApprenant($apprenantId)
{
    try {
        // Récupérer l'apprenant avec ses évaluations, cours, enseignants et notes
        $apprenant = Apprenant::with([
            'evaluationApprenants.evaluation', // Charger les évaluations associées
            'evaluationApprenants.evaluation.cours', // Charger les cours associés aux évaluations
            'evaluationApprenants.evaluation.cours.enseignant', // Charger les enseignants associés aux cours
            'evaluationApprenants.note', // Charger les notes associées
        ])
        ->where('id', $apprenantId)
        ->first();

        // Si l'apprenant n'est pas trouvé
        if (!$apprenant) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Apprenant non trouvé.',
            ], 404);
        }

        // Traiter les données de l'apprenant et ses notes
        $result = [
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
                'numero_CNI' => $apprenant->numero_CNI ?? null,
                'niveau_education' => $apprenant->niveau_education ?? null,
                'statut_marital' => $apprenant->statut_marital ?? null,
                'classe' => $apprenant->classe ? [
                    'id' => $apprenant->classe->id,
                    'nom' => $apprenant->classe->nom,
                    'niveau_classe' => $apprenant->classe->niveau_classe,
                ] : null,
            ],
            'notes' => $apprenant->evaluationApprenants->map(function ($evaluationApprenant) {
                // Vérifier s'il existe une note associée à l'évaluation
                $note = $evaluationApprenant->note;
                return [
                    'note_value' => $note ? $note->note : null, // Afficher la note ou null si absente
                    'type_note' => $note ? $note->type_note : null, // Afficher le type de note ou null
                    'date_note' => $note ? $note->date_note : null, // Afficher la date de note ou null
                    'evaluation' => [
                        'id' => $evaluationApprenant->evaluation->id ?? null,
                        'nom_evaluation' => $evaluationApprenant->evaluation->nom_evaluation ?? null,
                        'date_evaluation' => $evaluationApprenant->evaluation->date_evaluation ?? null,
                        'type_evaluation' => $evaluationApprenant->evaluation->type_evaluation ?? null,
                    ],
                    'cours' => [
                        'id' => $evaluationApprenant->evaluation->cours->id ?? null,
                        'nom' => $evaluationApprenant->evaluation->cours->nom ?? null,
                    ],
                    'enseignant' => [
                        'id' => $evaluationApprenant->evaluation->cours->enseignant->id ?? null,
                        'nom' => $evaluationApprenant->evaluation->cours->enseignant->user->nom ?? null,
                        'prenom' => $evaluationApprenant->evaluation->cours->enseignant->user->prenom ?? null,
                    ],
                ];
            }),
        ];

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Notes récupérées avec succès.',
            'data' => $result,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne serveur',
            'error' => $e->getMessage(),
        ], 500);
    }
}
//la fonction qui nous permet d'afficher tous les notes des apprenants d'une classe
public function showNotesByClasse($classeId)
{
    try {
        // Récupérer les apprenants de la classe avec leurs notes, évaluations, cours et enseignants
        $apprenants = Apprenant::with([
            'evaluationApprenants.evaluation', // Chargement des évaluations associées à chaque apprenant
            'evaluationApprenants.evaluation.cours',  // Charger les cours associés aux évaluations
            'evaluationApprenant.evaluation.cours.enseignant.user',
            'evaluationApprenants.note',
            'classe.salle', // Charger la classe de l'apprenant
        ])
        ->where('classe_id', $classeId)
        ->get();


        // Si aucun apprenant n'a été trouvé
        if ($apprenants->isEmpty()) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Aucun apprenant trouvé pour cette classe.',
            ], 404);
        }

        // Traiter chaque apprenant et leurs notes
        $result = $apprenants->map(function ($apprenant) {
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
                    'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                    'niveau_education' => $apprenant->niveau_education,
                    'statut_marital' => $apprenant->statut_marital,
                    'classe' => $apprenant->classe ? [
                        'id' => $apprenant->classe->id,
                        'nom' => $apprenant->classe->nom,
                        'niveau_classe' => $apprenant->classe->niveau_classe,
                        'salle' => $apprenant->classe->salle ? [
                            'id' => $apprenant->classe->salle->id,
                            'nom' => $apprenant->classe->salle->nom,
                            'capacity' => $apprenant->classe->salle->capacity,
                            'type' => $apprenant->classe->salle->type
                        ] : null
                    ] : null,
                    ],
                'notes' => $apprenant->evaluationApprenants->map(function ($evaluationApprenant) {
                    return [
                        'note_value' => $evaluationApprenant->note ?? null, // Access to the note in evaluationApprenant
                        'type_note' => $evaluationApprenant->note->type_note ?? null, // Corrected access to the note's type
                        'note' => $evaluationApprenant->note->note ?? null, // Corrected access to the note's value
                        'date_note' => $evaluationApprenant->note->date_note ?? null, // Corrected access to the note's date
                        'evaluation' => [
                            'id' => $evaluationApprenant->evaluation->id ?? null,
                            'nom_evaluation' => $evaluationApprenant->evaluation->nom_evaluation ?? null,
                            'date_evaluation' => $evaluationApprenant->evaluation->date_evaluation ?? null,
                            'type_evaluation' => $evaluationApprenant->evaluation->type_evaluation ?? null,
                        ],
                        'cours' => [
                            'id' => $evaluationApprenant->evaluation->cours->id ?? null,
                            'nom' => $evaluationApprenant->evaluation->cours->nom ?? null,
                        ],
                        'enseignant' => [
                            'id' => $evaluationApprenant->evaluation->cours->enseignant->id ?? null,
                            'nom' => $evaluationApprenant->evaluation->cours->enseignant->user->nom ?? null,
                            'prenom' => $evaluationApprenant->evaluation->cours->enseignant->user->prenom ?? null,
                        ],
                    ];
                }),
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Notes récupérées avec succès.',
            'data' => $result,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne serveur',
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
