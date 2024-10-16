<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\Apprenant\UpdateApprenantRequest;
use App\Http\Requests\Tuteur\UpdateTuteurRequest;
use App\Http\Requests\Directeur\UpdateDirecteurRequest;
use App\Http\Requests\Enseignant\UpdateEnseignantRequest;
use App\Http\Requests\Apprenant\CreateApprenantRequest;
use App\Http\Requests\Apprenant\UpdateApprenantTuteurRequest;
use App\Http\Requests\Apprenant\CreateApprenantTuteurRequest;
use App\Http\Requests\Directeur\CreateDirecteurRequest;
use App\Http\Requests\Enseignant\CreateEnseignantRequest;
use App\Http\Requests\Tuteur\CreateTuteurRequest;
use App\Http\Requests\PersonnelAdministratif\CreatePersonnelAdministratifRequest;
use App\Http\Requests\PersonnelAdministratif\UpdatePersonnelAdministratifRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\LogUserRequest;
use App\Models\Classe;
use App\Models\Role;
use App\Models\Tuteur;
use App\Models\Apprenant;
use App\Models\PersonnelAdministratif;
use App\Models\Enseignant;
use App\Models\Directeur;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','getApprenantDetailsWithNotes', 'getApprenantDetailsWithPresence','ListerPersonnelAdministratif','supprimerPersonnelAdministratif','showApprenant','ListerEnseignantNiveauEcole','showDirecteur','showEnseignant','showUserEnseignant','showUserApprenant','showUserTuteur','showUserDirecteur','showTuteur','ListerPersonnelAdministratifPoste','registerEnseignant','registerApprenant','ListeUtilisateur','showUserPersonnelAdministratif','registerPersonnelAdministratif','updateUserPersonnelAdministratif','ListerApprenant','updateApprenantTuteur','ListerTuteur','supprimerUserPersonnelAdministratif','showPersonnelAdministratif', 'ListerDirecteur', 'ListerEnseignant','registerDirecteur','supprimerEnseignant','updatePersonnelAdministratif','supprimerTuteur','supprimerApprenant','supprimerUserApprenant','registerApprenantTuteur','archiverPersonnelAdministratif','supprimerUserDirecteur','supprimerUserEnseignant','indexPersonnelAdministaratifs','supprimerUserTuteur','supprimerDirecteur','indexApprenants','indexDirecteurs','showUserPersonnelAdministratif','indexEnseignants','indexTuteurs','updateUserApprenant','updateApprenant','updateTuteur','updateUserTuteur','updateUserEnseignant','ListerApprenantParNiveau','updateEnseignant','updateUserDirecteur','updateDirecteur','updateUserEnseignant','updateUserEnseignant','archiverUser','archiverApprenant','archiverDirecteur','archiverEnseignant','archiverTuteur','refresh']]);
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
        } elseif ($user->role_nom === 'personnel_administratif' && $user->etat === 'actif') {
            return response()->json([
                'status' => 200,
                'message' => 'Salut personnel_administratif',
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
    //----------------------Tuteur-------------------------

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
            'image' => $request->image,
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

public function registerApprenantTuteurs(CreateApprenantTuteurRequest $request)
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
            'etat' => data_get($request->tuteur, 'etat', 'actif'), // Utilisez 'actif' par défaut si etat n'est pas fourni
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
            'etat' => data_get($request->tuteur, 'etat', 'actif'), // Utilisez 'actif' par défaut si etat n'est pas fourni
            'role_nom' => 'tuteur',
        ]);

        // Gestion du fichier image du tuteur
        $tuteurImageFileName = null;
        if ($request->file('tuteur.image')) {
            $tuteurFile = $request->file('tuteur.image');
            $tuteurFileName = date('YmdHi').$tuteurFile->getClientOriginalName();
            $tuteurFile->move(public_path('images'), $tuteurFileName);
            $tuteurImageFileName = $tuteurFileName;
        }

        // Création du tuteur
        $tuteur = $userTuteur->tuteur()->create([
            'profession' => $request->tuteur['profession'],
            'statut_marital' => $request->tuteur['statut_marital'],
            'numero_CNI' => $request->tuteur['numero_CNI'],
            'image' => $tuteurImageFileName, // Utiliser le nom du fichier image du tuteur s'il est défini
        ]);

        // Gestion du fichier image de l'apprenant
        $apprenantImageFileName = null;
        if ($request->file('image')) {
            $apprenantFile = $request->file('image');
            $apprenantFileName = date('YmdHi').$apprenantFile->getClientOriginalName();
            $apprenantFile->move(public_path('images'), $apprenantFileName);
            $apprenantImageFileName = $apprenantFileName;
        }

        // Création de l'apprenant avec le tuteur_id
        $apprenant = $userApprenant->apprenant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire,
            'niveau_education' => $request->niveau_education,
            'statut_marital' => $request->statut_marital,
            'image' => $apprenantImageFileName, // Utiliser le nom du fichier image de l'apprenant s'il est défini
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
            'etat' => 'actif',
            'role_nom' => 'apprenant',
        ]);

        // Gestion du fichier image de l'apprenant
        $apprenantImageFileName = null;
        if ($request->file('image')) {
            $apprenantFile = $request->file('image');
            $apprenantFileName = date('YmdHi').$apprenantFile->getClientOriginalName();
            $apprenantFile->move(public_path('images'), $apprenantFileName);
            $apprenantImageFileName = $apprenantFileName;
        }

        // Création de l'apprenant
        $apprenant = $userApprenant->apprenant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire,
            'niveau_education' => $request->niveau_education,
            'statut_marital' => $request->statut_marital,
            'image' => $apprenantImageFileName,
            'classe_id' => $request->classe_id,
        ]);

        // Création du tuteur seulement si les informations de tuteur sont fournies
        if ($request->has('tuteur')) {
            // Vérifier si le tuteur existe déjà par son email ou son numéro CNI
            $userTuteur = User::where('email', $request->tuteur['email'])
                ->orWhere('numero_CNI', $request->tuteur['numero_CNI'])
                ->first();

            // Si le tuteur n'existe pas, on le crée
            if (!$userTuteur) {
                $userTuteur = User::create([
                    'nom' => $request->tuteur['nom'],
                    'prenom' => $request->tuteur['prenom'],
                    'email' => $request->tuteur['email'],
                    'password' => Hash::make($request->tuteur['password']),
                    'telephone' => $request->tuteur['telephone'],
                    'adresse' => $request->tuteur['adresse'],
                    'genre' => $request->tuteur['genre'],
                    'etat' => data_get($request->tuteur, 'etat', 'actif'),
                    'role_nom' => 'tuteur',
                ]);

                // Gestion du fichier image du tuteur
                $tuteurImageFileName = null;
                if ($request->file('tuteur.image')) {
                    $tuteurFile = $request->file('tuteur.image');
                    $tuteurFileName = date('YmdHi').$tuteurFile->getClientOriginalName();
                    $tuteurFile->move(public_path('images'), $tuteurFileName);
                    $tuteurImageFileName = $tuteurFileName;
                }

                // Création du tuteur
                $tuteur = $userTuteur->tuteur()->create([
                    'profession' => $request->tuteur['profession'],
                    'statut_marital' => $request->tuteur['statut_marital'],
                    'numero_CNI' => $request->tuteur['numero_CNI'],
                    'image' => $tuteurImageFileName,
                ]);
            } else {
                // Si le tuteur existe, on vérifie si le téléphone correspond
                if ($userTuteur->telephone !== $request->tuteur['telephone']) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Le téléphone du tuteur ne correspond pas à celui existant.',
                    ], 400);
                }

                // Récupération des informations du tuteur existant
                $tuteur = $userTuteur->tuteur;
            }

            // Mise à jour de l'apprenant pour lui associer le tuteur
            $apprenant->update([
                'tuteur_id' => $tuteur->id
            ]);
        }

        // Récupération des informations de la classe
        $classe = Classe::find($request->classe_id);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Apprenant et Tuteur créés avec succès',
            'user_apprenant' => $userApprenant,
            'apprenant' => $apprenant,
            'user_tuteur' => isset($userTuteur) ? $userTuteur : null,
            'tuteur' => isset($tuteur) ? $tuteur : null,
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
//modifier apprenanttuteur---------------------------------

