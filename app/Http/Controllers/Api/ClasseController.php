<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use Exception;
use App\Http\Requests\Classe\CreateClasseRequest; // Assurez-vous d'importer votre Request

class ClasseController extends Controller
{
    public function storeClasse(CreateClasseRequest $request)
    {
        try {
            $classe = new Classe();
            $classe->nom = $request->nom;
            $classe->niveau_classe = $request->niveau_classe; // Corrigez l'orthographe de 'niveau_classe'
            $classe->enseignant_id = $request->enseignant_id; // Corrigez l'orthographe de 'enseignant_id'
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
}
