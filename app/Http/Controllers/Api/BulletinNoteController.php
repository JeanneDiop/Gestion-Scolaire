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


public function updateBulletinNotess(Request $request, $bulletinId)
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

    // Vérifier si le bulletin existe avec l'identifiant donné
    $bulletin = BulletinNote::find($bulletinId);
    if (!$bulletin) {
        return response()->json(['error' => 'Le bulletin n\'existe pas'], 404);
    }

    $apprenant = Apprenant::find($bulletin->apprenant_id);
    if (!$apprenant) {
        return response()->json(['error' => 'Apprenant non trouvé'], 404);
    }

    $elevesDeLaClasse = Apprenant::where('classe_id', $apprenant->classe_id)->get();
    $elevesAvecMoyenne = $elevesDeLaClasse->sortByDesc('moyenne');
    $rangEleve = $elevesAvecMoyenne->search(fn($eleve) => $eleve->id == $apprenant->id) + 1;

    $totalMoyenneX = 0;
    $totalCoefficient = 0;
    $disciplinesData = [];

    foreach ($validatedData['disciplines'] as $discipline) {
        $disciplineNom = $discipline['discipline_nom'];
        $coefficient = Cours::where('nom', $disciplineNom)->value('coefficient') ?? 1;
        $moyenneNote = ($discipline['note_devoir'] + $discipline['note_composition']) / 2;
        $moyenneX = $moyenneNote * $coefficient;

        $moyennesDesApprenants = DB::table('notes')
            ->join('evaluation_apprenants', 'evaluation_apprenants.id', '=', 'notes.evaluation_apprenant_id')
            ->join('evaluations', 'evaluations.id', '=', 'evaluation_apprenants.evaluation_id')
            ->join('cours', 'cours.id', '=', 'evaluations.cours_id')
            ->where('cours.nom', $disciplineNom)
            ->select('evaluation_apprenants.apprenant_id', DB::raw('AVG(notes.note) as moyenne'))
            ->groupBy('evaluation_apprenants.apprenant_id')
            ->orderByDesc('moyenne')
            ->get();

        $rangNote = $moyennesDesApprenants->pluck('apprenant_id')->search($apprenant->id) + 1;

        $totalMoyenneX += $moyenneX;
        $totalCoefficient += $coefficient;

        $disciplinesData[] = [
            'discipline_nom' => $disciplineNom,
            'note_devoir' => $discipline['note_devoir'],
            'note_composition' => $discipline['note_composition'],
            'moyenne_note' => $moyenneNote,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenneX,
            'rang_note' => $rangNote,
            'appreciation' => $this->getAppreciation($moyenneNote),
        ];
    }

    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;
    $observations = $this->getObservations($moyenneEleve);

    $updated = $bulletin->update([
    'moyenne_eleve' => $moyenneEleve,
    'total_retards' => $validatedData['total_retards'] ?? 0,
    'total_absences' => $validatedData['total_absences'] ?? 0,
    'appreciation' => $this->getObservations($moyenneEleve),
    'rang_eleve' => $rangEleve,
    'disciplines' => json_encode($disciplinesData),
    'total' => json_encode([
        'total_coefficient' => $totalCoefficient,
        'total_moyenne_x' => $totalMoyenneX,
    ]),
]);

if (!$updated) {
    return response()->json(['error' => 'La mise à jour a échoué'], 500);
}

    return response()->json([
        'message' => 'Bulletin mis à jour avec succès',
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $observations,
    ]);
}

