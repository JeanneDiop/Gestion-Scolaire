<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\Apprenant\UpdateApprenantRequest;
use App\Http\Requests\Tuteur\UpdateTuteurRequest;
use App\Http\Requests\Directeur\UpdateDirecteurRequest;
use App\Http\Requests\Enseignant\UpdateEnseignantRequest;
use App\Http\Requests\Apprenant\CreateApprenantRequest;
use App\Http\Requests\Apprenant\CreateApprenantTuteurRequest;
use App\Http\Requests\Directeur\CreateDirecteurRequest;
use App\Http\Requests\Enseignant\CreateEnseignantRequest;
use App\Http\Requests\Tuteur\CreateTuteurRequest;

use App\Http\Requests\User\LogUserRequest;
use App\Models\Classe;
use App\Models\Role;
use App\Models\Tuteur;
use App\Models\Apprenant;
use App\Models\Enseignant;
use App\Models\Directeur;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','showApprenant','ListerEnseignantNiveauEcole','showDirecteur','showEnseignant','showUserEnseignant','showUserApprenant','showUserTuteur','showUserDirecteur','showTuteur','registerEnseignant','registerApprenant','ListeUtilisateur','ListerApprenant','ListerTuteur', 'ListerDirecteur', 'ListerEnseignant','registerDirecteur','supprimerEnseignant','supprimerTuteur','supprimerApprenant','supprimerUserApprenant','registerApprenantTuteur','supprimerUserDirecteur','supprimerUserEnseignant','supprimerUserTuteur','supprimerDirecteur','indexApprenants','indexDirecteurs','indexEnseignants','indexTuteurs','updateUserApprenant','updateApprenant','updateTuteur','updateUserTuteur','updateUserEnseignant','ListerApprenantParNiveau','updateEnseignant','updateUserDirecteur','updateDirecteur','updateUserEnseignant','updateUserEnseignant','archiverUser','archiverApprenant','archiverDirecteur','archiverEnseignant','archiverTuteur','refresh']]);
    }

public function login(LogUserRequest $request)
{

    $credentials = $request->only('email', 'password');
    $token = Auth::attempt($credentials);

    if (!$token) {
        return response()->json([
            'status'=>401,
            'message' => 'Connexion échouée',
        ]);
    }else{
        $user = Auth::user();
        if($user->etat==='inactif'){
            return response()->json([
                'status'=>405,
                'message' => 'Compte n\'existe pas',
            ]);
        }
        if($user->role_nom ==='employé' && $user->etat ==='actif'){
            return response()->json([
                'status'=>200,
                'message' => 'Salut employe',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

        }elseif($user->role_nom==='apprenant' && $user->etat ==='actif'){
            return response()->json([
                'status'=>200,
                'message' => 'Salut apprenant',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } elseif ($user->role_nom === 'enseignant' && $user->etat === 'actif') {
            return response()->json([
                'status' => 200,
                'message' => 'Salut enseignant',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } elseif ($user->role_nom === 'tuteur' && $user->etat === 'actif') {
            return response()->json([
                'status' => 200,
                'message' => 'Salut parent',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } elseif ($user->role_nom === 'directeur' && $user->etat === 'actif') {
            return response()->json([
                'status' => 200,
                'message' => 'Salut directeur',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        }else{
            return response()->json([
                'status'=>200,
                'message' => 'Salut Admin',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        }
    }

}

    public function logout()
    {
        if (auth('api')->check()) {
            auth('api')->logout();
        } elseif (auth('api')->check()) {
            auth('api')->logout();
        }
        return response()->json([
            "status" => true,
            "message" => "Utilisateur deconnecté avec succés"
        ], 200);
    }
    //----------------------Tuteur-------------------

public function registerTuteur(CreateTuteurRequest $request)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Créer un nouvel utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: 'actif', // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'tuteur',
        ]);

        // Créer un nouvel tuteur
        $tuteur = $user->tuteur()->create([
            'profession' => $request->profession,
            'statut_marital' => $request->statut_marital,
            'numero_CNI' => $request->numero_CNI,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'tuteur' => $tuteur,
        ]);
    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la création de l\'utilisateur.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function registerApprenantTuteur(CreateApprenantTuteurRequest $request)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        // Création de l'utilisateur Apprenant
        $userApprenant = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: 'actif', // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'apprenant',
        ]);

        // Création de l'utilisateur Tuteur
        $userTuteur = User::create([
            'nom' => $request->tuteur['nom'],
            'prenom' => $request->tuteur['prenom'],
            'email' => $request->tuteur['email'],
            'password' => Hash::make($request->tuteur['password']),
            'telephone' => $request->tuteur['telephone'],
            'adresse' => $request->tuteur['adresse'],
            'genre' => $request->tuteur['genre'],
            'etat' => $request->tuteur['etat'] ?: 'actif', // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'tuteur',
        ]);

        // Création du tuteur
        $tuteur = $userTuteur->tuteur()->create([
            'profession' => $request->tuteur['profession'],
            'statut_marital' => $request->tuteur['statut_marital'],
            'numero_CNI' => $request->tuteur['numero_CNI'],
        ]);

        // Création de l'apprenant avec le tuteur_id
        $apprenant = $userApprenant->apprenant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire,
            'niveau_education' => $request->niveau_education,
            'statut_marital' => $request->statut_marital,
            'classe_id' => $request->classe_id,
            'tuteur_id' => $tuteur->id, // Associer l'ID du tuteur à l'apprenant
        ]);

        // Récupération des informations de la classe
        $classe = Classe::find($request->classe_id);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Apprenant et Tuteur créés avec succès',
            'user_apprenant' => $userApprenant,
            'apprenant' => $apprenant,
            'user_tuteur' => $userTuteur,
            'tuteur' => $tuteur,
            'classe' => $classe,
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la création de l\'apprenant et du tuteur.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//supprimer un tuteur via sa table
public function supprimerTuteur(Tuteur $tuteur)
{
    try {
        // Vérifier si le tuteur existe bien
        if (!$tuteur) {
            return response()->json([
                'status' => 404,
                'message' => 'Tuteur non trouvé'
            ],404);
        }

        // Vérifier si le tuteur est encore assigné à des apprenants
        if ($tuteur->apprenants()->count() > 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Le tuteur est encore assigné à des apprenants et ne peut pas être supprimé.'
            ]);
        }

        // Supprimer le tuteur dans sa table (cela supprimera aussi l'utilisateur lié via l'héritage si onDelete('cascade') est défini)
        $tuteur->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Le tuteur et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du tuteur.',
            'error' => $e->getMessage()
        ]);
    }
}

//supprimer tuteur via la table user
public function supprimerUserTuteur(User $user)
{
    try {
        // Vérifier si l'utilisateur est bien un tuteur
        if (!$user->tuteur) {
            return response()->json([
                'status' => 404,
                'message' => 'Tuteur non trouvé'
            ]);
        }

        // Récupérer le tuteur associé à l'utilisateur
        $tuteur = $user->tuteur;

        // Vérifier si le tuteur est encore assigné à des apprenants
        if ($tuteur->apprenants()->count() > 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Le tuteur est encore assigné à des apprenants et ne peut pas être supprimé.'
            ]);
        }

        // Supprimer l'utilisateur (cela supprime aussi le tuteur via l'héritage)
        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Le tuteur et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du tuteur.',
            'error' => $e->getMessage()
        ],500);
    }
}
///---------------Apprenant-----------------------------
public function registerApprenant(CreateApprenantRequest $request)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        // Création de l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: 'actif', // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'apprenant',
        ]);

        // Création de l'apprenant
        $apprenant = $user->apprenant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire,
            'niveau_education' =>$request->niveau_education,
            'statut_marital' => $request->statut_marital,
            'tuteur_id' => $request->tuteur_id,
            'classe_id' => $request->classe_id,
        ]);

        // Vous devez récupérer les informations du tuteur et de la classe si nécessaire
        $tuteur = Tuteur::find($request->tuteur_id); // Assurez-vous d'importer le modèle Tuteur
        $classe = Classe::find($request->classe_id); // Assurez-vous d'importer le modèle Classe

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'apprenant' => $apprenant,
            'tuteur' => $tuteur,
            'classe' => $classe
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la création de l\'apprenant.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//modifier apprenant via la table user
public function updateUserApprenant(UpdateApprenantRequest $request, $userId)
{
    // Commencer une transaction
    DB::beginTransaction();

    try {
        // Récupérer l'utilisateur avec son apprenant associé
        $user = User::with('apprenant')->find($userId);

        // Vérifier si l'utilisateur et l'apprenant existent
        if (!$user || !$user->apprenant) {
            DB::rollBack(); // Annuler la transaction si l'utilisateur ou l'apprenant n'est pas trouvé
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur ou apprenant non trouvé.',
            ], 404);
        }

        // Mise à jour des informations de l'utilisateur
        $user->update([
            'nom' => $request->nom ?: $user->nom,
            'prenom' => $request->prenom ?: $user->prenom,
            'email' => $request->email ?: $user->email,
            'telephone' => $request->telephone ?: $user->telephone,
            'adresse' => $request->adresse ?: $user->adresse,
            'genre' => $request->genre ?: $user->genre,
            'etat' => $request->etat ?: $user->etat,
        ]);

        // Mise à jour des informations spécifiques de l'apprenant
        $user->apprenant->update([
            'date_naissance' => $request->date_naissance ?: $user->apprenant->date_naissance,
            'lieu_naissance' => $request->lieu_naissance ?: $user->apprenant->lieu_naissance,
            'numero_CNI' => $request->numero_CNI ?: $user->apprenant->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire ?: $user->apprenant->numero_carte_scolaire,
            'niveau_education' => $request->niveau_education ?: $user->apprenant->niveau_education,
            'statut_marital' => $request->statut_marital ?: $user->apprenant->statut_marital,
            'tuteur_id' => $request->tuteur_id ?: $user->apprenant->tuteur_id,
            'classe_id' => $request->classe_id ?: $user->apprenant->classe_id,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur et apprenant mis à jour avec succès.',
            'user' => $user,
            'apprenant' => $user->apprenant,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour.',
            'error' => $e->getMessage(),
        ],500);
    }
}