public function updateApprenantTuteur(UpdateApprenantTuteurRequest $request, $id)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        // Recherche de l'apprenant par son ID
        $apprenant = Apprenant::findOrFail($id);

        // Mise à jour de l'utilisateur Apprenant
        $userApprenant = $apprenant->user;
        $userApprenant->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $userApprenant->password, // Mise à jour si le mot de passe est fourni
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => data_get($request->tuteur, 'etat', $userApprenant->etat), // Utilise l'état actuel s'il n'est pas fourni
            'role_nom' => 'apprenant',
        ]);

        // Recherche du tuteur associé
        $tuteur = $apprenant->tuteur;
        $userTuteur = $tuteur->user;

        // Mise à jour de l'utilisateur Tuteur
        $userTuteur->update([
            'nom' => $request->tuteur['nom'],
            'prenom' => $request->tuteur['prenom'],
            'email' => $request->tuteur['email'],
            'password' => $request->tuteur['password'] ? Hash::make($request->tuteur['password']) : $userTuteur->password, // Mise à jour si le mot de passe est fourni
            'telephone' => $request->tuteur['telephone'],
            'adresse' => $request->tuteur['adresse'],
            'genre' => $request->tuteur['genre'],
            'etat' => data_get($request->tuteur, 'etat', $userTuteur->etat), // Utilise l'état actuel s'il n'est pas fourni
            'role_nom' => 'tuteur',
        ]);

        // Gestion de l'image du tuteur (si un fichier est fourni)
        $tuteurImageFileName = $tuteur->image; // Conserve l'image actuelle si aucun fichier n'est fourni
        if ($request->file('tuteur.image')) {
            $tuteurFile = $request->file('tuteur.image');
            $tuteurFileName = date('YmdHi') . $tuteurFile->getClientOriginalName();
            $tuteurFile->move(public_path('images'), $tuteurFileName);
            $tuteurImageFileName = $tuteurFileName;
        }

        // Mise à jour des informations du tuteur
        $tuteur->update([
            'profession' => $request->tuteur['profession'],
            'statut_marital' => $request->tuteur['statut_marital'],
            'numero_CNI' => $request->tuteur['numero_CNI'],
            'image' => $tuteurImageFileName, // Utilise l'image mise à jour ou conserve l'actuelle
        ]);

        // Gestion de l'image de l'apprenant (si un fichier est fourni)
        $apprenantImageFileName = $apprenant->image; // Conserve l'image actuelle si aucun fichier n'est fourni
        if ($request->file('image')) {
            $apprenantFile = $request->file('image');
            $apprenantFileName = date('YmdHi') . $apprenantFile->getClientOriginalName();
            $apprenantFile->move(public_path('images'), $apprenantFileName);
            $apprenantImageFileName = $apprenantFileName;
        }

        // Mise à jour des informations de l'apprenant
        $apprenant->update([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'numero_carte_scolaire' => $request->numero_carte_scolaire,
            'niveau_education' => $request->niveau_education,
            'statut_marital' => $request->statut_marital,
            'image' => $apprenantImageFileName, // Utilise l'image mise à jour ou conserve l'actuelle
            'classe_id' => $request->classe_id,
            'tuteur_id' => $tuteur->id, // Associe l'ID du tuteur à l'apprenant
        ]);

        // Récupération des informations de la classe
        $classe = Classe::find($request->classe_id);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Apprenant et Tuteur mis à jour avec succès',
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
            'message' => 'Une erreur est survenue lors de la mise à jour de l\'apprenant et du tuteur.',
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
            'image' => $request->image,
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
            'image' => $request->image?:$user->apprenant->image,
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
            'image' => $request->image?:$user->apprenant->image,
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

        // Gestion du fichier image de l'enseignant
        $fileName = null;
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName);
        }

        // Création de l'enseignant
        $enseignant = $user->enseignant()->create([
            'specialite' => $request->specialite,
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'niveau_ecole' => $request->niveau_ecole,
            'numero_CNI' => $request->numero_CNI,
            'image' => $fileName, // Utiliser le nom du fichier image si l'image est uploadée
            'numero_securite_social' => $request->numero_securite_social,
            'statut' => $request->statut,
            'montant_salaire' => $request->montant_salaire,
            'cotisation_salariale' => $request->cotisation_salariale,
            'net_payer' => $request->net_payer,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant créé avec succès',
            'user' => $user,
            'enseignant' => $enseignant
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction en cas d'erreur

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la création de l\'enseignant.',
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

        // Gestion de l'image si elle est téléchargée
        $fileName = $user->enseignant->image; // Conserver l'image actuelle si aucune nouvelle image n'est fournie
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName);
        }

        // Mise à jour des informations spécifiques de l'enseignant
        $user->enseignant->update([
            'specialite' => $request->specialite,
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'image' => $fileName,
            'niveau_ecole' => $request->niveau_ecole,
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
        ], 500);
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

        // Gestion de l'image si elle est téléchargée
        $fileName = $enseignant->image; // Conserver l'image actuelle si aucune nouvelle image n'est fournie
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName);
        }

        // Mise à jour des informations spécifiques de l'enseignant
        $enseignant->update([
            'specialite' => $request->specialite,
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'image' => $fileName, // Mettre à jour avec la nouvelle image ou conserver l'ancienne
            'niveau_ecole' => $request->niveau_ecole,
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
        ], 500);
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
            ], 404);
        }

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
        ], 500);
    }
}



