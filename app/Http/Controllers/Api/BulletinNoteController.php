<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bulletin\CreateBulletinRequest;
use App\Http\Requests\Bulletin\updateBulletinRequest;
use App\Models\Apprenant;
use App\Models\BulletinNote;
use App\Models\Classe;
use App\Models\Cours;
use App\Models\Evaluation;
use App\Models\EvaluationApprenant;
use App\Models\Note;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulletinNoteController extends Controller
{

private function getAppreciation($moyenneNote)
{
    if ($moyenneNote >= 18) {
        return 'Excellent travail';
    } elseif ($moyenneNote >= 16) {
        return 'Très bon travail';
    } elseif ($moyenneNote >= 14) {
        return 'Bon travail';
    } elseif ($moyenneNote >= 12) {
        return 'Travail satisfaisant';
    } elseif ($moyenneNote >= 10) {
        return 'Peut mieux faire';
    } elseif ($moyenneNote >= 8) {
        return 'Insuffisant';
    } else {
        return 'Médiocre';
    }
}

// Fonction pour obtenir les observations
private function getObservations($moyenneEleve)
{
    if ($moyenneEleve >= 18) {
        return 'tableau honneur';
    } elseif ($moyenneEleve >= 16) {
        return 'félicitations';
    } elseif ($moyenneEleve >= 14) {
        return 'encouragement';
    } elseif ($moyenneEleve >= 12) {
        return 'satisfaisant';
    } elseif ($moyenneEleve >= 10) {
        return 'peut mieux faire';
    } elseif ($moyenneEleve >= 8) {
        return 'insuffisant';
    } elseif ($moyenneEleve >= 6) {
        return 'risque redoublement';
    } elseif ($moyenneEleve >= 4) {
        return 'risque exclusion';
    } elseif ($moyenneEleve >= 2) {
        return 'avertissement';
    } else {
        return 'blâme';
    }
}

public function getNotesForSemestreAndCreateBulletin($apprenantId, $semestreId)
{
    $apprenant = Apprenant::with('user', 'classe')->find($apprenantId);
    if (!$apprenant) {
        return response()->json(['error' => 'Apprenant introuvable'], 404);
    }

    // Récupérer les notes pour l'apprenant et le semestre spécifié
    $notes = DB::table('notes')
        ->join('evaluation_apprenants', 'evaluation_apprenants.id', '=', 'notes.evaluation_apprenant_id')
        ->join('evaluations', 'evaluations.id', '=', 'evaluation_apprenants.evaluation_id')
        ->join('cours', 'cours.id', '=', 'evaluations.cours_id')
        ->select(
            'notes.*',
            'cours.id as discipline_id',
            'cours.nom as discipline_nom',
            'cours.coefficient'
        )
        ->where('evaluation_apprenants.apprenant_id', $apprenantId)
        ->where('notes.semestre', $semestreId)
        ->orderBy('cours.id')
        ->get();

    if ($notes->isEmpty()) {
        return response()->json(['error' => 'Aucune note trouvée pour cet apprenant au semestre spécifié'], 404);
    }

    $disciplinesData = [];
    $totalMoyenneX = 0;
    $totalCoefficient = 0;
    $totalRetards = 0;
    $totalAbsences = 0;

    // Regroupement des notes par discipline
    foreach ($notes->groupBy('discipline_id') as $disciplineId => $disciplineNotes) {
        $discipline = Cours::find($disciplineId);
        if (!$discipline) continue;

        $note_devoir1 = 0;
        $note_devoir2 = 0;
        $note_composition = 0;

        foreach ($disciplineNotes as $note) {
            if ($note->type_note === 'devoir1') {
                $note_devoir1 = $note->note;
            }
            if ($note->type_note === 'devoir2') {
                $note_devoir2 = $note->note;
            }
            if ($note->type_note === 'examen') {
                $note_composition = $note->note;
            }
        }

        // Correction : Vérification pour éviter une division par zéro
        $moyenne_devoirs = ($note_devoir1 + $note_devoir2) > 0 ? ($note_devoir1 + $note_devoir2) / 2 : 0;
        $moyenne_note = $note_composition ? ($moyenne_devoirs + $note_composition) / 2 : $moyenne_devoirs;

        // Vérification du coefficient
        $coefficient = $discipline->coefficient ?? 1;
        $moyenne_x = $moyenne_note * $coefficient;

        // Récupérer les moyennes des autres apprenants
        $moyennesDesApprenants = DB::table('notes')
            ->join('evaluation_apprenants', 'evaluation_apprenants.id', '=', 'notes.evaluation_apprenant_id')
            ->join('evaluations', 'evaluations.id', '=', 'evaluation_apprenants.evaluation_id')
            ->where('evaluations.cours_id', $disciplineId)
            ->where('notes.semestre', '=', $semestreId)
            ->select('evaluation_apprenants.apprenant_id', DB::raw('AVG(notes.note) as moyenne'))
            ->groupBy('evaluation_apprenants.apprenant_id')
            ->orderByDesc('moyenne')
            ->get();

        // Déterminer le rang de l'apprenant
        $rangNote = $moyennesDesApprenants->pluck('apprenant_id')->search($apprenantId) + 1;

        // Ajouter les données de cette discipline
        $disciplinesData[] = [
            'discipline_nom' => $discipline->nom,
            'note_devoir' => $moyenne_devoirs,
            'note_composition' => $note_composition,
            'moyenne_note' => $moyenne_note,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenne_x,
            'rang_note' => $rangNote,
            'th' => 'th',
            'appreciation' => $this->getAppreciation($moyenne_note),

        ];

        $totalMoyenneX += $moyenne_x;
        $totalCoefficient += $coefficient;
    }

    // Présences
    $presences = Presence::where('apprenant_id', $apprenantId)
        ->whereIn('cours_id', array_column($notes->toArray(), 'discipline_id'))
        ->get();

    $totalRetards = $presences->where('statut', 'retard')->count();
    $totalAbsences = $presences->where('statut', 'absence')->count();

    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;

    // Calcul du rang
    // Calculer les moyennes des élèves et trier par moyenne croissante
$elevesAvecMoyenne = Apprenant::where('classe_id', $apprenant->classe_id)
->get()
->map(function ($eleve) use ($semestreId) {
    $notes = Note::whereHas('evaluationApprenant', function ($query) use ($eleve, $semestreId) {
        $query->where('apprenant_id', $eleve->id)->where('semestre', $semestreId);
    })->get();

    $totalMoyenneX = 0;
    $totalCoefficient = 0;

    foreach ($notes as $note) {
        $cours = Cours::find($note->evaluationApprenant->evaluation->cours_id);
        if (!$cours) continue;

        $coefficient = $cours->coefficient ?? 1;
        $moyenneX = $note->note * $coefficient;

        $totalMoyenneX += $moyenneX;
        $totalCoefficient += $coefficient;
    }

    return [
        'apprenant_id' => $eleve->id,
        'moyenne_eleve' => $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0,
    ];
})
->sortBy('moyenne_eleve') // Trier par moyenne croissante
->values();

// Déterminer le rang de l'apprenant dans la liste triée
$rangEleve = $elevesAvecMoyenne->search(function ($item) use ($apprenant) {
return $item['apprenant_id'] === $apprenant->id;
}) + 1;


    // Mise à jour du bulletin
    BulletinNote::updateOrCreate(
        ['apprenant_id' => $apprenantId, 'semestre' => $semestreId],
        [
           'note_devoir' => $moyenne_devoirs,
        'note_composition' => $note_composition,
        'moyenne_note' => $moyenne_note,
        'coefficient' => $coefficient,
        'moyenne_x' => $moyenne_x,
        'rang_eleve' => $rangEleve,
        'moyenne_eleve' => $moyenneEleve,
        'total_retards' => $totalRetards,
        'total_absences' => $totalAbsences,
        'appreciation' => $this->getObservations($moyenne_note),
        'rang_note' => $rangNote, // Mettre le rangNote dans la base
        'disciplines' => json_encode($disciplinesData),// Mettre disciplinesData dans la base
        'observations' => $this->getObservations($moyenneEleve),
        'total' => [  // Regroupement sous "Total"
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX
        ],
        ]
    );

    return response()->json([
        'apprenant_infos' => [
            'apprenant_id' => $apprenant->id,
            'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
            'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
            'date_naissance' => $apprenant->date_naissance ?? 'Date de naissance non trouvée',
            'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu de naissance non trouvé',
            'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro d\'identification non trouvé',
            'classe' => $apprenant->classe->nom ?? 'N/A',
            'semestre' => $semestreId,
        ],
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'coefficient' =>$totalCoefficient,
        'moyenne_x' =>$totalMoyenneX,
        'rang_eleve' => $rangEleve,
        'total_retards' => $totalRetards,
        'total_absences' => $totalAbsences,
        'observations' => $this->getObservations($moyenneEleve),
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX
        ],
        'obervation_conseil_professeur' => '',
        'chef_etablissement' => '',
    ]);
}