public function updateApprenant(UpdateApprenantRequest $request, $apprenant)
{
    // Commencer une transaction
    DB::beginTransaction();

    try {
        // Récupérer l'apprenant
        $apprenant = Apprenant::find($apprenant);

        if (!$apprenant) {
            DB::rollBack(); // Annuler la transaction si l'apprenant n'est pas trouvé
            return response()->json([
                'status' => 404,
                'message' => 'Apprenant non trouvé.',
            ], 404);
        }

        // Vérifier si l'apprenant est associé à un utilisateur
        $user = User::find($apprenant->user_id);

        if (!$user) {
            DB::rollBack(); // Annuler la transaction si l'utilisateur n'est pas trouvé
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur associé non trouvé.',
            ], 404);
        }

        // Mise à jour des informations de l'utilisateur (si nécessaire)
        $user->update([
            'nom' => $request->nom ?: $user->nom,
            'prenom' => $request->prenom ?: $user->prenom,
            'email' => $request->email ?: $user->email,
            'telephone' => $request->telephone ?: $user->telephone,
            'adresse' => $request->adresse ?: $user->adresse,
            'genre' => $request->genre ?: $user->genre,
            'etat' => $request->etat ?: $user->etat,
        ]);

        // Mise à jour des informations spécifiques de l'apprenant
        $apprenant->update([
            'date_naissance' => $request->date_naissance ?: $apprenant->date_naissance,
            'lieu_naissance' => $request->lieu_naissance ?: $apprenant->lieu_naissance,
            'numero_CNI' => $request->numero_CNI ?: $apprenant->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire ?: $apprenant->numero_carte_scolaire,
            'niveau_education' => $request->niveau_education ?: $user->apprenant->niveau_education,
            'statut_marital' => $request->statut_marital ?: $apprenant->statut_marital,
            'tuteur_id' => $request->tuteur_id ?: $apprenant->tuteur_id,
            'classe_id' => $request->classe_id ?: $apprenant->classe_id,
        ]);

        // Récupérer les informations du tuteur et de la classe si nécessaire
        $tuteur = Tuteur::find($apprenant->tuteur_id);
        $classe = Classe::find($apprenant->classe_id);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Apprenant mis à jour avec succès.',
            'user' => $user,
            'apprenant' => $apprenant,
            'tuteur' => $tuteur,
            'classe' => $classe,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour de l\'apprenant.',
            'error' => $e->getMessage(),
        ],500);
    }
}

//supprimer apprenant via sa table
public function supprimerApprenant(Apprenant $apprenant)
{
    try {
        // Rechercher l'apprenant par son ID avec ses relations
        $apprenant = Apprenant::with(['tuteur', 'classe', 'user'])->find($apprenant->id);

        // Vérifier si l'apprenant existe
        if (!$apprenant) {
            return response()->json([
                'status' => 404,
                'message' => 'Apprenant non trouvé'
            ],404);
        }

        // Récupérer le tuteur, la classe et l'utilisateur associés avant suppression
        $tuteur = $apprenant->tuteur;
        $classe = $apprenant->classe;
        $user = $apprenant->user;

        // Vérifier si le tuteur a d'autres apprenants avant de supprimer
        $tuteurApprenantsCount = $tuteur ? $tuteur->apprenants()->count() : 0;

        // Supprimer l'apprenant
        $apprenant->delete();

        // Supprimer l'utilisateur si la relation d'héritage est manuelle
        if ($user) {
            $user->delete();
            $userMessage = 'L\'utilisateur associé a été supprimé.';
        } else {
            $userMessage = 'Aucun utilisateur associé à cet apprenant.';
        }

        // Si le tuteur n'a plus d'apprenants, le supprimer
        if ($tuteur && $tuteurApprenantsCount == 1) {
            $tuteur->delete();
            $tuteurMessage = 'Le tuteur a été supprimé car aucun autre apprenant n\'est lié.';
        } else if ($tuteur && $tuteurApprenantsCount > 1) {
            $tuteurMessage = 'Le tuteur est toujours actif car d\'autres apprenants lui sont liés.';
        } else {
            $tuteurMessage = 'Aucun tuteur associé à cet apprenant.';
        }

        // Vérifier si la classe n'a plus d'apprenants
        if ($classe && $classe->apprenants()->count() == 0) {
            $classe->delete();
            $classeMessage = 'La classe a été supprimée car aucun autre apprenant n\'est lié.';
        } else if ($classe && $classe->apprenants()->count() > 0) {
            $classeMessage = 'La classe est toujours active car d\'autres apprenants y sont inscrits.';
        } else {
            $classeMessage = 'Aucune classe associée à cet apprenant.';
        }

        return response()->json([
            'status' => 200,
            'message' => 'L\'apprenant a été supprimé avec succès.',
            'details' => [
                'user' => $userMessage,
                'tuteur' => $tuteurMessage,
                'classe' => $classeMessage
            ]
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression de l\'apprenant.',
            'error' => $e->getMessage()
        ],500);
    }
}


public function supprimerUserApprenant(User $user)
{
    try {
        // Récupérer l'utilisateur par ID
        $user = User::find($user->id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur non trouvé.'
            ],404);
        }

        // Récupérer l'apprenant associé à cet utilisateur
        $apprenant = Apprenant::where('user_id', $user->id)->with(['tuteur', 'classe'])->first();

        // Vérifier si l'apprenant existe
        if (!$apprenant) {
            return response()->json([
                'status' => 404,
                'message' => 'Apprenant non trouvé pour cet utilisateur.'
            ],404);
        }

        // Récupérer le tuteur et la classe associés
        $tuteur = $apprenant->tuteur;
        $classe = $apprenant->classe;

        // Supprimer l'apprenant
        $apprenant->delete();

        // Supprimer l'utilisateur
        $user->delete();

        // Vérifier si le tuteur n'a plus d'apprenants
        if ($tuteur) {
            if ($tuteur->apprenants()->count() == 0) {
                $tuteur->delete();
                $tuteurMessage = 'Le tuteur a été supprimé car aucun autre apprenant n\'est lié.';
            } else {
                $tuteurMessage = 'Le tuteur est toujours actif car d\'autres apprenants lui sont liés.';
            }
        } else {
            $tuteurMessage = 'Aucun tuteur associé à cet apprenant.';
        }

        // Vérifier si la classe n'a plus d'apprenants
        if ($classe) {
            if ($classe->apprenants()->count() == 0) {
                $classe->delete();
                $classeMessage = 'La classe a été supprimée car aucun autre apprenant n\'est lié.';
            } else {
                $classeMessage = 'La classe est toujours active car d\'autres apprenants y sont inscrits.';
            }
        } else {
            $classeMessage = 'Aucune classe associée à cet apprenant.';
        }

        return response()->json([
            'status' => 200,
            'message' => 'L\'utilisateur et l\'apprenant ont été supprimés avec succès.',
            'details' => [
                'tuteur' => $tuteurMessage,
                'classe' => $classeMessage
            ]
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur.',
            'error' => $e->getMessage()
        ],500);
    }
}


//-----------------Enseignant-------------------------------
public function registerEnseignant(CreateEnseignantRequest $request)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        // Création de l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: 'actif', // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'enseignant',
        ]);

        // Création de l'enseignant
        $enseignant = $user->enseignant()->create([
            'specialite' => $request->specialite,
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'niveau_ecole' =>$request->niveau_ecole,
            'numero_CNI' => $request->numero_CNI,
            'numero_securite_social' => $request->numero_securite_social,
            'statut' => $request->statut,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'enseignant' => $enseignant
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la création de l\'utilisateur.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function updateUserEnseignant(UpdateEnseignantRequest $request, $userId)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Récupérer l'utilisateur et vérifier s'il est associé à un enseignant
        $user = User::with('enseignant')->find($userId);

        if (!$user || !$user->enseignant) {
            DB::rollBack(); // Annuler la transaction en cas de problème
            return response()->json([
                'status' => 404,
                'message' => 'Enseignant non trouvé.',
            ], 404);
        }

        // Mise à jour des informations de l'utilisateur
        $user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: $user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
        ]);

        // Mise à jour des informations spécifiques de l'enseignant
        $user->enseignant->update([
            'specialite' => $request->specialite,
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'niveau_ecole' =>$request->niveau_ecole,
            'numero_CNI' => $request->numero_CNI,
            'numero_securite_social' => $request->numero_securite_social,
            'statut' => $request->statut,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant mis à jour avec succès.',
            'user' => $user,
            'enseignant' => $user->enseignant,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour de l\'enseignant.',
            'error' => $e->getMessage(),
        ],500);
    }
}


