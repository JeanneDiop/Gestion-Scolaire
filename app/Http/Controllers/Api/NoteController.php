<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Note\CreateNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;

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
            $note->evaluation_id = $request->evaluation_id;
            $note->save();

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
        // Récupérer la note par son ID avec les relations associées
        $note = Note::with(['evaluation.apprenant.user', 'evaluation.cours.enseignant.user'])
            ->findOrFail($id); // Lève une exception 404 si la note n'est pas trouvée

        // Récupérer les informations de l'apprenant
        $apprenant = $note->evaluation->apprenant;

        // Construire la réponse avec les détails de la note
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
                'lieu_naissance' => $apprenant->lieu_naissance,
                'date_naissance' => $apprenant->date_naissance,
                'numero_CNI' => $apprenant->numero_CNI,
                'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                'niveau_education' => $apprenant->niveau_education,
                'statut_marital' => $apprenant->statut_marital,
            ],
            'note' => [
                'id' => $note->id,
                'note' => $note->note,
                'type_note' => $note->type_note,
                'date_note' => $note->date_note,
                'evaluation' => [
                    'id' => $note->evaluation->id,
                    'nom_evaluation' => $note->evaluation->nom_evaluation,
                    'date_evaluation' => $note->evaluation->date_evaluation,
                    'type_evaluation' => $note->evaluation->type_evaluation,
                    'cours' => [
                        'id' => $note->evaluation->cours->id,
                        'nom' => $note->evaluation->cours->nom,
                        'enseignant' => [
                            'id' => $note->evaluation->cours->enseignant->id,
                            'nom' => $note->evaluation->cours->enseignant->user->nom,
                            'specialite' => $note->evaluation->cours->enseignant->specialite,
                        ]
                    ]
                ],

            ]
        ];

        return response()->json($result);
    } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite Note non trouvée ou une erreur est survenue..',
                'error' => $e->getMessage(),
            ], 500);
    }
}



public function index()
{
    try {
        $notes = Note::with(['evaluation.apprenant.user', 'evaluation.cours.enseignant.user'])
            ->get()
            ->groupBy(function ($note) {
                return $note->evaluation->apprenant->id;
            });

        // Formater les résultats pour éviter la répétition des données de l'apprenant
        $result = [];

        foreach ($notes as $apprenantId => $apprenantNotes) {
            $apprenant = $apprenantNotes->first()->evaluation->apprenant;

            // Récupérer les notes pour cet apprenant
            $notesArray = $apprenantNotes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'type_note' => $note->type_note,
                    'date_note' => $note->date_note,
                    'evaluation' => [
                        'id' => $note->evaluation->id,
                        'nom_evaluation' => $note->evaluation->nom_evaluation,
                        'date_evaluation' => $note->evaluation->date_evaluation,
                        'type_evaluation' => $note->evaluation->type_evaluation,
                        'cours' => [
                            'id' => $note->evaluation->cours->id,
                            'nom' => $note->evaluation->cours->nom,
                            'enseignant' => [
                                'id' => $note->evaluation->cours->enseignant->id,
                                'nom' => $note->evaluation->cours->enseignant->user->nom,
                                'specialite' => $note->evaluation->cours->enseignant->specialite,
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
                    'lieu_naissance' => $apprenant->lieu_naissance,
                    'date_naissance' => $apprenant->date_naissance,
                    'numero_CNI' => $apprenant->numero_CNI,
                    'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                    'niveau_education' => $apprenant->niveau_education,
                    'statut_marital' => $apprenant->statut_marital,
                ],
                'notes' => $notesArray,
            ];
        }

        return response()->json($result);
    } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'error => Une erreur est survenue lors de la récupération des notes.',
                'error' => $e->getMessage(),
            ], 500);
    }
}

//la fonction qui nous permet d'afficher tous les notes des apprenants d'une classe
    public function showNotesByClasse($classeId)
{
    try {
        // Récupérer les apprenants de la classe
        $apprenants = Apprenant::with(['notes.evaluation.cours.enseignant.user'])
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
                        'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                        'niveau_education' => $apprenant->niveau_education,
                        'statut_marital' => $apprenant->statut_marital,
                    ],
                    'note' => [
                        'note_value' => $note->note,
                        'cours' => [
                            'nom' => $note->evaluation->cours->nom ?? null,
                            'enseignant' => $note->evaluation->cours->enseignant->user->nom ?? null,
                        ],
                        'evaluation' => [
                            'id' => $note->evaluation->id,
                            'nom_evaluation' => $note->evaluation->nom_evaluation,
                            'date_evaluation' => $note->evaluation->date_evaluation,
                            'type_evaluation' => $note->evaluation->type_evaluation,
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
