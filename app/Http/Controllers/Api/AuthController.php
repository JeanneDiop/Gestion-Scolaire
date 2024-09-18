<?php

namespace App\Http\Controllers\API;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Apprenant\CreateApprenantRequest;
use App\Http\Requests\Enseignant\CreateEnseignantRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\Tuteur\CreateTuteurRequest;
use App\Http\Requests\User\LogUserRequest;
use App\Models\Role;
use App\Models\Classe;
use App\Models\Tuteur;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','registerEnseignant','registerApprenant','ListeUtilisateur','refresh']]);
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
    ]);

    return response()->json([
        'status'=>200,
        'message' => 'Utilisateur créer avec succes',
        'user' => $user,
        'tuteur' => $tuteur,
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

    $enseigant = $user->enseigant()->create([
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
        'enseigant' => $enseigant
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

}