//modifier enseignant dans sa table
public function updateEnseignant(UpdateEnseignantRequest $request, $id)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Récupérer l'enseignant et son utilisateur associé via l'ID
        $enseignant = Enseignant::with('user')->find($id);

        // Vérifier si l'enseignant existe
        if (!$enseignant) {
            DB::rollBack(); // Annuler la transaction
            return response()->json([
                'status' => 404,
                'message' => 'Enseignant non trouvé.',
            ], 404);
        }

        // Mise à jour des informations de l'utilisateur associé à cet enseignant
        $enseignant->user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: $enseignant->user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
        ]);

        // Mise à jour des informations spécifiques de l'enseignant
        $enseignant->update([
            'specialite' => $request->specialite,
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'niveau_ecole' =>$request->niveau_ecole,
            'numero_CNI' => $request->numero_CNI,
            'numero_securite_social' => $request->numero_securite_social,
            'statut' => $request->statut,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant et informations utilisateur mis à jour avec succès.',
            'enseignant' => $enseignant,
            'user' => $enseignant->user,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour de l\'enseignant.',
            'error' => $e->getMessage(),
        ],500);
    }
}


//Supprimer enseignant via la table user
public function supprimerUserEnseignant(User $user)
{
    try {
        // Vérifier si l'utilisateur est bien un enseignant
        if (!$user->enseignant()->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Enseignant non trouvé'
            ],404);
        }

        // Mettre à jour les classes pour retirer la référence à cet enseignant
        Classe::where('enseignant_id', $user->id)->update(['enseignant_id' => null]);

        // Supprimer l'utilisateur (cela supprime aussi l'enseignant via l'héritage)
        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'L\'enseignant et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression de l\'enseignant',
            'error' => $e->getMessage()
        ],500);
    }
}



//------------------- directeur-------------
public function registerDirecteur(CreateDirecteurRequest $request)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        // Validation des données d'entrée
        $validatedData = $request->validate([
            'annee_experience' => ['required', 'regex:/^\d+\s*(ans|année|années)?$/'],
            'date_prise_fonction' => 'required|integer|min:1900|max:' . date('Y'), // Validation pour INTEGER
        ]);

        // Extrait les chiffres uniquement
        $annee_experience = preg_replace('/\D/', '', $validatedData['annee_experience']);
        $date_prise_fonction = $validatedData['date_prise_fonction'];

        // Création de l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: 'actif', // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'directeur',
        ]);

        // Création du directeur
        $directeur = $user->directeur()->create([
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'qualification_academique' => $request->qualification_academique,
            'date_prise_fonction' => $date_prise_fonction,
            'annee_experience' => $annee_experience,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat
        ]);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'directeur' => $directeur
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la création de l\'utilisateur.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//modifier directeur via la table user
public function updateUserDirecteur(UpdateDirecteurRequest $request, $userId)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Récupérer l'utilisateur et vérifier si c'est un directeur
        $user = User::with('directeur')->find($userId);

        if (!$user || !$user->directeur) {
            DB::rollBack(); // Annuler la transaction
            return response()->json([
                'status' => 404,
                'message' => 'Directeur non trouvé.',
            ], 404);
        }

        // Validation des données
        $validatedData = $request->validate([
            'annee_experience' => ['nullable', 'regex:/^\d+\s*(ans|année|années)?$/'],
            'date_prise_fonction' => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);

        // Extraire les chiffres uniquement pour l'expérience
        if (isset($validatedData['annee_experience'])) {
            $annee_experience = preg_replace('/\D/', '', $validatedData['annee_experience']);
        } else {
            $annee_experience = $user->directeur->annee_experience; // Conserver l'actuel si non fourni
        }

        $date_prise_fonction = $validatedData['date_prise_fonction'] ?? $user->directeur->date_prise_fonction;

        // Mise à jour des informations de l'utilisateur
        $user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: $user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
        ]);

        // Mise à jour des informations spécifiques du directeur
        $user->directeur->update([
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'qualification_academique' => $request->qualification_academique,
            'date_prise_fonction' => $date_prise_fonction,
            'annee_experience' => $annee_experience,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Directeur mis à jour avec succès.',
            'user' => $user,
            'directeur' => $user->directeur,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour du directeur.',
            'error' => $e->getMessage(),
        ]);
    }
}


public function supprimerDirecteur(Directeur $directeur)
{
    try {
        // Vérifier si le directeur existe
        if (!$directeur) {
            return response()->json([
                'status' => 404,
                'message' => 'Directeur non trouvé'
            ]);
        }

        // Supprimer l'utilisateur correspondant, qui est hérité par le directeur
        $user = $directeur->user; // Accéder à l'utilisateur lié

        if ($user) {
            $user->delete(); // Supprimer l'utilisateur (ce qui supprime aussi le directeur grâce à l'héritage)
        }

        return response()->json([
            'status' => 200,
            'message' => 'Le directeur et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du directeur',
            'error' => $e->getMessage()
        ]);
    }
}

//supprimer directeur via la table user
public function supprimerUserDirecteur(User $user)
{
    try {
        // Vérifier si l'utilisateur est bien un directeur
        if (!$user->directeur) {
            return response()->json([
                'status' => 404,
                'message' => 'Le directeur associé à cet utilisateur n\'a pas été trouvé.'
            ]);
        }

        // Supprimer l'utilisateur (cela supprime également le directeur via l'héritage)
        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Le directeur et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du directeur',
            'error' => $e->getMessage()
        ]);
    }
}

//fonction modifier la table directeur
public function updateDirecteur(UpdateDirecteurRequest $request, $id)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Récupérer le directeur et son utilisateur associé via l'ID
        $directeur = Directeur::with('user')->find($id);
        // Vérifier si le directeur existe
        if (!$directeur) {
            DB::rollBack(); // Annuler la transaction
            return response()->json([
                'status' => 404,
                'message' => 'Directeur non trouvé.',
            ], 404);
        }
        // Validation des données
        $validatedData = $request->validate([
            'annee_experience' => ['nullable', 'regex:/^\d+\s*(ans|année|années)?$/'],
            'date_prise_fonction' => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);


        // Extraire les chiffres uniquement pour l'expérience
        $annee_experience = isset($validatedData['annee_experience'])
            ? preg_replace('/\D/', '', $validatedData['annee_experience'])
            : $directeur->annee_experience; // Conserver l'actuel si non fourni

        $date_prise_fonction = $validatedData['date_prise_fonction'] ?? $directeur->date_prise_fonction; // Conserver l'actuel si non fourni
        // Mise à jour des informations de l'utilisateur associé
        $directeur->user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: $directeur->user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
        ]);

        // Mise à jour des informations spécifiques du directeur
        $directeur->update([
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'qualification_academique' => $request->qualification_academique,
            'date_prise_fonction' => $date_prise_fonction, // Utiliser la valeur validée
            'annee_experience' => $annee_experience, // Utiliser la valeur validée
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Directeur et informations utilisateur mis à jour avec succès.',
            'directeur' => $directeur,
            'user' => $directeur->user,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour du directeur.',
            'error' => $e->getMessage(),
        ]);
    }
}
protected function respondWithToken($token,$user )
{
    return response()->json([
       'user'=>$user,
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 120
    ]);
}

