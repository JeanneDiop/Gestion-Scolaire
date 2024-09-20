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
use App\Models\Apprenant;
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
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','showApprenant','showDirecteur','showEnseignant','showTuteur','registerEnseignant','registerApprenant','ListeUtilisateur','ListerApprenant','ListerTuteur', 'ListerDirecteur', 'ListerEnseignant','registerDirecteur', 'indexApprenants','indexDirecteurs','indexEnseignants','indexTuteurs','refresh']]);
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
    // Récupérer tous les apprenants avec les informations du tuteur et de la classe
    $apprenants = Apprenant::with(['tuteur.user', 'classe.salle', 'classe.enseignant.user'])->get();

    // Fusionner les informations du tuteur et de l'enseignant
    $apprenants = $apprenants->map(function ($apprenant) {
        // Vérification du tuteur
        if ($apprenant->tuteur && is_object($apprenant->tuteur)) {
            $tuteur = $apprenant->tuteur;
            $tuteurData = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
            $apprenant->tuteur = $tuteurData;
        }

        // Vérification de la classe et de l'enseignant
        if ($apprenant->classe) {
            if ($apprenant->classe->enseignant && is_object($apprenant->classe->enseignant)) {
                $enseignant = $apprenant->classe->enseignant;
                $enseignantData = $enseignant->user ? array_merge($enseignant->toArray(), $enseignant->user->toArray()) : $enseignant->toArray();
                $apprenant->classe->enseignant = $enseignantData;
            }
        }

        return $apprenant;
    });

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenants,
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

    // Fusionner les attributs du Tuteur et de User
    $apprenants = $apprenants->map(function ($user) {
        if ($user->apprenant) {
            // Fusionner le tuteur
            if ($user->apprenant->tuteur) {
                $tuteur = $user->apprenant->tuteur;
                $tuteurData = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
                $user->apprenant->tuteur = $tuteurData;
            }

            // Fusionner l'enseignant
            if ($user->apprenant->classe && $user->apprenant->classe->enseignant) {
                $enseignant = $user->apprenant->classe->enseignant;
                if (is_object($enseignant)) { // Vérifie si c'est un objet
                    $enseignantData = $enseignant->user ? array_merge($enseignant->toArray(), $enseignant->user->toArray()) : $enseignant->toArray();
                } else {
                    $enseignantData = $enseignant; // Si c'est déjà un tableau
                }
                $user->apprenant->classe->enseignant = $enseignantData;
            }
        }

        return $user;
    });

    return response()->json([
        'status' => 200,
        'apprenants' => $apprenants,
    ]);
}





//------afficher information dun apprenant dans sa table

public function showApprenant($id)
{
    // Récupérer l'apprenant avec l'ID spécifié depuis la table 'apprenant'
    $apprenant = Apprenant::with(['tuteur.user', 'classe.salle', 'classe.enseignant.user'])
        ->where('id', $id)
        ->first();

    if (!$apprenant) {
        return response()->json([
            'status' => 404,
            'message' => 'Apprenant non trouvé.',
        ], 404);
    }

    // Fusionner les informations du tuteur
    if ($apprenant->tuteur) {
        $tuteur = $apprenant->tuteur;
        $tuteurData = $tuteur->user ? array_merge($tuteur->toArray(), $tuteur->user->toArray()) : $tuteur->toArray();
        $apprenant->tuteur = $tuteurData;
    }

    // Fusionner les informations de la classe et de l'enseignant
    if ($apprenant->classe) {
        if ($apprenant->classe->enseignant) {
            $enseignant = $apprenant->classe->enseignant;
            $enseignantData = $enseignant->user ? array_merge($enseignant->toArray(), $enseignant->user->toArray()) : $enseignant->toArray();
            $apprenant->classe->enseignant = $enseignantData;
        }
    }

    return response()->json([
        'status' => 200,
        'apprenant' => $apprenant,
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

}

