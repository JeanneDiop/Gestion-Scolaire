<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Note\CreateNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;
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
        // Trouver la note par son ID
        $note = Note::findOrFail($id);

        // Mettre à jour les attributs de la note
        $note->note = $request->note;
        $note->type_note = $request->type_note;
        $note->date_note = $request->date_note;
        $note->evaluation_id = $request->evaluation_id;
        $note->save();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Note mise à jour avec succès',
            'data' => $note,
        ],200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Note non trouvée',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la note',
            'error' => $e->getMessage(),
        ],500);
    }
}
public function index()
{
    try {
        // Récupérer toutes les notes avec les relations associées
        $notes = Note::with([
            'evaluation.apprenant.user',        // Récupérer l'apprenant et l'utilisateur associé
            'evaluation.apprenant.tuteur.user', // Récupérer le tuteur et l'utilisateur associé
            'evaluation.apprenant.classe.salle',// Récupérer la classe de l'apprenant et la salle associée
            'evaluation.cours.enseignant.user'  // Récupérer le cours, l'enseignant et l'utilisateur associé
        ])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des notes récupérée avec succès',
            'data' => $notes,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des notes',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupérer la note avec les relations associées
        $note = Note::with([
            'evaluation.apprenant.user', // Récupérer l'apprenant et l'utilisateur associé
            'evaluation.apprenant.tuteur.user', // Récupérer le tuteur et l'utilisateur associé
            'evaluation.apprenant.classe.salle', // Récupérer la classe de l'apprenant et la salle associée
            'evaluation.cours.enseignant.user' // Récupérer le cours, l'enseignant et l'utilisateur associé
        ])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Note récupérée avec succès',
            'data' => $note,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la note',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showNotesByApprenant($apprenantId)
    {
        try {
            // Récupérer les notes de l'apprenant spécifié avec les relations nécessaires
            $notes = Note::with([
                'evaluation.cours.enseignant.user', // Récupérer les informations de l'enseignant et son utilisateur
                'evaluation.apprenant.user',        // Récupérer les informations de l'apprenant et son utilisateur
                'evaluation.apprenant.tuteur.user', // Récupérer les informations du tuteur
                'evaluation.apprenant.classe.salle' // Récupérer les informations de la salle
            ])
            ->whereHas('evaluation.apprenant', function($query) use ($apprenantId) {
                $query->where('id', $apprenantId);
            })
            ->get();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Notes de l\'apprenant récupérées avec succès.',
                'data' => $notes, // Les notes incluront déjà les données liées
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de la récupération des notes de l\'apprenant.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
//afficher les notes dune classe
public function showNotesByClasse($classeId)
{
    try {
        // Récupérer les notes de tous les apprenants dans la classe spécifiée avec les relations nécessaires
        $notes = Note::with([
            'evaluation.cours.enseignant.user',
            'evaluation.apprenant.user',
            'evaluation.apprenant.tuteur.user',
            'evaluation.apprenant.classe.salle'  
        ])
        ->whereHas('evaluation.apprenant.classe', function($query) use ($classeId) {
            $query->where('id', $classeId);
        })
        ->get();

        // Préparer la structure de données à retourner sans grouper les notes incorrectement
        $result = $notes->map(function ($note) {
            $apprenant = $note->evaluation->apprenant;
            return [
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
                    'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
                    'niveau_education' => $apprenant->niveau_education,
                    'statut_marital' => $apprenant->statut_marital,
                ],
                'note' => [
                    'note_value' => $note->note, // Assurez-vous que 'note' est le bon attribut
                    'cours' => [
                        'nom' => $note->evaluation->cours->nom,
                        'enseignant' => $note->evaluation->cours->enseignant->user->nom,
                    ],
                    'evaluation' => [
                        'id' => $note->evaluation->id,
                        'nom_evaluation' => $note->evaluation->nom_evaluation, // Assurez-vous que 'nom_evaluation' est le bon attribut de l'évaluation
                        'date_evaluation' => $note->evaluation->date_evaluation,   // Assurez-vous que 'date_evaluation' est le bon attribut de l'évaluation
                        'type_evaluation' => $note->evaluation->type_evaluation    // Exemple : type d'évaluation (examen, test, etc.)
                    ]
                ]
            ];
        });

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Notes de la classe récupérées avec succès.',
            'data' => $result,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des notes de la classe.',
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