///-------lister tous les utilisateurs-----------------
public function ListeUtilisateur()
{
    $users = User::where('role_nom', 'directeur')->orWhere('role_nom', 'enseignant')->orWhere('role_nom', 'tuteur')->orWhere('role_nom', 'apprenant')->orWhere('role_nom', 'employé')->get();
    return response()->json([
        'status'=>200,
        'users' => $users
    ]);
}
//lister tous les apprenants dans sa table
public function ListerApprenant()
{
    // Récupérer tous les apprenants avec les informations du tuteur, de la classe, et de l'utilisateur associé
    $apprenants = Apprenant::with(['user', 'tuteur.user', 'classe.salle', 'classe.enseignant.user'])->get();

    // Créer une nouvelle structure de données
    $apprenantsData = $apprenants->map(function ($apprenant) {
        // Informations de base de l'apprenant
        $data = [
            'id' => $apprenant->id,
            'date_naissance' => $apprenant->date_naissance,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'numero_CNI' => $apprenant->numero_CNI,
            'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
            'niveau_education' => $apprenant->niveau_education,
            'statut_marital' => $apprenant->statut_marital,
            'user' => $apprenant->user ? [
                'id' => $apprenant->user->id,
                'nom' => $apprenant->user->nom,
                'prenom' => $apprenant->user->prenom,
                'telephone' => $apprenant->user->telephone,
                'email' => $apprenant->user->email,
                'genre' => $apprenant->user->genre,
                'etat' => $apprenant->user->etat,
                'adresse' => $apprenant->user->adresse,
                'role_nom' => $apprenant->user->role_nom,
            ] : null,
        ];

        // Vérification du tuteur
        if ($apprenant->tuteur) {
            $tuteur = $apprenant->tuteur;
            // Si l'utilisateur associé au tuteur existe, on fusionne les données du tuteur et de l'utilisateur
            $data['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
        } else {
            $data['tuteur'] = null; // Si pas de tuteur, on définit à null
        }

        // Vérification de la classe et de la salle
        if ($apprenant->classe) {
            $data['classe'] = [
                'id' => $apprenant->classe->id,
                'nom_classe' => $apprenant->classe->nom,
                'niveau_classe' => $apprenant->classe->niveau_classe, // Ajout du nom de la classe
                'salle' => $apprenant->classe->salle ? [
                    'id' => $apprenant->classe->salle->id,
                    'nom' => $apprenant->classe->salle->nom,
                    'capacity' => $apprenant->classe->salle->capacity, // Capacité de la salle
                ] : null, // Si pas de salle, on définit à null
                'enseignant' => $apprenant->classe->enseignant ? [
                    'id' => $apprenant->classe->enseignant->id,
                    'nom' => $apprenant->classe->enseignant->user->nom,
                    'prenom' => $apprenant->classe->enseignant->user->prenom,
                    'telephone' => $apprenant->classe->enseignant->user->telephone,
                    'email' => $apprenant->classe->enseignant->user->email,
                    'specialite' => $apprenant->classe->enseignant->user->specialite,
                ] : null, // Si pas d'enseignant, on définit à null
            ];
        } else {
            $data['classe'] = null; // Si pas de classe, on définit à null
        }

        return $data; // Retour des données de l'apprenant formatées
    });

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenantsData, // Retour des données des apprenants
    ]);
}

//lister apprenant par niveau
public function ListerApprenantParNiveau(Request $request, $niveauEducation)
{
    // Récupérer les apprenants filtrés par niveau d'éducation
    $apprenants = Apprenant::with(['user', 'tuteur.user', 'classe.salle', 'classe.enseignant.user'])
                            ->where('niveau_education', $niveauEducation)
                            ->get();

    // Créer une nouvelle structure de données
    $apprenantsData = $apprenants->map(function ($apprenant) {
        // Informations de base de l'apprenant
        $data = [
            'id' => $apprenant->id,
            'date_naissance' => $apprenant->date_naissance,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'numero_CNI' => $apprenant->numero_CNI,
            'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
            'niveau_education' => $apprenant->niveau_education,
            'statut_marital' => $apprenant->statut_marital,
            'user' => $apprenant->user ? [
                'id' => $apprenant->user->id,
                'nom' => $apprenant->user->nom,
                'prenom' => $apprenant->user->prenom,
                'telephone' => $apprenant->user->telephone,
                'email' => $apprenant->user->email,
                'genre' => $apprenant->user->genre,
                'etat' => $apprenant->user->etat,
                'adresse' => $apprenant->user->adresse,
                'role_nom' => $apprenant->user->role_nom,
            ] : null,
        ];

        // Vérification du tuteur
        if ($apprenant->tuteur) {
            $tuteur = $apprenant->tuteur;
            // Si l'utilisateur associé au tuteur existe, on fusionne les données du tuteur et de l'utilisateur
            $data['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
        } else {
            $data['tuteur'] = null; // Si pas de tuteur, on définit à null
        }

        // Vérification de la classe et de la salle
        if ($apprenant->classe) {
            $data['classe'] = [
                'id' => $apprenant->classe->id,
                'nom_classe' => $apprenant->classe->nom,
                'niveau_classe' => $apprenant->classe->niveau_classe, // Ajout du nom de la classe
                'salle' => $apprenant->classe->salle ? [
                    'id' => $apprenant->classe->salle->id,
                    'nom' => $apprenant->classe->salle->nom,
                    'capacity' => $apprenant->classe->salle->capacity, // Capacité de la salle
                ] : null, // Si pas de salle, on définit à null
                'enseignant' => $apprenant->classe->enseignant ? [
                    'id' => $apprenant->classe->enseignant->id,
                    'nom' => $apprenant->classe->enseignant->user->nom,
                    'prenom' => $apprenant->classe->enseignant->user->prenom,
                    'telephone' => $apprenant->classe->enseignant->user->telephone,
                    'email' => $apprenant->classe->enseignant->user->email,
                    'specialite' => $apprenant->classe->enseignant->user->specialite,
                ] : null, // Si pas d'enseignant, on définit à null
            ];
        } else {
            $data['classe'] = null; // Si pas de classe, on définit à null
        }

        return $data; // Retour des données de l'apprenant formatées
    });

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenantsData, // Retour des données des apprenants
    ]);
}


 // Récupérer tous les enseignants depuis la table 'enseignants'
 public function ListerEnseignant()
{
    // Charger les enseignants avec leurs informations de User et Classes avec Salle
    $enseignants = Enseignant::with(['user', 'classes.salle'])->get();

    // Créer une nouvelle structure de données sans duplications
    $enseignantsData = $enseignants->map(function ($enseignant) {
        return [
            'id' => $enseignant->id,
            'specialite' => $enseignant->specialite,
            'statut_marital' => $enseignant->statut_marital,
            'date_naissance' => $enseignant->date_naissance,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'niveau_ecole' => $enseignant->niveau_ecole,
            'numero_CNI' => $enseignant->numero_CNI,
            'numero_securite_social' => $enseignant->numero_securite_social,
            'statut' => $enseignant->statut,
            'date_embauche' => $enseignant->date_embauche,
            'date_fin_contrat' => $enseignant->date_fin_contrat,
            'user' => $enseignant->user ? [
                'id' => $enseignant->user->id,
                'nom' => $enseignant->user->nom,
                'prenom' => $enseignant->user->prenom,
                'telephone' => $enseignant->user->telephone,
                'email' => $enseignant->user->email,
                'genre' => $enseignant->user->genre,
                'etat' => $enseignant->user->etat,
                'adresse' => $enseignant->user->adresse,
                'role_nom' => $enseignant->user->role_nom,
            ] : null,
            'classes' => $enseignant->classes->map(function ($classe) {
                return [
                    'id' => $classe->id,
                    'nom' => $classe->nom,
                    'niveau_classe' => $classe->niveau_classe,
                    'salle' => $classe->salle ? [
                        'id' => $classe->salle->id,
                        'nom' => $classe->salle->nom,
                        'capacity' => $classe->salle->capacity,
                        'type' => $classe->salle->type,
                    ] : null,
                ];
            }),
        ];
    });

    return response()->json([
        'status' => 200,
        'enseignants' => $enseignantsData,
    ]);
}



public function ListerEnseignantNiveauEcole($niveauEcole)
{
    // Charger les enseignants filtrés par niveau d'école avec leurs informations de User et Classes avec Salle
    $enseignants = Enseignant::with(['user', 'classes.salle'])
        ->where('niveau_ecole', $niveauEcole)
        ->get();

    // Créer une nouvelle structure de données
    $enseignantsData = $enseignants->map(function ($enseignant) {
        return [
            'id' => $enseignant->id,
            'specialite' => $enseignant->specialite,
            'statut_marital' => $enseignant->statut_marital,
            'date_naissance' => $enseignant->date_naissance,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'niveau_ecole' => $enseignant->niveau_ecole,
            'numero_CNI' => $enseignant->numero_CNI,
            'numero_securite_social' => $enseignant->numero_securite_social,
            'statut' => $enseignant->statut,
            'date_embauche' => $enseignant->date_embauche,
            'date_fin_contrat' => $enseignant->date_fin_contrat,
            'user' => $enseignant->user ? [
                'id' => $enseignant->user->id,
                'nom' => $enseignant->user->nom,
                'prenom' => $enseignant->user->prenom,
                'telephone' => $enseignant->user->telephone,
                'email' => $enseignant->user->email,
                'genre' => $enseignant->user->genre,
                'etat' => $enseignant->user->etat,
                'adresse' => $enseignant->user->adresse,
                'role_nom' => $enseignant->user->role_nom,
            ] : null,
            'classes' => $enseignant->classes->map(function ($classe) {
                return [
                    'id' => $classe->id,
                    'nom' => $classe->nom,
                    'niveau_classe' => $classe->niveau_classe,
                    'salle' => $classe->salle ? [
                        'id' => $classe->salle->id,
                        'nom' => $classe->salle->nom,
                        'capacity' => $classe->salle->capacity,
                        'type' => $classe->salle->type,
                    ] : null,
                ];
            }),
        ];
    });

    return response()->json([
        'status' => 200,
        'enseignants' => $enseignantsData,
    ]);
}


