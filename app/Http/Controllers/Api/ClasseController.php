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
          return response()->json([
            'status_code' => 200,
            'status_message' => 'tous les classes ont été recupéré',
            'data' => Classe::all(),
          ]);
        } catch (Exception $e) {
          return response()->json($e);
        }
      }

      public function showClasse(string $id)
      {
          try {
              $classe = Classe::findOrFail($id);

              return response()->json($classe);
          } catch (Exception) {
              return response()->json(['message' => 'Désolé, pas de classe trouvé.'], 404);
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

public function destroyClasse(string $id, Request $request)
{
    try {
        // Trouver la classe à supprimer
        $classe = Classe::findOrFail($id);

        // Récupérer l'ID de la nouvelle classe depuis la requête
        $nouvelleClasseId = $request->input('nouvelle_classe_id');

        // Vérifier si la nouvelle classe existe
        if (!Classe::find($nouvelleClasseId)) {
            return response()->json([
                'status_code' => 404,
                'status_message' => 'Nouvelle classe non trouvée',
            ], 404);
        }

        // Réaffecter les apprenants à la nouvelle classe
        $classe->apprenants()->update(['classe_id' => $nouvelleClasseId]);

        // Supprimer la classe
        $classe->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe et ses apprenants ont été réaffectés et supprimés avec succès',
            'data' => $classe
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Classe non trouvée',
            'error' => $e->getMessage()
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