//supprimer enseignant dans sa table
public function supprimerEnseignant(Enseignant $enseignant)
{
    try {
        // Vérifier si l'enseignant existe
        if (!$enseignant) {
            return response()->json([
                'status' => 404,
                'message' => 'Enseignant non trouvé.'
            ], 404);
        }

        // Accéder à l'utilisateur associé à cet enseignant
        $user = $enseignant->user; // Assurez-vous que la relation est définie dans le modèle Enseignant

        // Supprimer uniquement l'enseignant sans affecter les classes
        $enseignant->delete();

        // Supprimer l'utilisateur associé (cela supprime aussi l'enseignant grâce à l'héritage)
        if ($user) {
            $user->delete();
        }

        return response()->json([
            'status' => 200,
            'message' => 'L\'enseignant et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression de l\'enseignant.',
            'error' => $e->getMessage()
        ], 500);
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

        // Gestion de l'image
        $fileName = null; // Initialisation de la variable pour le nom du fichier
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName); // Déplace le fichier vers le bon dossier
        }

        // Création du directeur
        $directeur = $user->directeur()->create([
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'qualification_academique' => $request->qualification_academique,
            'date_prise_fonction' => $date_prise_fonction,
            'annee_experience' => $annee_experience,
            'image' => $fileName, // Utilise le nom du fichier
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

//register personnel_administratif
public function registerPersonnelAdministratif(CreatePersonnelAdministratifRequest $request)
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
            'etat' => $request->etat ?: 'actif',
            'role_nom' => 'personneladministratif',
        ]);

        // Gestion de l'image
        $fileName = null; // Initialisation de la variable pour le nom du fichier
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName); // Déplace le fichier vers le bon dossier
        }

        // Créer un nouvel enregistrement pour le personnel administratif
        $personneladministratif = $user->personneladministratif()->create([
            'poste' => $request->poste,
            'image' => $fileName, // Utilise le nom du fichier
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'statut' => $request->statut,
            'type_salaire' => $request->type_salaire,
            'statut_marital' => $request->statut_marital,
            'numero_securite_social' => $request->numero_securite_social,
            'numero_CNI' => $request->numero_CNI,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'personneladministratif' => $personneladministratif,
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

//modifier un personnel_administratif dans sa table
public function updatePersonnelAdministratif(UpdatePersonnelAdministratifRequest $request, $id)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Récupérer le personnel administratif et son utilisateur associé via l'ID
        $personneladministratif = PersonnelAdministratif::with('user')->find($id);

        // Vérifier si le personnel administratif existe
        if (!$personneladministratif) {
            DB::rollBack(); // Annuler la transaction
            return response()->json([
                'status' => 404,
                'message' => 'personneladministratif non trouvé.',
            ], 404);
        }

        // Mise à jour des informations de l'utilisateur associé à ce personnel administratif
        $personneladministratif->user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: $personneladministratif->user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
        ]);

        // Gestion de l'image
        $fileName = $personneladministratif->image; // Utilise l'image actuelle par défaut
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName); // Déplace le fichier vers le bon dossier
        }

        // Mise à jour des informations spécifiques du personnel administratif
        $personneladministratif->update([
            'poste' => $request->poste,
            'image' => $fileName, // Utilise le nouveau nom du fichier ou l'image actuelle
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'statut' => $request->statut,
            'type_salaire' => $request->type_salaire,
            'statut_marital' => $request->statut_marital,
            'numero_securite_social' => $request->numero_securite_social,
            'numero_CNI' => $request->numero_CNI,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant et informations utilisateur mis à jour avec succès.',
            'personnel_administratif' => $personneladministratif,
            'user' => $personneladministratif->user,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour du personnel administratif.',
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
}

//modifier personnel_administratif dans la table user
public function updateUserPersonnelAdministratif(UpdatePersonnelAdministratifRequest $request, $userId)
{
    // Démarrer une transaction
    DB::beginTransaction();

    try {
        // Récupérer l'utilisateur et vérifier s'il est associé à un personnel administratif
        $user = User::with('personneladministratif')->find($userId);

        if (!$user || !$user->personneladministratif) {
            DB::rollBack(); // Annuler la transaction en cas de problème
            return response()->json([
                'status' => 404,
                'message' => 'PersonnelAdministratif non trouvé.',
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

        // Gestion de l'image
        $fileName = $user->personneladministratif->image; // Utilise l'image actuelle par défaut
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName); // Déplace le fichier vers le bon dossier
        }

        // Mise à jour des informations spécifiques du personnel administratif
        $user->personneladministratif->update([
            'poste' => $request->poste,
            'image' => $fileName, // Utilise le nouveau nom du fichier ou l'image actuelle
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'statut' => $request->statut,
            'type_salaire' => $request->type_salaire,
            'statut_marital' => $request->statut_marital,
            'numero_securite_social' => $request->numero_securite_social,
            'numero_CNI' => $request->numero_CNI,
            'date_embauche' => $request->date_embauche,
            'date_fin_contrat' => $request->date_fin_contrat,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'PersonnelAdministratif mis à jour avec succès.',
            'user' => $user,
            'personneladministratif' => $user->personneladministratif,
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour du personnel administratif.',
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

        // Gestion de l'image
        $fileName = $user->directeur->image; // Utilise l'image actuelle par défaut
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName); // Déplace le fichier vers le bon dossier
        }

        // Mise à jour des informations spécifiques du directeur
        $user->directeur->update([
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'qualification_academique' => $request->qualification_academique,
            'image' => $fileName, // Utilise le nouveau nom du fichier ou l'image actuelle
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
        ], 200);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour du directeur.',
            'error' => $e->getMessage(),
        ], 500);
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
        ],200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du directeur',
            'error' => $e->getMessage()
        ],500);
    }
}
//supprimer dans la table user
public function supprimerUserPersonnelAdministratif(User $user)
{
    try {
        // Vérifier si l'utilisateur est bien un personnel administratif
        if (!$user->personnelAdministratif) {
            return response()->json([
                'status' => 404,
                'message' => 'Le personnel administratif associé à cet utilisateur n\'a pas été trouvé.'
            ]);
        }

        // Supprimer l'utilisateur (cela supprime également le personnel administratif via l'héritage)
        $user->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Le personnel administratif et l\'utilisateur associé ont été supprimés avec succès.'
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du personnel administratif',
            'error' => $e->getMessage()
        ], 500);
    }
}

