<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\Apprenant\CreateApprenantRequest;
;
use App\Http\Requests\Directeur\CreateDirecteurRequest;
use App\Http\Requests\Enseignant\CreateEnseignantRequest;
use App\Http\Requests\Tuteur\CreateTuteurRequest;
use App\Http\Requests\User\EditUserRequest;

use App\Http\Requests\User\LogUserRequest;
use App\Models\Classe;
use App\Models\Role;
use App\Models\Tuteur;

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
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','showApprenant','registerEnseignant','registerApprenant','ListeUtilisateur', 'registerDirecteur', 'indexApprenants','indexEnseignants','indexTuteurs','refresh']]);
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
//afficher lapprenant authentifier
public function showApprenant()
{
    // Vérifier si l'utilisateur est authentifié
    if (!Auth::check()) {
        return response()->json([
            'status' => 401,
            'message' => 'Utilisateur non authentifié.',
        ], 401);
    }

    // Récupérer l'utilisateur authentifié
    $user = Auth::user();

    // Vérifier si l'utilisateur a un apprenant associé
    $apprenant = $user->apprenant;
    if (!$apprenant) {
        return response()->json([
            'status' => 404,
            'message' => 'Aucun apprenant associé à cet utilisateur.',
        ], 404);
    }

    // Vérifier si l'utilisateur associé à l'apprenant existe
    if (!$apprenant->user) {
        return response()->json([
            'status' => 404,
            'message' => 'Utilisateur associé à l\'apprenant non trouvé.',
        ], 404);
    }

    // Préparer la réponse
    return response()->json([
        'status' => 200,
        'apprenant' => [
            'nom' => $apprenant->user->nom,
            'prenom' => $apprenant->user->prenom,
            'email' => $apprenant->user->email,
            'genre' => $apprenant->user->genre,
            'telephone' => $apprenant->user->telephone,
            'etat' => $apprenant->user->etat,
            'adresse' => $apprenant->adresse,
            'date_naissance' => $apprenant->date_naiss,
            'tuteur_id' => $apprenant->tuteur_id,
            'classe_id' => $apprenant->classe_id,
        ]
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

///lister apprenants
public function indexApprenants() {
    $apprenants = User::where('role_nom', 'apprenant')->get();

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenants,
    ]);
}
//lister enseignants
public function indexEnseignants() {
    $enseignants = User::where('role_nom', 'enseignant')->get();

    return response()->json([
        'status' => 200,
        'enseignants' => $enseignants,
    ]);
}
//lister tuteur
public function indexTuteurs() {
    $tuteurs = User::where('role_nom', 'tuteur')->get();

    return response()->json([
        'status' => 200,
        'enseignants' => $tuteurs,
    ]);
}
}

