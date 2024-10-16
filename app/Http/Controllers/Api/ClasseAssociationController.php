<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClasseAssociation;
use App\Models\Apprenant;
use App\Models\Enseignant;
use App\Models\Cours;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\ClasseAssociation\CreateClasseAssociationRequest;
use App\Http\Requests\ClasseAssociation\UpdateClasseAssociationRequest;
class ClasseAssociationController extends Controller
{
public function store(CreateClasseAssociationRequest $request)
{
    try {
        // Créer l'association
        $classeAssociation = ClasseAssociation::create([
            'classe_id' => $request->classe_id,
            'apprenant_id' => $request->apprenant_id,
            'enseignant_id' => $request->enseignant_id,
            'cours_id' => $request->cours_id,
        ]);

        // Optionnel : tu peux ajouter une variable pour stocker la classe associée
        $classeAssociee = $request->classe_id;

        return response()->json([
            'message' => 'Association créée avec succès.',
            'classeAssociation' => $classeAssociation,
            'classeAssociee' => $classeAssociee // Pour référence future
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Une erreur est survenue lors de la création de l\'association.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function show($id)
{
    try {
        // Récupérer la classe association avec les relations apprenant, cours et enseignant
        $classeAssociation = ClasseAssociation::with(['apprenant.user', 'cours', 'enseignant.user'])->findOrFail($id);

        // Structure des données à retourner
        $classeAssociationData = [
            'id' => $classeAssociation->id,

            // Informations de l'apprenant
            'apprenant' => $classeAssociation->apprenant ? [
                'id' => $classeAssociation->apprenant->id,
                'date_naissance' => $classeAssociation->apprenant->date_naissance,
                'lieu_naissance' => $classeAssociation->apprenant->lieu_naissance,
                'niveau_education' => $classeAssociation->apprenant->niveau_education,
                'numero_CNI' => $classeAssociation->apprenant->numero_CNI,
                'image' => $classeAssociation->apprenant->image,
                'numero_CNI' => $classeAssociation->apprenant->numero_CNI,
                'numero_carte_scolaire' => $classeAssociation->apprenant->numero_carte_scolaire,
                'numero_CNI' => $classeAssociation->apprenant->numero_CNI,
                'statut_marital' => $classeAssociation->apprenant->statut_marital,

                // Informations de l'utilisateur associé à l'apprenant
                'user' => $classeAssociation->apprenant->user ? [
                    'id' => $classeAssociation->apprenant->user->id,
                    'nom' => $classeAssociation->apprenant->user->nom,
                    'prenom' => $classeAssociation->apprenant->user->prenom,
                    'email' => $classeAssociation->apprenant->user->email,
                    'telephone' => $classeAssociation->apprenant->user->telephone,
                    'adresse' => $classeAssociation->apprenant->user->telephone,
                    'genre' => $classeAssociation->apprenant->user->telephone,
                    'etat' => $classeAssociation->apprenant->user->etat,
                ] : null,
            ] : null,

            // Informations du cours
            'cours' => $classeAssociation->cours ? [
                'id' => $classeAssociation->cours->id,
                'nom' => $classeAssociation->cours->nom,
                'description' => $classeAssociation->cours->description,
                'niveau_education' => $classeAssociation->cours->niveau_education,
                'duree' => $classeAssociation->cours->duree,
                'credits' => $classeAssociation->cours->credits,
            ] : null,

            // Informations de l'enseignant
            'enseignant' => $classeAssociation->enseignant ? [
                'id' => $classeAssociation->enseignant->id,
                'specialite' => $classeAssociation->enseignant->specialite,
                'statut_marital' => $classeAssociation->enseignant->statut_marital,
                'date_naissance' => $classeAssociation->enseignant->date_naissance,
                'lieu_naissance' => $classeAssociation->enseignant->lieu_naissance,
                'image' => $classeAssociation->enseignant->image,
                'numero_CNI' => $classeAssociation->enseignant->numero_CNI,
                'numero_securite_social' => $classeAssociation->enseignant->numero_securite_social,
                'statut' => $classeAssociation->enseignant->statut,
                'date_embauche' => $classeAssociation->enseignant->date_embauche,
                'date_fin' => $classeAssociation->enseignant->date_fin,

                // Informations de l'utilisateur associé à l'enseignant
                'user' => $classeAssociation->enseignant->user ? [
                    'id' => $classeAssociation->enseignant->user->id,
                    'nom' => $classeAssociation->enseignant->user->nom,
                    'prenom' => $classeAssociation->enseignant->user->prenom,
                    'email' => $classeAssociation->enseignant->user->email,
                    'telephone' => $classeAssociation->enseignant->user->telephone,
                    'genre' => $classeAssociation->enseignant->user->genre,
                    'adresse' => $classeAssociation->enseignant->user->adresse,
                    'etat' => $classeAssociation->enseignant->user->etat,
                ] : null,
            ] : null,
        ];

        // Retourner les informations en JSON
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Classe Association récupérée avec succès.',
            'data' => $classeAssociationData,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Classe Association non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    }
}
public function index()
{
    try {
        // Récupérer toutes les associations de classes avec les relations apprenant, cours et enseignant
        $classeAssociations = ClasseAssociation::with(['apprenant.user', 'cours', 'enseignant.user'])->get();

        // Construire une structure de données pour chaque association
        $classeAssociationsData = $classeAssociations->map(function ($classeAssociation) {
            return [
                'id' => $classeAssociation->id,

                // Informations de l'apprenant
                'apprenant' => $classeAssociation->apprenant ? [
                    'id' => $classeAssociation->apprenant->id,
                    'date_naissance' => $classeAssociation->apprenant->date_naissance,
                    'lieu_naissance' => $classeAssociation->apprenant->lieu_naissance,
                    'niveau_education' => $classeAssociation->apprenant->niveau_education,
                    'numero_CNI' => $classeAssociation->apprenant->numero_CNI,
                    'image' => $classeAssociation->apprenant->image,
                    'numero_carte_scolaire' => $classeAssociation->apprenant->numero_carte_scolaire,
                    'statut_marital' => $classeAssociation->apprenant->statut_marital,

                    // Informations de l'utilisateur associé à l'apprenant
                    'user' => $classeAssociation->apprenant->user ? [
                        'id' => $classeAssociation->apprenant->user->id,
                        'nom' => $classeAssociation->apprenant->user->nom,
                        'prenom' => $classeAssociation->apprenant->user->prenom,
                        'email' => $classeAssociation->apprenant->user->email,
                        'telephone' => $classeAssociation->apprenant->user->telephone,
                        'adresse' => $classeAssociation->apprenant->user->adresse,
                        'genre' => $classeAssociation->apprenant->user->genre,
                        'etat' => $classeAssociation->apprenant->user->etat,
                    ] : null,
                ] : null,

                // Informations du cours
                'cours' => $classeAssociation->cours ? [
                    'id' => $classeAssociation->cours->id,
                    'nom' => $classeAssociation->cours->nom,
                    'description' => $classeAssociation->cours->description,
                    'niveau_education' => $classeAssociation->cours->niveau_education,
                    'duree' => $classeAssociation->cours->duree,
                    'credits' => $classeAssociation->cours->credits,
                ] : null,

                // Informations de l'enseignant
                'enseignant' => $classeAssociation->enseignant ? [
                    'id' => $classeAssociation->enseignant->id,
                    'specialite' => $classeAssociation->enseignant->specialite,
                    'statut_marital' => $classeAssociation->enseignant->statut_marital,
                    'date_naissance' => $classeAssociation->enseignant->date_naissance,
                    'lieu_naissance' => $classeAssociation->enseignant->lieu_naissance,
                    'image' => $classeAssociation->enseignant->image,
                    'numero_CNI' => $classeAssociation->enseignant->numero_CNI,
                    'numero_securite_social' => $classeAssociation->enseignant->numero_securite_social,
                    'statut' => $classeAssociation->enseignant->statut,
                    'date_embauche' => $classeAssociation->enseignant->date_embauche,
                    'date_fin' => $classeAssociation->enseignant->date_fin,

                    // Informations de l'utilisateur associé à l'enseignant
                    'user' => $classeAssociation->enseignant->user ? [
                        'id' => $classeAssociation->enseignant->user->id,
                        'nom' => $classeAssociation->enseignant->user->nom,
                        'prenom' => $classeAssociation->enseignant->user->prenom,
                        'email' => $classeAssociation->enseignant->user->email,
                        'telephone' => $classeAssociation->enseignant->user->telephone,
                        'genre' => $classeAssociation->enseignant->user->genre,
                        'adresse' => $classeAssociation->enseignant->user->adresse,
                        'etat' => $classeAssociation->enseignant->user->etat,
                    ] : null,
                ] : null,
            ];
        });

        // Retourner les informations en JSON
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des associations récupérée avec succès.',
            'data' => $classeAssociationsData,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Erreur lors de la récupération des associations.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // Trouver l'association de classe par ID
        $classeAssociation = ClasseAssociation::findOrFail($id);

        // Supprimer l'association
        $classeAssociation->delete();

        // Retourner une réponse de succès
        return response()->json([
            'status_code' => 200,
            'status_message' => 'Association de classe supprimée avec succès.',
        ], 200);
    } catch (\Exception $e) {
        // Gérer les erreurs, notamment si l'ID n'existe pas
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Erreur lors de la suppression, association non trouvée.',
            'error' => $e->getMessage(),
        ], 404);
    }
}

    }

