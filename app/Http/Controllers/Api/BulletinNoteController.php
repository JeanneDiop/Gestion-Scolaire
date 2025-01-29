<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Bulletin\CreateBulletinRequest;
use App\Http\Requests\Bulletin\updateBulletinRequest;
use App\Models\EvaluationApprenant;
use App\Models\BulletinNote;
use App\Models\Note;
use App\Models\Presence;
use App\Models\Cours;
use App\Models\Apprenant;
class BulletinNoteController extends Controller
{




public function getNotesForSemestreAndCreateBulletins($apprenantId, $semestreId, Request $request)
{
    // Récupérer les informations de l'apprenant et de sa classe
    $apprenant = Apprenant::with('user', 'classe')->find($apprenantId);
    if (!$apprenant) {
        return ['error' => 'Apprenant introuvable'];
    }

    // Récupérer les notes groupées par discipline et node
    $notesGroupedByDisciplineAndNode = Note::whereHas('evaluationApprenant', function ($query) use ($apprenantId, $semestreId) {
        $query->where('apprenant_id', $apprenantId)
              ->where('semestre', $semestreId);
    })
    ->with(['evaluationApprenant.evaluation' => function ($query) {
        $query->select('id', 'cours_id'); // Charger les cours associés
    }, 'evaluationApprenant.apprenant'])
    ->get()
    ->groupBy(function ($note) {
        return $note->evaluationApprenant->evaluation->cours_id . '-' . $note->node_id;
    });

    $bulletins = []; // Tableau pour stocker les bulletins par discipline

    $totalMoyenneX = 0;
    $totalCoefficient = 0;

    foreach ($notesGroupedByDisciplineAndNode as $key => $notes) {
        // Extraire discipline (cours_id) et node_id à partir de la clé
        [$disciplineId, $nodeId] = explode('-', $key);

        // Charger le nom et le coefficient depuis la table cours
        $discipline = Cours::find($disciplineId);
        $disciplineNom = $discipline->nom ?? 'N/A';
        $coefficient = $discipline->coefficient ?? 1;

        // Calcul des notes
        $note_devoir1 = null;
        $note_devoir2 = null;
        $note_composition = null;

        foreach ($notes as $note) {
            if ($note->type_note === 'devoir1') {
                $note_devoir1 = $note->note;
            } elseif ($note->type_note === 'devoir2') {
                $note_devoir2 = $note->note;
            } elseif ($note->type_note === 'examen') {
                $note_composition = $note->note;
            }
        }

        // Calcul des moyennes
        $moyenne_devoirs = (($note_devoir1 ?? 0) + ($note_devoir2 ?? 0)) / 2;
        $moyenne_note = ($moyenne_devoirs + ($note_composition ?? 0)) / 2;
        $moyenne_x = $moyenne_note * $coefficient;

        // Accumuler les totaux pour le bulletin général
        $totalMoyenneX += $moyenne_x;
        $totalCoefficient += $coefficient;

        // Calcul des observations
        $appreciation = ''; // Initialiser l'appréciation
        if ($moyenne_note >= 18) {
            $appreciation = 'Très bon travail';
        } elseif ($moyenne_note >= 16) {
            $appreciation = 'Assez bien';
        } elseif ($moyenne_note >= 14) {
            $appreciation = 'Bon travail';
        } elseif ($moyenne_note >= 12) {
            $appreciation = 'Insuffisant';
        } else {
            $appreciation = 'Excellent travail';
        }



        // Trier les élèves par leur moyenne pour chaque discipline et déterminer le rang de la note
        $notesForDiscipline = $notes->sortByDesc(function ($note) use ($note_devoir1, $note_devoir2, $note_composition) {
            $moyenneDevoirs = (($note_devoir1 ?? 0) + ($note_devoir2 ?? 0)) / 2;
            $moyenneNote = ($moyenneDevoirs + ($note_composition ?? 0)) / 2;
            return $moyenneNote;
        });

        // Trouver le rang de la note dans la discipline
        $rangNote = $notesForDiscipline->search(function ($note) use ($note_devoir1, $note_devoir2, $note_composition) {
            $moyenneDevoirs = (($note_devoir1 ?? 0) + ($note_devoir2 ?? 0)) / 2;
            $moyenneNote = ($moyenneDevoirs + ($note_composition ?? 0)) / 2;
            return $moyenneNote === $note->note;
        }) + 1;  // +1 car les index commencent à 0

        $bulletins[] = [
            'discipline_id' => $disciplineId,
            'discipline_nom' => $disciplineNom,
            'node_id' => $nodeId,
            'note_devoir1' => $note_devoir1,
            'note_devoir2' => $note_devoir2,
            'note_composition' => $note_composition,
            'moyenne_note' => $moyenne_note,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenne_x,
            'TH' => 'th',
            'appreciation' => $appreciation,
            'rang_note' => $rangNote // Ajouter le rang de la note
        ];
    }

    // Calcul du total pour le bulletin
    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;

    $observations = ''; // Initialiser les observations

        if ($moyenneEleve >= 18) {
            $observations = 'tableau honneur';
        } elseif ($moyenneEleve >= 16) {
            $observations = 'felicitation';
        } elseif ($moyenneEleve >= 14) {
            $observations = 'encouragement';
        } elseif ($moyenneEleve >= 12) {
            $observations = 'satisfaisant';
        } elseif ($moyenneEleve >= 10) {
            $observations = 'peut mieux faire';
        } elseif ($moyenneEleve >= 8) {
            $observations = 'insuffisant';
        } elseif ($moyenneEleve >= 6) {
            $observations = 'insuffisant';
        } elseif ($moyenneEleve >= 4) {
            $observations = 'risque redoubler';
        } elseif ($moyenneEleve >= 2) {
            $observations = 'avertissement';
        } else {
            $observations = 'blâme';
        }
    // Récupérer les élèves de la même classe
    $elevesDeLaClasse = Apprenant::where('classe_id', $apprenant->classe_id)
        ->with('user') // Récupérer les informations sur l'apprenant
        ->get();

    // Calculer la moyenne générale pour chaque élève
    $elevesAvecMoyenne = $elevesDeLaClasse->map(function($eleve) use ($semestreId) {
        // Calcul de la moyenne de chaque élève pour le semestre
        $notes = Note::whereHas('evaluationApprenant', function($query) use ($eleve, $semestreId) {
            $query->where('apprenant_id', $eleve->id)
                  ->where('semestre', $semestreId);
        })->get();

        // Calcul de la moyenne de l'élève
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

    // Trier les élèves par moyenne décroissante
    $elevesAvecMoyenne = $elevesAvecMoyenne->sortByDesc('moyenne');

    // Trouver le rang de l'élève
    $rangEleve = $elevesAvecMoyenne->search(function ($eleve) use ($apprenant) {
        return $eleve['apprenant_id'] == $apprenant->id;
    }) + 1;  // +1 car les index commencent à 0

    // Ajouter le rang à l'information du bulletin
    $bulletinGeneral['rang_eleve'] = $rangEleve;

    // Récupérer les retards et absences depuis la table presences
    $presences = Presence::where('apprenant_id', $apprenantId)
        ->whereIn('cours_id', array_column($bulletins, 'discipline_id'))
        ->get();

    $totalRetards = $presences->where('statut', 'retard')->count();
    $totalAbsences = $presences->where('statut', 'absence')->count();


    // Observations
    $observationsConseilProfesseur = 'Excellent travail, continuez ainsi.';
    $observationsChefEtablissement = 'Très prometteur, à suivre de près.';

    // Ajouter les totaux et observations dans le bulletin général
    $bulletinGeneral = [
        'Total' => [  // Regroupement sous "Total"
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX
        ],
        'moyenne_eleve' => $moyenneEleve,
        'rang_eleve' => $rangEleve,
        'total_retards' => $totalRetards,
        'total_absences' => $totalAbsences,
        'observations' => $observations,
        'observations_conseil_professeur' => $observationsConseilProfesseur,
        'observations_chef_etablissement' => $observationsChefEtablissement,
    ];

    // Informations de l'apprenant et du semestre
    $apprenantInfos = [
        'apprenant_id' => $apprenant->id,
        'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
        'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
        'classe' => $apprenant->classe->nom ?? 'N/A',
        'semestre' => $semestreId,
    ];

    return [
        'apprenant_infos' => $apprenantInfos,
        'bulletins_notes' => $bulletins,
        'bulletin_general' => $bulletinGeneral
    ];
}


public function getNotesForSemestreAndCreateBulletin($apprenantId, $semestreId)
{
    $apprenant = Apprenant::with('user', 'classe')->find($apprenantId);
    if (!$apprenant) {
        return ['error' => 'Apprenant introuvable'];
    }

    // Récupérer les notes groupées par discipline et node
    $notesGroupedByDisciplineAndNode = Note::whereHas('evaluationApprenant', function ($query) use ($apprenantId, $semestreId) {
        $query->where('apprenant_id', $apprenantId)
              ->where('semestre', $semestreId);
    })
    ->with(['evaluationApprenant.evaluation' => function ($query) {
        $query->select('id', 'cours_id'); // Charger les cours associés
    }, 'evaluationApprenant.apprenant'])
    ->get()
    ->groupBy(function ($note) {
        return $note->evaluationApprenant->evaluation->cours_id . '-' . $note->node_id;
    });

    $bulletins = []; // Tableau pour stocker les bulletins par discipline

    $totalMoyenneX = 0;
    $totalCoefficient = 0;

    foreach ($notesGroupedByDisciplineAndNode as $key => $notes) {
        // Extraire discipline (cours_id) et node_id à partir de la clé
        [$disciplineId, $nodeId] = explode('-', $key);

        // Charger le nom et le coefficient depuis la table cours
        $discipline = Cours::find($disciplineId);
        $disciplineNom = $discipline->nom ?? 'N/A';
        $coefficient = $discipline->coefficient ?? 1;

        // Calcul des notes
        $note_devoir1 = null;
        $note_devoir2 = null;
        $note_composition = null;

        foreach ($notes as $note) {
            if ($note->type_note === 'devoir1') {
                $note_devoir1 = $note->note;
            } elseif ($note->type_note === 'devoir2') {
                $note_devoir2 = $note->note;
            } elseif ($note->type_note === 'examen') {
                $note_composition = $note->note;
            }
        }

        // Calcul des moyennes
        $moyenne_devoirs = (($note_devoir1 ?? 0) + ($note_devoir2 ?? 0)) / 2;
        $moyenne_note = ($moyenne_devoirs + ($note_composition ?? 0)) / 2;
        $moyenne_x = $moyenne_note * $coefficient;

        // Accumuler les totaux pour le bulletin général
        $totalMoyenneX += $moyenne_x;
        $totalCoefficient += $coefficient;

        // Calcul des observations
        $appreciation = ''; // Initialiser l'appréciation
        if ($moyenne_note >= 18) {
            $appreciation = 'Très bon travail';
        } elseif ($moyenne_note >= 16) {
            $appreciation = 'Assez bien';
        } elseif ($moyenne_note >= 14) {
            $appreciation = 'Bon travail';
        } elseif ($moyenne_note >= 12) {
            $appreciation = 'Insuffisant';
        } else {
            $appreciation = 'Excellent travail';
        }

        // Trier les élèves par leur moyenne pour chaque discipline et déterminer le rang de la note
        $notesForDiscipline = $notes->sortByDesc(function ($note) use ($note_devoir1, $note_devoir2, $note_composition) {
            $moyenneDevoirs = (($note_devoir1 ?? 0) + ($note_devoir2 ?? 0)) / 2;
            $moyenneNote = ($moyenneDevoirs + ($note_composition ?? 0)) / 2;
            return $moyenneNote;
        });

        // Trouver le rang de la note dans la discipline
        $rangNote = $notesForDiscipline->search(function ($note) use ($note_devoir1, $note_devoir2, $note_composition) {
            $moyenneDevoirs = (($note_devoir1 ?? 0) + ($note_devoir2 ?? 0)) / 2;
            $moyenneNote = ($moyenneDevoirs + ($note_composition ?? 0)) / 2;
            return $moyenneNote === $note->note;
        }) + 1;  // +1 car les index commencent à 0

        // Ajouter l'enregistrement dans la table BulletinNote
        BulletinNote::create([
            'apprenant_id' => $apprenantId,
            'semestre_id' => $semestreId,
            'discipline_id' => $disciplineId,
            'discipline_nom' => $disciplineNom,
            'node_id' => $nodeId,
            'note_devoir1' => $note_devoir1,
            'note_devoir2' => $note_devoir2,
            'note_composition' => $note_composition,
            'moyenne_note' => $moyenne_note,
            'coefficient' => $coefficient,
            'moyenne_x' => $moyenne_x,
            'appreciation' => $appreciation,
            'rang_note' => $rangNote, // Ajustez selon votre logique de calcul du rang
        ]);

        // Ajouter les détails du bulletin dans le tableau $bulletins
        $bulletins[] = [
            'discipline_nom' => $disciplineNom,
            'note_devoir1' => $note_devoir1,
            'note_devoir2' => $note_devoir2,
            'note_composition' => $note_composition,
            'moyenne_note' => $moyenne_note,
            'moyenne_x' => $moyenne_x,
            'coefficient' => $coefficient,
            'appreciation' => $appreciation,
        ];
    }

    // Calcul du total pour le bulletin
    $moyenneEleve = $totalCoefficient > 0 ? $totalMoyenneX / $totalCoefficient : 0;

    $observations = ''; // Initialiser les observations
    if ($moyenneEleve >= 18) {
        $observations = 'tableau honneur';
    } elseif ($moyenneEleve >= 16) {
        $observations = 'felicitation';
    } elseif ($moyenneEleve >= 14) {
        $observations = 'encouragement';
    } elseif ($moyenneEleve >= 12) {
        $observations = 'satisfaisant';
    } elseif ($moyenneEleve >= 10) {
        $observations = 'peut mieux faire';
    } elseif ($moyenneEleve >= 8) {
        $observations = 'insuffisant';
    } elseif ($moyenneEleve >= 6) {
        $observations = 'insuffisant';
    } elseif ($moyenneEleve >= 4) {
        $observations = 'risque redoubler';
    } elseif ($moyenneEleve >= 2) {
        $observations = 'avertissement';
    } else {
        $observations = 'blâme';
    }

    // Récupérer les élèves de la même classe
    $elevesDeLaClasse = Apprenant::where('classe_id', $apprenant->classe_id)
        ->with('user') // Récupérer les informations sur l'apprenant
        ->get();

    // Calculer la moyenne générale pour chaque élève
    $elevesAvecMoyenne = $elevesDeLaClasse->map(function($eleve) use ($semestreId) {
        // Calcul de la moyenne de chaque élève pour le semestre
        $notes = Note::whereHas('evaluationApprenant', function($query) use ($eleve, $semestreId) {
            $query->where('apprenant_id', $eleve->id)
                  ->where('semestre', $semestreId);
        })->get();

        // Calcul de la moyenne de l'élève
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

    // Trier les élèves par moyenne décroissante
    $elevesAvecMoyenne = $elevesAvecMoyenne->sortByDesc('moyenne');

    // Trouver le rang de l'élève
    $rangEleve = $elevesAvecMoyenne->search(function ($eleve) use ($apprenant) {
        return $eleve['apprenant_id'] == $apprenant->id;
    }) + 1;

    $presences = Presence::where('apprenant_id', $apprenantId)
    ->whereIn('cours_id', array_column($bulletins, 'discipline_id'))
    ->get();

$totalRetards = $presences->where('statut', 'retard')->count();
$totalAbsences = $presences->where('statut', 'absence')->count();


// Observations
$observationsConseilProfesseur = 'Excellent travail, continuez ainsi.';
$observationsChefEtablissement = 'Très prometteur, à suivre de près.';
    // Préparer le bulletin général
    $bulletinGeneral = [
        'Total' => [  // Regroupement sous "Total"
            'total_coefficient' => $totalCoefficient,
            'total_moyenne_x' => $totalMoyenneX
        ],
        'moyenne_eleve' => $moyenneEleve,
        'rang_eleve' => $rangEleve,
        'total_retards' => $totalRetards,
        'total_absences' => $totalAbsences,
        'observations' => $observations,
        'observations_conseil_professeur' => $observationsConseilProfesseur,
        'observations_chef_etablissement' => $observationsChefEtablissement,
    ];

    // Informations de l'apprenant et du semestre
    $apprenantInfos = [
        'apprenant_id' => $apprenant->id,
        'apprenant_nom' => $apprenant->user->nom ?? 'Nom non trouvé',
        'apprenant_prenom' => $apprenant->user->prenom ?? 'Prénom non trouvé',
        'classe' => $apprenant->classe->nom ?? 'N/A',
        'semestre' => $semestreId,
    ];

    return [
        'apprenant_infos' => $apprenantInfos,
        'bulletins_notes' => $bulletins,
        'bulletin_general' => $bulletinGeneral
    ];
}





























}
