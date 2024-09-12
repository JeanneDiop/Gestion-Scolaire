<?php

namespace App\Http\Controllers\Api;
use Exception;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\LogUserRequest;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','refresh']
    ]);
    }


    ///public function login(LogUserRequest $request)
    ///{
        //$token = auth('admin')->attempt($request->only('email', 'password'));
        //$user = auth('admin')->user();
        //$typeUser = "admin";
        //if (empty($token)) {
           /// $token = auth('api')->attempt($request->only('email', 'password'));
            ///$user = auth('api')->user();
            //$typeUser = "utilisateur";
        //}
        //if (!empty($user->etat) &&  $user->etat !== "actif") {
           // auth('api')->logout();
            //return response()->json([
                //"status" => false,
                //"message" => "Votre compte a été $user->etat par l'administrateur",
                //"motif" => $user->motif
            //], 401);
        ///if (!empty($token)) {

           /// return response()->json([
                ///"status" => true,
                ///"message" => "Bienvenue dans votre espace personnelle, vous êtes connecté en tant admin ",
                ///"user" => $user,
                ///"token" => $token,
              ///"expires_in" => "3600 seconds"
           ///], 200);
       /// }
       /// return response()->json([
           /// "status" => false,
           /// "message" => "Les informations fournis sont incorrect"
       ///], 401);
 ///}

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
public function register(CreateUserRequest $request)
{
    try {
    Role::FindOrFail($request->role_id);
      $user = new User();
      $user->nom = $request->nom;
      $user->prenom = $request->prenom;
      $user->email = $request->email;
      $user->password = Hash::make($request->password);
      $user->telephone = $request->telephone;
      $user->etat = $request->etat;
      $user->adresse = $request->adresse;
      $user->role_id = $request->role_id;
      $user->save();

      return response()->json([
        'status_code' => 200,
        'status_message' => 'user a été ajouté',
        'data' => $user
      ]);

  // }else {
  //         // Un utilisateur avec un role_id différent de 1 n'a pas le droit de modifier
  //         return response()->json([
  //             'status_code' => 403,
  //             'status_message' => "Vous n'avez pas la permission d'ajouter cet utilisateur",
  //         ]);
  //     }
    } catch (Exception $e) {
      return response()->json($e);
    }
}



public function login()
{
    $email = request(['email']);
    $user = User::where('email', $email['email'])->first();
    $credentials = request(['email', 'password']);

    if ($user->etat === 'actif') {
        // Authentifier l'utilisateur et obtenir le token
        if (!$token = auth()->attempt($credentials)) {
           return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Retourner les informations de l'utilisateur, le token, et un message de succès
        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ]);
    } else {
        return response()->json(['error' => 'Votre compte est inactif, vous ne pouvez pas vous connecter'], 403);
    }
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


}