//----------------lister tuteur dans sa table
public function ListerTuteur()
{
    // Charger les tuteurs avec leurs informations de User et leurs apprenants
    $tuteurs = Tuteur::with(['user', 'apprenants.classe.salle', 'apprenants.classe.enseignant'])->get();

    // Créer une nouvelle structure de données sans duplications
    $tuteursData = $tuteurs->map(function ($tuteur) {
        return [
            // Attributs spécifiques au modèle Tuteur
            'id' => $tuteur->id,
            'profession' => $tuteur->profession,
            'statut_marital' => $tuteur->statut_marital,
            'numero_CNI' => $tuteur->numero_CNI,
            // Attributs spécifiques au modèle User
            'user' => $tuteur->user ? [
                'id' => $tuteur->user->id,
                'nom' => $tuteur->user->nom,
                'prenom' => $tuteur->user->prenom,
                'telephone' => $tuteur->user->telephone,
                'email' => $tuteur->user->email,
                'genre' => $tuteur->user->genre,
                'etat' => $tuteur->user->etat,
                'adresse' => $tuteur->user->adresse,
                'role_nom' => $tuteur->user->role_nom,
            ] : null,
            // Inclure les apprenants associés avec les classes, salles et enseignants
            'apprenants' => $tuteur->apprenants->map(function ($apprenant) {
                return [
                    'id' => $apprenant->id,
                    'nom' => $apprenant->user->nom,
                    'prenom' => $apprenant->user->prenom,
                    'date_naissance' => $apprenant->date_naissance,
                    'classe' => $apprenant->classe ? [
                        'id' => $apprenant->classe->id,
                        'nom' => $apprenant->classe->nom,
                        'niveau_classe' => $apprenant->classe->niveau_classe,
                        'salle' => $apprenant->classe->salle ? [
                            'id' => $apprenant->classe->salle->id,
                            'nom' => $apprenant->classe->salle->nom,
                            'capacity' => $apprenant->classe->salle->capacity,
                            'type' => $apprenant->classe->salle->type,
                        ] : null,
                        'enseignant' => $apprenant->classe->enseignant ? [
                            'id' => $apprenant->classe->enseignant->id,
                            'nom' => $apprenant->classe->enseignant->user->nom,
                            'prenom' => $apprenant->classe->enseignant->user->prenom,
                            'adresse' => $apprenant->classe->enseignant->user->adresse,
                            'email' => $apprenant->classe->enseignant->user->email,
                            'telephone' => $apprenant->classe->enseignant->user->telephone,
                            'specialite' => $apprenant->classe->enseignant->specialite,
                        ] : null,
                    ] : null,
                ];
            }),
        ];
    });

    return response()->json([
        'status' => 200,
        'tuteurs' => $tuteursData,
    ]);
}

//LIster Directeur dans sa table
public function ListerDirecteur()
{
    // Récupérer tous les directeurs de la table 'directeur'
    $directeurs = Directeur::with('user')->get(); // Récupération des directeurs avec leur relation utilisateur si nécessaire

    // Créer une nouvelle structure de données
    $directeursData = $directeurs->map(function ($directeur) {
        return [
            // Attributs spécifiques au modèle Directeur
            'id' => $directeur->id, // Assurez-vous que cela correspond à la clé primaire de la table directeur
            'date_naissance' => $directeur->date_naissance,
            'lieu_naissance' => $directeur->lieu_naissance,
            'annee_experience' => $directeur->annee_experience,
            'date_prise_fonction' => $directeur->date_prise_fonction,
            'numero_CNI' => $directeur->numero_CNI,
            'qualification_academique' => $directeur->qualification_academique,
            'statut_marital' => $directeur->statut_marital,
            'date_embauche' => $directeur->date_embauche,
            'date_fin_contrat' => $directeur->date_fin_contrat,
            // Ajoutez d'autres attributs spécifiques au modèle Directeur si nécessaire
            'user' => [
                'nom' => $directeur->user->nom,
                'prenom' => $directeur->user->prenom,
                'telephone' => $directeur->user->telephone,
                'email' => $directeur->user->email,
                'genre' => $directeur->user->genre,
                'etat' => $directeur->user->etat,
                'adresse' => $directeur->user->adresse,
                'role_nom' => $directeur->user->role_nom,
            ],
        ];
    });

    return response()->json([
        'status' => 200,
        'directeurs' => $directeursData,
    ]);
}

///-----lister tous les apprenants qui se trouve dans la table user
public function indexApprenants()
{
    // Charger les utilisateurs avec les rôles 'apprenant' et les informations associées
    $apprenants = User::with([
        'apprenant.tuteur.user', // Charger le tuteur et son utilisateur associé
        'apprenant.classe.salle', // Charger la salle associée
        'apprenant.classe.enseignant.user' // Charger l'enseignant et son utilisateur associé
    ])->where('role_nom', 'apprenant')->get();

    // Créer une nouvelle structure de données similaire à showUser
    $apprenantsData = $apprenants->map(function ($user) {
        $apprenant = $user->apprenant;

        // Structurer les données de l'apprenant
        $apprenantData = [
            'id' => $apprenant->id,
            'date_naissance' => $apprenant->date_naissance,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'numero_CNI' => $apprenant->numero_CNI,
            'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
            'niveau_eductaion' => $apprenant->niveau_education,
            'statut_marital' => $apprenant->statut_marital,
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'telephone' => $user->telephone,
                'email' => $user->email,
                'genre' => $user->genre,
                'etat' => $user->etat,
                'adresse' => $user->adresse,
                'role_nom' => $user->role_nom,
                // Ajouter d'autres champs nécessaires
            ]
        ];

        // Vérification du tuteur
        if ($apprenant->tuteur) {
            $tuteur = $apprenant->tuteur;
            // Fusionner les données du tuteur et de l'utilisateur associé
            $apprenantData['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
        } else {
            $apprenantData['tuteur'] = null; // Si pas de tuteur, on définit à null
        }

        // Vérification de la classe, de la salle et de l'enseignant
        if ($apprenant->classe) {
            $apprenantData['classe'] = [
                'id' => $apprenant->classe->id,
                'nom' => $apprenant->classe->nom, // Nom de la classe
                'niveau_classe' => $apprenant->classe->niveau_classe, // Niveau de la classe
                'salle' => $apprenant->classe->salle ? [
                    'id' => $apprenant->classe->salle->id,
                    'nom' => $apprenant->classe->salle->nom,
                    'capacity' => $apprenant->classe->salle->capacity, // Capacité de la salle
                    'type' => $apprenant->classe->salle->type, // Type de la salle
                ] : null, // Si la salle n'existe pas, on met null
                'enseignant' => $apprenant->classe->enseignant ? [
                    'id' => $apprenant->classe->enseignant->id,
                    'nom' => $apprenant->classe->enseignant->user->nom,
                    'prenom' => $apprenant->classe->enseignant->user->prenom,
                    'telephone' => $apprenant->classe->enseignant->user->telephone,
                    'adresse' => $apprenant->classe->enseignant->user->adresse,
                    'specialite' => $apprenant->classe->enseignant->user->specialite,
                    'statut_marital' => $apprenant->classe->enseignant->user->statut_marital,
                    'date_naissance' => $apprenant->classe->enseignant->user->date_naissance,
                    'lieu_naissance' => $apprenant->classe->enseignant->user->lieu_naissance,
                    'numero_CNI' => $apprenant->classe->enseignant->user->numero_CNI,
                    'numero_securite_social' => $apprenant->classe->enseignant->user->numero_securite_social,
                    'statut' => $apprenant->classe->enseignant->user->statut, // Statut de l'enseignant
                ] : null, // Si l'enseignant n'existe pas, on met null
            ];
        } else {
            $apprenantData['classe'] = null; // Si pas de classe, on met null
        }

        return $apprenantData;
    });

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenantsData,
    ]);
}





//------afficher information dun apprenant dans sa table

