<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employe;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Employe\CreateEmployeRequest;
use App\Http\Requests\Employe\EditEmployeRequest;

class EmployeController extends Controller
{
    public function store(CreateEmployeRequest $request)
    {
        {
            try {
              $employe = new Employe();
              $employe->nom= $request->nom;
              $employe->prenom= $request->prenom;
              $employe->telephone= $request->telephone;
              $employe->email= $request->email;
              $employe->adresse= $request->adresse;
              $employe->poste= $request->poste;
              $employe->image = $request->image;
              $employe->date_embauche = $request->date_embauche;
              $employe->statut_emploie = $request->statut_emploie;
              $employe->type_salaire = $request->type_salaire;
              $employe->date_naissance = $request->date_naissance;
              $employe->lieu_naissance = $request->lieu_naissance;
              $employe->sexe = $request->sexe;
              $employe->statut_marital= $request->statut_marital;
              $employe->numero_securite_social = $request->numero_securite_social;
              $employe->numero_CNI = $request->numero_CNI;
              $employe->date_fin_contrat= $request->date_fin_contrat;
              $employe->save();
              return response()->json([
                'status_code' => 200,
                'status_message' => 'employe a été ajouté',
                'data' => $employe,
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

    public function update(EditEmployeRequest $request, $id)
    {

        try {
              $employe= Employe::find($id);
              $employe->nom= $request->nom;
              $employe->prenom= $request->prenom;
              $employe->telephone= $request->telephone;
              $employe->email= $request->email;
              $employe->adresse= $request->adresse;
              $employe->poste= $request->poste;
              $employe->image = $request->image;
              $employe->date_embauche = $request->date_embauche;
              $employe->statut_emploie = $request->statut_emploie;
              $employe->type_salaire = $request->type_salaire;
              $employe->date_naissance = $request->date_naissance;
              $employe->lieu_naissance = $request->lieu_naissance;
              $employe->sexe = $request->sexe;
              $employe->statut_marital= $request->statut_marital;
              $employe->numero_securite_social = $request->numero_securite_social;
              $employe->numero_CNI = $request->numero_CNI;
              $employe->date_fin_contrat= $request->date_fin_contrat;
              $employe->update();

          return response()->json([
            'status_code' => 200,
            'status_message' => 'employe a été modifié',
            'data' => $employe
          ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'error' => $e->getMessage(),
            ], 500);
        }

        }
    public function index()
    {
        try {
          return response()->json([
            'status_code' => 200,
            'status_message' => 'tous les employes ont été recupéré',
            'data' => Employe::all(),
          ]);
        }catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
      }

      public function show(string $id)
      {
          try {
              $employe = Employe::findOrFail($id);

              return response()->json($employe);
          } catch (Exception) {
              return response()->json(['message' => 'Désolé, pas de employe trouvé.'], 404);
          }
      }

      public function destroy(string $id)
{
    try {
        // Tente de trouver l'employé par ID
        $employe = Employe::findOrFail($id);

        // Supprime l'employé
        $employe->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'L\'employé a été bien supprimé',
            'data' => $employe
        ]);
    } catch (ModelNotFoundException $e) {
        // Gère le cas où l'employé n'est pas trouvé
        return response()->json([
            'status_code' => 404,
            'status_message' => 'L\'employé avec l\'ID ' . $id . ' n\'existe pas.',
        ], 404);
    } catch (Exception $e) {
        // Gère les autres exceptions
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur interne du serveur',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
