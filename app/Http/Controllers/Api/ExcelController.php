<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Programme;
use Carbon\Carbon;
use Exception;
use App\Models\Historique;
use App\Models\Cours;
use App\Models\Competence;
use App\Models\CategorieCours;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProgrammeClasse\CreateProgrammeClasseCoursRequest;


class ExcelController extends Controller
{
    public function storeProgrammeCours(CreateProgrammeClasseCoursRequest $request)
    {
        try {
            DB::beginTransaction();

            // Étape 1: Récupérer un programme par défaut (source = Excel)
            $programmeDefaut = Programme::where('source', 'excel')->first();  // Exemple pour récupérer un programme par défaut

            // Étape 2: Pré-remplir les données avec celles du programme par défaut si elles existent
            $programme = new Programme();
            $programme->nom = $request->nom ?? $programmeDefaut->nom;
            $programme->niveau_education = $request->niveau_education ?? $programmeDefaut->niveau_education;
            $programme->niveau_classe = $request->niveau_classe ?? $programmeDefaut->niveau_classe;
            $programme->cycle = $request->cycle ?? $programmeDefaut->cycle ?? null;
            $programme->annee_scolaire = $request->annee_scolaire ?? $programmeDefaut->annee_scolaire;
            $programme->langue_enseignee = $request->langue_enseignee ?? $programmeDefaut->langue_enseignee ?? null;
            $programme->classe_id = $request->classe_id ?? $programmeDefaut->classe_id ?? null;
            $programme->source = 'manuel';  // Source du programme manuel
            $programme->save();

            // Créer un historique pour l'ajout du programme manuel
            Historique::create([
                'action' => 'create',
                'message' => 'Programme manuel ajouté : ' . $programme->nom,
                'user_id' => auth()->id(),
                'programme_id' => $programme->id,
                'created_at' => Carbon::now(),
            ]);

            // Étape 3: Ajouter les cours et les compétences comme dans votre logique précédente
            foreach ($request->cours as $coursData) {
                // Création du cours et lien avec le programme
                $cours = new Cours();
                $cours->nom = $coursData['nom'];
                $cours->description = $coursData['description'] ?? $programmeDefaut->cours->description ?? null;
                $cours->niveau_education = $coursData['niveau_education'] ?? $programmeDefaut->cours->niveau_education;
                $cours->niveau_classe = $coursData['niveau_classe'] ?? $programmeDefaut->cours->niveau_classe;
                $cours->heure_allouee = $coursData['heure_allouee'] ?? $programmeDefaut->cours->heure_allouee;
                $cours->etat = $coursData['etat'] ?? 'encours';
                $cours->credits = $coursData['credits'] ?? $programmeDefaut->cours->credits ?? null;
                $cours->coefficient = $coursData['coefficient'] ?? $programmeDefaut->cours->coefficient ?? null;
                $cours->semestre = $coursData['semestre'] ?? $programmeDefaut->cours->semestre ?? null;
                $cours->enseignant_id = $coursData['enseignant_id'] ?? $programmeDefaut->cours->enseignant_id ?? null;
                $cours->objectif_generaux = $coursData['objectif_generaux'] ?? $programmeDefaut->cours->objectif_generaux ?? null;
                $cours->objectif_specifiques = $coursData['objectif_specifiques'] ?? $programmeDefaut->cours->objectif_specifiques ?? null;
                $cours->programme_id = $programme->id; // Associer le cours au programme
                $cours->save();

                // Créer un historique pour l'ajout du cours
                Historique::create([
                    'action' => 'create',
                    'message' => 'Cours ajouté : ' . $cours->nom,
                    'user_id' => auth()->id(),
                    'cours_id' => $cours->id,
                    'created_at' => Carbon::now(),
                ]);

                // Étape 4: Ajout des catégories et compétences
                if (isset($coursData['categories'])) {
                    foreach ($coursData['categories'] as $categorieData) {
                        $bareme = $categorieData['bareme'] ?? null;
                        $this->validateBareme($coursData['niveau_education'], $bareme);

                        // Création de la catégorie de cours et lien avec le cours
                        $categorie = new CategorieCours();
                        $categorie->nom = $categorieData['nom'] ?? $programmeDefaut->cours->categories->first()->nom ?? null;
                        $categorie->cours_id = $cours->id; // Associer la catégorie au cours
                        $categorie->volume_horaire = $categorieData['volume_horaire'] ?? $programmeDefaut->cours->categories->first()->volume_horaire ?? null;
                        $categorie->type_exercices = $categorieData['type_exercices'] ?? $programmeDefaut->cours->categories->first()->type_exercices ?? null;
                        $categorie->leçons = $categorieData['leçons'] ?? $programmeDefaut->cours->categories->first()->leçons ?? null;
                        $categorie->duree_seance = $categorieData['duree_seance'] ?? $programmeDefaut->cours->categories->first()->duree_seance ?? null;
                        $categorie->mode_evaluation = $categorieData['mode_evaluation'] ?? $programmeDefaut->cours->categories->first()->mode_evaluation ?? null;
                        $categorie->heure_debut = $categorieData['heure_debut'] ?? $programmeDefaut->cours->categories->first()->heure_debut ?? null;
                        $categorie->heure_fin = $categorieData['heure_fin'] ?? $programmeDefaut->cours->categories->first()->heure_fin ?? null;
                        $categorie->frequence_evaluation = $categorieData['frequence_evaluation'] ?? $programmeDefaut->cours->categories->first()->frequence_evaluation ?? null;
                        $categorie->bareme = $bareme; // Associer le barème à la catégorie
                        $categorie->save();
                        Historique::create([
                            'action' => 'create',
                            'message' => 'Categorie ajoutée : ' . $categorie->nom,
                            'user_id' => auth()->id(),
                            'categorie_id' => $categorie->id,
                            'created_at' => Carbon::now(),
                        ]);

                        // Ajouter les compétences spécifiques pour cette catégorie
                        if (isset($categorieData['competences'])) {
                            foreach ($categorieData['competences'] as $competenceData) {
                                $competence = new Competence();
                                $competence->nom = $competenceData['nom'] ?? $programmeDefaut->cours->categories->first()->competences->first()->nom ?? null;
                                $competence->description = $competenceData['description'] ?? $programmeDefaut->cours->categories->first()->competences->first()->description ?? null;
                                $competence->categorie_cours_id = $categorie->id; // Associer la compétence à la catégorie
                                $competence->save();
                                Historique::create([
                                    'action' => 'create',
                                    'message' => 'Competence ajoutée : ' . $competence->nom,
                                    'user_id' => auth()->id(),
                                    'competence_id' => $competence->id,
                                    'created_at' => Carbon::now(),
                                ]);
                            }
                        }
                    }
                }
            }

            // Validation de la transaction
            DB::commit();

            // Retour de la réponse
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Programme, cours et compétences ajoutés avec succès',
                'data' => [
                    'programme' => $programme,
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de l\'enregistrement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
