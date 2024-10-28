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
        try {
            $employe = new Employe();

            // Initialize image filename as null
            $fileName = null;
            if ($request->file('image')) {
                $file = $request->file('image');
                $fileName = date('YmdHi') . $file->getClientOriginalName(); // Create a unique filename
                $file->move(public_path('images'), $fileName); // Move the file to the specified directory
            }

            $cvFileName = null;
            if ($request->file('cv_diplomes')) {
            $cvFile = $request->file('cv_diplomes');
            $cvFileName = date('YmdHi') . $cvFile->getClientOriginalName();
            $cvFile->move(public_path('cv_diplomes'), $cvFileName);
            }
            $employe->nom = $request->nom;
            $employe->prenom = $request->prenom;
            $employe->telephone = $request->telephone;
            $employe->email = $request->email;
            $employe->adresse = $request->adresse;
            $employe->poste_occupé = $request->poste_occupé;
            $employe->image = $fileName;
            $employe->date_naissance = $request->date_naissance;
            $employe->lieu_naissance = $request->lieu_naissance;
            $employe->genre = $request->genre;
            $employe->nationalité = $request->nationalité;
            $employe->numero_CNI = $request->numero_CNI;
            $employe->date_debut_service = $request->date_debut_service;
            $employe->statut_employé = $request->statut_employé;
            $employe->type_contrat = $request->type_contrat;
            $employe->horaires_travail = $request->horaires_travail ?? null;
            $employe->numero_identification_employe = $request->numero_identification_employe;
            $employe->superviseur = $request->superviseur ?? null;
            $employe->salaire_base = $request->salaire_base;
            $employe->type_salaire = $request->type_salaire;
            $employe->prime_indemnités = $request->prime_indemnités ?? null ;
            $employe->cotisation_sociales = $request->cotisation_sociales ?? null;
            $employe->part_employeur = $request->part_employeur ?? null;
            $employe->retenue_salaire = $request->retenue_salaire ?? null;
            $employe->mode_paiement = $request->mode_paiement;
            $employe->banque_domiciliation = $request->banque_domiciliation ?? null;
            $employe->numero_compte_bancaire = $request->numero_carte_bancaire ?? null;
            $employe->cv_diplomes = $cvFileName ?? null;
            $employe->contrat_travail= $request->contrat_travail ?? null;
            $employe->ancienneté = $request->ancienneté ?? null;
            $employe->evaluation_performance = $request->evaluation_performance ?? null;
            $employe->commentaires_notes = $request->commentaires_notes ?? null;


            // Save the employe to the database
            $employe->save();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Employé a été ajouté',
                'data' => $employe,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(EditEmployeRequest $request, $id)
{
    try {
        $employe = Employe::find($id);

        // Initialize with the current image name
        $fileName = null;
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName(); // Create a unique filename
            $file->move(public_path('images'), $fileName); // Move the file to the specified directory
        }

        $cvFileName = null;
        if ($request->file('cv_diplomes')) {
        $cvFile = $request->file('cv_diplomes');
        $cvFileName = date('YmdHi') . $cvFile->getClientOriginalName();
        $cvFile->move(public_path('cv_diplomes'), $cvFileName);
        }
        $employe->nom = $request->nom;
        $employe->prenom = $request->prenom;
        $employe->telephone = $request->telephone;
        $employe->email = $request->email;
        $employe->adresse = $request->adresse;
        $employe->poste_occupé = $request->poste_occupé;
        $employe->image = $fileName;
        $employe->date_naissance = $request->date_naissance;
        $employe->lieu_naissance = $request->lieu_naissance;
        $employe->genre = $request->genre;
        $employe->nationalité = $request->nationalité;
        $employe->numero_CNI = $request->numero_CNI;
        $employe->date_debut_service = $request->date_debut_service;
        $employe->statut_employé = $request->statut_employé;
        $employe->type_contrat = $request->type_contrat;
        $employe->horaires_travail = $request->horaires_travail ?? null;
        $employe->numero_identification_employe = $request->numero_identification_employe;
        $employe->superviseur = $request->superviseur ?? null;
        $employe->salaire_base = $request->salaire_base;
        $employe->type_salaire = $request->type_salaire;
        $employe->prime_indemnités = $request->prime_indemnités ?? null ;
        $employe->cotisation_sociales = $request->cotisation_sociales ?? null;
        $employe->part_employeur = $request->part_employeur ?? null;
        $employe->retenue_salaire = $request->retenue_salaire ?? null;
        $employe->mode_paiement = $request->mode_paiement;
        $employe->banque_domiciliation = $request->banque_domiciliation ?? null;
        $employe->numero_compte_bancaire = $request->numero_carte_bancaire ?? null;
        $employe->cv_diplomes = $cvFileName ?? null;
        $employe->contrat_travail= $request->contrat_travail ?? null;
        $employe->ancienneté = $request->ancienneté ?? null;
        $employe->evaluation_performance = $request->evaluation_performance ?? null;
        $employe->commentaires_notes = $request->commentaires_notes ?? null;


        // Save the updated employe data
        $employe->update();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Employé a été modifié',
            'data' => $employe,
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
            'status_message' => 'Tous les employés ont été récupérés',
            'data' => Employe::all(),
        ]);
    } catch (Exception $e) {
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
