<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Salle;
use App\Models\Historique;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Salle\CreateSalleRequest;
use App\Http\Requests\Salle\EditSalleRequest;

class SalleController extends Controller
{
    public function storeSalle(CreateSalleRequest $request)
    {
        try {
            $salle = new Salle();
            $salle->nom = $request->nom;
            $salle->capacity = $request->capacity;
            $salle->type = $request->type;
            $salle->save();

            Historique::create([
                'action' => 'create',
                'message' => 'Salle ajoutée : ' . $salle->nom,
                'user_id' => auth()->id(),
                'salle_id' => $salle->id,  // Remplacer par l'ID de l'utilisateur si nécessaire
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Salle a été ajoutée',
                'data' => $salle,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'uen erreur  s\'est produite lors de l\'enrigistrement de la salle',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function indexSalle()
    {
        try {
          return response()->json([
            'status_code' => 200,
            'status_message' => 'tous les salles ont été recupéré',
            'data' => Salle::all(),
          ]);
        } catch (Exception $e) {
          return response()->json($e);
        }
      }

      public function showSalle(string $id)
      {
          try {
              $client = Salle::findOrFail($id);

              return response()->json($client);
          } catch (Exception) {
              return response()->json(['message' => 'Désolé, pas de salle trouvé.'], 404);
          }
      }

      public function updateSalle(EditSalleRequest $request, $id)
      {
          DB::beginTransaction();

          try {

              $salle = Salle::findOrFail($id);
              $salle->nom = $request->nom;
              $salle->capacity = $request->capacity;
              $salle->type = $request->type;
              $salle->update();

              DB::commit(); // Valide la transaction
              Historique::create([
                'action' => 'update',  // Action 'update' pour la modification
                'message' => 'Salle modifiée : ' . $salle->nom,
                'user_id' => auth()->id(), // ID de l'utilisateur authentifié
                'salle_id' => $salle->id,  // ID de la salle modifiée
                'created_at' => Carbon::now(),
            ]);
              return response()->json([
                  'status_code' => 200,
                  'status_message' => 'La salle a été modifiée avec succès',
                  'data' => $salle,
              ]);
          } catch (\Exception $e) {
              DB::rollBack(); // Annule la transaction en cas d'erreur

              return response()->json([
                  'status_code' => 500,
                  'status_message' => 'Une erreur est survenue lors de la mise à jour de la salle.',
                  'error' => $e->getMessage(),
              ], 500);
          }
      }
      public function destroySalle(string $id)
    {
        try{
          $salle = Salle::findOrFail($id);

          $salle->delete();

          return response()->json([
            'status_code' => 200,
            'status_message' => 'salle a été bien supprimer',
            'data' => $salle
          ]);
        } catch (Exception $e) {
          return response()->json($e);
        }

    }





    
}