//supprimer personnel administratif via sa table
public function supprimerPersonnelAdministratif(PersonnelAdministratif $personneladministratif)
{
    try {
        // Vérifier si le personnel administratif existe
        if (!$personneladministratif) {
            return response()->json([
                'status' => 404,
                'message' => 'Personnel administratif non trouvé.'
            ]);
        }

        // Accéder à l'utilisateur lié
        $user = $personneladministratif->user;

        // Logique pour la suppression
        if ($user) {
            // Vérifier si l'utilisateur existe avant de tenter la suppression
            if ($user->exists) {
                $user->delete(); // Supprimer l'utilisateur
            }
        }

        // Supprimer le personnel administratif
        $personneladministratif->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Le personnel administratif et l\'utilisateur associé ont été supprimés avec succès.'
        ]);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du personnel administratif.',
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

        // Gestion de l'image
        $fileName = $directeur->image; // Utilise l'image actuelle par défaut
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName); // Déplace le fichier vers le bon dossier
        }

        // Mise à jour des informations spécifiques du directeur
        $directeur->update([
            'statut_marital' => $request->statut_marital,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'image' => $fileName, // Utilise le nouveau nom du fichier ou l'image actuelle
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
        ], 200);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();

        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la mise à jour du directeur.',
            'error' => $e->getMessage(),
        ], 500);
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
    $users = User::where('role_nom', 'directeur')->orWhere('role_nom', 'enseignant')->orWhere('role_nom', 'personnel_administratif')->orWhere('role_nom', 'tuteur')->orWhere('role_nom', 'apprenant')->orWhere('role_nom', 'employé')->get();
    return response()->json([
        'status'=>200,
        'users' => $users
    ]);
}
//lister tous les apprenants dans sa table
public function ListerApprenant()
{
    $apprenants = Apprenant::with(['user', 'tuteur.user', 'classe.salle', 'classeAssociations.classe'])->get();

    if ($apprenants->isEmpty()) {
        return response()->json([
            'status' => 404,
            'message' => 'Aucun apprenant trouvé.'
        ], 404);
    }

    // Structurer les données pour chaque apprenant
    $apprenantsData = $apprenants->map(function ($apprenant) {
        $apprenantData = [
            'id' => $apprenant->id,
            'date_naissance' => $apprenant->date_naissance,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'numero_CNI' => $apprenant->numero_CNI,
            'image' => $apprenant->image,
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
            $apprenantData['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
        } else {
            $apprenantData['tuteur'] = null;
        }

        // Vérification de la classe principale
        if ($apprenant->classe) {
            $apprenantData['classe'] = [
                'id' => $apprenant->classe->id,
                'nom' => $apprenant->classe->nom,
                'niveau_classe' => $apprenant->classe->niveau_classe,
                'salle' => $apprenant->classe->salle ? [
                    'id' => $apprenant->classe->salle->id,
                    'nom' => $apprenant->classe->salle->nom,
                    'capacity' => $apprenant->classe->salle->capacity,
                    'type' => $apprenant->classe->salle->type,
                ] : null,
            ];
        } else {
            $apprenantData['classe'] = null;
        }

        // Ajouter les classes associées
        $apprenantData['classes_associées'] = $apprenant->classeAssociations->map(function ($association) {
            return [
                'classe_id' => $association->classe_id,
                'niveau_classe' => $association->classe ? $association->classe->niveau_classe : null,
            ];
        });

        return $apprenantData;
    });

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenantsData,
    ], 200);
}