public function updateBulletinNote(Request $request, $bulletinId)
{
    // Valider les données entrantes
    $validatedData = $request->validate([
        'disciplines' => 'required|array',
        'disciplines.*.discipline_nom' => 'required|string',
        'disciplines.*.note_devoir1' => 'required|numeric|min:0|max:20',
        'disciplines.*.note_devoir2' => 'required|numeric|min:0|max:20',
        'disciplines.*.note_composition' => 'required|numeric|min:0|max:20',
        'total_retards' => 'nullable|integer|min:0',
        'total_absences' => 'nullable|integer|min:0',
        'observations' => 'nullable|string',
    ]);

    // Vérifier si le bulletin existe
    $bulletin = BulletinNote::find($bulletinId);
    if (!$bulletin) {
        return response()->json(['error' => 'Le bulletin n\'existe pas'], 404);
    }

    // Vérifier si l'apprenant existe
    $apprenant = Apprenant::find($bulletin->apprenant_id);
    if (!$apprenant) {
        return response()->json(['error' => 'Apprenant non trouvé'], 404);
    }

    $totalMoyenneX = 0;
    $totalCoefficient = 0;
    $disciplinesData = [];

    foreach ($validatedData['disciplines'] as $discipline) {
        $disciplineNom = $discipline['discipline_nom'];
        $coefficient = Cours::where('nom', $disciplineNom)->value('coefficient') ?? 1;

        // Assurer que les notes ne sont pas nulles
        $noteDevoir1 = $discipline['note_devoir1'] ?? 0;
        $noteDevoir2 = $discipline['note_devoir2'] ?? 0;
        $noteComposition = $discipline['note_composition'] ?? 0;

        // Calcul de la moyenne des devoirs
        $noteDevoir = ($noteDevoir1 + $noteDevoir2) / 2;

        // Calcul de la moyenne de la discipline
        $moyenneNote = ($noteDevoir + $noteComposition) / 2;
        $moyenneX = $moyenneNote * $coefficient;

        // Mise à jour des notes dans la table 'notes'
        DB::table('notes')
            ->join('evaluation_apprenants', 'evaluation_apprenants.id', '=', 'notes.evaluation_apprenant_id')
            ->join('evaluations', 'evaluations.id', '=', 'evaluation_apprenants.evaluation_id')
            ->join('cours', 'cours.id', '=', 'evaluations.cours_id')
            ->where('cours.nom', $disciplineNom)
            ->where('evaluation_apprenants.apprenant_id', $apprenant->id)
            ->update([
                'notes.note' => DB::raw("CASE
                    WHEN notes.type_note = 'devoir1' THEN $noteDevoir1
                    WHEN notes.type_note = 'devoir2' THEN $noteDevoir2
                    WHEN notes.type_note = 'composition' THEN $noteComposition
                    ELSE 0 END")
            ]);

        $totalMoyenneX += $moyenneX;
        $totalCoefficient += $coefficient;

        $disciplinesData[] = [
            'discipline_nom' => $disciplineNom,
            'note_devoir1' => $noteDevoir1,
            'note_devoir2' => $noteDevoir2,
            'note_devoir' => $noteDevoir, // Calculée
            'note_composition' => $noteComposition,
            'moyenne_note' => $moyenneNote,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenneX,
            'appreciation' => $this->getAppreciation($moyenneNote),
        ];
    }

    // Calcul de la moyenne générale de l'élève
    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;

    // Déterminer le rang des élèves
    $elevesAvecMoyenne = BulletinNote::whereNotNull('moyenne_eleve')
        ->orderByDesc('moyenne_eleve')
        ->get();

    $rangEleve = 1;
    $moyennePrecedente = null;

    $elevesAvecMoyenne->each(function ($eleve, $index) use (&$rangEleve, &$moyennePrecedente) {
        if ($moyennePrecedente !== null && $eleve->moyenne_eleve < $moyennePrecedente) {
            $rangEleve = $index + 1;
        }
        $eleve->rang = $rangEleve;
        $moyennePrecedente = $eleve->moyenne_eleve;
    });

    // Mise à jour du bulletin
    $updated = $bulletin->update([
        'moyenne_eleve' => $moyenneEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'appreciation' => $this->getObservations($moyenneEleve),
        'rang_eleve' => $rangEleve,
        'disciplines' => json_encode($disciplinesData),
        'total' => json_encode([
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ]),
    ]);

    if (!$updated) {
        return response()->json(['error' => 'La mise à jour a échoué'], 500);
    }

    return response()->json([
        'message' => 'Bulletin mis à jour avec succès',
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $this->getObservations($moyenneEleve),
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

    // Vérifier si l'apprenant existe
    $apprenant = Apprenant::find($apprenantId);
    if (!$apprenant) {
        return response()->json(['error' => 'Apprenant non trouvé'], 404);
    }

    // Vérifier si le bulletin existe pour cet apprenant et ce semestre
    $bulletin = BulletinNote::where('apprenant_id', $apprenantId)
                            ->where('semestre', $semestreId)
                            ->first();

    if (!$bulletin) {
        return response()->json(['error' => 'Bulletin non trouvé pour cet apprenant et ce semestre'], 404);
    }

    // Calcul des nouvelles moyennes et rangs
    $totalMoyenneX = 0;
    $totalCoefficient = 0;
    $disciplinesData = [];

    foreach ($validatedData['disciplines'] as $discipline) {
        $disciplineNom = $discipline['discipline_nom'];
        $coefficient = Cours::where('nom', $disciplineNom)->value('coefficient') ?? 1;
        $moyenneNote = ($discipline['note_devoir'] + $discipline['note_composition']) / 2;
        $moyenneX = $moyenneNote * $coefficient;

        // Calcul du rang de l'élève dans la matière
        $moyennesDesApprenants = DB::table('notes')
            ->join('evaluation_apprenants', 'evaluation_apprenants.id', '=', 'notes.evaluation_apprenant_id')
            ->join('evaluations', 'evaluations.id', '=', 'evaluation_apprenants.evaluation_id')
            ->join('cours', 'cours.id', '=', 'evaluations.cours_id')
            ->where('cours.nom', $disciplineNom)
            ->select('evaluation_apprenants.apprenant_id', DB::raw('AVG(notes.note) as moyenne'))
            ->groupBy('evaluation_apprenants.apprenant_id')
            ->orderByDesc('moyenne')
            ->get();

        $rangNote = $moyennesDesApprenants->pluck('apprenant_id')->search($apprenant->id) + 1;

        $totalMoyenneX += $moyenneX;
        $totalCoefficient += $coefficient;

        $disciplinesData[] = [
            'discipline_nom' => $disciplineNom,
            'note_devoir' => $discipline['note_devoir'],
            'note_composition' => $discipline['note_composition'],
            'moyenne_note' => $moyenneNote,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenneX,
            'rang_note' => $rangNote,
            'appreciation' => $this->getAppreciation($moyenneNote),
        ];
    }

    // Calcul de la moyenne de l'élève
    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;

    // Calcul du rang de l'élève dans la classe
    $elevesDeLaClasse = Apprenant::where('classe_id', $apprenant->classe_id)->get();
    $elevesAvecMoyenne = $elevesDeLaClasse->sortByDesc('moyenne');
    $rangEleve = $elevesAvecMoyenne->search(fn($eleve) => $eleve->id == $apprenant->id) + 1;

    // Mise à jour du bulletin
    $bulletin->update([
        'moyenne_eleve' => $moyenneEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'observations' => $validatedData['observations'] ?? '',
        'rang_eleve' => $rangEleve,
        'disciplines' => json_encode($disciplinesData),
        'total' => json_encode([
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ]),
    ]);

    return response()->json([
        'message' => 'Bulletin mis à jour avec succès',
        'apprenant_id' => $apprenantId,
        'semestre' => $semestreId,
        'disciplines' => $disciplinesData,
        'moyenne_eleve' => $moyenneEleve,
        'rang_eleve' => $rangEleve,
        'total_retards' => $validatedData['total_retards'] ?? 0,
        'total_absences' => $validatedData['total_absences'] ?? 0,
        'total' => [
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX,
        ],
        'observations' => $validatedData['observations'] ?? '',
    ]);
}


public function showBulletin($apprenantId, $semestreId)
{
    // Vérifier si l'apprenant existe
    $apprenant = Apprenant::with('user', 'classe')->find($apprenantId);
    if (!$apprenant) {
        return response()->json(['error' => 'Apprenant introuvable'], 404);
    }

    // Vérifier si le bulletin existe
    $bulletin = BulletinNote::where('apprenant_id', $apprenantId)
        ->where('semestre', $semestreId)
        ->first();

    if (!$bulletin) {
        return response()->json(['error' => 'Bulletin non trouvé pour cet apprenant et ce semestre'], 404);
    }

    // Récupérer les données nécessaires
    return response()->json([
        'apprenant_infos' => [
            'apprenant_id' => $apprenant->id,
            'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
            'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
            'date_naissance' => $apprenant->date_naissance ?? 'Date non trouvée',
            'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu non trouvé',
            'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro non trouvé',
            'classe' => $apprenant->classe->nom ?? 'N/A',
            'semestre' => $semestreId,
        ],
        'disciplines' => json_decode($bulletin->disciplines, true), // Décodage JSON des disciplines
        'moyenne_eleve' => $bulletin->moyenne_eleve,
        'rang_eleve' => $bulletin->rang_eleve,
        'total_retards' => $bulletin->total_retards,
        'total_absences' => $bulletin->total_absences,
        'observations' => $bulletin->observations,
        'total' => [
            'total_coefficient' => $bulletin->total['total_coefficient'] ?? 0,
            'total_moyenne_x' => $bulletin->total['total_moyenne_x'] ?? 0,
        ],
        'obervation_conseil_professeur' => $bulletin->obervation_conseil_professeur ?? '',
        'chef_etablissement' => $bulletin->chef_etablissement ?? '',
    ]);
}


public function showBulletinByBulletin($bulletinId)
{
    // Vérifier si le bulletin existe
    $bulletin = BulletinNote::with('apprenant.user', 'apprenant.classe')->find($bulletinId);

    if (!$bulletin) {
        return response()->json(['error' => 'Bulletin non trouvé'], 404);
    }

    // Récupérer les informations de l'apprenant
    $apprenant = $bulletin->apprenant;
    $user = $apprenant->user;
    $classe = $apprenant->classe;

    // Décoder les disciplines du bulletin
    $disciplines = json_decode($bulletin->disciplines, true);
    if ($disciplines === null) {
        $disciplines = []; // Ou vous pouvez envoyer une erreur si le format JSON est incorrect
    }

    return response()->json([
        'apprenant_infos' => [
            'apprenant_id' => $apprenant->id,
            'apprenant_nom' => $user->nom ?? 'Nom non renseigné',
            'apprenant_prenom' => $user->prenom ?? 'Prénom non renseigné',
            'date_naissance' => $apprenant->date_naissance ?? 'Date non renseignée',
            'lieu_naissance' => $apprenant->lieu_naissance ?? 'Lieu non renseigné',
            'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? 'Numéro non renseigné',
            'classe' => $classe->nom ?? 'Classe non renseignée',
            'semestre' => $bulletin->semestre,
        ],
        'disciplines' => $disciplines,
        'moyenne_eleve' => $bulletin->moyenne_eleve,
        'rang_eleve' => $bulletin->rang_eleve,
        'total_retards' => $bulletin->total_retards,
        'total_absences' => $bulletin->total_absences,
        'observations' => $bulletin->observations,
        'total' => [
            'total_coefficient' => $bulletin->total['total_coefficient'] ?? 0,
            'total_moyenne_x' => $bulletin->total['total_moyenne_x'] ?? 0,
        ],
        'obervation_conseil_professeur' => $bulletin->obervation_conseil_professeur ?? '',
        'chef_etablissement' => $bulletin->chef_etablissement ?? '',
    ]);
}




public function indexBulletins($classeId, $semestreId)
{
    Log::info("Requête pour les bulletins - Classe ID: $classeId, Semestre ID: $semestreId");

    // Vérifier si des bulletins existent pour ce semestre
    $bulletins = BulletinNote::where('semestre', $semestreId)
        ->whereHas('apprenant', function ($query) use ($classeId) {
            $query->where('classe_id', $classeId);
        })
        ->with(['apprenant.user', 'apprenant.classe'])
        ->get();

    // Debugging pour voir ce qui est récupéré
    Log::info('Nombre de bulletins trouvés : ' . $bulletins->count());

    if ($bulletins->isEmpty()) {
        return response()->json(['error' => 'Aucun bulletin trouvé'], 404);
    }

    // Vérifier les données brutes avant formatage
    Log::info('Données brutes récupérées : ', $bulletins->toArray());

    // Formatter correctement les résultats
    $formattedBulletins = $bulletins->map(function ($bulletin) {
        return [
            'bulletin_note_id' => $bulletin->id,
            'apprenant_infos' => [
                'apprenant_id' => $bulletin->apprenant->id ?? 'ID apprenant manquant',
                'apprenant_nom' => $bulletin->apprenant->user->nom ?? 'Nom non renseigné',
                'apprenant_prenom' => $bulletin->apprenant->user->prenom ?? 'Prénom non renseigné',
                'classe' => $bulletin->apprenant->classe->nom ?? 'Classe non renseignée',
            ],
            'moyenne_eleve' => $bulletin->moyenne_eleve ?? 'Moyenne non renseignée',
            'rang_eleve' => $bulletin->rang_eleve ?? 'Rang non renseigné',
        ];
    });

    // Vérifier le JSON final avant envoi
    Log::info('Bulletins formatés envoyés : ', $formattedBulletins->toArray());

    return response()->json(['bulletins' => $formattedBulletins]);
}


public function isEligibleForNextClass($apprenantId, $classeId)
{
    // Vérifier si l'apprenant existe
    $apprenant = Apprenant::find($apprenantId);
    if (!$apprenant) {
        return response()->json(['erreur' => "L'apprenant n'existe pas."], 404);
    }

    // Vérifier si la classe existe
    $classe = Classe::find($classeId);
    if (!$classe) {
        return response()->json(['erreur' => "La classe spécifiée est introuvable."], 404);
    }

    // Récupérer la classe actuelle de l'apprenant
    $classeActuelle = Classe::find($apprenant->classe_id);
    if (!$classeActuelle) {
        return response()->json(['erreur' => "La classe de l'apprenant est introuvable."], 404);
    }

    // Récupérer les moyennes des deux semestres (1 et 2 uniquement)
    $moyenneSemestre1 = BulletinNote::where('apprenant_id', $apprenantId)
        ->where('semestre', 1)
        ->value('moyenne_eleve');

    $moyenneSemestre2 = BulletinNote::where('apprenant_id', $apprenantId)
        ->where('semestre', 2)
        ->value('moyenne_eleve');

    // Vérifier si les deux moyennes existent
    if (is_null($moyenneSemestre1) || is_null($moyenneSemestre2)) {
        return response()->json(['erreur' => "Moyennes incomplètes pour cet élève."], 400);
    }

    // Calcul de la moyenne générale des deux semestres
    $moyenneGenerale = ($moyenneSemestre1 + $moyenneSemestre2) / 2;

    // Si la moyenne est inférieure à 10, l'élève redouble
    if ($moyenneGenerale < 10) {
        return response()->json([
            'message' => "L'élève redouble.",
            'moyenne_generale' => $moyenneGenerale
        ], 200);
    }

    // Tableau de classement des niveaux
    $niveauClasseRanking = [
        'ci' => 1,
        'cp' => 2,
        'ce1' => 3,
        'ce2' => 4,
        'cm1' => 5,
        'cm2' => 6,
        '6e' => 7,
        '5e' => 8,
        '4e' => 9,
        '3e' => 10,
        '2nde' => 11,
        '1ere' => 12,
        'terminale' => 13
    ];

    // Récupérer le niveau de la classe actuelle
    $niveauActuel = $classeActuelle->niveau_classe;

    // Vérifier si le niveau actuel existe dans le tableau
    if (isset($niveauClasseRanking[$niveauActuel])) {
        // Trouver le niveau suivant en fonction du classement des niveaux
        $niveauSuivant = $niveauClasseRanking[$niveauActuel] + 1;

        // Trouver la classe suivante avec le niveau correspondant
        $classeSuivante = Classe::where('niveau_classe', array_search($niveauSuivant, $niveauClasseRanking))
            ->first();
    } else {
        return response()->json(['erreur' => "Niveau de classe inconnu."], 400);
    }

    // Vérifier si une classe suivante a été trouvée
    if (!$classeSuivante) {
        return response()->json([
            'message' => "L'élève passe en classe supérieure.",
            'moyenne_generale' => $moyenneGenerale
        ], 200);
    }

    // Mettre à jour la classe de l'apprenant vers la classe suivante
    $apprenant->classe_id = $classeSuivante->id;
    $apprenant->save();

    return response()->json([
        'message' => "L'élève passe en classe supérieure.",
        'nouvelle_classe' => $classeSuivante->nom, // Nom de la classe
        'niveau_classe' => $classeSuivante->niveau_classe, // Ajout du niveau de la classe
        'moyenne_generale' => $moyenneGenerale
    ], 200);
}

public function verifierPassage($apprenantId, $classeId)
{
    $resultat = $this->isEligibleForNextClass($apprenantId, $classeId);
    return response()->json(['message' => $resultat]);
}
























}

































