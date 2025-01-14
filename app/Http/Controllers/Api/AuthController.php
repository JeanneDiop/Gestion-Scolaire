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
use Illuminate\Validation\Rule;
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
       $this->middleware('auth:api', ['except' => ['login','registerTuteur','getApprenantDetailsWithNotes', 'getApprenantDetailsWithPresence', 'getEnseignantDetailsWithPresence','ListerPersonnelAdministratif','supprimerPersonnelAdministratif','showApprenant','ListerEnseignantNiveauEcole','showDirecteur','showEnseignant','showUserEnseignant','showUserApprenant','showUserTuteur','showUserDirecteur','showTuteur','ListerPersonnelAdministratifPoste','registerEnseignant','registerApprenant','ListeUtilisateur','showUserPersonnelAdministratif','registerPersonnelAdministratif','updateUserPersonnelAdministratif','ListerApprenant','updateApprenantTuteur','ListerTuteur','supprimerUserPersonnelAdministratif','showPersonnelAdministratif', 'ListerDirecteur', 'ListerEnseignant','registerDirecteur','supprimerEnseignant','updatePersonnelAdministratif','supprimerTuteur','supprimerApprenant','supprimerUserApprenant','registerApprenantTuteur','archiverPersonnelAdministratif','supprimerUserDirecteur','supprimerUserEnseignant','indexPersonnelAdministaratifs','supprimerUserTuteur','supprimerDirecteur','indexApprenants','indexDirecteurs','showUserPersonnelAdministratif','indexEnseignants','indexTuteurs','updateUserApprenant','updateApprenant','updateTuteur','updateUserTuteur','updateUserEnseignant','ListerApprenantParNiveau','updateEnseignant','updateUserDirecteur','updateDirecteur','updateUserEnseignant','updateUserEnseignant','archiverUser','archiverApprenant','archiverDirecteur','archiverEnseignant','archiverTuteur','refresh']]);
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













