<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\Apprenant\UpdateApprenantRequest;
use App\Http\Requests\Tuteur\UpdateTuteurRequest;
use App\Http\Requests\Directeur\UpdateDirecteurRequest;
use App\Http\Requests\Enseignant\UpdateEnseignantRequest;
use App\Http\Requests\Apprenant\CreateApprenantRequest;
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
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','showApprenant','showDirecteur','showEnseignant','showTuteur','registerEnseignant','registerApprenant','ListeUtilisateur','ListerApprenant','ListerTuteur', 'ListerDirecteur', 'ListerEnseignant','registerDirecteur','supprimerEnseignant','supprimerTuteur','supprimerApprenant','supprimerUserApprenant','supprimerUserDirecteur','supprimerUserEnseignant','supprimerUserTuteur','supprimerDirecteur','indexApprenants','indexDirecteurs','indexEnseignants','indexTuteurs','updateUserApprenant','updateApprenant','updateTuteur','updateUserTuteur','updateUserEnseignant','updateEnseignant','updateUserDirecteur','updateDirecteur','updateUserEnseignant','updateUserEnseignant','archiverUser','archiverApprenant','archiverDirecteur','archiverEnseignant','archiverTuteur','refresh']]);
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

public function registerTuteur(CreateTuteurRequest $request){
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

    $tuteur = $user->tuteur()->create([
        'profession'=>$request->profession,
        'statut_marital'=>$request->statut_marital,
        'numero_CNI'=>$request->numero_CNI,
    ]);

    return response()->json([
        'status'=>200,
        'message' => 'Utilisateur créer avec succes',
        'user' => $user,
        'tuteur' => $tuteur,
    ]);
}

//modifier tuteur via sa table
public function updateTuteur(UpdateTuteurRequest $request, $id)
{
    // Récupérer le tuteur et son utilisateur associé via l'ID
    $tuteur = Tuteur::with('user')->find($id);

    // Vérifier si le tuteur existe
    if (!$tuteur) {
        return response()->json([
            'status' => 404,
            'message' => 'Tuteur non trouvé.',
        ], 404);
    }

    // Mise à jour des informations de l'utilisateur
    $tuteur->user->update([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'telephone' => $request->telephone,
        'adresse' => $request->adresse,
        'genre' => $request->genre,
        'etat' => $request->etat ?: $tuteur->user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
    ]);

    // Mise à jour des informations spécifiques du tuteur
    $tuteur->update([
        'profession' => $request->profession,
        'statut_marital' => $request->statut_marital,
        'numero_CNI' => $request->numero_CNI,
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'Tuteur mis à jour avec succès.',
        'tuteur' => $tuteur,
    ]);
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
            ]);
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
        ]);
    }
}


//modifier tuteur via la table user
public function updateUserTuteur(UpdateTuteurRequest $request, $userId)
{
    // Récupérer l'utilisateur avec le tuteur associé
    $user = User::with('tuteur')->find($userId);

    // Vérifier si l'utilisateur existe
    if (!$user) {
        return response()->json([
            'status' => 404,
            'message' => 'Utilisateur non trouvé.',
        ], 404);
    }

    // Mettre à jour les informations de l'utilisateur
    $user->update([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'telephone' => $request->telephone,
        'adresse' => $request->adresse,
        'genre' => $request->genre,
        'etat' => $request->etat ?: $user->etat, // Conserver l'état actuel si aucun nouvel état n'est fourni
    ]);

    // Vérifier si l'utilisateur a un tuteur associé
    if ($user->tuteur) {
        // Mettre à jour les informations spécifiques du tuteur
        $user->tuteur->update([
            'profession' => $request->profession,
            'statut_marital' => $request->statut_marital,
            'numero_CNI' => $request->numero_CNI,
        ]);
    }

    return response()->json([
        'status' => 200,
        'message' => 'Utilisateur et tuteur mis à jour avec succès.',
        'user' => $user,
        'tuteur' => $user->tuteur,
    ]);
}