public function updateBulletinNotes(Request $request, $apprenantId, $semestreId)
{
    // Valider les données entrantes
    $validatedData = $request->validate([
        'disciplines' => 'required|array',
        'disciplines.*.discipline_nom' => 'required|string',
        'disciplines.*.note_devoir' => 'required|numeric|min:0|max:20',
        'disciplines.*.note_composition' => 'required|numeric|min:0|max:20',
        'total_retards' => 'nullable|integer|min:0',
        'total_absences' => 'nullable|integer|min:0',
        'observations' => 'nullable|string',
    ]);

    // Vérifier si le bulletin existe
    $bulletin = BulletinNote::where('apprenant_id', $apprenantId)
                             ->where('semestre', $semestreId)
                             ->first();

    if (!$bulletin) {
        return response()->json(['error' => ' Ce Bulletin n\'existe pas dans la base de donné'], 404);
    }

    $apprenant = Apprenant::find($apprenantId);
    $elevesDeLaClasse = Apprenant::where('classe_id', $apprenant->classe_id)->get();

    $elevesAvecMoyenne = $elevesDeLaClasse->sortByDesc('moyenne');

    // Trouver le rang de l'élève
    $rangEleve = $elevesAvecMoyenne->search(function ($eleve) use ($apprenant) {
        return $eleve['apprenant_id'] == $apprenant->id;
    }) + 1;

    $totalMoyenneX = 0;
    $totalCoefficient = 0;
    $disciplinesData = [];

    foreach ($validatedData['disciplines'] as $discipline) {
        $coefficient = Cours::where('nom', $discipline['discipline_nom'])->value('coefficient') ?? 1;
        $moyenneNote = ($discipline['note_devoir'] + $discipline['note_composition']) / 2;
        $moyenneX = $moyenneNote * $coefficient;

        $totalMoyenneX += $moyenneX;
        $totalCoefficient += $coefficient;

        $disciplinesData[] = [
            'discipline_nom' => $discipline['discipline_nom'],
            'note_devoir' => $discipline['note_devoir'],
            'note_composition' => $discipline['note_composition'],
            'moyenne_note' => $moyenneNote,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenneX,
            'rang_eleve' => $rangEleve,
            'th'=>'th',
            'appreciation' => $this->getAppreciation($moyenneNote),
        ];
    }

    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;
    $observations = $this->getObservations($moyenneEleve);

    $elevesAvecMoyenne = $elevesDeLaClasse->map(function($eleve) use ($semestreId) {
        $notes = Note::whereHas('evaluationApprenant', function($query) use ($eleve, $semestreId) {
            $query->where('apprenant_id', $eleve->id)
                  ->where('semestre', $semestreId);
        })->get();

        $totalMoyenneX = 0;
        $totalCoefficient = 0;

        foreach ($notes as $note) {
            $coefficient = Cours::find($note->evaluationApprenant->evaluation->cours_id)->coefficient ?? 1;
            $moyenneNote = (($note->note_devoir1 ?? 0) + ($note->note_devoir2 ?? 0) + ($note->note_composition ?? 0)) / 3;
            $moyenneX = $moyenneNote * $coefficient;

            $totalMoyenneX += $moyenneX;
            $totalCoefficient += $coefficient;
        }

        $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;

        return [
            'apprenant_id' => $eleve->id,
            'moyenne' => $moyenneEleve,
        ];
    });

    $apprenantInfos = [
        'apprenant_id' => $apprenant->id,
        'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
        'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
        'date_naissance' => $apprenant->date_naissance ?? 'Date de naissance non trouvée',
        'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu de naissance non trouvé',
        'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro d\'identification non trouvé',
        'classe' => $apprenant->classe->nom ?? 'N/A',
        'semestre' => $semestreId,
    ];

    $elevesAvecMoyenne = $elevesAvecMoyenne->sortByDesc('moyenne');

    $rangEleve = $elevesAvecMoyenne->search(function ($eleve) use ($apprenant) {
        return $eleve['apprenant_id'] == $apprenant->id;
    }) + 1;

    $bulletin->update([
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'coefficient' => $totalCoefficient,
        'moyenne_x' => $totalMoyenneX,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $observations,
    ]);

    return response()->json([
        'message' => 'Bulletin mis à jour avec succès',
        'apprenant_infos' => $apprenantInfos,
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'coefficient' => $totalCoefficient,
        'moyenne_x' => $totalMoyenneX,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $observations,
        'obervation_conseil_professeur' => '',
        'chef_etablissement' => '',
    ]);
}