public function getApprenantDetailsWithPresence($id)
{
    // Récupérer l'apprenant avec ses enregistrements de présence et absence
    $apprenant = Apprenant::with(['absencePresences.cours'])
        ->find($id);

    // Vérifier si l'apprenant existe
    if (!$apprenant) {
        return response()->json([
            'message' => "Aucun apprenant trouvé avec l'ID {$id}."
        ], 404);
    }

    // Initialiser un tableau pour stocker les détails de présence/absence
    $presenceDetails = [];

    // Boucler à travers les enregistrements de présence/absence
    foreach ($apprenant->absencePresences as $presenceAbsence) {
        $statut = strtolower($presenceAbsence->present) === 'oui' ? 'Présent' : 'Absent';

        // Vérifier si le statut est "Absent"
        if ($statut === 'Absent') {
            $presenceDetails[] = [
                'statut' => $statut,
                'date' => $presenceAbsence->date_absent,
                'raison' => $presenceAbsence->raison_absence,
                'cours' => $presenceAbsence->cours ? $presenceAbsence->cours->nom : 'N/A',
            ];
        } else {
            // Ajouter uniquement si le statut est "Présent"
            $presenceDetails[] = [
                'statut' => $statut,
                'date' => $presenceAbsence->date_present,
                'cours' => $presenceAbsence->cours ? $presenceAbsence->cours->nom : 'N/A',
            ];
        }
    }

    // Utiliser array_unique pour éviter les doublons (basé sur la date et le statut)
    $presenceDetails = array_map("unserialize", array_unique(array_map("serialize", $presenceDetails)));

    // Retourner les détails de l'apprenant avec ses informations de présence et absence
    return [
        'apprenant' => [
            'id' => $apprenant->id,
            'nom' => $apprenant->user->nom,
            'prenom' => $apprenant->user->prenom,
            'telephone' => $apprenant->user->telephone,
            'email' => $apprenant->user->email,
            'adresse' => $apprenant->user->adresse,
            'genre' => $apprenant->user->genre,
            'etat' => $apprenant->user->etat,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'date_naissance' => $apprenant->date_naissance,
            'numero_CNI' => $apprenant->numero_CNI,
            'numero_carte_scolaire' => $apprenant->numero_carte_scolaire,
            'niveau_education' => $apprenant->niveau_education,
            'statut_marital' => $apprenant->statut_marital,
        ],
        'absence' => $presenceDetails
    ];
}