//-----------------Enseignant-------------------------------
public function registerEnseignant(CreateEnseignantRequest $request){
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

    $enseignant = $user->enseignant()->create([
        'specialite'=>$request->specialite,
        'statut_marital'=>$request->statut_marital,
        'date_naissance' =>$request->date_naissance,
        'lieu_naissance' =>$request->lieu_naissance,
        'numero_CNI' =>$request->numero_CNI,
        'numero_securite_social'=>$request->numero_securite_social,
        'statut' =>$request->statut,
        'date_embauche' =>$request->date_embauche,
        'date_fin_contrat' =>$request->date_fin_contrat,
    ]);

    return response()->json([
        'status'=>200,
        'message' => 'Utilisateur créer avec succes',
        'user' => $user,
        'enseigant' => $enseignant
    ]);
}
///---------------Apprenant-----------------------------
public function registerApprenant(CreateApprenantRequest $request)
{
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

    $apprenant = $user->apprenant()->create([
        'date_naissance' => $request->date_naissance,
        'lieu_naissance' => $request->lieu_naissance,
        'numero_CNI' => $request->numero_CNI,
        'numero_carte_scolaire' => $request->numero_carte_scolaire,
        'statut_marital' => $request->statut_marital,
        'tuteur_id' => $request->tuteur_id,
        'classe_id' => $request->classe_id,
    ]);

    // Vous devez récupérer les informations du tuteur et de la classe si nécessaire
    $tuteur = Tuteur::find($request->tuteur_id); // Assurez-vous d'importer le modèle Tuteur
    $classe = Classe::find($request->classe_id); // Assurez-vous d'importer le modèle Classe

    return response()->json([
        'status' => 200,
        'message' => 'Utilisateur créé avec succès',
        'user' => $user,
        'apprenant' => $apprenant,
        'tuteur' => $tuteur,
        'classe' => $classe
    ]);
}

//modifier apprenant via la table user
public function updateUserApprenant(UpdateApprenantRequest $request, $userId)
{
    // Récupérer l'utilisateur et vérifier s'il est associé à un apprenant
    $user = User::with('apprenant')->find($userId);

    if (!$user || !$user->apprenant) {
        return response()->json([
            'status' => 404,
            'message' => 'Apprenant non trouvé.',
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

    // Mise à jour des informations spécifiques de l'apprenant
    $user->apprenant->update([
        'date_naissance' => $request->date_naissance,
        'lieu_naissance' => $request->lieu_naissance,
        'numero_CNI' => $request->numero_CNI,
        'numero_carte_scolaire' => $request->numero_carte_scolaire,
        'statut_marital' => $request->statut_marital,
        'tuteur_id' => $request->tuteur_id,
        'classe_id' => $request->classe_id,
    ]);

    // Vous pouvez récupérer les informations du tuteur et de la classe si nécessaire
    $tuteur = Tuteur::find($request->tuteur_id); // Assurez-vous d'importer le modèle Tuteur
    $classe = Classe::find($request->classe_id); // Assurez-vous d'importer le modèle Classe

    return response()->json([
        'status' => 200,
        'message' => 'Apprenant mis à jour avec succès.',
        'user' => $user,
        'apprenant' => $user->apprenant,
        'tuteur' => $tuteur,
        'classe' => $classe
    ]);
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
            ]);
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
        ]);
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
            ]);
        }

        // Récupérer l'apprenant associé à cet utilisateur
        $apprenant = Apprenant::where('user_id', $user->id)->with(['tuteur', 'classe'])->first();

        // Vérifier si l'apprenant existe
        if (!$apprenant) {
            return response()->json([
                'status' => 404,
                'message' => 'Apprenant non trouvé pour cet utilisateur.'
            ]);
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
        ]);
    }
}



public function updateUserEnseignant(UpdateEnseignantRequest $request, $userId)
{
    // Récupérer l'utilisateur et vérifier si c'est un enseignant
    $user = User::with('enseignant')->find($userId);

    if (!$user || !$user->enseignant) {
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
        'numero_CNI' => $request->numero_CNI,
        'numero_securite_social' => $request->numero_securite_social,
        'statut' => $request->statut,
        'date_embauche' => $request->date_embauche,
        'date_fin_contrat' => $request->date_fin_contrat,
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'Enseignant mis à jour avec succès.',
        'user' => $user,
        'enseignant' => $user->enseignant,
    ]);
}
public function updateEnseignant(UpdateEnseignantRequest $request, $id)
{
    // Récupérer l'enseignant et son utilisateur associé via l'ID
    $enseignant = Enseignant::with('user')->find($id);

    // Vérifier si l'enseignant existe
    if (!$enseignant) {
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
        'numero_CNI' => $request->numero_CNI,
        'numero_securite_social' => $request->numero_securite_social,
        'statut' => $request->statut,
        'date_embauche' => $request->date_embauche,
        'date_fin_contrat' => $request->date_fin_contrat,
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'Enseignant et informations utilisateur mis à jour avec succès.',
        'enseignant' => $enseignant,
        'user' => $enseignant->user,
    ]);
}

///supprimer enseignant via sa table
public function supprimerEnseignant(Enseignant $enseignant)
{
    try {
        // Vérifier si l'enseignant existe bien
        if (!$enseignant) {
            return response()->json([
                'status' => 404,
                'message' => 'Enseignant non trouvé'
            ]);
        }

        // Mettre à jour les classes pour retirer la référence à cet enseignant
        Classe::where('enseignant_id', $enseignant->id)->update(['enseignant_id' => null]);

        // Supprimer l'enregistrement dans la table 'enseignants'
        $enseignant->delete();

        // Supprimer l'utilisateur associé dans la table 'users'
        $user = $enseignant->user;
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
            'message' => 'Une erreur est survenue lors de la suppression de l\'enseignant',
            'error' => $e->getMessage()
        ]);
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
            ]);
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
        ]);
    }
}



