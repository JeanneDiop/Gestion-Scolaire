<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cours;
use App\Models\Enseignant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Cours\CreateCoursRequest;
use App\Http\Requests\Cours\updateCoursRequest;

class CoursController extends Controller
{
    public function store(CreateCoursRequest $request)
    {
        try {
            $cours = new Cours();
            $cours->nom = $request->nom;
            $cours->description = $request->description;
            $cours->niveau_education = $request->niveau_education;
            $cours->matiere = $request->matiere;
            $cours->type = $request->type;
            $cours->duree = $request->duree;
            $cours->credits = $request->credits;
            $cours->enseignant_id = $request->enseignant_id;
            $cours->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Cours a été ajoutée',
                'data' => $cours,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement du cours',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function index()
{
    try {
        // Récupération de tous les cours avec les informations des enseignants et des utilisateurs associés
        $cours = Cours::with(['enseignant.user'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Tous les cours ont été récupérés avec succès.',
            'data' => $cours,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


      public function show(string $id)
{
    try {
        // Récupération du cours par ID avec les informations de l'enseignant et de l'utilisateur
        $cours = Cours::with(['enseignant.user'])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Cours récupéré avec succès.',
            'data' => $cours,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Désolé, pas de cours trouvé.',
            'error' => $e->getMessage(),
        ], 404);
    }
}


      public function update(UpdateCoursRequest $request, $id)
      {
          DB::beginTransaction();

          try {
              // Récupération du cours
              $cours = Cours::findOrFail($id);

              // Mise à jour des données du cours
              $cours->update([
                  'nom' => $request->nom,
                  'description' => $request->description,
                  'niveau_education' => $request->niveau_education,
                  'matiere' => $request->matiere,
                  'type' => $request->type,
                  'duree' => $request->duree,
                  'credits' => $request->credits,
              ]);

              // Vérification si un enseignant est spécifié et existe
              if ($request->enseignant_id) {
                  $enseignant = Enseignant::find($request->enseignant_id);

                  if (!$enseignant) {
                      return response()->json([
                          'status_code' => 404,
                          'status_message' => 'L\'enseignant spécifié n\'a pas été trouvé.',
                      ], 404);
                  }
              }

              DB::commit(); // Valide la transaction

              return response()->json([
                  'status_code' => 200,
                  'status_message' => 'Le cours a été modifié avec succès.',
                  'data' => $cours,
                  'enseignant' => $enseignant ?? null, // Retourne l'enseignant s'il est spécifié, sinon null
              ]);
          } catch (\Exception $e) {
              DB::rollBack(); // Annule la transaction en cas d'erreur

              return response()->json([
                  'status_code' => 500,
                  'status_message' => 'Une erreur est survenue lors de la mise à jour du cours.',
                  'error' => $e->getMessage(),
              ], 500);
          }
      }


      public function destroy($id)
{
    DB::beginTransaction();

    try {
        // Récupération du cours à supprimer
        $cours = Cours::findOrFail($id);

        // Vérification si le cours a un enseignant associé
        if ($cours->enseignant_id) {
            // Mettre à null l'enseignant associé avant la suppression du cours
            $cours->enseignant_id = null;
            $cours->save();
        }

        // Suppression du cours
        $cours->delete();

        DB::commit(); // Valide la transaction

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Le cours a été supprimé avec succès.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur est survenue lors de la suppression du cours.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