public function showApprenant($id)
{
    // Récupérer l'apprenant avec l'ID spécifié depuis la table 'apprenant'
    $apprenant = Apprenant::with(['user', 'tuteur.user', 'classe.salle', 'classe.enseignant.user'])
        ->where('id', $id)
        ->first();

    if (!$apprenant) {
        return response()->json([
            'status' => 404,
            'message' => 'Apprenant non trouvé.',
        ], 404);
    }

    // Structurer les données de l'apprenant
    $apprenantData = [
        'id' => $apprenant->id,
        'date_naissance' => $apprenant->date_naissance,
        'lieu_naissance' => $apprenant->lieu_naissance,
        'numero_CNI' => $apprenant->numero_CNI,
        'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
        'niveau_education' => $apprenant->niveau_education,
        'statut_marital' => $apprenant->statut_marital,
        'user' => $apprenant->user ? [
            'id' => $apprenant->user->id,
            'nom' => $apprenant->user->nom,
            'prenom' => $apprenant->user->prenom,
            'telephone' => $apprenant->user->telephone,
            'email' => $apprenant->user->email,
            'genre' => $apprenant->user->genre,
            'etat' => $apprenant->user->etat,
            'adresse' => $apprenant->user->adresse,
            'role_nom' => $apprenant->user->role_nom,
        ] : null,
    ];

    // Vérification du tuteur
    if ($apprenant->tuteur) {
        $tuteur = $apprenant->tuteur;
        // Si l'utilisateur associé au tuteur existe, on fusionne les données du tuteur et de l'utilisateur
        $apprenantData['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
    } else {
        $apprenantData['tuteur'] = null; // Si pas de tuteur, on définit à null
    }

    // Vérification de la classe, de la salle et de l'enseignant
    if ($apprenant->classe) {
        $apprenantData['classe'] = [
            'id' => $apprenant->classe->id,
            'nom' => $apprenant->classe->nom, // Nom de la classe
            'niveau_classe' => $apprenant->classe->niveau_classe, // Niveau de la classe
            'salle' => $apprenant->classe->salle ? [
                'id' => $apprenant->classe->salle->id,
                'nom' => $apprenant->classe->salle->nom,
                'capacity' => $apprenant->classe->salle->capacity, // Capacité de la salle
                'type' => $apprenant->classe->salle->type, // Type de la salle
            ] : null, // Si la salle n'existe pas, on met null
            'enseignant' => $apprenant->classe->enseignant ? [
                'id' => $apprenant->classe->enseignant->id,
                'nom' => $apprenant->classe->enseignant->user->nom,
                'prenom' => $apprenant->classe->enseignant->user->prenom,
                'telephone' => $apprenant->classe->enseignant->user->telephone,
                'adresse' => $apprenant->classe->enseignant->user->adresse,
                'specialite' => $apprenant->classe->enseignant->user->specialite,
                'statut_marital' => $apprenant->classe->enseignant->user->statut_marital,
                'date_naissance' => $apprenant->classe->enseignant->user->date_naissance,
                'lieu_naissance' => $apprenant->classe->enseignant->user->lieu_naissance,
                'numero_CNI' => $apprenant->classe->enseignant->user->numero_CNI,
                'numero_securite_social' => $apprenant->classe->enseignant->user->numero_securite_social,
                'statut' => $apprenant->classe->enseignant->user->statut, // Statut de l'enseignant
            ] : null, // Si l'enseignant n'existe pas, on met null
        ];
    } else {
        $apprenantData['classe'] = null; // Si pas de classe, on met null
    }

    return response()->json([
        'status' => 200,
        'apprenant' => $apprenantData,
    ]);
}


public function showUserApprenant($id)
{
    // Récupérer l'utilisateur avec l'ID spécifié, en incluant les relations avec les modèles 'apprenant', 'tuteur' et 'enseignant'
    $user = User::with(['apprenant.classe.salle', 'apprenant.classe.enseignant.user', 'tuteur.user', 'enseignant.user'])
        ->find($id);

    if (!$user) {
        return response()->json([
            'status' => 404,
            'message' => 'Utilisateur non trouvé.',
        ], 404);
    }

    // Structurer les données de l'utilisateur
    $userData = [
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'telephone' => $user->telephone,
        'email' => $user->email,
        'genre' => $user->genre,
        'etat' => $user->etat,
        'adresse' => $user->adresse,
        'role_nom' => $user->role_nom,
    ];

    // Vérification de l'apprenant associé
    if ($user->apprenant) {
        $apprenant = $user->apprenant;
        $userData['apprenant'] = [
            'id' => $apprenant->id,
            'date_naissance' => $apprenant->date_naissance,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'numero_CNI' => $apprenant->numero_CNI,
            'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
            'niveau_education' => $apprenant->niveau_education,
            'statut_marital' => $apprenant->statut_marital,
            'classe' => $apprenant->classe ? [
                'id' => $apprenant->classe->id,
                'nom' => $apprenant->classe->nom,
                'niveau_classe' => $apprenant->classe->niveau_classe,
                'salle' => $apprenant->classe->salle ? [
                    'id' => $apprenant->classe->salle->id,
                    'nom' => $apprenant->classe->salle->nom,
                    'capacity' => $apprenant->classe->salle->capacity,
                    'type' => $apprenant->classe->salle->type,
                ] : null,
                'enseignant' => $apprenant->classe->enseignant ? [
                    'id' => $apprenant->classe->enseignant->id,
                    'nom' => $apprenant->classe->enseignant->user->nom,
                    'prenom' => $apprenant->classe->enseignant->user->prenom,
                    'telephone' => $apprenant->classe->enseignant->user->telephone,
                    'adresse' => $apprenant->classe->enseignant->user->adresse,
                    'specialite' => $apprenant->classe->enseignant->specialite,
                    'statut_marital' => $apprenant->classe->enseignant->statut_marital,
                    'date_naissance' => $apprenant->classe->enseignant->date_naissance,
                    'lieu_naissance' => $apprenant->classe->enseignant->lieu_naissance,
                    'numero_CNI' => $apprenant->classe->enseignant->numero_CNI,
                    'numero_securite_social' => $apprenant->classe->enseignant->numero_securite_social,
                    'statut' => $apprenant->classe->enseignant->statut,
                ] : null,
            ] : null,
        ];
    } else {
        $userData['apprenant'] = null; // Si pas d'apprenant, on met null
    }

    // Vérification du tuteur associé
    if ($user->tuteur) {
        $tuteur = $user->tuteur;
        $userData['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
    } else {
        $userData['tuteur'] = null; // Si pas de tuteur, on met null
    }

    // Vérification de l'enseignant associé
    if ($user->enseignant) {
        $enseignant = $user->enseignant;
        $userData['enseignant'] = [
            'id' => $enseignant->id,
            'specialite' => $enseignant->specialite,
            'statut_marital' => $enseignant->statut_marital,
            'date_naissance' => $enseignant->date_naissance,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'numero_CNI' => $enseignant->numero_CNI,
            'numero_securite_social' => $enseignant->numero_securite_social,
            'statut' => $enseignant->statut,
            'date_embauche' => $enseignant->date_embauche,
            'date_fin_contrat' => $enseignant->date_fin_contrat,
            'user' => $enseignant->user ? [
                'id' => $enseignant->user->id,
                'nom' => $enseignant->user->nom,
                'prenom' => $enseignant->user->prenom,
                'telephone' => $enseignant->user->telephone,
                'email' => $enseignant->user->email,
                'genre' => $enseignant->user->genre,
                'etat' => $enseignant->user->etat,
                'adresse' => $enseignant->user->adresse,
                'role_nom' => $enseignant->user->role_nom,
            ] : null,
        ];
    } else {
        $userData['enseignant'] = null; // Si pas d'enseignant, on met null
    }

    return response()->json([
        'status' => 200,
        'user' => $userData,
    ]);
}

//----------info enseignant dans sa table
public function showEnseignant($id)
{
    // Récupérer l'enseignant avec l'ID spécifié en incluant les informations de User et les classes associées
    $enseignant = Enseignant::with(['user', 'classes.salle'])->find($id);

    // Vérifier si l'enseignant n'existe pas
    if (!$enseignant) {
        return response()->json([
            'status' => 404,
            'message' => 'Enseignant non trouvé.',
        ], 404);
    }

    // Structurer les données de l'enseignant et de l'utilisateur
    $enseignantData = [
        'id' => $enseignant->id,
        'specialite' => $enseignant->specialite,
        'statut_marital' => $enseignant->statut_marital,
        'date_naissance' => $enseignant->date_naissance,
        'lieu_naissance' => $enseignant->lieu_naissance,
        'niveau_ecole' => $enseignant->niveau_ecole,
        'numero_CNI' => $enseignant->numero_CNI,
        'numero_securite_social' => $enseignant->numero_securite_social,
        'statut' => $enseignant->statut,
        'date_embauche' => $enseignant->date_embauche,
        'date_fin_contrat' => $enseignant->date_fin_contrat,
        'user' => $enseignant->user ? [
            'id' => $enseignant->user->id,
            'nom' => $enseignant->user->nom,
            'prenom' => $enseignant->user->prenom,
            'telephone' => $enseignant->user->telephone,
            'email' => $enseignant->user->email,
            'genre' => $enseignant->user->genre,
            'etat' => $enseignant->user->etat,
            'adresse' => $enseignant->user->adresse,
            'role_nom' => $enseignant->user->role_nom,
        ] : null,
        'classes' => $enseignant->classes->map(function ($classe) {
            return [
                'id' => $classe->id,
                'nom' => $classe->nom,
                'niveau_classe' => $classe->niveau_classe,
                'salle' => $classe->salle ? [
                    'id' => $classe->salle->id,
                    'nom' => $classe->salle->nom,
                    'capacity' => $classe->salle->capacity,
                    'type' => $classe->salle->type,
                ] : null,
            ];
        }),
    ];

    return response()->json([
        'status' => 200,
        'enseignant' => $enseignantData,
    ]);
}


//---information dun directeur dans sa table
public function showDirecteur($id)
{
    // Récupérer le directeur avec l'ID spécifié
    $directeur = Directeur::with('user')->where('id', $id)->first();

    if (!$directeur) {
        return response()->json([
            'status' => 404,
            'message' => 'Directeur non trouvé.',
        ], 404);
    }

    // Créer une structure de données personnalisée
    $directeurData = [
        'id' => $directeur->id,
        'date_naissance' => $directeur->date_naissance,
        'lieu_naissance' => $directeur->lieu_naissance,
        'annee_experience' => $directeur->annee_experience,
        'date_prise_fonction' => $directeur->date_prise_fonction,
        'numero_CNI' => $directeur->numero_CNI,
        'qualification_academique' => $directeur->qualification_academique,
        'statut_marital' => $directeur->statut_marital,
        'date_embauche' => $directeur->date_embauche,
        'date_fin_contrat' => $directeur->date_fin_contrat,
        'user' => $directeur->user ? [
            'id' => $directeur->user->id,
            'nom' => $directeur->user->nom,
            'prenom' => $directeur->user->prenom,
            'telephone' => $directeur->user->telephone,
            'email' => $directeur->user->email,
            'genre' => $directeur->user->genre,
            'etat' => $directeur->user->etat,
            'adresse' => $directeur->user->adresse,
            'role_nom' => $directeur->user->role_nom,
        ] : null,
    ];

    return response()->json([
        'status' => 200,
        'directeur' => $directeurData,
    ]);
}
public function showUserDirecteur($id)
{
    // Récupérer l'utilisateur avec le rôle 'directeur' et son directeur associé
    $user = User::with('directeur')->where('id', $id)->where('role_nom', 'directeur')->first();

    // Vérifier si l'utilisateur existe et a un rôle de directeur
    if (!$user || !$user->directeur) {
        return response()->json([
            'status' => 404,
            'message' => 'Directeur non trouvé.',
        ], 404);
    }

    // Créer une structure de données personnalisée
    $directeurData = [
        'id' => $user->directeur->id,
        'date_naissance' => $user->directeur->date_naissance,
        'lieu_naissance' => $user->directeur->lieu_naissance,
        'annee_experience' => $user->directeur->annee_experience,
        'date_prise_fonction' => $user->directeur->date_prise_fonction,
        'numero_CNI' => $user->directeur->numero_CNI,
        'qualification_academique' => $user->directeur->qualification_academique,
        'statut_marital' => $user->directeur->statut_marital,
        'date_embauche' => $user->directeur->date_embauche,
        'date_fin_contrat' => $user->directeur->date_fin_contrat,
        'user' => [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'genre' => $user->genre,
            'etat' => $user->etat,
            'adresse' => $user->adresse,
            'role_nom' => $user->role_nom,
        ],
    ];

    return response()->json([
        'status' => 200,
        'directeur' => $directeurData,
    ]);
}



public function showUserEnseignant($id)
{
    // Récupérer l'utilisateur avec l'ID spécifié qui a le rôle 'enseignant' et charger les relations nécessaires
    $user = User::with(['enseignant', 'enseignant.classes.salle'])
                ->where('id', $id)
                ->where('role_nom', 'enseignant')
                ->first();

    // Vérifier si l'utilisateur n'existe pas ou n'est pas un enseignant
    if (!$user || !$user->enseignant) {
        return response()->json([
            'status' => 404,
            'message' => 'Enseignant non trouvé.',
        ], 404);
    }

    // Structurer les données de l'enseignant et de l'utilisateur
    $enseignantData = [
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'telephone' => $user->telephone,
        'email' => $user->email,
        'genre' => $user->genre,
        'etat' => $user->etat,
        'adresse' => $user->adresse,
        'role_nom' => $user->role_nom,
        'enseignant' => [
            'id' => $user->enseignant->id,
            'specialite' => $user->enseignant->specialite,
            'statut_marital' => $user->enseignant->statut_marital,
            'date_naissance' => $user->enseignant->date_naissance,
            'lieu_naissance' => $user->enseignant->lieu_naissance,
            'niveau_ecole' => $user->enseignant->niveau_ecole,
            'numero_CNI' => $user->enseignant->numero_CNI,
            'numero_securite_social' => $user->enseignant->numero_securite_social,
            'statut' => $user->enseignant->statut,
            'date_embauche' => $user->enseignant->date_embauche,
            'date_fin_contrat' => $user->enseignant->date_fin_contrat,
            'classes' => $user->enseignant->classes->map(function ($classe) {
                return [
                    'id' => $classe->id,
                    'nom' => $classe->nom,
                    'niveau_classe' => $classe->niveau_classe,
                    'salle' => $classe->salle ? [
                        'id' => $classe->salle->id,
                        'nom' => $classe->salle->nom,
                        'capacity' => $classe->salle->capacity,
                        'type' => $classe->salle->type,
                    ] : null,
                ];
            }),
        ]
    ];

    return response()->json([
        'status' => 200,
        'enseignant' => $enseignantData,
    ]);
}

//afficher les details dun tuteur dans sa table
public function showTuteur($id)
{
    // Récupérer le tuteur avec l'ID spécifié et l'apprenant associé
    $tuteur = Tuteur::with(['user', 'apprenants.classe.salle', 'apprenants.classe.enseignant'])->where('id', $id)->first();

    if (!$tuteur) {
        return response()->json([
            'status' => 404,
            'message' => 'Tuteur non trouvé.',
        ], 404);
    }

    // Créer une structure de données sans duplications
    $tuteurData = [
        // Attributs spécifiques au modèle Tuteur
        'id' => $tuteur->id,
        'profession' => $tuteur->profession,
        'statut_marital' => $tuteur->statut_marital,
        'numero_CNI' => $tuteur->numero_CNI,
        // Attributs spécifiques au modèle User
        'user' => [
            'id' => $tuteur->user->id,
            'nom' => $tuteur->user->nom,
            'prenom' => $tuteur->user->prenom,
            'telephone' => $tuteur->user->telephone,
            'email' => $tuteur->user->email,
            'genre' => $tuteur->user->genre,
            'etat' => $tuteur->user->etat,
            'adresse' => $tuteur->user->adresse,
            'role_nom' => $tuteur->user->role_nom,
        ],
        // Inclure les apprenants associés avec les classes, salles et enseignants
        'apprenants' => $tuteur->apprenants->map(function ($apprenant) {
            return [
                'id' => $apprenant->id,
                // Accès direct à l'héritage de User
                'nom' => $apprenant->user->nom,
                'prenom' => $apprenant->user->prenom,
                'date_naissance' => $apprenant->date_naissance,
                'classe' => $apprenant->classe ? [
                    'id' => $apprenant->classe->id,
                    'nom' => $apprenant->classe->nom,
                    'niveau_classe' => $apprenant->classe->niveau_classe,
                    'salle' => $apprenant->classe->salle ? [
                        'id' => $apprenant->classe->salle->id,
                        'nom' => $apprenant->classe->salle->nom,
                        'capacity' => $apprenant->classe->salle->capacity,
                        'type' => $apprenant->classe->salle->type,
                    ] : null,
                    'enseignant' => $apprenant->classe->enseignant ? [
                        'id' => $apprenant->classe->enseignant->id,
                        'nom' => $apprenant->classe->enseignant->user->nom,
                        'prenom' => $apprenant->classe->enseignant->user->prenom,
                        'adresse'=>$apprenant->classe->enseignant->user->adresse,
                        'email'=>$apprenant->classe->enseignant->user->email,
                        'telephone'=>$apprenant->classe->enseignant->user->telephone,
                        'specialite' => $apprenant->classe->enseignant->specialite,
                    ] : null,
                ] : null,
            ];
        }),
    ];

    return response()->json([
        'status' => 200,
        'tuteur' => $tuteurData,
    ]);
}

public function showUserTuteur($id)
{
    // Récupérer l'utilisateur avec l'ID spécifié et les informations du tuteur associé
    $user = User::with(['tuteur', 'tuteur.apprenants.classe.salle', 'tuteur.apprenants.classe.enseignant'])
        ->where('id', $id)
        ->first();

    if (!$user) {
        return response()->json([
            'status' => 404,
            'message' => 'Utilisateur non trouvé.',
        ], 404);
    }

    // Créer une structure de données pour le tuteur
    $tuteurData = [
        // Attributs spécifiques au modèle User
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'telephone' => $user->telephone,
        'email' => $user->email,
        'genre' => $user->genre,
        'etat' => $user->etat,
        'adresse' => $user->adresse,
        'role_nom' => $user->role_nom,
        // Informations spécifiques au modèle Tuteur
        'tuteur' => $user->tuteur ? [
            'profession' => $user->tuteur->profession,
            'statut_marital' => $user->tuteur->statut_marital,
            'numero_CNI' => $user->tuteur->numero_CNI,
            // Inclure les apprenants associés avec les classes, salles et enseignants
            'apprenants' => $user->tuteur->apprenants->map(function ($apprenant) {
                return [
                    'id' => $apprenant->id,
                    'nom' => $apprenant->user->nom,
                    'prenom' => $apprenant->user->prenom,
                    'date_naissance' => $apprenant->date_naissance,
                    'classe' => $apprenant->classe ? [
                        'id' => $apprenant->classe->id,
                        'nom' => $apprenant->classe->nom,
                        'niveau_classe' => $apprenant->classe->niveau_classe,
                        'salle' => $apprenant->classe->salle ? [
                            'id' => $apprenant->classe->salle->id,
                            'nom' => $apprenant->classe->salle->nom,
                            'capacity' => $apprenant->classe->salle->capacity,
                            'type' => $apprenant->classe->salle->type,
                        ] : null,
                        'enseignant' => $apprenant->classe->enseignant ? [
                            'id' => $apprenant->classe->enseignant->id,
                            'nom' => $apprenant->classe->enseignant->user->nom,
                            'prenom' => $apprenant->classe->enseignant->user->prenom,
                            'adresse' => $apprenant->classe->enseignant->user->adresse,
                            'email' => $apprenant->classe->enseignant->user->email,
                            'telephone' => $apprenant->classe->enseignant->user->telephone,
                            'specialite' => $apprenant->classe->enseignant->specialite,
                        ] : null,
                    ] : null,
                ];
            }),
        ] : null,
    ];

    return response()->json([
        'status' => 200,
        'user' => $tuteurData,
    ]);
}


//lister enseignants qui se trouve dans la table user
public function indexEnseignants()
{
    // Charger les utilisateurs avec le rôle "enseignant" et leurs informations liées
    $enseignants = User::with(['enseignant', 'enseignant.classes.salle'])
                        ->where('role_nom', 'enseignant')
                        ->get();

    // Créer une nouvelle structure de données
    $enseignantsData = $enseignants->map(function ($user) {
        return [
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'genre' => $user->genre,
            'etat' => $user->etat,
            'adresse' => $user->adresse,
            'role_nom' => $user->role_nom,
            'enseignant' => $user->enseignant ? [
                'id' => $user->enseignant->id,
                'specialite' => $user->enseignant->specialite,
                'statut_marital' => $user->enseignant->statut_marital,
                'date_naissance' => $user->enseignant->date_naissance,
                'lieu_naissance' => $user->enseignant->lieu_naissance,
                'niveau_ecole' => $user->enseignant->niveau_ecole,
                'numero_CNI' => $user->enseignant->numero_CNI,
                'numero_securite_social' => $user->enseignant->numero_securite_social,
                'statut' => $user->enseignant->statut,
                'date_embauche' => $user->enseignant->date_embauche,
                'date_fin_contrat' => $user->enseignant->date_fin_contrat,
                'classes' => $user->enseignant->classes->map(function ($classe) {
                    return [
                        'id' => $classe->id,
                        'nom' => $classe->nom,
                        'niveau_classe' => $classe->niveau_classe,
                        'salle' => $classe->salle ? [
                            'id' => $classe->salle->id,
                            'nom' => $classe->salle->nom,
                            'capacity' => $classe->salle->capacity,
                            'type' => $classe->salle->type,
                        ] : null,
                    ];
                }),
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'enseignants' => $enseignantsData,
    ]);
}



//lister tous tuteurs dans la table user
public function indexTuteurs()
{
    // Récupérer les tuteurs à partir de la table User
    $tuteurs = User::where('role_nom', 'tuteur')->with(['tuteur.apprenants.classe.salle', 'tuteur.apprenants.classe.enseignant'])->get();

    // Créer une nouvelle structure de données
    $tuteursData = $tuteurs->map(function ($user) {
        return [
            // Attributs spécifiques au modèle User
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'genre' => $user->genre,
            'etat' => $user->etat,
            'adresse' => $user->adresse,
            'role_nom' => $user->role_nom,
            // Attributs spécifiques au modèle Tuteur
            'tuteur' => $user->tuteur ? [
                'profession' => $user->tuteur->profession,
                'statut_marital' => $user->tuteur->statut_marital,
                'numero_CNI' => $user->tuteur->numero_CNI,
                // Inclure les apprenants associés avec les classes, salles et enseignants
                'apprenants' => $user->tuteur->apprenants->map(function ($apprenant) {
                    return [
                        'id' => $apprenant->id,
                        'nom' => $apprenant->user->nom,
                        'prenom' => $apprenant->user->prenom,
                        'date_naissance' => $apprenant->date_naissance,
                        'classe' => $apprenant->classe ? [
                            'id' => $apprenant->classe->id,
                            'nom' => $apprenant->classe->nom,
                            'niveau_classe' => $apprenant->classe->niveau_classe,
                            'salle' => $apprenant->classe->salle ? [
                                'id' => $apprenant->classe->salle->id,
                                'nom' => $apprenant->classe->salle->nom,
                                'capacity' => $apprenant->classe->salle->capacity,
                                'type' => $apprenant->classe->salle->type,
                            ] : null,
                            'enseignant' => $apprenant->classe->enseignant ? [
                                'id' => $apprenant->classe->enseignant->id,
                                'nom' => $apprenant->classe->enseignant->user->nom,
                                'prenom' => $apprenant->classe->enseignant->user->prenom,
                                'adresse' => $apprenant->classe->enseignant->user->adresse,
                                'email' => $apprenant->classe->enseignant->user->email,
                                'telephone' => $apprenant->classe->enseignant->user->telephone,
                                'specialite' => $apprenant->classe->enseignant->specialite,
                            ] : null,
                        ] : null,
                    ];
                }),
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'tuteurs' => $tuteursData,
    ]);
}


//lister tous les directeurs dans la table user
public function indexDirecteurs()
{
    $directeurs = User::where('role_nom', 'directeur')->with('directeur')->get();

    // Créer une nouvelle structure de données
    $directeursData = $directeurs->map(function ($user) {
        return [
            // Attributs spécifiques au modèle User
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'email' => $user->email,
            'genre' => $user->genre,
            'etat' => $user->etat,
            'adresse' => $user->adresse,
            'role_nom' => $user->role_nom,
            // Attributs spécifiques au modèle Directeur
            'directeur' => $user->directeur ? [
                'date_naissance' => $user->directeur->date_naissance,
                'lieu_naissance' => $user->directeur->lieu_naissance,
                'annee_experience' => $user->directeur->annee_experience,
                'date_prise_fonction' => $user->directeur->date_prise_fonction,
                'numero_CNI' => $user->directeur->numero_CNI,
                'qualification_academique' => $user->directeur->qualification_academique,
                'statut_marital' => $user->directeur->statut_marital,
                'date_embauche' => $user->directeur->date_embauche,
                'date_fin_contrat' => $user->directeur->date_fin_contrat,
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'directeurs' => $directeursData,
    ]);
}
//archiver ou desarchiver un user
public function archiverUser(User $user) {
    if ($user->etat === 'actif') {
        $user->etat = 'inactif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte désactivé',
            'user' => $user
        ]);
    } else {
        $user->etat = 'actif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte activé',
            'user' => $user
        ]);
    }
}

//archiver tuteur via sa table
public function archiverTuteur(Tuteur $tuteur) {
    // Récupérer l'utilisateur associé au tuteur
    $user = $tuteur->user;

    if ($user->etat === 'actif') {
        $user->etat = 'inactif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte désactivé',
            'user' => $user
        ]);
    } else {
        $user->etat = 'actif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte activé',
            'user' => $user
        ]);
    }
}
//archiver apprenant via sa table
public function archiverApprenant(Apprenant $apprenant) {
    // Récupérer l'utilisateur associé au apprenant
    $user = $apprenant->user;

    if ($user->etat === 'actif') {
        $user->etat = 'inactif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte désactivé',
            'user' => $user
        ]);
    } else {
        $user->etat = 'actif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte activé',
            'user' => $user
        ]);
    }
}