public function updateBulletinNote(Request $request, $bulletinId)
{
    // Valider les données entrantes
    $validatedData = $request->validate([
        'disciplines' => 'required|array',
        'disciplines.*.discipline_nom' => 'required|string',
        'disciplines.*.note_devoir' => 'required|numeric|min:0|max:20',
        'disciplines.*.note_composition' => 'required|numeric|min:0|max:20',
        'total_retards' => 'nullable|integer|min:0',
        'total_absences' => 'nullable|integer|min:0',
        'observations' => 'nullable|string',
    ]);

    // Vérifier si le bulletin existe en utilisant bulletinId
    $bulletin = BulletinNote::find($bulletinId);

    if (!$bulletin) {
        return response()->json(['error' => 'Bulletin introuvable'], 404);
    }

    $apprenant = Apprenant::find($bulletin->apprenant_id);
    $elevesDeLaClasse = Apprenant::where('classe_id', $apprenant->classe_id)->get();

    $elevesAvecMoyenne = $elevesDeLaClasse->sortByDesc('moyenne');

    // Trouver le rang de l'élève
    $rangEleve = $elevesAvecMoyenne->search(function ($eleve) use ($apprenant) {
        return $eleve['apprenant_id'] == $apprenant->id;
    }) + 1;

    // Traitement des disciplines et calcul des moyennes
    $disciplinesData = [];
    $totalMoyenneX = 0;
    $totalCoefficient = 0;

    foreach ($validatedData['disciplines'] as $discipline) {
        $coefficient = Cours::where('nom', $discipline['discipline_nom'])->value('coefficient') ?? 1;
        $moyenneNote = ($discipline['note_devoir'] + $discipline['note_composition']) / 2;
        $moyenneX = $moyenneNote * $coefficient;

        $totalMoyenneX += $moyenneX;
        $totalCoefficient += $coefficient;

        $disciplinesData[] = [
            'discipline_nom' => $discipline['discipline_nom'],
            'note_devoir' => $discipline['note_devoir'],
            'note_composition' => $discipline['note_composition'],
            'moyenne_note' => $moyenneNote,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenneX,
            'rang_eleve' => $rangEleve,
            'th' => 'th',
            'appreciation' => $this->getAppreciation($moyenneNote),
        ];
    }

    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;
    $observations = $this->getObservations($moyenneEleve);

    // Mise à jour du bulletin
    $bulletin->update([
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'coefficient' => $totalCoefficient,
        'moyenne_x' => $totalMoyenneX,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $observations,
    ]);

    return response()->json([
        'message' => 'Bulletin mis à jour avec succès',
        'apprenant_infos' => [
            'apprenant_id' => $apprenant->id,
            'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
            'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
            'date_naissance' => $apprenant->date_naissance ?? 'Date de naissance non trouvée',
            'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu de naissance non trouvé',
            'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro d’identification non trouvé',
            'classe' => $apprenant->classe->nom ?? 'N/A',
            'semestre' => $bulletin->semestre,
        ],
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'coefficient' => $totalCoefficient,
        'moyenne_x' => $totalMoyenneX,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $observations,
        'obervation_conseil_professeur' => '',
        'chef_etablissement' => '',
    ]);
}



