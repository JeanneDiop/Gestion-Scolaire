<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Ecole;
use App\Http\Requests\Ecole\CreateEcoleRequest;
use App\Http\Requests\Ecole\UpdateEcoleRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class EcoleController extends Controller
{
    public function store(CreateEcoleRequest $request)
    {
        try {
            $ecole = new Ecole();
            $ecole->nom = $request->nom;
            $ecole->adresse = $request->adresse;
            $ecole->telephone = $request->telephone;
            $ecole->email = $request->email;
            $ecole->siteweb = $request->siteweb;
            $ecole->logo= $request->logo;
            $ecole->annee_creation= $request->annee_creation;
            $ecole->type_ecole= $request->type_ecole;
            $ecole->niveau_education= $request->niveau_education;
            $ecole->directeur_id = $request->directeur_id;
            $ecole->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'ecole a été ajoutée',
                'data' =>  $ecole,
            ],200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Une erreur s\'est produite lors de l\'enregistrement d\'ecole',
                'error' => $e->getMessage(),
            ],500);
        }
    }



    public function update(UpdateEcoleRequest $request, $id)
{
    try {
        // Récupérer l'école à mettre à jour
        $ecole = Ecole::findOrFail($id);

        // Mettre à jour les champs avec les données de la requête
        $ecole->nom = $request->nom;
        $ecole->adresse = $request->adresse;
        $ecole->telephone = $request->telephone;
        $ecole->email = $request->email;
        $ecole->siteweb = $request->siteweb;
        $ecole->logo = $request->logo;
        $ecole->annee_creation = $request->annee_creation;
        $ecole->type_ecole = $request->type_ecole;
        $ecole->niveau_education = $request->niveau_education;
        $ecole->directeur_id = $request->directeur_id; // Corrigé le champ de directeurs

        // Enregistrer les modifications
        $ecole->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'École mise à jour avec succès',
            'data' => $ecole,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la mise à jour de l\'école',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function show($id)
{
    try {
        // Récupérer l'école par son ID avec les informations du directeur et de l'utilisateur
        $ecole = Ecole::with(['directeur.user'])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'École trouvée avec succès',
            'data' => $ecole,
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'École non trouvée',
            'error' => 'Aucune école ne correspond à cet ID',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de l\'école',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function index()
{
    try {
        // Récupérer toutes les écoles avec les informations des directeurs et des utilisateurs
        $ecoles = Ecole::with(['directeur.user'])->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Écoles récupérées avec succès',
            'data' => $ecoles,
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des écoles',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//lister les ecoles par niveau
public function indexByNiveau()
{
    try {
        // Récupérer toutes les écoles et les grouper par niveau d'éducation
        $ecolesParNiveau = Ecole::with(['directeur.user']) // Assurez-vous d'inclure les relations nécessaires
            ->get()
            ->groupBy('niveau_education');

        // Formater les résultats
        $result = [];
        foreach ($ecolesParNiveau as $niveau => $ecoles) {
            $result[] = [
                'niveau_education' => $niveau,
                'ecoles' => $ecoles,
            ];
        }

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des écoles par niveau récupérée avec succès',
            'data' => $result,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération des écoles par niveau',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function destroy($id)
{
    try {

        $ecole = Ecole::findOrFail($id);


        $ecole->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'École supprimée avec succès',
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'École non trouvée',
            'error' => 'Aucune école ne correspond à cet ID',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de l\'école',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