public function registerApprenantTuteur(CreateApprenantTuteurRequest $request)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        // Gestion de l'image du tuteur
        $tuteurImageFileName = null;
        if ($request->file('tuteur.image')) {
            $tuteurImageFileName = $this->handleImageUpload($request->file('tuteur.image'));
        }

        // Création de l'utilisateur Tuteur
        $userTuteur = User::firstOrCreate(
            ['email' => $request->tuteur['email']],
            [
                'nom' => $request->tuteur['nom'],
                'prenom' => $request->tuteur['prenom'],
                'password' => Hash::make($request->tuteur['password']),
                'telephone' => $request->tuteur['telephone'],
                'adresse' => $request->tuteur['adresse'],
                'genre' => $request->tuteur['genre'],
                'etat' => data_get($request->tuteur, 'etat', 'actif'),
                'role_nom' => 'tuteur',
            ]
        );

        $userTuteur = User::where('email', $request->tuteur['email'])->first();

        // Création ou récupération du tuteur
        $tuteur = $userTuteur->tuteur()->create([
            'profession' => $request->tuteur['profession'],
            'nationalité' => $request->tuteur['nationalité'] ?? null,
            'nombre_enfants_inscrits' => $request->tuteur['nombre_enfants_inscrits'] ?? null,
            'lien_parenté' => $request->tuteur['lien_parenté'],
            'numero_CNI' => $request->tuteur['numero_CNI'] ?? null,
            'image' => $tuteurImageFileName,
        ]);

        // Gestion de l'image de l'apprenant
        $apprenantImageFileName = null;
        if ($request->file('image')) {
            $apprenantImageFileName = $this->handleImageUpload($request->file('image'));
        }

        // Gestion de l'acte de naissance de l'apprenant
        $acteNaissanceFileName = null;
        if ($request->file('acte_naissance')) {
            $acteNaissanceFileName = $this->handleImageUpload($request->file('acte_naissance'));
        }

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

        // Création de l'apprenant avec l'association du tuteur et tous les champs supplémentaires
        $apprenant = $userApprenant->apprenant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI ?? null,
            'niveau_education' => $request->niveau_education,
            'image' => $apprenantImageFileName,
            'nationalité' =>$request->nationalité ?? null,
            'acte_naissance' => $acteNaissanceFileName,
            'classe_id' => $request->classe_id ?? null,
            'tuteur_id' => $tuteur->id,
            'numero_identification_eleve' => $request->numero_identification_eleve ?? null,
            'regime_paiement' => $request->regime_paiement ?? null,
            'reduction_bourse' => $request->reduction_bourse ?? null,
            'statut_paiement_actuel' => $request->statut_paiement_actuel ?? null,
            'references_factures' => $request->references_factures ?? null,
            'conditions_medicales' => $request->conditions_medicales ?? null,
            'contact_urgence' => $request->contact_urgence ?? null,
            'note_resultat_anterieur' => $request->note_resultat_anterieur ?? null,
            'evaluations_specifiques' => $request->evaluations_specifiques ?? null,
            'langue_parlee_maison' => $request->langue_parlee_maison ?? null,
            'activites_extraordinaires' => $request->activites_extraordinaires ?? null,
            'remarque_eleve' => $request->remarque_eleve ?? null,
            'autorisation_parentale' => $request->autorisation_parentale ?? null,
            'année_inscription' => $request->année_inscription ?? null,
            'niveau_entrée' => $request->niveau_entrée ?? null,
            'statut_inscription' => $request->statut_inscription ?? null,
            'transport_scolaire' => $request->transport_scolaire ?? null, // Ici, il est pris tel quel
            'service_transport' => ($request->transport_scolaire === 'Oui') ? $request->service_transport : null, // Condition pour service_transport
            'programme_special' => $request->programme_special ?? null,
        ]);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Apprenant et Tuteur créés avec succès',
            'user_apprenant' => $userApprenant,
            'apprenant' => $apprenant,
            'user_tuteur' => $userTuteur,
            'tuteur' => $tuteur,
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
            'nationalité' => $request->tuteur['nationalité'] ?? null,
            'nombre_enfants_inscrits' => $request->tuteur['nombre_enfants_inscrits'] ??  null,
            'lien_parenté' => $request->tuteur['lien_parenté'],
            'numero_CNI' => $request->tuteur['numero_CNI'] ?? null,
            'image' => $tuteurImageFileName,
        ]);

        // Gestion de l'image de l'apprenant (si un fichier est fourni)
        $apprenantImageFileName = $apprenant->image; // Conserve l'image actuelle si aucun fichier n'est fourni
        if ($request->file('image')) {
            $apprenantFile = $request->file('image');
            $apprenantFileName = date('YmdHi') . $apprenantFile->getClientOriginalName();
            $apprenantFile->move(public_path('images'), $apprenantFileName);
            $apprenantImageFileName = $apprenantFileName;
        }
        $acteNaissanceFileName = null;
        if ($request->file('acte_naissance')) {
            $acteNaissanceFileName = $this->handleImageUpload($request->file('acte_naissance'));
        }

        // Mise à jour des informations de l'apprenant
        $apprenant->update([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI ?? null,
            'niveau_education' => $request->niveau_education,
            'image' => $apprenantImageFileName,
            'nationalité' =>$request->nationalité ?? null,
            'acte_naissance' => $acteNaissanceFileName,
            'classe_id' => $request->classe_id ?? null,
            'tuteur_id' => $tuteur->id,
            'numero_identification_eleve' => $request->numero_identification_eleve ?? null,
            'regime_paiement' => $request->regime_paiement ?? null,
            'reduction_bourse' => $request->reduction_bourse ?? null,
            'statut_paiement_actuel' => $request->statut_paiement_actuel ?? null,
            'references_factures' => $request->references_factures ?? null,
            'conditions_medicales' => $request->conditions_medicales ?? null,
            'contact_urgence' => $request->contact_urgence ?? null,
            'note_resultat_anterieur' => $request->note_resultat_anterieur ?? null,
            'evaluations_specifiques' => $request->evaluations_specifiques ?? null,
            'langue_parlee_maison' => $request->langue_parlee_maison ?? null,
            'activites_extraordinaires' => $request->activites_extraordinaires ?? null,
            'remarque_eleve' => $request->remarque_eleve ?? null,
            'autorisation_parentale' => $request->autorisation_parentale ?? null,
            'année_inscription' => $request->année_inscription?? null,
            'niveau_entrée' => $request->niveau_entrée ?? null,
            'statut_inscription' => $request->statut_inscription ?? null,
            'transport_scolaire' => $request->transport_scolaire ?? null, // Ici, il est pris tel quel
            'service_transport' => ($request->transport_scolaire === 'Oui') ? $request->service_transport : null,
            'programme_special' => $request->programme_special ?? null,
            'classe_id' => $request->classe_id,
            'tuteur_id' => $tuteur->id,
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
                'message' => 'Tuteur non trouvé',
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
        if (!$user->tuteur) {
            return response()->json([
                'status' => 404,
                'message' => 'Tuteur non trouvé',
            ]);
        }
        $tuteur = $user->tuteur;
        if ($tuteur->apprenants()->count() > 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Le tuteur est encore assigné à des apprenants et ne peut pas être supprimé.',
            ]);
        }

        $user->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Le tuteur et l\'utilisateur associé ont été supprimés avec succès.',
        ],200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Une erreur est survenue lors de la suppression du tuteur.',
            'error' => $e->getMessage(),
        ], 500);
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

        $fileName = null;
        if ($request->file('image')) {
            $file = $request->file('image');
            $fileName = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('images'), $fileName);
        }
        // Création de l'apprenant
        $apprenant = $user->apprenant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'image' => $fileName,
            'numero_identification_eleve' => $request->numero_identification_eleve,
            'niveau_education' => $request->niveau_education,
            'regime_paiement' => $request->regime_paiement,
            'reduction_bourse' => $request->reduction_bourse,
            'statut_paiement_actuel' => $request->statut_paiement_actuel,
            'reference_factures' => $request->reference_factures,
            'nationalité' => $request->nationalité,
            'conditions_medicales' => $request->conditions_medicales,
            'contact_urgence' => $request->contact_urgence,
            'note_resultat_anterieur' => $request->note_resultat_anterieur,
            'evaluations_specifiques' => $request->evaluations_specifiques,
            'langue_parlee_maison' => $request->langue_parlee_maison,
            'activités_extraordinaires' => $request->activités_extraordinaires,
            'remarque_eleve' => $request->remarque_eleve,
            'acte_naissance' => $request->acte_naissance,
            'autorisation_parentale' => $request->autorisation_parentale,
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
            'classe' => $classe,
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
        // Gestion de l'image de l'enseignant
        $enseignantImageFileName = null;
        if ($request->file('image')) {
            $enseignantImageFileName = $this->handleImageUpload($request->file('image'));
        }

        // Gestion du CV de l'enseignant
        $cvFileName = null;
        if ($request->file('cv_diplomes')) {
            $cvFileName = $this->handleImageUpload($request->file('cv_diplomes'), 'cv_diplomes');
        }

        // Gestion de l'acte de naissance de l'enseignant (si applicable)
        $acteNaissanceFileName = null;
        if ($request->file('acte_naissance')) {
            $acteNaissanceFileName = $this->handleImageUpload($request->file('acte_naissance'));
        }

        // Création de l'utilisateur enseignant
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => data_get($request, 'etat', 'actif'),
            'role_nom' => 'enseignant',
        ]);

        // Création de l'enseignant avec les informations spécifiques
        $enseignant = $user->enseignant()->create([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'image' => $enseignantImageFileName,
            'cv_diplomes' => $cvFileName,
            'acte_naissance' => $acteNaissanceFileName, // Ajout de l'acte de naissance
            'matiere_enseignée' => $request->matiere_enseignée,
            'numero_identification_enseignant' => $request->numero_identification_enseignant,
            'niveau_enseignant' => $request->niveau_enseignant,
            'nationalité' => $request->nationalité ?? null,
            'statut_enseignant' => $request->statut_enseignant,
            'date_debut_service' => $request->date_debut_service,
            'type_contrat' => $request->type_contrat ?? null,
            'heure_travail_hebdomadaire' => $request->heure_travail_hebdomadaire,
            'salaire_base' => $request->salaire_base,
            'type_salaire' => $request->type_salaire,
            'prime_indemnités' => $request->prime_indemnités ?? null,
            'cotisation_sociales' => $request->cotisation_sociales ?? null,
            'part_employeur' => $request->part_employeur ?? null,
            'retenue_salaire' => $request->retenue_salaire ?? null,
            'mode_paiement' => $request->mode_paiement,
            'banque_domiciliation' => $request->banque_domiciliation ?? null,
            'numero_RIB' => $request->numero_RIB ?? null,
            'contrat_travail' => $request->contrat_travail ?? null,
            'ancienneté' => $request->ancienneté ?? null,
            'evaluation_performance' => $request->evaluation_performance ?? null,
            'commentaires_notes' => $request->commentaires_notes ?? null,
        ]);

        DB::commit(); // Valide la transaction

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant créé avec succès',
            'user' => $user,
            'enseignant' => $enseignant,
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

    DB::beginTransaction();

    try {

        $user = User::with('enseignant')->find($userId);

        if (!$user || !$user->enseignant) {
            DB::rollBack();
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
        $enseignantImageFileName = null;
        if ($request->file('image')) {
            $enseignantImageFileName = $this->handleImageUpload($request->file('image'));
        }

        // Gestion du CV de l'enseignant
        $cvFileName = null;
        if ($request->file('cv_diplomes')) {
            $cvFileName = $this->handleImageUpload($request->file('cv_diplomes'), 'cv_diplomes');
        }

        // Gestion de l'acte de naissance de l'enseignant (si applicable)
        $acteNaissanceFileName = null;
        if ($request->file('acte_naissance')) {
            $acteNaissanceFileName = $this->handleImageUpload($request->file('acte_naissance'));
        }

        // Mise à jour des informations spécifiques de l'enseignant
        $user->enseignant->update([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'image' => $enseignantImageFileName,
            'cv_diplomes' => $cvFileName,
            'acte_naissance' => $acteNaissanceFileName, // Ajout de l'acte de naissance
            'matiere_enseignée' => $request->matiere_enseignée,
            'numero_identification_enseignant' => $request->numero_identification_enseignant,
            'niveau_enseignant' => $request->niveau_enseignant,
            'nationalité' => $request->nationalité ?? null,
            'statut_enseignant' => $request->statut_enseignant,
            'date_debut_service' => $request->date_debut_service,
            'type_contrat' => $request->type_contrat ?? null,
            'heure_travail_hebdomadaire' => $request->heure_travail_hebdomadaire,
            'salaire_base' => $request->salaire_base,
            'type_salaire' => $request->type_salaire,
            'prime_indemnités' => $request->prime_indemnités ?? null,
            'cotisation_sociales' => $request->cotisation_sociales ?? null,
            'part_employeur' => $request->part_employeur ?? null,
            'retenue_salaire' => $request->retenue_salaire ?? null,
            'mode_paiement' => $request->mode_paiement,
            'banque_domiciliation' => $request->banque_domiciliation ?? null,
            'numero_RIB' => $request->numero_RIB ?? null,
            'contrat_travail' => $request->contrat_travail ?? null,
            'ancienneté' => $request->ancienneté ?? null,
            'evaluation_performance' => $request->evaluation_performance ?? null,
            'commentaires_notes' => $request->commentaires_notes ?? null,
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant mis à jour avec succès.',
            'enseignant' => $user,
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
    DB::beginTransaction();

    try {
        $enseignant = Enseignant::with('user')->find($id);

        if (!$enseignant) {
            DB::rollBack();
            return response()->json(['status' => 404, 'message' => 'Enseignant non trouvé.'], 404);
        }

        $enseignant->user->update([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'genre' => $request->genre,
            'etat' => $request->etat ?: $enseignant->user->etat,
        ]);

        // Gestion de l'image
        $enseignantImageFileName = null;
        if ($request->file('image')) {
            $enseignantImageFileName = $this->handleImageUpload($request->file('image'));
        }

        // Gestion du CV de l'enseignant
        $cvFileName = null;
        if ($request->file('cv_diplomes')) {
            $cvFileName = $this->handleImageUpload($request->file('cv_diplomes'), 'cv_diplomes');
        }

        // Gestion de l'acte de naissance de l'enseignant (si applicable)
        $acteNaissanceFileName = null;
        if ($request->file('acte_naissance')) {
            $acteNaissanceFileName = $this->handleImageUpload($request->file('acte_naissance'));
        }

        // Mise à jour des informations spécifiques de l'enseignant
        $enseignant->update([
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'numero_CNI' => $request->numero_CNI,
            'image' => $enseignantImageFileName,
            'cv_diplomes' => $cvFileName,
            'acte_naissance' => $acteNaissanceFileName,
            'matiere_enseignée' => $request->matiere_enseignée,
            'numero_identification_enseignant' => $request->numero_identification_enseignant,
            'niveau_enseignant' => $request->niveau_enseignant,
            'nationalité' => $request->nationalité ?? null,
            'statut_enseignant' => $request->statut_enseignant,
            'date_debut_service' => $request->date_debut_service,
            'type_contrat' => $request->type_contrat ?? null,
            'heure_travail_hebdomadaire' => $request->heure_travail_hebdomadaire,
            'salaire_base' => $request->salaire_base,
            'type_salaire' => $request->type_salaire,
            'prime_indemnités' => $request->prime_indemnités ?? null,
            'cotisation_sociales' => $request->cotisation_sociales ?? null,
            'part_employeur' => $request->part_employeur ?? null,
            'retenue_salaire' => $request->retenue_salaire ?? null,
            'mode_paiement' => $request->mode_paiement,
            'banque_domiciliation' => $request->banque_domiciliation ?? null,
            'numero_RIB' => $request->numero_RIB ?? null,
            'contrat_travail' => $request->contrat_travail ?? null,
            'ancienneté' => $request->ancienneté ?? null,
            'evaluation_performance' => $request->evaluation_performance ?? null,
            'commentaires_notes' => $request->commentaires_notes ?? null,
        ]);

        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant et informations utilisateur mis à jour avec succès.',
            'user' => $enseignant->user,
            'enseignant' => $enseignant->only([
                'id', 'matiere_enseignée', 'numero_identification_enseignant', 'date_naissance',
                'lieu_naissance', 'nationalité', 'image', 'numero_CNI', 'niveau_enseignant',
                'statut_enseignant', 'date_debut_service', 'type_contrat',
                'heure_travail_hebdomadaire', 'salaire_base', 'type_salaire',
                'prime_indemnités', 'cotisation_sociales', 'part_employeur',
                'retenue_salaire', 'mode_paiement', 'banque_domiciliation',
                'numero_RIB', 'cv_diplomes', 'contrat_travail', 'ancienneté',
                'evaluation_performance', 'commentaires_notes'
            ]),
        ]);

    } catch (\Throwable $e) {
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
            'image' => $fileName,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'type_salaire' => $request->type_salaire,
            'numero_CNI' => $request->numero_CNI,
            'date_naissance' =>$request->date_naissance,
            'nationalité'=>$request->nationalité ??null,
           'date_debut_service'=>$request->date_debut_service ?? null,
            'statut_employé'=>$request->statut_employé,
            'type_contrat' =>$request->type_contrat,
            'departement_service' =>$request->departement_service,
             'horaires_travail' =>$request->horaires_travail ?? null,
             'numero_identification_directeur' =>$request->numero_identification_directeur,
             'salaire_base' =>$request->salaire_base,
             'prime_indemnités' =>$request->prime_indemnités ?? null,
            'cotisation_sociales' =>$request->cotisation_sociales ?? null,
            'departement_service' =>$request->departement_service,
            'part_employeur' =>$request->part_employeur ?? null,
           'retenue_salaire' =>$request->retenue_salaire ?? null,
           'mode_paiement' =>$request->mode_paiement,
            'banque_domiciliation' =>$request->banque_domiciliation ?? null,
            'numero_compte_bancaire' =>$request->numero_compte_bancaire ?? null,
            'cv_diplomes' =>$request->cv_diplomes ?? null,
            'certification_formations' =>$request->certification_formations ?? null,
            'contrat_travail' =>$request->contrat_travail ?? null,
            'ancienneté' =>$request->ancienneté ?? null,
            'evaluation_performance' =>$request->evaluation_performance ?? null,
            'commentaires_notes' =>$request->commentaires_notes ?? null
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
            'poste_occupé' => $request->poste_occupé,
            'image' => $fileName,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'type_salaire' => $request->type_salaire,
            'numero_CNI' => $request->numero_CNI,
            'date_naissance' =>$request->date_naissance,
            'nationalité'=>$request->nationalité ??null,
           'date_debut_service'=>$request->date_debut_service ?? null,
            'statut_employé'=>$request->statut_employé,
            'type_contrat' =>$request->type_contrat,
            'departement_service' =>$request->departement_service,
             'horaires_travail' =>$request->horaires_travail ?? null,
             'numero_identification_employe' =>$request->numero_identification_employe,
             'superviseur' =>$request->superviseur ?? null,
             'salaire_base' =>$request->salaire_base,
             'prime_indemnités' =>$request->prime_indemnités ?? null,
            'cotisation_sociales' =>$request->cotisation_sociales ?? null,
            'departement_service' =>$request->departement_service,
            'part_employeur' =>$request->part_employeur ?? null,
           'retenue_salaire' =>$request->retenue_salaire ?? null,
           'mode_paiement' =>$request->mode_paiement,
           'certification_formations' =>$request->certification_formations ?? null,
            'banque_domiciliation' =>$request->banque_domiciliation ?? null,
            'numero_compte_bancaire' =>$request->numero_compte_bancaire ?? null,
            'cv_diplomes' =>$request->cv_diplomes ?? null,
            'contrat_travail' =>$request->contrat_travail ?? null,
            'ancienneté' =>$request->ancienneté ?? null,
            'evaluation_performance' =>$request->evaluation_performance ?? null,
            'commentaires_notes' =>$request->commentaires_notes ?? null

        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Utilisateur créé avec succès',
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
           'poste_occupé' => $request->poste_occupé,
            'image' => $fileName,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'type_salaire' => $request->type_salaire,
            'numero_CNI' => $request->numero_CNI,
            'date_naissance' =>$request->date_naissance,
            'nationalité'=>$request->nationalité ??null,
           'date_debut_service'=>$request->date_debut_service ?? null,
            'statut_employé'=>$request->statut_employé,
            'type_contrat' =>$request->type_contrat,
            'departement_service' =>$request->departement_service,
             'horaires_travail' =>$request->horaires_travail ?? null,
             'numero_identification_employe' =>$request->numero_identification_employe,
             'superviseur' =>$request->superviseur ?? null,
             'salaire_base' =>$request->salaire_base,
             'prime_indemnités' =>$request->prime_indemnités ?? null,
            'cotisation_sociales' =>$request->cotisation_sociales ?? null,
            'departement_service' =>$request->departement_service,
            'part_employeur' =>$request->part_employeur ?? null,
           'retenue_salaire' =>$request->retenue_salaire ?? null,
           'mode_paiement' =>$request->mode_paiement,
            'banque_domiciliation' =>$request->banque_domiciliation ?? null,
            'numero_compte_bancaire' =>$request->numero_compte_bancaire ?? null,
            'cv_diplomes' =>$request->cv_diplomes ?? null,
            'certification_formations' =>$request->certification_formations ?? null,
            'contrat_travail' =>$request->contrat_travail ?? null,
            'ancienneté' =>$request->ancienneté ?? null,
            'evaluation_performance' =>$request->evaluation_performance ?? null,
            'commentaires_notes' =>$request->commentaires_notes ?? null
        ]);

        // Valider la transaction
        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'Enseignant et informations utilisateur mis à jour avec succès.',
            'personnel_administratif' => $personneladministratif,

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
            'poste_occupé' => $request->poste_occupé,
            'image' => $fileName,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'type_salaire' => $request->type_salaire,
            'numero_CNI' => $request->numero_CNI,
            'date_naissance' =>$request->date_naissance,
            'nationalité'=>$request->nationalité ??null,
           'date_debut_service'=>$request->date_debut_service ?? null,
            'statut_employé'=>$request->statut_employé,
            'type_contrat' =>$request->type_contrat,
            'departement_service' =>$request->departement_service,
             'horaires_travail' =>$request->horaires_travail ?? null,
             'numero_identification_employe' =>$request->numero_identification_employe,
             'superviseur' =>$request->superviseur ?? null,
             'salaire_base' =>$request->salaire_base,
             'prime_indemnités' =>$request->prime_indemnités ?? null,
            'cotisation_sociales' =>$request->cotisation_sociales ?? null,
            'departement_service' =>$request->departement_service,
            'part_employeur' =>$request->part_employeur ?? null,
           'retenue_salaire' =>$request->retenue_salaire ?? null,
           'mode_paiement' =>$request->mode_paiement,
           'certification_formations' =>$request->certification_formations ?? null,
            'banque_domiciliation' =>$request->banque_domiciliation ?? null,
            'numero_compte_bancaire' =>$request->numero_compte_bancaire ?? null,
            'cv_diplomes' =>$request->cv_diplomes ?? null,
            'contrat_travail' =>$request->contrat_travail ?? null,
            'ancienneté' =>$request->ancienneté ?? null,
            'evaluation_performance' =>$request->evaluation_performance ?? null,
            'commentaires_notes' =>$request->commentaires_notes ?? null
        ]);

        DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'PersonnelAdministratif mis à jour avec succès.',
            'user'=>$user,
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
           'image' => $fileName,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'type_salaire' => $request->type_salaire,
            'numero_CNI' => $request->numero_CNI,
            'date_naissance' =>$request->date_naissance,
            'nationalité'=>$request->nationalité ??null,
           'date_debut_service'=>$request->date_debut_service ?? null,
            'statut_employé'=>$request->statut_employé,
            'type_contrat' =>$request->type_contrat,
            'departement_service' =>$request->departement_service,
             'horaires_travail' =>$request->horaires_travail ?? null,
             'numero_identification_directeur' =>$request->numero_identification_directeur,
             'salaire_base' =>$request->salaire_base,
             'prime_indemnités' =>$request->prime_indemnités ?? null,
            'cotisation_sociales' =>$request->cotisation_sociales ?? null,
            'departement_service' =>$request->departement_service,
            'part_employeur' =>$request->part_employeur ?? null,
           'retenue_salaire' =>$request->retenue_salaire ?? null,
           'mode_paiement' =>$request->mode_paiement,
            'banque_domiciliation' =>$request->banque_domiciliation ?? null,
            'numero_compte_bancaire' =>$request->numero_compte_bancaire ?? null,
            'cv_diplomes' =>$request->cv_diplomes ?? null,
            'certification_formations' =>$request->certification_formations ?? null,
            'contrat_travail' =>$request->contrat_travail ?? null,
            'ancienneté' =>$request->ancienneté ?? null,
            'evaluation_performance' =>$request->evaluation_performance ?? null,
            'commentaires_notes' =>$request->commentaires_notes ?? null
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
           'image' => $fileName,
            'date_naissance' => $request->date_naissance,
            'lieu_naissance' => $request->lieu_naissance,
            'type_salaire' => $request->type_salaire,
            'numero_CNI' => $request->numero_CNI,
            'date_naissance' =>$request->date_naissance,
            'nationalité'=>$request->nationalité ??null,
           'date_debut_service'=>$request->date_debut_service ?? null,
            'statut_employé'=>$request->statut_employé,
            'type_contrat' =>$request->type_contrat,
            'departement_service' =>$request->departement_service,
             'horaires_travail' =>$request->horaires_travail ?? null,
             'numero_identification_directeur' =>$request->numero_identification_directeur,
             'salaire_base' =>$request->salaire_base,
             'prime_indemnités' =>$request->prime_indemnités ?? null,
            'cotisation_sociales' =>$request->cotisation_sociales ?? null,
            'departement_service' =>$request->departement_service,
            'part_employeur' =>$request->part_employeur ?? null,
           'retenue_salaire' =>$request->retenue_salaire ?? null,
           'mode_paiement' =>$request->mode_paiement,
            'banque_domiciliation' =>$request->banque_domiciliation ?? null,
            'numero_compte_bancaire' =>$request->numero_compte_bancaire ?? null,
            'cv_diplomes' =>$request->cv_diplomes ?? null,
            'certification_formations' =>$request->certification_formations ?? null,
            'contrat_travail' =>$request->contrat_travail ?? null,
            'ancienneté' =>$request->ancienneté ?? null,
            'evaluation_performance' =>$request->evaluation_performance ?? null,
            'commentaires_notes' =>$request->commentaires_notes ?? null
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
            'numero_CNI' =>$apprenant->numero_CNI,
            'niveau_education' => $apprenant->niveau_education,
            'image' => $apprenant->image,
            'nationalité' =>$apprenant->nationalité,
            'acte_naissance' => $apprenant->acte_naissance,
            'numero_identification_eleve' => $apprenant->numero_identification_eleve,
            'regime_paiement' => $apprenant->regime_paiement ,
            'reduction_bourse' =>$apprenant->reduction_bourse,
            'statut_paiement_actuel' => $apprenant->statut_paiement_actuel,
            'references_factures' => $apprenant->references_factures,
            'conditions_medicales' =>$apprenant->conditions_medicales,
            'contact_urgence' => $apprenant->contact_urgence,
            'note_resultat_anterieur' => $apprenant->note_resultat_anterieur,
            'evaluations_specifiques' => $apprenant->evaluations_specifiques,
            'langue_parlee_maison' => $apprenant->langue_parlee_maison,
            'activites_extraordinaires' =>$apprenant->activites_extraordinaires,
            'remarque_eleve' =>$apprenant->remarque_eleve,
            'autorisation_parentale' => $apprenant->autorisation_parentale,
            'année_inscription' => $apprenant->année_inscription,
            'niveau_entrée' => $apprenant->niveau_entrée,
            'statut_inscription'  => $apprenant->statut_inscription,
            'transport_scolaire'  => $apprenant->transport_scolaire,
            'service_transport' => $apprenant->service_transport,
            'programme_special' => $apprenant->programme_special,
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
                'niveau_education' => $apprenant->classe->niveau_education,
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
    $apprenant = Apprenant::with(['presences.cours'])->find($id);

    // Vérifier si l'apprenant existe
    if (!$apprenant) {
        return response()->json([
            'message' => "Aucun apprenant trouvé avec l'ID {$id}."
        ], 404);
    }

    // Initialiser un tableau pour stocker les détails de présence/absence
    $presenceDetails = [];

    // Boucler à travers les enregistrements de présence/absence/retard seulement si la relation existe
    if ($apprenant->presences) {
        foreach ($apprenant->presences as $presence) {
            $statut = ucfirst(strtolower($presence->statut)); // Capitaliser le statut

            // Ajouter les informations selon le statut
            if ($statut === 'Absent') {
                $presenceDetails[] = [
                    'statut' => $statut,
                    'date_absent' => $presence->date_absent,
                    'raison_absence' => $presence->raison_absence,
                    'cours' => $presence->cours ? $presence->cours->nom : 'N/A',
                ];
            } elseif ($statut === 'Present') {
                $presenceDetails[] = [
                    'statut' => $statut,
                    'date_present' => $presence->date_present,
                    'cours' => $presence->cours ? $presence->cours->nom : 'N/A',
                ];
            } elseif ($statut === 'Retard') {
                $presenceDetails[] = [
                    'statut' => $statut,
                    'heure_arrivee' => $presence->heure_arrivee,
                    'duree_retard' => $presence->duree_retard,
                    'cours' => $presence->cours ? $presence->cours->nom : 'N/A',
                ];
            }
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
            'numero_identification_eleve' => $apprenant->numero_identification_eleve,
            'niveau_education' => $apprenant->niveau_education,

        ],
        'details' => $presenceDetails
    ];
}

