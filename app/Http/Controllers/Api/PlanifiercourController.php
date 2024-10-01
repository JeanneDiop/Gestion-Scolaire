<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanifiercourController extends Controller
{
    public function store(CreatePlanifiercourRequest $request)
    {
        {
            try {
              $planifiercour = new Planifiercour();
              $planifiercour->nom= $request->nom;
              $planifiercour->prenom= $request->prenom;
              $planifiercour->telephone= $request->telephone;
              $planifiercour->email= $request->email;
              $planifiercour->adresse= $request->adresse;
              $planifiercour->poste= $request->poste;
              $planifiercour->image = $request->image;
              $planifiercour->date_embauche = $request->date_embauche;
              $planifiercour->statut_emploie = $request->statut_emploie;
              $planifiercour->save();
              return response()->json([
                'status_code' => 200,
                'status_message' => 'employe a été ajouté',
                'data' => $planifiercour,
              ],200);
            }  catch (Exception $e) {
                return response()->json([
                    'status_code' => 500,
                    'status_message' => 'Erreur interne du serveur',
                    'error' => $e->getMessage(),
                ], 500);
          }
        }
    }
}