public function showBulletin($apprenantId, $semestreId)
{
    // Récupérer l'apprenant
    $apprenant = Apprenant::with('user', 'classe')->find($apprenantId);
    if (!$apprenant) {
        return response()->json(['error' => 'Apprenant introuvable'], 404);
    }

    // Récupérer le bulletin de l'apprenant pour le semestre spécifié
    $bulletin = BulletinNote::where('apprenant_id', $apprenantId)
                            ->where('semestre', $semestreId)
                            ->first();

    if (!$bulletin) {
        return response()->json(['error' => 'Bulletin non trouvé pour cet apprenant au semestre spécifié'], 404);
    }

    // Récupérer les informations liées au bulletin
    $disciplinesData = json_decode($bulletin->disciplines, true);
    $observations = $bulletin->observations ?? 'Aucune observation disponible';
    $totalCoefficient = $bulletin->total['total_coefficient'] ?? 0;
    $totalMoyenneX = $bulletin->total['total_moyenne_x'] ?? 0;
    $moyenneEleve = $bulletin->moyenne_eleve ?? 0;
    $rangEleve = $bulletin->rang_eleve ?? 0;
    $totalRetards = $bulletin->total_retards ?? 0;
    $totalAbsences = $bulletin->total_absences ?? 0;

    return response()->json([
        'apprenant_infos' => [
            'apprenant_id' => $apprenant->id,
            'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
            'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
            'date_naissance' => $apprenant->date_naissance ?? 'Date de naissance non trouvée',
            'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu de naissance non trouvé',
            'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro d\'identification non trouvé',
            'classe' => $apprenant->classe->nom ?? 'N/A',
            'semestre' => $semestreId,
        ],
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'coefficient' => $totalCoefficient,
        'moyenne_x' => $totalMoyenneX,
        'rang_eleve' => $rangEleve,
        'total_retards' => $totalRetards,
        'total_absences' => $totalAbsences,
        'observations' => $observations,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX
        ],
        'obervation_conseil_professeur' => '',
        'chef_etablissement' => '',
    ]);
}