//------------------- directeur-------------
public function registerDirecteur(CreateDirecteurRequest $request){

    $validatedData = $request->validate([
        'annee_experience' => ['required', 'regex:/^\d+\s*(ans|année|années)?$/'],
        'date_prise_fonction' => 'required|integer|min:1900|max:' . date('Y'), // Validation pour INTEGER
    ]);

    $annee_experience = preg_replace('/\D/', '', $validatedData['annee_experience']); // Extrait les chiffres uniquement

    $date_prise_fonction = $validatedData['date_prise_fonction'];
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

    $directeur = $user->directeur()->create([
        'statut_marital'=>$request->statut_marital,
        'date_naissance' =>$request->date_naissance,
        'lieu_naissance' =>$request->lieu_naissance,
        'numero_CNI' =>$request->numero_CNI,
        'qualification_academique'=>$request->qualification_academique,
        'date_prise_fonction' => $date_prise_fonction,
        'annee_experience' => $annee_experience,
        'date_embauche' =>$request->date_embauche,
        'date_fin_contrat' =>$request->date_fin_contrat
    ]);

    return response()->json([
        'status'=>200,
        'message' => 'Utilisateur créer avec succes',
        'user' => $user,
        'directeur' => $directeur
    ]);
}
//modifier directeur via la table user
public function updateUserDirecteur(UpdateDirecteurRequest $request, $userId)
{
    // Récupérer l'utilisateur et vérifier si c'est un directeur
    $user = User::with('directeur')->find($userId);

    if (!$user || !$user->directeur) {
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

    return response()->json([
        'status' => 200,
        'message' => 'Directeur mis à jour avec succès.',
        'user' => $user,
        'directeur' => $user->directeur,
    ]);
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
    // Récupérer le directeur et son utilisateur associé via l'ID
    $directeur = Directeur::with('user')->find($id);

    // Vérifier si le directeur existe
    if (!$directeur) {
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
        $annee_experience = $directeur->annee_experience; // Conserver l'actuel si non fourni
    }

    $date_prise_fonction = $validatedData['date_prise_fonction'] ?? $directeur->date_prise_fonction;

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
        'date_prise_fonction' => $date_prise_fonction,
        'annee_experience' => $annee_experience,
        'date_embauche' => $request->date_embauche,
        'date_fin_contrat' => $request->date_fin_contrat,
    ]);

    return response()->json([
        'status' => 200,
        'message' => 'Directeur et informations utilisateur mis à jour avec succès.',
        'directeur' => $directeur,
        'user' => $directeur->user,
    ]);
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
     // Charger les enseignants avec leurs informations de User
     $enseignants = Enseignant::with('user')->get();

     // Créer une nouvelle structure de données sans duplications
     $enseignantsData = $enseignants->map(function ($enseignant) {
         return [
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
     });

     return response()->json([
         'status' => 200,
         'enseignants' => $enseignantsData,
     ]);
 }


 // Récupérer tous les directeurs depuis la table 'directeurs'
 public function ListerDirecteur()
 {
     // Charger les directeurs avec leurs informations de User
     $directeurs = Directeur::with('user')->get();

     // Créer une nouvelle structure de données sans duplications
     $directeursData = $directeurs->map(function ($directeur) {
         return [
            // Ajoute ici les attributs spécifiques à Directeur
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
     });

     return response()->json([
         'status' => 200,
         'directeurs' => $directeursData,
     ]);
 }

//----------------lister tuteur dans sa table
public function ListerTuteur()
{
    // Charger les tuteurs avec leurs informations de User
    $tuteurs = Tuteur::with('user')->get();

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
        ];
    });

    return response()->json([
        'status' => 200,
        'tuteurs' => $tuteursData,
    ]);
}

