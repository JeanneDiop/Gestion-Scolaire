<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SalleController;
use App\Http\Controllers\API\ClasseController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
//ces routes sont groupes dans un midleware api
Route::group(['middleware' => 'api'], function ($router) {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});


Route::post('login', [AuthController::class, 'login']);

//------lister tous les users------------------------
Route::get('ListeUtilisateur',[AuthController::class,'ListeUtilisateur']);
//----------Gestion Role----------------
//ajouter role
Route::post('ajouter/role', [RoleController::class, 'store']);
//modifier role
Route::put('role/edit/{id}', [RoleController::class, 'update']);
//supprimer un role
Route::delete('role/{role}', [RoleController::class, 'destroy']);
//lister les roles
Route::get('role/lister', [RoleController::class, 'index']);
//recuper un role specifique
Route::get('role/{id}', [RoleController::class, 'show']);

//-----------gestion Utilisateur tuteur----------------
Route::post('ajouter/tuteur', [AuthController::class, 'registerTuteur']);
//lister tuteur user
Route::get('tuteurs', [AuthController::class, 'indexTuteurs']);
//----afficher tuteur
Route::get('/tuteur/{id}',[AuthController::class,'showTuteur']);
//lister tous les tuteurs dans sa table
Route::get('/liste/tuteur',[AuthController::class,'ListerTuteur']);


//-----------gestion user enseignant-------------
Route::post('ajouter/enseignant', [AuthController::class, 'registerEnseignant']);
//lister Enseignant user
Route::get('enseignants', [AuthController::class, 'indexEnseignants']);
//afficher un enseignant
Route::get('/enseignant/{id}',[AuthController::class,'showEnseignant']);
//lister tous les enseignants dans sa table
Route::get('/liste/enseignant',[AuthController::class,'ListerEnseignant']);


//--------------gestion apprenant-------------
//ajouter un apprenant
Route::post('ajouter/apprenant', [AuthController::class, 'registerApprenant']);
//lister les apprenants user
Route::get('apprenants', [AuthController::class, 'indexApprenants']);
//lister les infos d'un apprenant
Route::get('/apprenant/{id}',[AuthController::class,'showApprenant']);
//lister tous les apprenants dans sa table
Route::get('/liste/apprenant',[AuthController::class,'ListerApprenant']);

//------gestion directeur---------------------
//afficher info dun directeur
Route::get('/directeur/{id}',[AuthController::class,'showDirecteur']);
//ajouter directeur
Route::post('ajouter/directeur', [AuthController::class, 'registerDirecteur']);
//lister tous les directeurs dans sa table
Route::get('/liste/directeur',[AuthController::class,'ListerDirecteur']);
//lister tous les directeurs dans users
Route::get('directeurs', [AuthController::class, 'indexDirecteurs']);



//--------------------gestion classe-----------------------
Route::post('ajouter/classe', [ClasseController::class, 'storeClasse']);
//lister les classes
Route::get('classe/lister', [ClasseController::class, 'indexClasse']);
//afficher classe
Route::get('classe/detail/{id}', [ClasseController::class, 'showClasse']);


//------------------gestion salle-------------------------
Route::post('ajouter/salle', [SalleController::class, 'storeSalle']);
//lister les salles
Route::get('salle/lister', [SalleController::class, 'indexSalle']);
//afficher salle
Route::get('salle/detail/{id}', [SalleController::class, 'showSalle']);