public function indexBulletins()
{
    // Récupérer tous les bulletins avec les relations nécessaires
    $bulletins = BulletinNote::with(['apprenant.user', 'apprenant.classe'])->get();

    // Vérifier s'il y a des bulletins disponibles
    if ($bulletins->isEmpty()) {
        return response()->json(['error' => 'Aucun bulletin trouvé'], 404);
    }

    // Transformer les données pour la réponse
    $result = $bulletins->map(function ($bulletin) {
        $apprenant = $bulletin->apprenant;
        $disciplinesData = json_decode($bulletin->disciplines, true);

        return [
            'apprenant_infos' => [
                'apprenant_id' => $apprenant->id,
                'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
                'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
                'date_naissance' => $apprenant->date_naissance ?? 'Date de naissance non trouvée',
                'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu de naissance non trouvé',
                'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro d\'identification non trouvé',
                'classe' => $apprenant->classe->nom ?? 'N/A',
                'semestre' => $bulletin->semestre,
            ],
            'disciplines' => $disciplinesData,
            'moyenne_eleve' => $bulletin->moyenne_eleve ?? 0,
            'coefficient' => $bulletin->total['total_coefficient'] ?? 0,
            'moyenne_x' => $bulletin->total['total_moyenne_x'] ?? 0,
            'rang_eleve' => $bulletin->rang_eleve ?? 0,
            'total_retards' => $bulletin->total_retards ?? 0,
            'total_absences' => $bulletin->total_absences ?? 0,
            'observations' => $bulletin->observations ?? 'Aucune observation disponible',
            'total' => [
                'total_coefficient' => $bulletin->total['total_coefficient'] ?? 0,
                'total_moyenne_x' => $bulletin->total['total_moyenne_x'] ?? 0
            ],
            'observation_conseil_professeur' => '',
            'chef_etablissement' => '',
        ];
    });

    return response()->json($result);
}

















}

































