<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Salle;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Classe\CreateClasseRequest;
use App\Http\Requests\Classe\EditClasseRequest;

class ClasseController extends Controller
{
    public function storeClasse(CreateClasseRequest $request)
    {
        try {
            $classe = new Classe();
            $classe->nom = $request->nom;
            $classe->niveau_classe = $request->niveau_classe; // Corrigez l'orthographe de 'niveau_classe
            $classe->salle_id = $request->salle_id;
            $classe->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Classe a été ajoutée',
                'data' => $classe,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement de la classe',
                'error' => $e->getMessage(),
            ]);
        }
    }
   public function indexClasse()
      {
          try {
              // Récupérer toutes les classes avec les informations de la salle associée
              $classes = Classe::with('salle')->get();

              return response()->json([
                  'status_code' => 200,
                  'status_message' => 'Toutes les classes ont été récupérées',
                  'data' => $classes,
              ]);
          } catch (\Exception $e) {
              return response()->json([
                  'status_code' => 500,
                  'status_message' => 'Une erreur s\'est produite lors de la récupération des classes',
                  'error' => $e->getMessage(),
              ]);
          }
      }


      public function showClasse($id)
{
    try {

        $classe = Classe::with('salle')->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la classe récupérés avec succès',
            'data' => $classe,
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Classe non trouvée',
            'error' => 'La classe avec l\'ID spécifié n\'existe pas.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des détails de la classe',
            'error' => $e->getMessage(),
        ]);
    }
}


    public function updateClasse(EditClasseRequest $request, $id)
    {
    DB::beginTransaction();

    try {

        $classe = Classe::findOrFail($id);
        $classe->nom = $request->nom;
        $classe->niveau_classe = $request->niveau_classe;
        $classe->salle_id = $request->salle_id;
        $classe->update();

        // Récupération des données de l'enseignant et de la salle

        $salle = Salle::find($request->salle_id); // Assurez-vous d'importer le modèle Salle

        DB::commit(); // Valide la transaction

        return response()->json([
            'status_code' => 200,
            'status_message' => 'La classe a été modifiée avec succès',
            'data' => $classe,
            'salle' => $salle
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur est survenue lors de la mise à jour de la classe.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function destroy($id)
{
    try {
        // Récupérer la classe à supprimer
        $classe = Classe::findOrFail($id);

        // Supprimer la classe
        $classe->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe supprimée avec succès',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'vous ne pouvez pas supprimer la classe parceque ya des apprenants qui sont associés' ,
            'error' => $e->getMessage(),
        ],500);
    }
}






}
