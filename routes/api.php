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

Route::put('directeur/edit/{user}', [AuthController::class, 'update']);

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
//lister tuteur
Route::get('tuteurs', [AuthController::class, 'indexTuteurs']);
//-----------gestion user enseignant-------------
Route::post('ajouter/enseignant', [AuthController::class, 'registerEnseignant']);

//lister Enseignant
Route::get('enseignants', [AuthController::class, 'indexEnseignants']);
//--------------gestion apprenant-------------
//ajouter un apprenant
Route::post('ajouter/apprenant', [AuthController::class, 'registerApprenant']);
//lister les apprenants
Route::get('apprenants', [AuthController::class, 'indexApprenants']);
//--------------------gestion classe-----------------------
Route::post('ajouter/classe', [ClasseController::class, 'storeClasse']);
//------------------gestion salle-------------------------

Route::post('ajouter/salle', [SalleController::class, 'storeSalle']);