//archiver enseignant via sa table
public function archiverEnseignant(Enseignant $enseignant) {
    // Récupérer l'utilisateur associé au enseignant
    $user = $enseignant->user;

    if ($user->etat === 'actif') {
        $user->etat = 'inactif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte désactivé',
            'user' => $user
        ]);
    } else {
        $user->etat = 'actif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte activé',
            'user' => $user
        ]);
    }
}
//archiver directeur via sa table
public function archiverDirecteur(Directeur $directeur) {
    // Récupérer l'utilisateur associé au directeur
    $user = $directeur->user;

    if ($user->etat === 'actif') {
        $user->etat = 'inactif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte désactivé',
            'user' => $user
        ]);
    } else {
        $user->etat = 'actif';
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Compte activé',
            'user' => $user
        ]);
    }
}
//modifier password tuteur
public function updatePasswordTuteur(Request $request)
{
    // Valider les données de la requête
    $validatedData = $request->validate([
        'password' => 'required|min:8', ],
         ['password.required' => 'Le champ mot de passe est requis.',]);

    // Vérifier si l'utilisateur existe
    $tuteur = Tuteur::where('user_id')->first(); // Assurez-vous que le champ user_id est correct

    if (!$tuteur) {
        return response()->json([
            'status_code' => 404,
            'status_message' => 'Tuteur non trouvé.'
        ], 404);
    }

    // Mettre à jour le mot de passe de l'utilisateur associé
    $user = $tuteur->user; // Récupérer l'utilisateur associé
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json([
        'status_code' => 200,
        'status_message' => 'Mot de passe mis à jour avec succès.',
        'data' => $user,
    ]);
}

}