//afficher les details de lapprenant par à ses notes
public function getApprenantDetailsWithNotes($id)
{
    try {
        $apprenant = Apprenant::with([
            'classe.salle',
            'evaluations.cours.enseignant.user'
        ])->find($id);

        if (!$apprenant) {
            return response()->json([
                'message' => "Aucun apprenant trouvé avec l'ID {$id}."
            ], 404);
        }

        $noteDetails = [];

        // Boucler à travers les évaluations et leurs notes
        foreach ($apprenant->evaluations as $evaluation) {
            foreach ($evaluation->notes as $note) {
                $noteDetails[] = [
                    'id' => $evaluation->id,
                    'nom_evaluation' => $evaluation->nom_evaluation,
                    'niveau_education' => $evaluation->niveau_education,
                    'date_evaluation' => $evaluation->date_evaluation,
                    'type_note' => $note->type_note,
                    'note' => $note->note,
                    'date_note' => $note->date_note,
                    'id' => $evaluation->cours->id ?? null,
                    'cours_nom' => $evaluation->cours->nom ?? null,
                    'enseignant_nom' => $evaluation->cours->enseignant->user->nom ?? null,
                    'enseignant_prenom' => $evaluation->cours->enseignant->user->prenom ?? null,
                    'enseignant_email' => $evaluation->cours->enseignant->user->email ?? null,
                    'enseignant_telephone' => $evaluation->cours->enseignant->user->telephone ?? null
                ];
            }
        }
        return response()->json([
            'apprenant' => [
                'id' => $apprenant->id,
                'nom' => $apprenant->user->nom,
                'prenom' => $apprenant->user->prenom,
                'telephone' => $apprenant->user->telephone,
                'email' => $apprenant->user->email,
                'adresse' => $apprenant->user->adresse,
                'genre' => $apprenant->user->genre,
                'etat' => $apprenant->user->etat,
                'lieu_naissance' => $apprenant->lieu_naissance,
                'date_naissance' => $apprenant->date_naissance,
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
                        'type' => $apprenant->classe->salle->type
                    ] : null
                ] : null,
            ],
            'notes' => $noteDetails
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Une erreur est survenue lors de la récupération des détails de l\'apprenant.',
            'erreur' => $e->getMessage()
        ], 500);
    }
}


 // Récupérer tous les enseignants depuis la table 'enseignants'
 public function ListerEnseignant()
{
    // Charger les enseignants avec leurs informations utilisateur, classes et salles associées
    $enseignants = Enseignant::with(['user', 'classeAssociations.classe.salle'])->get();

    if ($enseignants->isEmpty()) {
        return response()->json([
            'status' => 404,
            'message' => 'Aucun enseignant trouvé.',
        ], 404);
    }

    // Structurer les données pour chaque enseignant
    $enseignantsData = $enseignants->map(function ($enseignant) {
        $enseignantData = [
            'id' => $enseignant->id,
            'specialite' => $enseignant->specialite,
            'statut_marital' => $enseignant->statut_marital,
            'date_naissance' => $enseignant->date_naissance,
            'image' => $enseignant->image,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'niveau_ecole' => $enseignant->niveau_ecole,
            'numero_CNI' => $enseignant->numero_CNI,
            'numero_securite_social' => $enseignant->numero_securite_social,
            'statut' => $enseignant->statut,
            'date_embauche' => $enseignant->date_embauche,
            'date_fin_contrat' => $enseignant->date_fin_contrat,

            // Informations de l'utilisateur associé
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

        // Ajout des classes associées
        $enseignantData['classes_associées'] = $enseignant->classeAssociations->map(function ($association) {
            return [
                'classe_id' => optional($association->classe)->id,
                'niveau_classe' => optional($association->classe)->niveau_classe,
                'salle' => optional($association->classe->salle) ? [
                    'salle_id' => $association->classe->salle->id,
                    'nom_salle' => $association->classe->salle->nom,
                    'capacity' => $association->classe->salle->capacity, // Capacité de la salle
                    'type' => $association->classe->salle->type, // Type de la salle
                ] : null, // Si la salle n'existe pas, on met null
            ];
        });

        return $enseignantData;
    });

    return response()->json([
        'status' => 200,
        'enseignants' => $enseignantsData,
    ]);
}

//lister personnel administratif dans sa table

public function ListerPersonnelAdministratif()
{
    // Charger les personnels administratifs avec leurs informations de User
    $personnelAdministratifs = PersonnelAdministratif::with(['user'])->get();

    // Créer une nouvelle structure de données sans duplications
    $personnelAdministratifsData = $personnelAdministratifs->map(function ($personnelAdministratif) {
        return [
            'id' => $personnelAdministratif->id,
            'poste' => $personnelAdministratif->poste,
            'image' => $personnelAdministratif->image,
            'date_naissance' => $personnelAdministratif->date_naissance,
            'lieu_naissance' => $personnelAdministratif->lieu_naissance,
            'statut_emploie' => $personnelAdministratif->statut_emploie, // Correction ici
            'type_salaire' => $personnelAdministratif->type_salaire, // Correction ici
            'statut_marital' => $personnelAdministratif->statut_marital, // Correction ici
            'numero_securite_social' => $personnelAdministratif->numero_securite_social,
            'numero_CNI' => $personnelAdministratif->numero_CNI,
            'date_embauche' => $personnelAdministratif->date_embauche, // Correction ici
            'date_fin_contrat' => $personnelAdministratif->date_fin_contrat,
            'user' => $personnelAdministratif->user ? [
                'id' => $personnelAdministratif->user->id,
                'nom' => $personnelAdministratif->user->nom,
                'prenom' => $personnelAdministratif->user->prenom,
                'telephone' => $personnelAdministratif->user->telephone,
                'email' => $personnelAdministratif->user->email,
                'genre' => $personnelAdministratif->user->genre,
                'etat' => $personnelAdministratif->user->etat,
                'adresse' => $personnelAdministratif->user->adresse,
                'role_nom' => $personnelAdministratif->user->role_nom,
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'personneladministratifs' => $personnelAdministratifsData,
    ]);
}
//lister personnel administratif par rapport à la poste
public function ListerPersonnelAdministratifPoste(Request $request, $poste)
{
    // Récupérer les personnels administratifs filtrés par poste
    $personnelAdministratifs = PersonnelAdministratif::with(['user'])
                                ->where('poste', $poste)
                                ->get();

    // Créer une nouvelle structure de données sans duplications
    $personnelAdministratifsData = $personnelAdministratifs->map(function ($personnelAdministratif) {
        return [
            'id' => $personnelAdministratif->id,
            'poste' => $personnelAdministratif->poste,
            'image' => $personnelAdministratif->image,
            'date_naissance' => $personnelAdministratif->date_naissance,
            'lieu_naissance' => $personnelAdministratif->lieu_naissance,
            'statut_emploie' => $personnelAdministratif->statut_emploie,
            'type_salaire' => $personnelAdministratif->type_salaire,
            'statut_marital' => $personnelAdministratif->statut_marital,
            'numero_securite_social' => $personnelAdministratif->numero_securite_social,
            'numero_CNI' => $personnelAdministratif->numero_CNI,
            'date_embauche' => $personnelAdministratif->date_embauche,
            'date_fin_contrat' => $personnelAdministratif->date_fin_contrat,
            'user' => $personnelAdministratif->user ? [
                'id' => $personnelAdministratif->user->id,
                'nom' => $personnelAdministratif->user->nom,
                'prenom' => $personnelAdministratif->user->prenom,
                'telephone' => $personnelAdministratif->user->telephone,
                'email' => $personnelAdministratif->user->email,
                'genre' => $personnelAdministratif->user->genre,
                'etat' => $personnelAdministratif->user->etat,
                'adresse' => $personnelAdministratif->user->adresse,
                'role_nom' => $personnelAdministratif->user->role_nom,
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'personnel_administratifs' => $personnelAdministratifsData,
    ]);
}

public function ListerEnseignantNiveauEcole($niveauEcole)
{
    // Charger les enseignants filtrés par niveau d'école avec leurs informations de User, Classes et Salle
    $enseignants = Enseignant::with(['user'])
        ->where('niveau_ecole', $niveauEcole)
        ->get();

    // Créer une nouvelle structure de données
    $enseignantsData = $enseignants->map(function ($enseignant) {
        return [
            'id' => $enseignant->id,
            'specialite' => $enseignant->specialite,
            'statut_marital' => $enseignant->statut_marital,
            'date_naissance' => $enseignant->date_naissance,
            'image' => $enseignant->image,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'niveau_ecole' => $enseignant->niveau_ecole,
            'numero_CNI' => $enseignant->numero_CNI,
            'numero_securite_social' => $enseignant->numero_securite_social,
            'statut' => $enseignant->statut,
            'date_embauche' => $enseignant->date_embauche,
            'date_fin_contrat' => $enseignant->date_fin_contrat,

            // Informations de l'utilisateur associé à l'enseignant
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
    $tuteurs = Tuteur::with(['user', 'apprenants.classe.salle'])->get();

    // Créer une nouvelle structure de données sans duplications
    $tuteursData = $tuteurs->map(function ($tuteur) {
        return [
            // Attributs spécifiques au modèle Tuteur
            'id' => $tuteur->id,
            'profession' => $tuteur->profession,
            'image' => $tuteur->image,
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
                    'lieu_naissance' => $apprenant->lieu_naissance,
                    'image' => $apprenant->image,
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
            'image' => $directeur->image,
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
            'image' => $apprenant->image,
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
    $apprenant = Apprenant::with(['user', 'tuteur.user', 'classe.salle', 'classeAssociations.classe']) // Inclure classeAssociations
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
        'image' => $apprenant->image,
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

    // Vérification de la classe principale
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
        ];
    } else {
        $apprenantData['classe'] = null; // Si pas de classe, on met null
    }

    // Ajout des classes associées
    $apprenantData['classes_associées'] = $apprenant->classeAssociations->map(function ($association) {
        return [
            'classe_id' => $association->classe_id,
            'niveau_classe' => $association->classe ? $association->classe->niveau_classe: null,
        ];
    });

    return response()->json([
        'status' => 200,
        'apprenant' => $apprenantData,
    ]);
}


public function showUserApprenant($id)
{
    // Récupérer l'utilisateur avec l'ID spécifié, en incluant les relations avec les modèles 'apprenant', 'tuteur' et 'enseignant'
    $user = User::with(['apprenant.classe.salle',  'tuteur.user', 'enseignant.user'])
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
            'image' => $apprenant->image,
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
            'image' => $enseignant->image,
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
    // Récupérer l'enseignant avec l'ID spécifié
    $enseignant = Enseignant::with(['user', 'classeAssociations.classe.salle']) // Inclure les classes et les salles associées
        ->where('id', $id)
        ->first();

    if (!$enseignant) {
        return response()->json([
            'status' => 404,
            'message' => 'Enseignant non trouvé.',
        ], 404);
    }

    // Structurer les données de l'enseignant
    $enseignantData = [
        'id' => $enseignant->id,
        'specialite' => $enseignant->specialite,
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

    // Ajout des classes associées
    $enseignantData['classes_associées'] = $enseignant->classeAssociations->map(function ($association) {
        return [
            'classe_id' => optional($association->classe)->id,
            'niveau_classe' => optional($association->classe)->niveau_classe,
            'salle' => optional($association->classe->salle) ? [
                'salle_id' => $association->classe->salle->id,
                'nom_salle' => $association->classe->salle->nom,
                'capacity' => $association->classe->salle->capacity, // Capacité de la salle
                'type' => $association->classe->salle->type, // Type de la salle
            ] : null, // Si la salle n'existe pas, on met null
        ];
    });

    return response()->json([
        'status' => 200,
        'enseignant' => $enseignantData,
    ]);
}

//afficher les details du personneladministratif dans sa table
public function showPersonnelAdministratif($id)
{
    // Récupérer le personnel administratif avec l'ID spécifié en incluant les informations de User
    $personnelAdministratif = PersonnelAdministratif::with(['user'])->find($id);

    // Vérifier si le personnel administratif n'existe pas
    if (!$personnelAdministratif) {
        return response()->json([
            'status' => 404,
            'message' => 'Personnel administratif non trouvé.',
        ], 404);
    }

    // Structurer les données du personnel administratif et de l'utilisateur
    $personnelAdministratifData = [
        'id' => $personnelAdministratif->id,
        'poste' => $personnelAdministratif->poste,
        'image' => $personnelAdministratif->image,
        'date_naissance' => $personnelAdministratif->date_naissance,
        'lieu_naissance' => $personnelAdministratif->lieu_naissance,
        'statut_emploi' => $personnelAdministratif->statut_emploi,
        'type_salaire' => $personnelAdministratif->type_salaire,
        'statut_marital' => $personnelAdministratif->statut_marital,
        'numero_CNI' => $personnelAdministratif->numero_CNI,
        'numero_securite_social' => $personnelAdministratif->numero_securite_social,
        'date_embauche' => $personnelAdministratif->date_embauche,
        'date_fin_contrat' => $personnelAdministratif->date_fin_contrat,
        'user' => $personnelAdministratif->user ? [
            'id' => $personnelAdministratif->user->id,
            'nom' => $personnelAdministratif->user->nom,
            'prenom' => $personnelAdministratif->user->prenom,
            'telephone' => $personnelAdministratif->user->telephone,
            'email' => $personnelAdministratif->user->email,
            'genre' => $personnelAdministratif->user->genre,
            'etat' => $personnelAdministratif->user->etat,
            'adresse' => $personnelAdministratif->user->adresse,
            'role_nom' => $personnelAdministratif->user->role_nom,
        ] : null,
    ];

    return response()->json([
        'status' => 200,
        'personneladministratif' => $personnelAdministratifData,
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
        'image' => $directeur->image,
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
        'image' => $user->directeur->image,
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
    $user = User::with(['enseignant'])
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

    // Structurer les données de l'enseignant, de l'utilisateur, de la classe et de la salle
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

        // Informations de l'enseignant
        'enseignant' => [
            'id' => $user->enseignant->id,
            'specialite' => $user->enseignant->specialite,
            'statut_marital' => $user->enseignant->statut_marital,
            'date_naissance' => $user->enseignant->date_naissance,
            'lieu_naissance' => $user->enseignant->lieu_naissance,
            'niveau_ecole' => $user->enseignant->niveau_ecole,
            'numero_CNI' => $user->enseignant->numero_CNI,
            'image' => $user->enseignant->image,
            'numero_securite_social' => $user->enseignant->numero_securite_social,
            'statut' => $user->enseignant->statut,
            'date_embauche' => $user->enseignant->date_embauche,
            'date_fin_contrat' => $user->enseignant->date_fin_contrat,
        ]
    ];

    return response()->json([
        'status' => 200,
        'enseignant' => $enseignantData,
    ]);
}

//afficher les details du personnel administratif dans la table user
public function showUserPersonnelAdministratif($id)
{
    // Récupérer l'utilisateur avec l'ID spécifié qui a le rôle 'personnel administratif' et charger les relations nécessaires
    $user = User::with('personnelAdministratif')
                ->where('id', $id)
                ->where('role_nom', 'personneladministratif')
                ->first();

    // Vérifier si l'utilisateur n'existe pas ou n'est pas un personnel administratif
    if (!$user || !$user->personnelAdministratif) {
        return response()->json([
            'status' => 404,
            'message' => 'Personnel administratif non trouvé.',
        ], 404);
    }

    // Structurer les données du personnel administratif et de l'utilisateur
    $personnelData = [
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'telephone' => $user->telephone,
        'email' => $user->email,
        'genre' => $user->genre,
        'etat' => $user->etat,
        'adresse' => $user->adresse,
        'role_nom' => $user->role_nom,
        'personnel_administratif' => [
            'id' => $user->personnelAdministratif->id,
            'poste' => $user->personnelAdministratif->poste,
            'statut_emploi' => $user->personnelAdministratif->statut_emploi,
            'type_salaire' => $user->personnelAdministratif->type_salaire,
            'date_naissance' => $user->personnelAdministratif->date_naissance,
            'lieu_naissance' => $user->personnelAdministratif->lieu_naissance,
            'numero_CNI' => $user->personnelAdministratif->numero_CNI,
            'image' => $user->personnelAdministratif->image,
            'numero_securite_social' => $user->personnelAdministratif->numero_securite_social,
            'statut_marital' => $user->personnelAdministratif->statut_marital,
            'date_embauche' => $user->personnelAdministratif->date_embauche,
            'date_fin_contrat' => $user->personnelAdministratif->date_fin_contrat,
        ]
    ];

    return response()->json([
        'status' => 200,
        'personneladministratif' => $personnelData,
    ]);
}


//afficher les details dun tuteur dans sa table
public function showTuteur($id)
{
    // Récupérer le tuteur avec l'ID spécifié et l'apprenant associé
    $tuteur = Tuteur::with(['user', 'apprenants.classe.salle'])->where('id', $id)->first();

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
        'image' => $tuteur->image,
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
    $user = User::with(['tuteur', 'tuteur.apprenants.classe.salle'])
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
            'image' => $user->tuteur->image,
            // Inclure les apprenants associés avec les classes, salles et enseignants
            'apprenants' => $user->tuteur->apprenants->map(function ($apprenant) {
                return [
                    'id' => $apprenant->id,
                    'nom' => $apprenant->user->nom,
                    'prenom' => $apprenant->user->prenom,
                    'date_naissance' => $apprenant->date_naissance,
                    'lieu_naissance' => $apprenant->lieu_naissance,
                    'image' => $apprenant->image,
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
    // Charger les utilisateurs avec le rôle "enseignant" et leurs informations liées (enseignant, classe et salle)
    $enseignants = User::with(['enseignant'])
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
                'image' => $user->enseignant->image,
                'date_embauche' => $user->enseignant->date_embauche,
                'date_fin_contrat' => $user->enseignant->date_fin_contrat,
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'enseignants' => $enseignantsData,
    ]);
}

//listerpersonneladministratif dans la table user
public function indexPersonnelAdministaratifs()
{
    // Charger les utilisateurs avec le rôle "administratif" et leurs informations liées
    $personnelsAdministratifs = User::with(['PersonnelAdministratif']) // Assurez-vous que la relation est définie dans le modèle User
                                     ->where('role_nom', 'personneladministratif') // Filtrer par le rôle administratif
                                     ->get();

    // Créer une nouvelle structure de données
    $personnelsAdministratifsData = $personnelsAdministratifs->map(function ($user) {
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
            'personnelAdministratif' => $user->personneladministratif ? [
                'id' => $user->personneladministratif->id,
                'poste' => $user->personneladministratif->poste,
                'image' => $user->personneladministratif->image,
                'date_naissance' => $user->personneladministratif->date_naissance,
                'lieu_naissance' => $user->personneladministratif->lieu_naissance,
                'statut_emploi' => $user->personneladministratif->statut_emploi,
                'type_salaire' => $user->personneladministratif->type_salaire,
                'statut_marital' => $user->personneladministratif->statut_marital,
                'numero_CNI' => $user->personneladministratif->numero_CNI,
                'image' => $user->personneladministratif->image,
                'numero_securite_social' => $user->personneladministratif->numero_securite_social,
                'date_embauche' => $user->personneladministratif->date_embauche,
                'date_fin_contrat' => $user->personneladministratif->date_fin_contrat,
            ] : null,
        ];
    });

    return response()->json([
        'status' => 200,
        'personnelsadministratifs' => $personnelsAdministratifsData,
    ]);
}


//lister tous tuteurs dans la table user
public function indexTuteurs()
{
    // Récupérer les tuteurs à partir de la table User
    $tuteurs = User::where('role_nom', 'tuteur')->with(['tuteur.apprenants.classe.salle'])->get();

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
                'image' => $user->tuteur->image,
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
                'image' => $user->directeur->image,
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
//archiver personneladministratif via sa table
public function archiverPersonnelAdministratif(PersonnelAdministratif $personneladministratif) {
    // Récupérer l'utilisateur associé au directeur
    $user = $personneladministratif->user;

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

