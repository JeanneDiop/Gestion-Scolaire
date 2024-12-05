<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClasseAssociation;
use App\Models\Apprenant;
use App\Models\Enseignant;
use App\Models\Classe;
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
            'classe_id' => $request->classe_id ?? null,
            'apprenant_id' => $request->apprenant_id ?? null,
            'enseignant_id' => $request->enseignant_id ?? null,
            'cours_id' => $request->cours_id ?? null,
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


public function getClassPresenceDetailsWithAssociation($classeId)
{
    // Récupérer la classe avec ses associations (apprenants, enseignants, cours, et présences)
    $classe = Classe::with([
        'classeAssociations.apprenant',
        'classeAssociations.cours',
        'classeAssociations.enseignant',
        'classeAssociations.apprenant.presences.cours'
    ])->find($classeId);

    // Vérifier si la classe existe
    if (!$classe) {
        return response()->json([
            'message' => "Aucune classe trouvée avec l'ID {$classeId}."
        ], 404);
    }

    // Initialiser un tableau pour stocker les détails
    $classPresenceDetails = [];

    // Récupérer toutes les présences des apprenants directement associés à la classe
    foreach ($classe->classeAssociations as $association) {
        $apprenant = $association->apprenant;
        $presenceDetails = [];

        // Vérifier si l'apprenant a des enregistrements de présence
        if ($apprenant && $apprenant->presences) {
            foreach ($apprenant->presences as $presence) {
                $statut = ucfirst(strtolower($presence->statut)); // Capitaliser le statut

                // Ajouter les informations selon le statut
                if ($statut === 'Absent') {
                    $presenceDetails[] = [
                        'statut' => $statut,
                        'date_absent' => $presence->date_absent,
                        'raison_absence' => $presence->raison_absence,
                        'cours' => $presence->cours ? [
                            'id' => $presence->cours->id,
                            'nom' => $presence->cours->nom,
                        ] : null,
                    ];
                } elseif ($statut === 'Present') {
                    $presenceDetails[] = [
                        'statut' => $statut,
                        'date_present' => $presence->date_present,
                        'cours' => $presence->cours ? [
                            'id' => $presence->cours->id,
                            'nom' => $presence->cours->nom,
                        ] : null,
                    ];
                } elseif ($statut === 'Retard') {
                    $presenceDetails[] = [
                        'statut' => $statut,
                        'heure_arrivee' => $presence->heure_arrivee,
                        'duree_retard' => $presence->duree_retard,
                        'cours' => $presence->cours ? [
                            'id' => $presence->cours->id,
                            'nom' => $presence->cours->nom,
                        ] : null,
                    ];
                }
            }
        }

        // Ajouter les détails de l'apprenant et ses présences
        $classPresenceDetails[] = [
            'apprenant' => [
               'id' => $apprenant->id ?? null,
                'nom' => $apprenant->user?->nom,
                'prenom' => $apprenant->user?->prenom,
                'telephone' => $apprenant->user?->telephone,
                'email' => $apprenant->user?->email,
                'adresse' => $apprenant->user?->adresse,
                'genre' => $apprenant->user?->genre,
                'etat' => $apprenant->user?->etat,
                'lieu_naissance' => $apprenant->lieu_naissance ?? null,
                'date_naissance' => $apprenant->date_naissance ?? null,
                'numero_CNI' => $apprenant->numero_CNI ?? null,
                'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? null,
                'niveau_education' => $apprenant->niveau_education ?? null,
            ],
            'presences' => $presenceDetails,
            'associations' => $classe->classeAssociations->map(function ($association) {
                return [
                    'apprenant' => $association->apprenant ? [
                        'id' => $association->apprenant->id,
                        'nom' => $association->apprenant->user->nom ?? null,
                        'prenom' => $association->apprenant->user->prenom ?? null,
                    ] : null,
                    'cours' => $association->cours ? [
                        'id' => $association->cours->id,
                        'nom' => $association->cours->nom,
                    ] : null,
                    'enseignant' => $association->enseignant ? [
                        'id' => $association->enseignant->id,
                        'nom' => $association->enseignant->user->nom ?? null,
                        'prenom' => $association->enseignant->user->prenom ?? null,
                        'matiere_enseignée' => $association->enseignant->matiere_enseignée ?? null,
                    ] : null,
                ];
            }),
        ];
    }

    // Récupérer toutes les présences des apprenants sans association
    $presencesSansAssociation = [];
foreach ($classe->apprenants as $apprenant) {
    if ($apprenant->presences) {
        foreach ($apprenant->presences as $presence) {
            $statut = ucfirst(strtolower($presence->statut));
            $presencesSansAssociation[] = [
                'apprenant' => [
                   'id' => $apprenant->id ?? null,
                'nom' => $apprenant->user?->nom,
                'prenom' => $apprenant->user?->prenom,
                'telephone' => $apprenant->user?->telephone,
                'email' => $apprenant->user?->email,
                'adresse' => $apprenant->user?->adresse,
                'genre' => $apprenant->user?->genre,
                'etat' => $apprenant->user?->etat,
                'lieu_naissance' => $apprenant->lieu_naissance ?? null,
                'date_naissance' => $apprenant->date_naissance ?? null,
                'numero_CNI' => $apprenant->numero_CNI ?? null,
                'numero_identification_eleve' => $apprenant->numero_identification_eleve ?? null,
                'niveau_education' => $apprenant->niveau_education ?? null,
                ],
                'presence' => [
                    'statut' => $statut,
                    'date_absent' => $presence->date_absent,
                    'raison_absence' => $presence->raison_absence,
                    'date_present' => $presence->date_present,
                    'heure_arrivee' => $presence->heure_arrivee,
                    'duree_retard' => $presence->duree_retard,
                    'cours' => $presence->cours ? [
                        'id' => $presence->cours->id ?? null,
                        'nom' => $presence->cours->nom ?? null,
                    ] : null,
                ]
            ];
        }
    }
}

    // Fusionner les présences des associations et les présences sans association
    $allPresenceDetails = array_merge($classPresenceDetails, $presencesSansAssociation);

    // Retourner les détails de la classe et les présences
    return response()->json([
        'classe' => [
            'id' => $classe->id,
            'nom' => $classe->nom,
            'niveau_education' => $classe->niveau_education,
            'niveau_classe' => $classe->niveau_classe,
        ],
        'details' => $allPresenceDetails,
    ]);
}
}