//afficher les details de lapprenant par à ses notes
public function getApprenantDetailsWithNotes($id)
{
    try {
        // Charge l'apprenant avec ses évaluations et les notes associées
        $apprenant = Apprenant::with([
            'classe.salle',
            'evaluationApprenant.evaluation.cours.enseignant.user'  // Charger la relation correcte
        ])->find($id);

        if (!$apprenant) {
            return response()->json([
                'message' => "Aucun apprenant trouvé avec l'ID {$id}."
            ], 404);
        }

        $noteDetails = [];

        // Boucler à travers les évaluations et leurs notes
        foreach ($apprenant->evaluationApprenant as $evaluationApprenant) {  // Accéder à l'apprenant et ses évaluations
            $evaluation = $evaluationApprenant->evaluation;  // Accéder à l'évaluation associée à la note

            if ($evaluation) {
                // Pour chaque évaluation, récupérer les notes associées
                foreach ($evaluation->notes as $note) {
                    $noteDetails[] = [
                        'id' => $evaluation->id,
                        'nom_evaluation' => $evaluation->nom_evaluation,
                        'niveau_education' => $evaluation->niveau_education,
                        'date_evaluation' => $evaluation->date_evaluation,
                        'type_note' => $note->type_note,
                        'note' => $note->note,
                        'date_note' => $note->date_note,
                        'cours_id' => $evaluation->cours->id ?? null,
                        'cours_nom' => $evaluation->cours->nom ?? null,
                        'enseignant_id' => $evaluation->cours->enseignant->user->id ?? null,
                        'enseignant_nom' => $evaluation->cours->enseignant->user->nom ?? null,
                        'enseignant_prenom' => $evaluation->cours->enseignant->user->prenom ?? null,
                        'enseignant_email' => $evaluation->cours->enseignant->user->email ?? null,
                        'enseignant_telephone' => $evaluation->cours->enseignant->user->telephone ?? null
                    ];
                }
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
            'notes' => array_unique($noteDetails, SORT_REGULAR)
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
            'date_naissance' => $enseignant->date_naissance,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'numero_CNI' => $enseignant->numero_CNI,
            'image' => $enseignant->image,
            'cv_diplomes' => $enseignant->cv_diplomes,
            'matiere_enseignée' => $enseignant->matiere_enseignée,
            'numero_identification_enseignant' => $enseignant->numero_identification_enseignant,
            'niveau_enseignant' => $enseignant->niveau_enseignant,
            'nationalité' => $enseignant->nationalité,
            'statut_enseignant' => $enseignant->statut_enseignant,
            'date_debut_service' => $enseignant->date_debut_service,
            'type_contrat' => $enseignant->type_contrat,
            'heure_travail_hebdomadaire' => $enseignant->heure_travail_hebdomadaire,
            'salaire_base' => $enseignant->salaire_base,
            'type_salaire' => $enseignant->type_salaire,
            'prime_indemnités' => $enseignant->prime_indemnités,
            'cotisation_sociales' => $enseignant->cotisation_sociales,
            'part_employeur' => $enseignant->part_employeur,
            'retenue_salaire' => $enseignant->retenue_salaire,
            'mode_paiement' => $enseignant->mode_paiement,
            'banque_domiciliation' => $enseignant->banque_domiciliation,
            'numero_RIB' => $enseignant->numero_RIB,
            'contrat_travail' => $enseignant->contrat_travail,
            'ancienneté' => $enseignant->ancienneté,
            'evaluation_performance' => $enseignant->evaluation_performance,
            'commentaires_notes' => $enseignant->commentaires_notes,

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


public function getEnseignantDetailsWithPresence($id)
{
    // Récupérer l'enseignant avec ses enregistrements de présence et absence
    $enseignant = Enseignant::with(['presences.cours'])->find($id);

    // Vérifier si l'enseignant existe
    if (!$enseignant) {
        return response()->json([
            'message' => "Aucun enseignant trouvé avec l'ID {$id}."
        ], 404);
    }

    // Initialiser un tableau pour stocker les détails de présence/absence
    $presenceDetails = [];

    // Boucler à travers les enregistrements de présence/absence/retard seulement si la relation existe
    if ($enseignant->presences) {
        foreach ($enseignant->presences as $presence) {
            $statut = ucfirst(strtolower($presence->statut)); // Capitaliser le statut

            // Ajouter les informations selon le statut
            if ($statut === 'Absent') {
                $presenceDetails[] = [
                    'statut' => $statut,
                    'date_absent' => $presence->date_absent,
                    'raison_absence' => $presence->raison_absence,
                    'cours' => $presence->cours ? $presence->cours->nom : 'N/A',
                ];
            } elseif ($statut === 'Present') {
                $presenceDetails[] = [
                    'statut' => $statut,
                    'date_present' => $presence->date_present,
                    'cours' => $presence->cours ? $presence->cours->nom : 'N/A',
                ];
            } elseif ($statut === 'Retard') {
                $presenceDetails[] = [
                    'statut' => $statut,
                    'heure_arrivee' => $presence->heure_arrivee,
                    'duree_retard' => $presence->duree_retard,
                    'cours' => $presence->cours ? $presence->cours->nom : 'N/A',
                ];
            }
        }
    }

    // Utiliser array_unique pour éviter les doublons (basé sur la date et le statut)
    $presenceDetails = array_map("unserialize", array_unique(array_map("serialize", $presenceDetails)));

    // Retourner les détails de l'enseignant avec ses informations de présence et absence
    return [
        'enseignant' => [
            'id' => $enseignant->id ?? null,
            'nom' => $enseignant->user->nom  ?? null,
            'prenom' => $enseignant->user->prenom  ?? null,
            'telephone' => $enseignant->user->telephone  ?? null,
            'email' => $enseignant->user->email  ?? null,
            'adresse' => $enseignant->user->adresse  ?? null,
            'genre' => $enseignant->user->genre  ?? null,
            'etat' => $enseignant->user->etat  ?? null,
            'lieu_naissance' => $enseignant->lieu_naissance  ?? null,
            'date_naissance' => $enseignant->date_naissance  ?? null,
            'numero_CNI' => $enseignant->numero_CNI  ?? null,
            'matiere_enseignée' => $enseignant->matiere_enseignée  ?? null,
            'numero_identification_enseignant' => $enseignant->numero_identification_enseignant  ?? null,
            'niveau_enseignant' => $enseignant->niveau_enseignant  ?? null,
        ],
        'details' => $presenceDetails
    ];
}
//lister personnel administratif dans sa table

public function ListerPersonnelAdministratif()
{
    // Charger les personnels administratifs avec leurs informations de User
    $personnelAdministratifs = PersonnelAdministratif::with(['user'])->get();

    // Créer une nouvelle structure de données sans duplications
    $personnelAdministratifsData = $personnelAdministratifs->map(function ($personnelAdministratif) {
        return [
            'id' =>$personnelAdministratif->id,
            'poste_occupé' => $personnelAdministratif->poste_occupé,
            'image' => $personnelAdministratif->image,
            'date_naissance' => $personnelAdministratif->date_naissance,
            'lieu_naissance' => $personnelAdministratif->lieu_naissance,
            'type_salaire' => $personnelAdministratif->type_salaire,
            'numero_CNI' => $personnelAdministratif->numero_CNI,
            'date_naissance' =>$personnelAdministratif->date_naissance,
            'nationalité'=>$personnelAdministratif->nationalité,
           'date_debut_service'=>$personnelAdministratif->date_debut_service,
            'statut_employé'=>$personnelAdministratif->statut_employé,
            'type_contrat' =>$personnelAdministratif->type_contrat,
            'departement_service' =>$personnelAdministratif->departement_service,
             'horaires_travail' =>$personnelAdministratif->horaires_travail,
             'numero_identification_employe' =>$personnelAdministratif->numero_identification_employe,
             'superviseur' =>$personnelAdministratif->superviseur,
             'salaire_base' =>$personnelAdministratif->salaire_base,
             'certification_formations' =>$personnelAdministratif->certification_formations,
             'prime_indemnités' =>$personnelAdministratif->prime_indemnités,
            'cotisation_sociales' =>$personnelAdministratif->cotisation_sociales ,
            'departement_service' =>$personnelAdministratif->departement_service,
            'part_employeur' =>$personnelAdministratif->part_employeur,
           'retenue_salaire' =>$personnelAdministratif->retenue_salaire,
           'mode_paiement' =>$personnelAdministratif->mode_paiement,
            'banque_domiciliation' =>$personnelAdministratif->banque_domiciliation,
            'numero_compte_bancaire' =>$personnelAdministratif->numero_compte_bancaire,
            'cv_diplomes' =>$personnelAdministratif->cv_diplomes,
            'contrat_travail' =>$personnelAdministratif->contrat_travail,
            'ancienneté' =>$personnelAdministratif->ancienneté,
            'evaluation_performance' =>$personnelAdministratif->evaluation_performance,
            'commentaires_notes' =>$personnelAdministratif->commentaires_notes,
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
                                ->where('poste_occupé', $poste)
                                ->get();

    // Créer une nouvelle structure de données sans duplications
    $personnelAdministratifsData = $personnelAdministratifs->map(function ($personnelAdministratif) {
        return [
           'id' =>$personnelAdministratif->id,
            'poste_occupé' => $personnelAdministratif->poste_occupé,
            'image' => $personnelAdministratif->image,
            'date_naissance' => $personnelAdministratif->date_naissance,
            'lieu_naissance' => $personnelAdministratif->lieu_naissance,
            'type_salaire' => $personnelAdministratif->type_salaire,
            'numero_CNI' => $personnelAdministratif->numero_CNI,
            'date_naissance' =>$personnelAdministratif->date_naissance,
            'nationalité'=>$personnelAdministratif->nationalité,
           'date_debut_service'=>$personnelAdministratif->date_debut_service,
            'statut_employé'=>$personnelAdministratif->statut_employé,
            'type_contrat' =>$personnelAdministratif->type_contrat,
            'departement_service' =>$personnelAdministratif->departement_service,
             'horaires_travail' =>$personnelAdministratif->horaires_travail,
             'numero_identification_employe' =>$personnelAdministratif->numero_identification_employe,
             'superviseur' =>$personnelAdministratif->superviseur,
             'salaire_base' =>$personnelAdministratif->salaire_base,
             'prime_indemnités' =>$personnelAdministratif->prime_indemnités,
            'cotisation_sociales' =>$personnelAdministratif->cotisation_sociales ,
            'departement_service' =>$personnelAdministratif->departement_service,
            'part_employeur' =>$personnelAdministratif->part_employeur,
           'retenue_salaire' =>$personnelAdministratif->retenue_salaire,
           'mode_paiement' =>$personnelAdministratif->mode_paiement,
            'banque_domiciliation' =>$personnelAdministratif->banque_domiciliation,
            'numero_compte_bancaire' =>$personnelAdministratif->numero_compte_bancaire,
            'cv_diplomes' =>$personnelAdministratif->cv_diplomes,
            'contrat_travail' =>$personnelAdministratif->contrat_travail,
            'ancienneté' =>$personnelAdministratif->ancienneté,
            'evaluation_performance' =>$personnelAdministratif->evaluation_performance,
            'commentaires_notes' =>$personnelAdministratif->commentaires_notes,
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
            'date_naissance' => $enseignant->date_naissance,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'numero_CNI' => $enseignant->numero_CNI,
            'image' => $enseignant->image,
            'cv_diplomes' => $enseignant->cv_diplomes,
            'matiere_enseignée' => $enseignant->matiere_enseignée,
            'numero_identification_enseignant' => $enseignant->numero_identification_enseignant,
            'niveau_enseignant' => $enseignant->niveau_enseignant,
            'nationalité' => $enseignant->nationalité,
            'statut_enseignant' => $enseignant->statut_enseignant,
            'date_debut_service' => $enseignant->date_debut_service,
            'type_contrat' => $enseignant->type_contrat,
            'heure_travail_hebdomadaire' => $enseignant->heure_travail_hebdomadaire,
            'salaire_base' => $enseignant->salaire_base,
            'type_salaire' => $enseignant->type_salaire,
            'prime_indemnités' => $enseignant->prime_indemnités,
            'cotisation_sociales' => $enseignant->cotisation_sociales,
            'part_employeur' => $enseignant->part_employeur,
            'retenue_salaire' => $enseignant->retenue_salaire,
            'mode_paiement' => $enseignant->mode_paiement,
            'banque_domiciliation' => $enseignant->banque_domiciliation,
            'numero_RIB' => $enseignant->numero_RIB,
            'contrat_travail' => $enseignant->contrat_travail,
            'ancienneté' => $enseignant->ancienneté,
            'evaluation_performance' => $enseignant->evaluation_performance,
            'commentaires_notes' => $enseignant->commentaires_notes,

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
            'nationalité' => $tuteur->nationalité,
            'nombre_enfants_inscrits' => $tuteur->nombre_enfants_inscrits,
            'lien_parenté' => $tuteur->lien_parenté,
            'numero_CNI' => $tuteur->numero_CNI,
            'image' => $tuteur->image,
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
                        'niveau_education' => $apprenant->classe->niveau_education,
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
            'image' => $directeur->image,
            'date_naissance' => $directeur->date_naissance,
            'lieu_naissance' =>$directeur->lieu_naissance,
            'type_salaire' => $directeur->type_salaire,
            'numero_CNI' => $directeur->numero_CNI,
            'date_naissance' =>$directeur->date_naissance,
            'nationalité'=>$directeur->nationalité ??null,
           'date_debut_service'=>$directeur->date_debut_service ?? null,
            'statut_employé'=>$directeur->statut_employé,
            'type_contrat' =>$directeur->type_contrat,
            'departement_service' =>$directeur->departement_service,
             'horaires_travail' =>$directeur->horaires_travail,
             'numero_identification_directeur' =>$directeur->numero_identification_directeur,
             'salaire_base' =>$directeur->salaire_base,
             'prime_indemnités' =>$directeur->prime_indemnités,
            'cotisation_sociales' =>$directeur->cotisation_sociales,
            'departement_service' =>$directeur->departement_service,
            'part_employeur' =>$directeur->part_employeur,
           'retenue_salaire' =>$directeur->retenue_salaire ,
           'mode_paiement' =>$directeur->mode_paiement,
            'banque_domiciliation' =>$directeur->banque_domiciliation,
            'numero_compte_bancaire' =>$directeur->numero_compte_bancaire ,
            'cv_diplomes' =>$directeur->cv_diplomes,
            'certification_formations' =>$directeur->certification_formations,
            'contrat_travail' =>$directeur->contrat_travail,
            'ancienneté' =>$directeur->ancienneté,
            'evaluation_performance' =>$directeur->evaluation_performance,
            'commentaires_notes' =>$directeur->commentaires_notes,
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
            'numero_CNI' =>$apprenant->numero_CNI,
            'niveau_education' => $apprenant->niveau_education,
            'image' => $apprenant->image,
            'nationalité' =>$apprenant->nationalité,
            'acte_naissance' => $apprenant->acte_naissance,
            'numero_identification_eleve' => $apprenant->numero_identification_eleve,
            'regime_paiement' => $apprenant->regime_paiement ,
            'reduction_bourse' =>$apprenant->reduction_bourse,
            'statut_paiement_actuel' => $apprenant->statut_paiement_actuel,
            'references_factures' => $apprenant->references_factures,
            'conditions_medicales' =>$apprenant->conditions_medicales,
            'contact_urgence' => $apprenant->contact_urgence,
            'note_resultat_anterieur' => $apprenant->note_resultat_anterieur,
            'evaluations_specifiques' => $apprenant->evaluations_specifiques,
            'langue_parlee_maison' => $apprenant->langue_parlee_maison,
            'activites_extraordinaires' =>$apprenant->activites_extraordinaires,
            'remarque_eleve' =>$apprenant->remarque_eleve,
            'autorisation_parentale' => $apprenant->autorisation_parentale,
            'année_inscription' => $apprenant->année_inscription,
            'niveau_entrée' => $apprenant->niveau_entrée,
            'statut_inscription'  => $apprenant->statut_inscription,
            'transport_scolaire'  => $apprenant->transport_scolaire,
            'service_transport' => $apprenant->service_transport,
            'programme_special' => $apprenant->programme_special,
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
                'niveau_classe' => $apprenant->classe->niveau_classe,
                'niveau_education' => $apprenant->classe->niveau_education, // Niveau de la classe
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
            'numero_CNI' =>$apprenant->numero_CNI,
            'niveau_education' => $apprenant->niveau_education,
            'image' => $apprenant->image,
            'nationalité' =>$apprenant->nationalité,
            'acte_naissance' => $apprenant->acte_naissance,
            'numero_identification_eleve' => $apprenant->numero_identification_eleve,
            'regime_paiement' => $apprenant->regime_paiement ,
            'reduction_bourse' =>$apprenant->reduction_bourse,
            'statut_paiement_actuel' => $apprenant->statut_paiement_actuel,
            'references_factures' => $apprenant->references_factures,
            'conditions_medicales' =>$apprenant->conditions_medicales,
            'contact_urgence' => $apprenant->contact_urgence,
            'note_resultat_anterieur' => $apprenant->note_resultat_anterieur,
            'evaluations_specifiques' => $apprenant->evaluations_specifiques,
            'langue_parlee_maison' => $apprenant->langue_parlee_maison,
            'activites_extraordinaires' =>$apprenant->activites_extraordinaires,
            'remarque_eleve' =>$apprenant->remarque_eleve,
            'autorisation_parentale' => $apprenant->autorisation_parentale,
            'année_inscription' => $apprenant->année_inscription,
            'niveau_entrée' => $apprenant->niveau_entrée,
            'statut_inscription'  => $apprenant->statut_inscription,
            'transport_scolaire'  => $apprenant->transport_scolaire,
            'service_transport' => $apprenant->service_transport,
            'programme_special' => $apprenant->programme_special,
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
            'niveau_classe' => $apprenant->classe->niveau_classe,
            'niveau_education' => $apprenant->classe->niveau_education,
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
            'id' => $apprenant->id,
            'date_naissance' => $apprenant->date_naissance,
            'lieu_naissance' => $apprenant->lieu_naissance,
            'numero_CNI' =>$apprenant->numero_CNI,
            'niveau_education' => $apprenant->niveau_education,
            'image' => $apprenant->image,
            'nationalité' =>$apprenant->nationalité,
            'acte_naissance' => $apprenant->acte_naissance,
            'numero_identification_eleve' => $apprenant->numero_identification_eleve,
            'regime_paiement' => $apprenant->regime_paiement ,
            'reduction_bourse' =>$apprenant->reduction_bourse,
            'statut_paiement_actuel' => $apprenant->statut_paiement_actuel,
            'references_factures' => $apprenant->references_factures,
            'conditions_medicales' =>$apprenant->conditions_medicales,
            'contact_urgence' => $apprenant->contact_urgence,
            'note_resultat_anterieur' => $apprenant->note_resultat_anterieur,
            'evaluations_specifiques' => $apprenant->evaluations_specifiques,
            'langue_parlee_maison' => $apprenant->langue_parlee_maison,
            'activites_extraordinaires' =>$apprenant->activites_extraordinaires,
            'remarque_eleve' =>$apprenant->remarque_eleve,
            'autorisation_parentale' => $apprenant->autorisation_parentale,
            'année_inscription' => $apprenant->année_inscription,
            'niveau_entrée' => $apprenant->niveau_entrée,
            'statut_inscription'  => $apprenant->statut_inscription,
            'transport_scolaire'  => $apprenant->transport_scolaire,
            'service_transport' => $apprenant->service_transport,
            'programme_special' => $apprenant->programme_special,
            'classe' => $apprenant->classe ? [
                'id' => $apprenant->classe->id,
                'nom' => $apprenant->classe->nom,
                'niveau_classe' => $apprenant->classe->niveau_classe,
                'niveau_education' => $apprenant->classe->niveau_education,
                'niveau_education' => $apprenant->classe->niveau_education,
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
            'date_naissance' => $enseignant->date_naissance,
            'lieu_naissance' => $enseignant->lieu_naissance,
            'numero_CNI' => $enseignant->numero_CNI,
            'image' => $enseignant->image,
            'cv_diplomes' => $enseignant->cv_diplomes,
            'acte_naissance' =>$enseignant->acte_naissance, // Ajout de l'acte de naissance
            'matiere_enseignée' => $enseignant->matiere_enseignée,
            'numero_identification_enseignant' => $enseignant->numero_identification_enseignant,
            'niveau_enseignant' => $enseignant->niveau_enseignant,
            'nationalité' => $enseignant->nationalité,
            'statut_enseignant' => $enseignant->statut_enseignant,
            'date_debut_service' => $enseignant->date_debut_service,
            'type_contrat' => $enseignant->type_contrat,
            'heure_travail_hebdomadaire' => $enseignant->heure_travail_hebdomadaire,
            'salaire_base' => $enseignant->salaire_base,
            'type_salaire' => $enseignant->type_salaire,
            'prime_indemnités' => $enseignant->prime_indemnités,
            'cotisation_sociales' => $enseignant->cotisation_sociales,
            'part_employeur' => $enseignant->part_employeur,
            'retenue_salaire' => $enseignant->retenue_salaire,
            'mode_paiement' => $enseignant->mode_paiement,
            'banque_domiciliation' => $enseignant->banque_domiciliation,
            'numero_RIB' => $enseignant->numero_RIB,
            'contrat_travail' => $enseignant->contrat_travail,
            'ancienneté' => $enseignant->ancienneté,
            'evaluation_performance' => $enseignant->evaluation_performance,
            'commentaires_notes' => $enseignant->commentaires_notes,
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
        'matiere_enseignée' => $enseignant->matiere_enseignée,
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
            'niveau_education' => optional($association->classe)->niveau_education,
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
        'id' =>$personnelAdministratif->id,
           'poste_occupé' => $personnelAdministratif->poste_occupé,
            'image' => $personnelAdministratif->image,
            'date_naissance' => $personnelAdministratif->date_naissance,
            'lieu_naissance' => $personnelAdministratif->lieu_naissance,
            'type_salaire' => $personnelAdministratif->type_salaire,
            'numero_CNI' => $personnelAdministratif->numero_CNI,
            'nationalité'=>$personnelAdministratif->nationalité,
           'date_debut_service'=>$personnelAdministratif->date_debut_service,
            'statut_employé'=>$personnelAdministratif->statut_employé,
            'type_contrat' =>$personnelAdministratif->type_contrat,
            'departement_service' =>$personnelAdministratif->departement_service,
            'certification_formations' =>$personnelAdministratif->certification_formations,
             'horaires_travail' =>$personnelAdministratif->horaires_travail,
             'numero_identification_employe' =>$personnelAdministratif->numero_identification_employe,
             'superviseur' =>$personnelAdministratif->superviseur,
             'salaire_base' =>$personnelAdministratif->salaire_base,
             'prime_indemnités' =>$personnelAdministratif->prime_indemnités,
            'cotisation_sociales' =>$personnelAdministratif->cotisation_sociales,
            'departement_service' =>$personnelAdministratif->departement_service,
            'part_employeur' =>$personnelAdministratif->part_employeur,
           'retenue_salaire' =>$personnelAdministratif->retenue_salaire,
           'mode_paiement' =>$personnelAdministratif->mode_paiement,
            'banque_domiciliation' =>$personnelAdministratif->banque_domiciliation,
            'numero_compte_bancaire' =>$personnelAdministratif->numero_compte_bancaire,
            'cv_diplomes' =>$personnelAdministratif->cv_diplomes,
            'contrat_travail' =>$personnelAdministratif->contrat_travail,
            'ancienneté' =>$personnelAdministratif->ancienneté,
            'evaluation_performance' =>$personnelAdministratif->evaluation_performance,
            'commentaires_notes' =>$personnelAdministratif->commentaires_notes,
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
        'image' => $directeur->image,
        'date_naissance' => $directeur->date_naissance,
        'lieu_naissance' =>$directeur->lieu_naissance,
        'type_salaire' => $directeur->type_salaire,
        'numero_CNI' => $directeur->numero_CNI,
        'date_naissance' =>$directeur->date_naissance,
        'nationalité'=>$directeur->nationalité ??null,
       'date_debut_service'=>$directeur->date_debut_service ?? null,
        'statut_employé'=>$directeur->statut_employé,
        'type_contrat' =>$directeur->type_contrat,
        'departement_service' =>$directeur->departement_service,
         'horaires_travail' =>$directeur->horaires_travail,
         'numero_identification_directeur' =>$directeur->numero_identification_directeur,
         'salaire_base' =>$directeur->salaire_base,
         'prime_indemnités' =>$directeur->prime_indemnités,
        'cotisation_sociales' =>$directeur->cotisation_sociales,
        'departement_service' =>$directeur->departement_service,
        'part_employeur' =>$directeur->part_employeur,
       'retenue_salaire' =>$directeur->retenue_salaire ,
       'mode_paiement' =>$directeur->mode_paiement,
        'banque_domiciliation' =>$directeur->banque_domiciliation,
        'numero_compte_bancaire' =>$directeur->numero_compte_bancaire ,
        'cv_diplomes' =>$directeur->cv_diplomes,
        'certification_formations' =>$directeur->certification_formations,
        'contrat_travail' =>$directeur->contrat_travail,
        'ancienneté' =>$directeur->ancienneté,
        'evaluation_performance' =>$directeur->evaluation_performance,
        'commentaires_notes' =>$directeur->commentaires_notes,
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
        'image' => $user->directeur->image,
            'date_naissance' => $user->directeur->date_naissance,
            'lieu_naissance' =>$user->directeur->lieu_naissance,
            'type_salaire' => $user->directeur->type_salaire,
            'numero_CNI' => $user->directeur->numero_CNI,
            'date_naissance' =>$user->directeur->date_naissance,
            'nationalité'=>$user->directeur->nationalité ??null,
           'date_debut_service'=>$user->directeur->date_debut_service ?? null,
            'statut_employé'=>$user->directeur->statut_employé,
            'type_contrat' =>$user->directeur->type_contrat,
            'departement_service' =>$user->directeur->departement_service,
             'horaires_travail' =>$user->directeur->horaires_travail,
             'numero_identification_directeur' =>$user->directeur->numero_identification_directeur,
             'salaire_base' =>$user->directeur->salaire_base,
             'prime_indemnités' =>$user->directeur->prime_indemnités,
            'cotisation_sociales' =>$user->directeur->cotisation_sociales,
            'departement_service' =>$user->directeur->departement_service,
            'part_employeur' =>$user->directeur->part_employeur,
           'retenue_salaire' =>$user->directeur->retenue_salaire ,
           'mode_paiement' =>$user->directeur->mode_paiement,
            'banque_domiciliation' =>$user->directeur->banque_domiciliation,
            'numero_compte_bancaire' =>$user->directeur->numero_compte_bancaire ,
            'cv_diplomes' =>$user->directeur->cv_diplomes,
            'certification_formations' =>$user->directeur->certification_formations,
            'contrat_travail' =>$user->directeur->contrat_travail,
            'ancienneté' =>$user->directeur->ancienneté,
            'evaluation_performance' =>$user->directeur->evaluation_performance,
            'commentaires_notes' =>$user->directeur->commentaires_notes,
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
            'date_naissance' => $user->enseignant->date_naissance,
            'lieu_naissance' => $user->enseignant->lieu_naissance,
            'numero_CNI' => $user->enseignant->numero_CNI,
            'image' => $user->enseignant->image,
            'cv_diplomes' => $user->enseignant->cv_diplomes,
            'matiere_enseignée' => $user->enseignant->matiere_enseignée,
            'numero_identification_enseignant' => $user->enseignant->numero_identification_enseignant,
            'niveau_enseignant' => $user->enseignant->niveau_enseignant,
            'nationalité' => $user->enseignant->nationalité,
            'statut_enseignant' => $user->enseignant->statut_enseignant,
            'date_debut_service' => $user->enseignant->date_debut_service,
            'type_contrat' => $user->enseignant->type_contrat,
            'heure_travail_hebdomadaire' => $user->enseignant->heure_travail_hebdomadaire,
            'salaire_base' =>$user->enseignant->salaire_base,
            'type_salaire' => $user->enseignant->type_salaire,
            'prime_indemnités' => $user->enseignant->prime_indemnités,
            'cotisation_sociales' => $user->enseignant->cotisation_sociales,
            'part_employeur' =>$user->enseignant->part_employeur,
            'retenue_salaire' =>$user->enseignant->retenue_salaire,
            'mode_paiement' =>$user->enseignant->mode_paiement,
            'banque_domiciliation' => $user->enseignant->banque_domiciliation,
            'numero_RIB' => $user->enseignant->numero_RIB,
            'contrat_travail' => $user->enseignant->contrat_travail,
            'ancienneté' =>$user->enseignant->ancienneté,
            'evaluation_performance' => $user->enseignant->evaluation_performance,
            'commentaires_notes' => $user->enseignant->commentaires_notes,

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
            'id' =>$user->personnelAdministratif->id,
           'poste_occupé' => $user->personnelAdministratif->poste_occupé,
            'image' => $user->personnelAdministratif->image,
            'date_naissance' =>$user->personnelAdministratif->date_naissance,
            'lieu_naissance' =>$user->personnelAdministratif->lieu_naissance,
            'type_salaire' => $user->personnelAdministratif->type_salaire,
            'numero_CNI' => $user->personnelAdministratif->numero_CNI,
            'date_naissance' =>$user->personnelAdministratif->date_naissance,
            'nationalité'=>$user->personnelAdministratif->nationalité,
           'date_debut_service'=>$user->personnelAdministratif->date_debut_service,
            'statut_employé'=>$user->personnelAdministratif->statut_employé,
            'type_contrat' =>$user->personnelAdministratif->type_contrat,
            'departement_service' =>$user->personnelAdministratif->departement_service,
             'horaires_travail' =>$user->personnelAdministratif->horaires_travail,
             'numero_identification_employe' =>$user->personnelAdministratif->numero_identification_employe,
             'superviseur' =>$user->personnelAdministratif->superviseur,
             'salaire_base' =>$user->personnelAdministratif->salaire_base,
             'prime_indemnités' =>$user->personnelAdministratif->prime_indemnités,
            'cotisation_sociales' =>$user->personnelAdministratif->cotisation_sociales,
            'departement_service' =>$user->personnelAdministratif->departement_service,
            'certification_formations' =>$user->personnelAdministratif->certification_formations,
            'part_employeur' =>$user->personnelAdministratif->part_employeur,
           'retenue_salaire' =>$user->personnelAdministratif->retenue_salaire,
           'mode_paiement' =>$user->personnelAdministratif->mode_paiement,
            'banque_domiciliation' =>$user->personnelAdministratif->banque_domiciliation,
            'numero_compte_bancaire' =>$user->personnelAdministratif->numero_compte_bancaire,
            'cv_diplomes' =>$user->personnelAdministratif->cv_diplomes,
            'contrat_travail' =>$user->personnelAdministratif->contrat_travail,
            'ancienneté' =>$user->personnelAdministratif->ancienneté,
            'evaluation_performance' =>$user->personnelAdministratif->evaluation_performance,
            'commentaires_notes' =>$user->personnelAdministratif->commentaires_notes,
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
            'nationalité' => $tuteur->nationalité,
            'nombre_enfants_inscrits' => $tuteur->nombre_enfants_inscrits,
            'lien_parenté' => $tuteur->lien_parenté,
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
                    'niveau_education' => $apprenant->classe->niveau_education,
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
           'id' => $user->tuteur->id,
            'profession' => $user->tuteur->profession,
            'nationalité' => $user->tuteur->nationalité,
            'nombre_enfants_inscrits' =>$user->tuteur->nombre_enfants_inscrits,
            'lien_parenté' =>$user->tuteur->lien_parenté,
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
                        'niveau_education' => $apprenant->classe->niveau_education,
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
            'date_naissance' => $user->enseignant->date_naissance,
            'lieu_naissance' => $user->enseignant->lieu_naissance,
            'numero_CNI' => $user->enseignant->numero_CNI,
            'image' => $user->enseignant->image,
            'cv_diplomes' => $user->enseignant->cv_diplomes,
            'matiere_enseignée' => $user->enseignant->matiere_enseignée,
            'numero_identification_enseignant' => $user->enseignant->numero_identification_enseignant,
            'niveau_enseignant' => $user->enseignant->niveau_enseignant,
            'nationalité' => $user->enseignant->nationalité,
            'statut_enseignant' => $user->enseignant->statut_enseignant,
            'date_debut_service' => $user->enseignant->date_debut_service,
            'type_contrat' => $user->enseignant->type_contrat,
            'heure_travail_hebdomadaire' => $user->enseignant->heure_travail_hebdomadaire,
            'salaire_base' =>$user->enseignant->salaire_base,
            'type_salaire' => $user->enseignant->type_salaire,
            'prime_indemnités' => $user->enseignant->prime_indemnités,
            'cotisation_sociales' => $user->enseignant->cotisation_sociales,
            'part_employeur' =>$user->enseignant->part_employeur,
            'retenue_salaire' =>$user->enseignant->retenue_salaire,
            'mode_paiement' =>$user->enseignant->mode_paiement,
            'banque_domiciliation' => $user->enseignant->banque_domiciliation,
            'numero_RIB' => $user->enseignant->numero_RIB,
            'contrat_travail' => $user->enseignant->contrat_travail,
            'ancienneté' =>$user->enseignant->ancienneté,
            'evaluation_performance' => $user->enseignant->evaluation_performance,
            'commentaires_notes' => $user->enseignant->commentaires_notes,
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
                'id' =>$user->personnelAdministratif->id,
           'poste_occupé' => $user->personnelAdministratif->poste_occupé,
            'image' => $user->personnelAdministratif->image,
            'date_naissance' =>$user->personnelAdministratif->date_naissance,
            'lieu_naissance' =>$user->personnelAdministratif->lieu_naissance,
            'type_salaire' => $user->personnelAdministratif->type_salaire,
            'numero_CNI' => $user->personnelAdministratif->numero_CNI,
            'date_naissance' =>$user->personnelAdministratif->date_naissance,
            'nationalité'=>$user->personnelAdministratif->nationalité,
           'date_debut_service'=>$user->personnelAdministratif->date_debut_service,
            'statut_employé'=>$user->personnelAdministratif->statut_employé,
            'type_contrat' =>$user->personnelAdministratif->type_contrat,
            'departement_service' =>$user->personnelAdministratif->departement_service,
             'horaires_travail' =>$user->personnelAdministratif->horaires_travail,
             'numero_identification_employe' =>$user->personnelAdministratif->numero_identification_employe,
             'superviseur' =>$user->personnelAdministratif->superviseur,
             'salaire_base' =>$user->personnelAdministratif->salaire_base,
             'certification_formations' =>$user->personnelAdministratif->certification_formations,
             'prime_indemnités' =>$user->personnelAdministratif->prime_indemnités,
            'cotisation_sociales' =>$user->personnelAdministratif->cotisation_sociales,
            'departement_service' =>$user->personnelAdministratif->departement_service,
            'part_employeur' =>$user->personnelAdministratif->part_employeur,
           'retenue_salaire' =>$user->personnelAdministratif->retenue_salaire,
           'mode_paiement' =>$user->personnelAdministratif->mode_paiement,
            'banque_domiciliation' =>$user->personnelAdministratif->banque_domiciliation,
            'numero_compte_bancaire' =>$user->personnelAdministratif->numero_compte_bancaire,
            'cv_diplomes' =>$user->personnelAdministratif->cv_diplomes,
            'contrat_travail' =>$user->personnelAdministratif->contrat_travail,
            'ancienneté' =>$user->personnelAdministratif->ancienneté,
            'evaluation_performance' =>$user->personnelAdministratif->evaluation_performance,
            'commentaires_notes' =>$user->personnelAdministratif->commentaires_notes,
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
                'id' => $user->tuteur->id,
            'profession' => $user->tuteur->profession,
            'nationalité' => $user->tuteur->nationalité,
            'nombre_enfants_inscrits' =>$user->tuteur->nombre_enfants_inscrits,
            'lien_parenté' =>$user->tuteur->lien_parenté,
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
                            'niveau_education' => $apprenant->classe->niveau_education,
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
            'id' => $user->directeur->id,
           'image' => $user->directeur->image,
            'date_naissance' => $user->directeur->date_naissance,
            'lieu_naissance' =>$user->directeur->lieu_naissance,
            'type_salaire' => $user->directeur->type_salaire,
            'numero_CNI' => $user->directeur->numero_CNI,
            'date_naissance' =>$user->directeur->date_naissance,
            'nationalité'=>$user->directeur->nationalité ??null,
           'date_debut_service'=>$user->directeur->date_debut_service ?? null,
            'statut_employé'=>$user->directeur->statut_employé,
            'type_contrat' =>$user->directeur->type_contrat,
            'departement_service' =>$user->directeur->departement_service,
             'horaires_travail' =>$user->directeur->horaires_travail,
             'numero_identification_directeur' =>$user->directeur->numero_identification_directeur,
             'salaire_base' =>$user->directeur->salaire_base,
             'prime_indemnités' =>$user->directeur->prime_indemnités,
            'cotisation_sociales' =>$user->directeur->cotisation_sociales,
            'departement_service' =>$user->directeur->departement_service,
            'part_employeur' =>$user->directeur->part_employeur,
           'retenue_salaire' =>$user->directeur->retenue_salaire ,
           'mode_paiement' =>$user->directeur->mode_paiement,
            'banque_domiciliation' =>$user->directeur->banque_domiciliation,
            'numero_compte_bancaire' =>$user->directeur->numero_compte_bancaire ,
            'cv_diplomes' =>$user->directeur->cv_diplomes,
            'certification_formations' =>$user->directeur->certification_formations,
            'contrat_travail' =>$user->directeur->contrat_travail,
            'ancienneté' =>$user->directeur->ancienneté,
            'evaluation_performance' =>$user->directeur->evaluation_performance,
            'commentaires_notes' =>$user->directeur->commentaires_notes,
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

