<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\ProgrammeClasse;
use App\Models\Cours;
use App\Models\Competence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseRequest;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseCoursRequest;
use App\Http\Requests\ProgrammeClasse\UpdateProgrammeClasseRequest;
class ProgrammeClasseController extends Controller
{
    public function store(CreateProgrammeClasseRequest $request)
    {
        try {
            $programme = new ProgrammeClasse();
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
        $programme = ProgrammeClasse::findOrFail($id);
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


public function storeProgrammeCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Validation du champ bareme selon le niveau d'éducation


        // Création du ProgrammeClasse
        $programme_classe = new ProgrammeClasse();
        $programme_classe->nom = $request->nom;
        $programme_classe->niveau_education = $request->niveau_education;
        $programme_classe->niveau_classe = $request->niveau_classe;
        $programme_classe->cycle = $request->cycle;
        $programme_classe->annee_scolaire = $request->annee_scolaire;
        $programme_classe->langue_enseignee = $request->langue_enseignee ?? null;
        $programme_classe->importer_programme = $request->importer_programme ?? null;
        $programme_classe->exporter_programme = $request->exporter_programme ?? null;
        $programme_classe->save();

        // Boucle pour ajouter chaque cours et ses compétences
        foreach ($request->cours as $coursData) {
            $bareme = $coursData['bareme'] ?? null;

            // Validation du champ bareme selon le niveau d'éducation
            $niveauEducation = $coursData['niveau_education'];
            if ($niveauEducation === 'maternelle') {
                if (!in_array($bareme, ['Acquis', 'En Progression'])) {
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Pour le niveau "maternelle", le barème doit être "Acquis" ou "En Progression".'
                    ], 400);
                }
            }

            if ($niveauEducation === 'primaire') {
                if (!preg_match('/^(10|[0-9])\/10$/', $bareme)) {
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Pour le niveau "primaire", le barème doit être au format "X/10".'
                    ], 400);
                }
            }

            if ($niveauEducation === 'secondaire') {
                if (!preg_match('/^(20|[1-9]?[0-9])\/20$/', $bareme)) {
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Pour le niveau "secondaire", le barème doit être au format "X/20".'
                    ], 400);
                }
            }
            // Création du cours
            $cours = new Cours();
            $cours->nom = $coursData['nom'];
            $cours->description = $coursData['description'] ?? null;
            $cours->niveau_education = $niveauEducation;
            $cours->niveau_classe = $coursData['niveau_classe'];
            $cours->heure_allouee = $coursData['heure_allouee'];
            $cours->duree_recommander_sceance = $coursData['duree_recommander_sceance'];
            $cours->etat = $coursData['etat'] ?? 'encours';
            $cours->bareme = $bareme ?? null;
            $cours->frequence_evaluation = $coursData['frequence_evaluation'] ?? null;
            $cours->type_evaluation = $coursData['type_evaluation ']?? null;
            $cours->type_exercice = $coursData['type_exercice ']?? null;
            $cours->credits = $coursData['credits'] ?? null;
            $cours->coefficient = $coursData['coefficient'] ?? null;
            $cours->semestre = $coursData['semestre'] ?? null;
            $cours->categorie_cours = $coursData['categorie_cours'];
            $cours->enseignant_id = $coursData['enseignant_id'] ?? null;
            $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
            $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
            $cours->programme_classe_id = $programme_classe->id;
            $cours->save();

            // Boucle pour ajouter les compétences spécifiques à ce cours
            if (isset($coursData['competences'])) {
                foreach ($coursData['competences'] as $competenceData) {
                    $competence = new Competence();
                    $competence->nom = $competenceData['nom'];
                    $competence->description = $competenceData['description'];
                    $competence->cours_id = $cours->id;
                    $competence->save();
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme_classe = ProgrammeClasse::with(['cours.competences'])->find($programme_classe->id);
        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse, cours et compétences ont été ajoutés avec succès',
            'data' =>  compact('programme_classe')
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function updateProgrammeCours($id, UpdateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Récupération du programme de classe à mettre à jour
        $programme_classe = ProgrammeClasse::findOrFail($id);
        $programme_classe->nom = $request->nom;
        $programme_classe->niveau_education = $request->niveau_education;
        $programme_classe->niveau_classe = $request->niveau_classe;
        $programme_classe->cycle = $request->cycle;
        $programme_classe->annee_scolaire = $request->annee_scolaire;
        $programme_classe->langue_enseignee = $request->langue_enseignee;
        $programme_classe->importer_programme = $request->importer_programme ?? null;
        $programme_classe->exporter_programme = $request->exporter_programme ?? null;
        $programme_classe->update();

        // Gestion des cours
        foreach ($request->cours as $coursData) {
            if (isset($coursData['id'])) {
                $cours = Cours::find($coursData['id']);
                if ($cours) {
                    $cours->nom = $coursData['nom'];
                    $cours->description = $coursData['description'] ?? null;
                    $cours->niveau_education = $coursData['niveau_education'];
                    $cours->niveau_classe = $coursData['niveau_classe'];
                    $cours->heure_allouee = $coursData['heure_allouee'];
                    $cours->duree_recommander_sceance = $coursData['duree_recommander_sceance'] ?? null;
                    $cours->bareme = $coursData['bareme'] ?? null;
                    $cours->frequence_evaluation = $coursData['frequence_evaluation'] ?? null;
                    $cours->type_evaluation = $coursData['type_evaluation'] ?? null;
                    $cours->type_exercice = $coursData['type_exercice ']?? null;
                    $cours->credits = $coursData['credits'] ?? null;
                    $cours->coefficient = $coursData['coefficient'] ?? null;
                    $cours->semestre = $coursData['semestre'];
                    $cours->categorie_cours = $coursData['categorie_cours'] ?? null;
                    $cours->enseignant_id = $coursData['enseignant_id'] ?? null;
                    $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
                    $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
                    $cours->programme_classe_id = $programme_classe->id;
                    $cours->update();

                    // Gestion des compétences
                    if (isset($coursData['competences'])) {
                        // Récupérer les IDs des compétences existantes
                        $existingCompetences = $cours->competences()->pluck('id')->toArray();

                        foreach ($coursData['competences'] as $competenceData) {
                            if (isset($competenceData['id']) && in_array($competenceData['id'], $existingCompetences)) {
                                $competence = Competence::find($competenceData['id']);
                                $competence->nom = $competenceData['nom'];
                                $competence->description = $competenceData['description'];
                                $competence->save();
                            } else {
                                // Créer une nouvelle compétence
                                $competence = new Competence();
                                $competence->nom = $competenceData['nom'];
                                $competence->description = $competenceData['description'];
                                $competence->cours_id = $cours->id;
                                $competence->save();
                            }
                        }
                    }
                }
            } else {
                // Si le cours n'a pas d'ID, on peut le créer
                $cours = new Cours();
                $cours->nom = $coursData['nom'];
                $cours->description = $coursData['description'] ?? null;
                $cours->niveau_education = $coursData['niveau_education'];
                $cours->niveau_classe = $coursData['niveau_classe']; // Nouveau champ
                $cours->heure_allouee = $coursData['heure_allouee'];
                $cours->duree_recommander_sceance = $coursData['duree_recommander_sceance'] ?? null; // Nouveau champ
                $cours->etat = $coursData['etat'] ?? 'encours';
                $cours->bareme = $coursData['bareme'] ?? null;
                $cours->frequence_evaluation = $coursData['frequence_evaluation'] ?? null;
                $cours->type_evaluation = $coursData['type_evaluation'] ?? null;
                $cours->type_exercice = $coursData['type_exercice ']?? null;
                $cours->credits = $coursData['credits'] ?? null;
                $cours->coefficient = $coursData['coefficient'] ?? null;
                $cours->semestre = $coursData['semestre'];
                $cours->categorie_cours = $coursData['categorie_cours'] ?? null; // Nouveau champ
                $cours->enseignant_id = $coursData['enseignant_id'];
                $cours->programme_classe_id = $programme_classe->id;
                $cours->save();

                // Gestion des compétences pour les nouveaux cours
                if (isset($coursData['competences'])) {
                    foreach ($coursData['competences'] as $competenceData) {
                        $competence = new Competence();
                        $competence->nom = $competenceData['nom'];
                        $competence->description = $competenceData['description'];
                        $competence->cours_id = $cours->id;
                        $competence->save();
                    }
                }
            }
        }

        DB::commit();
        $programme_classe->load('cours.competences');

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le programme de classe et les cours ont été mis à jour avec succès.',
            'data' => [
                'programmeclasse' => [
                    'id' => $programme_classe->id,
                    'nom' => $programme_classe->nom,
                    'niveau_classe' => $programme_classe->niveau_classe,
                    'niveau_education' => $programme_classe->niveau_education,
                    'description' => $programme_classe->description,
                    'cycle' => $programme_classe->cycle,
                    'annee_scolaire' => $programme_classe->annee_scolaire,
                    'langue_enseignee' => $programme_classe->langue_enseignee,
                    'cours' => $programme_classe->cours,
                ],
            ],
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de la classe et des cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function show($id)
{
    try {
        // Récupérer le programme de classe avec ses classes, cours, compétences et salle associée
        $programmeClasse = ProgrammeClasse::with(['classes.salle', 'cours.enseignant', 'cours.competences'])
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
        $programmeClasses = ProgrammeClasse::with(['classes.salle', 'cours.enseignant', 'cours.competences'])->get();

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
        $programme = ProgrammeClasse::findOrFail($id);
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


public function storeCours(array $coursData, $programmeClasse)
{
    DB::beginTransaction();

    try {
        // Créer une nouvelle instance de Cours
       
        
        $cours->save();

        // Boucle pour ajouter les catégories et leurs compétences spécifiques
        if (isset($coursData['categories'])) {
            foreach ($coursData['categories'] as $categorieData) {
                // Création de la catégorie pour ce cours
                $categorie = new CategorieCours();
                $categorie->nom = $categorieData['nom'];
                $categorie->description = $categorieData['description'] ?? null;
                $categorie->cours_id = $cours->id;
                $categorie->volume_horaire = $categorieData['volume_horaire'] ?? 10;
                $categorie->duree_recommander_sceance = $categorieData['duree_recommander_sceance'] ?? 1;
                $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? 'standard';
                $categorie->bareme = $categorieData['bareme'] ?? 20;
                
                $categorie->save();

                // Ajouter les compétences spécifiques pour cette catégorie
                if (isset($categorieData['competences'])) {
                    foreach ($categorieData['competences'] as $competenceData) {
                        $competence = new Competence();
                        $competence->nom = $competenceData['nom'];
                        $competence->description = $competenceData['description'] ?? null;
                        $competence->categorie_id = $categorie->id; // Lier la compétence à la catégorie
                        $competence->save();
                    }
                }
            }
        }

        // Validation de la transaction
        DB::commit();

        // Charger les relations pour le retour de réponse
        $programmeClasse = ProgrammeClasse::with(['cours.categories.competences'])->find($programmeClasse->id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse, cours, catégories et compétences ont été ajoutés avec succès',
            'data' => compact('programmeClasse')
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe, des cours, des catégories et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
} 


public function storeProgrammeClasseCours(CreateProgrammeClasseCoursRequest $request)
{
    try {
        DB::beginTransaction();

        // Validation du champ bareme selon le niveau d'éducation


        // Création du ProgrammeClasse
        $programme_classe = new ProgrammeClasse();
        $programme_classe->nom = $request->nom;
        $programme_classe->niveau_education = $request->niveau_education;
        $programme_classe->niveau_classe = $request->niveau_classe;
        $programme_classe->cycle = $request->cycle;
        $programme_classe->annee_scolaire = $request->annee_scolaire;
        $programme_classe->langue_enseignee = $request->langue_enseignee ?? null;
        $programme_classe->importer_programme = $request->importer_programme ?? null;
        $programme_classe->exporter_programme = $request->exporter_programme ?? null;
        $programme_classe->save();

            $cours->objectif_generaux = $coursData['objectif_generaux'] ?? null;
            $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? null;
       
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
        $cours->programme_classe_id = $programmeClasse->id;
        $cours->save();

         // Boucle pour ajouter chaque cours et ses compétences
         foreach ($request->categoriecours as $coursData) {
            $bareme = $coursData['bareme'] ?? null;

            // Validation du champ bareme selon le niveau d'éducation
            $niveauEducation = $coursData['niveau_education'];
            if ($niveauEducation === 'maternelle') {
                if (!in_array($bareme, ['Acquis', 'En Progression'])) {
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Pour le niveau "maternelle", le barème doit être "Acquis" ou "En Progression".'
                    ], 400);
                }
            }

            if ($niveauEducation === 'primaire') {
                if (!preg_match('/^(10|[0-9])\/10$/', $bareme)) {
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Pour le niveau "primaire", le barème doit être au format "X/10".'
                    ], 400);
                }
            }

            if ($niveauEducation === 'secondaire') {
                if (!preg_match('/^(20|[1-9]?[0-9])\/20$/', $bareme)) {
                    return response()->json([
                        'status_code' => 400,
                        'status_message' => 'Pour le niveau "secondaire", le barème doit être au format "X/20".'
                    ], 400);
                }
            }
            // Boucle pour ajouter les compétences spécifiques à ce cours
            if (isset($coursData['categories'])) {
                foreach ($coursData['categories'] as $categorieData) {
                    // Création de la catégorie pour ce cours
                    $categorie = new CategorieCours();
                    $categorie->nom = $categorieData['nom'];
                    $categorie->description = $categorieData['description'] ?? null;
                    $categorie->cours_id = $cours->id;
                    $categorie->volume_horaire = $categorieData['volume_horaire'] ?? null;
                    $categorie->duree_recommander_sceance = $categorieData['duree_recommander_sceance'] ?? null;
                    $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? null;
                    $categorie->bareme = $categorieData['bareme'] ?? null;
                    
                    $categorie->save();
    
                    // Ajouter les compétences spécifiques pour cette catégorie
                    if (isset($categorieData['competences'])) {
                        foreach ($categorieData['competences'] as $competenceData) {
                            $competence = new Competence();
                            $competence->nom = $competenceData['nom'];
                            $competence->description = $competenceData['description'] ?? null;
                            $competence->categorie_id = $categorie->id; // Lier la compétence à la catégorie
                            $competence->save();
                        }
                    }
                }
            }

        // Validation de la transaction
        DB::commit();

        // Chargement des relations pour le retour de réponse
        $programme_classe = ProgrammeClasse::with(['cours.competences'])->find($programme_classe->id);
        return response()->json([
            'status_code' => 200,
            'status_message' => 'ProgrammeClasse, cours et compétences ont été ajoutés avec succès',
            'data' =>  compact('programme_classe')
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe, des cours et des compétences',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
}