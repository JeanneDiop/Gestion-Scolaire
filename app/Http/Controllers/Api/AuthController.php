<?php

namespace App\Http\Controllers\API;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\Tuteur\CreateTuteurRequest;
use App\Http\Requests\User\LogUserRequest;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','refresh']]);
    }

 ///public function login()
///{
    // Valider les données de la requête
    ///$credentials = request()->validate([
        ///'email' => 'required|email',
        ///'password' => 'required'
    ///]);

    // Chercher l'utilisateur par email
    ///$user = User::where('email', $credentials['email'])->first();

    // Déboguer pour voir la valeur de $user
    // dd($user);

    // Vérifier si l'utilisateur existe
    ///if ($user) {
        // Vérifier si l'utilisateur est actif
        ///if ($user->etat === 'actif') {
            // Authentifier l'utilisateur et obtenir le token
            ///if (!$token = auth()->attempt($credentials)) {
                // Retourner une réponse 401 si les informations d'identification sont incorrectes
               /// return response()->json(['error' => 'Unauthorized'], 401);
           /// }

            // Retourner les informations de l'utilisateur et le token en cas de succès
            ///return response()->json([
                ///'message' => 'Connexion réussie',
                ///'user' => $user,
                ///'token' => $token
            ///]);
        ///} else {
            // Retourner une réponse 403 si l'utilisateur est inactif
            ///return response()->json(['error' => 'Votre compte est inactif, vous ne pouvez pas vous connecter'], 403);
       /// }
    ///} else {
        // Retourner une réponse 404 si l'utilisateur n'existe pas
        ///return response()->json(['error' => 'Utilisateur non trouvé'], 404);
    ///}
///}

public function login(LogUserRequest $request)
{
    //$request->validate([
        //'email' => 'required|string|email',
        //'password' => 'required|string',
    //]);
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
    
public function registerTuteur(CreateTuteurRequest $request){
    $user = User::create([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'telephone' => $request->telephone,
        'adresse' => $request->adresse,
        'genre' => $request->genre,
        'role_nom' => 'tuteur',
    ]);

    $tuteur = $user->tuteur()->create([
        'profession'=>$request->profession,
    ]);

    return response()->json([
        'status'=>200,
        'message' => 'Utilisateur créer avec succes',
        'user' => $user,
        //'parent' => $tuteur,
    ]);
}





public function refresh()
{
    // Assurez-vous que l'utilisateur est authentifié avec le garde 'api'
    if (auth('api')->check()) {
        // Rafraîchissez le token de l'utilisateur authentifié
        $nouveauToken = auth('api')->refresh();

        // Retournez la réponse avec le nouveau token
        return response()->json([
            "status" => true,
            "message" => "Votre nouveau token",
            "token" => $nouveauToken
        ], 200);
    } else {
        // Retournez une réponse d'erreur si l'utilisateur n'est pas authentifié
        return response()->json([
            "status" => false,
            "message" => "Utilisateur non authentifié"
        ], 401);
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

public function update(EditUserRequest $request, User $user)
{
    try {
        $userRole = auth()->user()->role_id;

        if ($userRole == 1) {
            // L'administrateur  (role_id égal à 1 ) aura le droit de modifier tous les comptes
            $user->nom = $request->nom;
            $user->prenom = $request->prenom;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->telephone = $request->telephone;
            $user->adresse = $request->adresse;
            $user->etat = $request->etat;
            $user->role_id = $request->role_id;

            $user->update();

            return response()->json([
                'status_code' => 200,
                'status_message' => "Modification du compte enregistré",
                'user' => $user
            ]);
        } else {
            // Un utilisateur avec un role_id différent de 1 n'a pas le droit de modifier
            return response()->json([
                'status_code' => 403,
                'status_message' => "Vous n'avez pas la permission de modifier cet utilisateur",
            ]);
        }
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}