///-----lister tous les apprenants qui se trouve dans la table user
public function indexApprenants()
{
    // Charger les apprenants avec les informations associées
    $apprenants = User::with([
        'apprenant.tuteur.user', // Charger le tuteur et son User
        'apprenant.classe.salle', // Charger la salle
        'apprenant.classe.enseignant.user' // Charger l'enseignant et son User
    ])->where('role_nom', 'apprenant')->get();

    // Créer une nouvelle structure de données
    $apprenantsData = $apprenants->map(function ($user) {
        $data = [
            'id' => $user->apprenant->id,
            'date_naissance' => $user->apprenant->date_naissance,
            'lieu_naissance' => $user->apprenant->lieu_naissance,
            'numero_CNI' => $user->apprenant->numero_CNI,
            'numero_carte_scolaire' => $user->apprenant->numero_carte_scolaire,
            'statut_marital' => $user->apprenant->statut_marital,
            'user' => $user ? [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'telephone' => $user->telephone,
                'email' => $user->email,
                'genre' => $user->genre,
                'etat' => $user->etat,
                'adresse' => $user->adresse,
                'role_nom' => $user->role_nom,
                // Ajoute d'autres champs si nécessaire
            ] : null,
        ];

        // Vérification du tuteur
        if ($user->apprenant->tuteur) {
            $tuteur = $user->apprenant->tuteur;
            $data['tuteur'] = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
        } else {
            $data['tuteur'] = null; // ou un tableau vide, selon ta préférence
        }

        // Vérification de la classe et de la salle
        if ($user->apprenant->classe) {
            $data['classe'] = [
                'id' => $user->apprenant->classe->id,
                'nom' => $user->apprenant->classe->nom, // Nom de la classe (si disponible)
                'niveau_classe' => $user->apprenant->classe->niveau_classe, // Ajoute d'autres attributs de la classe si nécessaires
                'salle' => $user->apprenant->classe->salle ? [
                    'id' => $user->apprenant->classe->salle->id,
                    'nom' => $user->apprenant->classe->salle->nom,
                    'capacity' => $user->apprenant->classe->salle->capacity, // Capacité de la salle
                    'type' => $user->apprenant->classe->salle->type, // Type ou emplacement de la salle
                ] : null,
                // Vérification de l'enseignant
                'enseignant' => $user->apprenant->classe->enseignant ? [
                    'id' => $user->apprenant->classe->enseignant->id,
                    'nom' => $user->apprenant->classe->enseignant->user->nom,
                    'prenom' => $user->apprenant->classe->enseignant->user->prenom,
                    'telephone' => $user->apprenant->classe->enseignant->user->telephone,
                    'adresse' => $user->apprenant->classe->enseignant->user->adresse,
                    'specialite' => $user->apprenant->classe->enseignant->user->specialite,
                    'statut_marital' => $user->apprenant->classe->enseignant->user->statut_marital,
                    'date_naissance' => $user->apprenant->classe->enseignant->user->date_naissance,
                    'lieu_naissance' => $user->apprenant->classe->enseignant->user->lieu_naissance,
                    'numero_CNI' => $user->apprenant->classe->enseignant->user->numero_CNI,
                    'numero_securite_social' => $user->apprenant->classe->enseignant->user->numero_securite_social,
                    'statut' => $user->apprenant->classe->enseignant->user->statut, // Corrigé ici
                ] : null,
            ];
        } else {
            $data['classe'] = null;
        }

        return $data;
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

//----------info enseignant dans sa table
public function showEnseignant($id)
{
    // Récupérer l'enseignant avec l'ID spécifié depuis la table 'enseignant'
    $enseignant = Enseignant::with('user')->where('id', $id)->first();

    if (!$enseignant) {
        return response()->json([
            'status' => 404,
            'message' => 'Enseignant non trouvé.',
        ], 404);
    }

    // Structurer les données de l'enseignant et de User
    $enseignantData = [
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

//afficher les details dun tuteur dans sa table
public function showTuteur($id)
{
    // Récupérer le tuteur avec l'ID spécifié
    $tuteur = Tuteur::with('user')->where('id', $id)->first();

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
    ];

    return response()->json([
        'status' => 200,
        'tuteur' => $tuteurData,
    ]);
}

//lister enseignants qui se trouve dans la table user
public function indexEnseignants()
{
    // Récupérer tous les utilisateurs avec le rôle 'enseignant'
    $enseignants = User::where('role_nom', 'enseignant')->get();

    // Récupérer les informations correspondantes des enseignants
    $enseignantsData = $enseignants->map(function ($user) {
        // Récupérer l'enseignant associé à cet utilisateur
        $enseignant = Enseignant::where('user_id', $user->id)->first(); // Remplace 'user_id' par la clé étrangère appropriée

        // Structurer les données
        $enseignantData = $enseignant ? [
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
        ] : [];

        // Fusionner les données de User et Enseignant
        return array_merge($user->toArray(), $enseignantData);
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
    $tuteurs = User::where('role_nom', 'tuteur')->with('tuteur')->get();

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
public function updatePasswordTuteur(Request $request, $id)
{
    // Valider les données de la requête
    $validatedData = $request->validate([
        'password' => 'required|min:8',
    ], [
        'password.required' => 'Le champ mot de passe est requis.',
    ]);

    // Vérifier si l'utilisateur existe
    $tuteur = Tuteur::where('user_id', $id)->first(); // Assurez-vous que le champ user_id est correct

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

