<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\Salle;
use App\Http\Requests\Salle\CreateSalleRequest;

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
}
