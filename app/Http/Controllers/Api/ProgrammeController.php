<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Programme;
use App\Models\Cours;
use App\Models\Competence;
use App\Models\CategorieCours;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseRequest;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseRequest;
class ProgrammeController extends Controller
{
    public function store(CreateProgrammeClasseRequest $request)
    {
        try {
            $programme = new Programme();
            $programme->nom = $request->nom;
            $programme->niveau_education = $request->niveau_education;
            $programme->description= $request->description;
            $programme->periode= $request->periode;
            $programme->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'ProgrammeClasse a été ajoutée',
                'data' => $programme,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la programmeclasse',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function update(UpdateProgrammeClasseRequest $request, $id)
{
    try {
        // Trouver le programme de classe par son ID
        $programme = Programme::findOrFail($id);
        $programme->nom = $request->nom;
        $programme->niveau_education = $request->niveau_education;
        $programme->description = $request->description;
        $programme->periode = $request->periode;
        $programme->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse a été mise à jour',
            'data' => $programme,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'ProgrammeClasse non trouvée',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la programmeclasse',
            'error' => $e->getMessage(),
        ]);
    }
}



public function updateProgrammeCours(UpdateProgrammeClasseCoursRequest $request, $id)
{
    try {
        DB::beginTransaction();

        // Récupération du programme à mettre à jour
        $programme = Programme::findOrFail($id);
        $programme->nom = $request->nom;
        $programme->niveau_education = $request->niveau_education;
        $programme->niveau_classe = $request->niveau_classe;
        $programme->cycle = $request->cycle ?? null;
        $programme->annee_scolaire = $request->annee_scolaire;
        $programme->langue_enseignee = $request->langue_enseignee ?? null;
        $programme->classe_id = $request->classe_id ?? null;
        $programme->save();

        // Mise à jour ou ajout des cours et leurs catégories/compétences
        foreach ($request->cours as $coursData) {
            // Récupération ou création du cours
            $cours = isset($coursData['id']) ? Cours::find($coursData['id']) : new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $coursData['niveau_education'];
            $cours->niveau_classe = $coursData['niveau_classe'];
            $cours->heure_allouee = $coursData['heure_allouee'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'] ?? null;
            $cours->coefficient = $coursData['coefficient'] ?? null;
            $cours->semestre = $coursData['semestre'] ?? null;
            $cours->enseignant_id = $coursData['enseignant_id'] ?? null;
            $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
            $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
            $cours->programme_id = $programme->id;
            $cours->save();

            // Mise à jour ou ajout des catégories (compétences) à ce cours
            if (isset($coursData['categories'])) {
                foreach ($coursData['categories'] as $categorieData) {
                    // Validation du barème pour chaque niveau d'éducation
                    $bareme = $categorieData['bareme'] ?? null;
                    $this->validateBareme($coursData['niveau_education'], $bareme);

                    // Récupération ou création de la catégorie de cours
                    $categorie = isset($categorieData['id']) ? CategorieCours::find($categorieData['id']) : new CategorieCours();
                    $categorie->nom = $categorieData['nom'];
                    $categorie->description = $categorieData['description'] ?? null;
                    $categorie->cours_id = $cours->id;
                    $categorie->volume_horaire = $categorieData['volume_horaire'] ?? null;
                    $categorie->type_exercices = $categorieData['type_exercices'] ?? null;
                    $categorie->leçons = $categorieData['leçons'] ?? null;
                    $categorie->duree_seance = $categorieData['duree_seance'] ?? null;
                    $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? null;
                    $categorie->heure_debut = $categorieData['heure_debut'] ?? null;
                    $categorie->heure_fin = $categorieData['heure_fin'] ?? null;
                    $categorie->frequence_evaluation = $categorieData['frequence_evaluation'] ?? null;
                    $categorie->bareme = $bareme;
                    $categorie->save();

                    // Mise à jour ou ajout des compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            // Récupération ou création de la compétence
                            $competence = isset($competenceData['id']) ? Competence::find($competenceData['id']) : new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_cours_id = $categorie->id;
                            $competence->save();
                        }
                    }
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme = $programme->fresh()->load(['cours.categories.competences']);
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme, cours et compétences ont été mis à jour avec succès',
            'data' => [
                'programme' => $programme,
            ]
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour du programme, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupérer le programme de classe avec ses classes, cours, compétences et salle associée
        $programmeClasse = Programme::with(['classes.salle', 'cours.enseignant', 'cours.competences'])
            ->findOrFail($id); // Si le programme de classe n'existe pas, une exception sera lancée

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme de classe récupéré avec succès',
            'data' => $programmeClasse,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Programme de classe non trouvé',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération du programme de classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function index()
{
    try {
        // Récupérer tous les programmes de classe avec leurs classes, cours et compétences associées
        $programmeClasses = Programme::with(['classes.salle', 'cours.enseignant', 'cours.categorie_cours','competence.categorie_cours'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des programmes de classe récupérée avec succès',
            'data' => $programmeClasses,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des programmes de classe',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        $programme = Programme::findOrFail($id);
        $programme->delete();
        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse supprimé avec succès',
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'ProgrammeClasse introuvable',
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression du ProgrammeClasse',
            'error' => $e->getMessage(),
        ]);
    }
}



public function storeProgrammeCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Création du Programme
        $programme = new Programme();
        $programme->nom = $request->nom;
        $programme->niveau_education = $request->niveau_education;
        $programme->niveau_classe = $request->niveau_classe;
        $programme->cycle = $request->cycle ?? null;
        $programme->annee_scolaire = $request->annee_scolaire;
        $programme->langue_enseignee = $request->langue_enseignee ?? null;
        $programme->classe_id = $request->classe_id ?? null;
        $programme->save();

        // Boucle pour ajouter chaque cours et ses compétences
        foreach ($request->cours as $coursData) {
            // Création du cours et lien avec le programme
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $coursData['niveau_education'];
            $cours->niveau_classe = $coursData['niveau_classe'];
            $cours->heure_allouee = $coursData['heure_allouee'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->credits = $coursData['credits'] ?? null;
            $cours->coefficient = $coursData['coefficient'] ?? null;
            $cours->semestre = $coursData['semestre'] ?? null;
            $cours->enseignant_id = $coursData['enseignant_id'] ?? null;
            $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
            $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
            $cours->programme_id = $programme->id; // Associer le cours au programme
            $cours->save();

            // Validation et ajout des catégories (compétences) à ce cours
            if (isset($coursData['categories'])) {
                foreach ($coursData['categories'] as $categorieData) {
                    // Validation du barème pour chaque niveau d'éducation
                    $bareme = $categorieData['bareme'] ?? null;
                    $this->validateBareme($coursData['niveau_education'], $bareme);

                    // Création de la catégorie de cours et lien avec le cours
                    $categorie = new CategorieCours();
                    $categorie->nom = $categorieData['nom'];
                    $categorie->description = $categorieData['description'] ?? null;
                    $categorie->cours_id = $cours->id; // Associer la catégorie au cours
                    $categorie->volume_horaire = $categorieData['volume_horaire'] ?? null;
                    $categorie->type_exercices = $categorieData['type_exercices'] ?? null;
                    $categorie->leçons = $categorieData['leçons'] ?? null;
                    $categorie->duree_seance = $categorieData['duree_seance'] ?? null;
                    $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? null;
                    $categorie->heure_debut = $categorieData['heure_debut'] ?? null;
                    $categorie->heure_fin = $categorieData['heure_fin'] ?? null;
                    $categorie->frequence_evaluation = $categorieData['frequence_evaluation'] ?? null;
                    $categorie->bareme = $bareme; // Associer le barème à la catégorie
                    $categorie->save();

                    // Ajouter les compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            $competence = new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_cours_id = $categorie->id; // Associer la compétence à la catégorie
                            $competence->save();
                        }
                    }
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme = Programme::with(['cours.categories.competences'])->find($programme->id);
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Programme, cours et compétences ont été ajoutés avec succès',
            'data' => [
                'programme' => $programme,
            ]
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du programme, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// Fonction de validation des barèmes
private function validateBareme($niveauEducation, $bareme)
{
    if ($niveauEducation === 'maternelle' && !in_array($bareme, ['Acquis', 'En Progression'])) {
        return response()->json([
            'status_code' => 400,
            'status_message' => 'Pour le niveau "maternelle", le barème doit être "Acquis" ou "En Progression".',
        ], 400);
    }

    if ($niveauEducation === 'primaire' && !preg_match('/^(10|[0-9])\/10$/', $bareme)) {
        return response()->json([
            'status_code' => 400,
            'status_message' => 'Pour le niveau "primaire", le barème doit être au format "X/10".',
        ], 400);
    }

    if ($niveauEducation === 'secondaire' && !preg_match('/^(20|[1-9]?[0-9])\/20$/', $bareme)) {
        return response()->json([
            'status_code' => 400,
            'status_message' => 'Pour le niveau "secondaire", le barème doit être au format "X/20".',
        ], 400);
    }
}

public function listerprogrammesexcel()
{
    try {
        // Récupérer uniquement les programmes importés via Excel
        $programmeClasses = Programme::where('source', 'import_excel')->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des programmes importés via Excel récupérée avec succès',
            'data' => $programmeClasses,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des programmes importés via Excel',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}








