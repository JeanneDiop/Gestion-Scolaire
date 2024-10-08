<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SalleController;
use App\Http\Controllers\API\ClasseController;
use App\Http\Controllers\API\EmployeController;
use App\Http\Controllers\API\CoursController;
use App\Http\Controllers\API\PlanifiercourController;
use App\Http\Controllers\API\EnseignantClasseController;
use App\Http\Controllers\API\ClasseAssociationController;
use App\Http\Controllers\API\EvaluationController;
use App\Http\Controllers\API\ParcoursController;
use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\ProgrammeController;
use App\Http\Controllers\API\PresenceAbsenceController;
use App\Http\Controllers\API\ApprenantClasseController;
use App\Http\Controllers\API\EcoleController;
use App\Http\Controllers\API\NiveauController;
use App\Http\Controllers\API\NiveauEcoleController;




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
//archiver ou desactiver un User
Route::post('archiveruser/{user}',[AuthController::class,'archiverUser']);

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
//modifier tuteur via user
Route::put('/modifierusertuteur/{user}',[AuthController::class,'updateUserTuteur']);
//modifier tuteur via sa table
Route::put('/modifiertuteur/{id}',[AuthController::class,'updateTuteur']);
//lister tous les tuteurs dans sa table
Route::get('/liste/tuteur',[AuthController::class,'ListerTuteur']);
//afficher un tuteur dans la table user
Route::get('/tuteur/user/{id}',[AuthController::class,'showUserTuteur']);
//supprimer tuteur dans sa table
Route::delete('/supprimertuteur/{tuteur}', [AuthController::class, 'supprimerTuteur']);
//supprimer tuteur dans la table user
Route::delete('/supprimerusertuteur/{user}', [AuthController::class, 'supprimerUserTuteur']);
//archiver ou desactiver un tuteur via sa table
Route::post('archivertuteur/{tuteur}',[AuthController::class,'archiverTuteur']);
//modifier password tuteur
Route::post('modifierpasswordtuteur',[AuthController::class,'updatePasswordTuteur']);

//-----------gestion user enseignant-------------
Route::post('ajouter/enseignant', [AuthController::class, 'registerEnseignant']);
//lister Enseignant user
Route::get('enseignants', [AuthController::class, 'indexEnseignants']);
//afficher un enseignant dans sa table
Route::get('/enseignant/{id}',[AuthController::class,'showEnseignant']);
//afficher un enseignant dans la table user
Route::get('/enseignant/user/{id}',[AuthController::class,'showUserEnseignant']);
//lister tous les enseignants dans sa table
Route::get('/liste/enseignant',[AuthController::class,'ListerEnseignant']);
//lister enseignant par niveauecole
Route::get('/enseignant/niveauecole/{niveauEcole}', [AuthController::class, 'ListerEnseignantNiveauEcole']);
//modifier enseignant via user
Route::put('/modifieruserenseignant/{user}',[AuthController::class,'updateUserEnseignant']);
//modifier enseignant via sa table
Route::put('/modifierenseignant/{id}',[AuthController::class,'updateEnseignant']);
//supprimer enseignant dans sa table
Route::delete('/supprimerenseignant/{enseignant}', [AuthController::class, 'supprimerEnseignant']);
//supprimer enseignant dans la table user
Route::delete('/supprimeruserenseignant/{user}', [AuthController::class, 'supprimerUserEnseignant']);
//archiver ou desactiver un enseignant via sa table
Route::post('archiverenseignant/{enseignant}',[AuthController::class,'archiverEnseignant']);

//--------------gestion apprenant-------------
Route::post('/registerapprenanttuteur', [AuthController::class, 'registerApprenantTuteur']);
// modifier apprenanttuteur
Route::put('/updateapprenanttuteur/{id}', [AuthController::class, 'updateApprenantTuteur']);
//ajouter un apprenant
Route::post('ajouter/apprenant', [AuthController::class, 'registerApprenant']);
//lister les apprenants user
Route::get('apprenants', [AuthController::class, 'indexApprenants']);
//lister les infos d'un apprenant
Route::get('/apprenant/{id}',[AuthController::class,'showApprenant']);
//lister les infos d'un apprenant dans user
Route::get('/apprenant/user/{id}',[AuthController::class,'showUserApprenant']);
//lister tous les apprenants dans sa table
Route::get('/liste/apprenant',[AuthController::class,'ListerApprenant']);
//lister apprenant par niveau_education
Route::get('/apprenants/niveau/{niveauEducation}', [AuthController::class, 'ListerApprenantParNiveau']);
//modifier apprenant via user
Route::put('/modifieruserapprenant/{user}',[AuthController::class,'updateUserApprenant']);
//modifier apprenant via sa table
Route::put('/modifierapprenant/{id}',[AuthController::class,'updateApprenant']);
//supprimer apprenant dans sa table
Route::delete('/supprimerapprenant/{apprenant}', [AuthController::class, 'supprimerApprenant']);
//supprimer apprenant dans la table user
Route::delete('/supprimeruserapprenant/{user}', [AuthController::class, 'supprimerUserApprenant']);
//archiver ou desactiver un apprenant via sa table
Route::post('archiverapprenant/{apprenant}',[AuthController::class,'archiverApprenant']);
//afficher les infos d'un apprenant par rapport à sa statut de presence
Route::get('/apprenant/details/{id}', [AuthController::class, 'getApprenantDetailsWithPresence']);
//afficher ses infos par rapport à la note
Route::get('/apprenants/notes/{id}', [AuthController::class, 'getApprenantDetailsWithNotes']);

//----------------------gestion directeur---------------------
//afficher info dun directeur
Route::get('/directeur/{id}',[AuthController::class,'showDirecteur']);
//afficher info dun directeur dans user
Route::get('/directeur/user/{id}',[AuthController::class,'showUserDirecteur']);
//ajouter directeur
Route::post('ajouter/directeur', [AuthController::class, 'registerDirecteur']);
//lister tous les directeurs dans sa table
Route::get('/liste/directeur',[AuthController::class,'ListerDirecteur']);
//lister tous les directeurs dans users
Route::get('directeurs', [AuthController::class, 'indexDirecteurs']);
//modifier directeur via user
Route::put('/modifieruserdirecteur/{user}',[AuthController::class,'updateUserDirecteur']);
//modifier directeur via sa table
Route::put('/modifierdirecteur/{id}',[AuthController::class,'updateDirecteur']);
//supprimer directeur dans sa table
Route::delete('/supprimerdirecteur/{directeur}', [AuthController::class, 'supprimerDirecteur']);
//supprimer directeur dans la table user
Route::delete('/supprimeruserdirecteur/{user}', [AuthController::class, 'supprimerUserDirecteur']);
//archiver ou desactiver un directeur via sa table
Route::post('archiverdirecteur/{directeur}',[AuthController::class,'archiverDirecteur']);

//----------------------gestion personneladministratif---------------------
//afficher info dun personnel
Route::get('/personneladministratif/{id}',[AuthController::class,'showPersonnelAdministratif']);
//afficher info dun personnel dans user
Route::get('/personnel/user/{id}',[AuthController::class,'showUserPersonnelAdministratif']);
//ajouter personnel
Route::post('ajouter/personnel', [AuthController::class, 'registerPersonnelAdministratif']);
//lister tous les personnels dans sa table
Route::get('/liste/personnel',[AuthController::class,'ListerPersonnelAdministratif']);
//lister tous les personnels dans users
Route::get('personnels', [AuthController::class, 'indexPersonnelAdministaratifs']);
//modifier personnel via user
Route::put('/modifieruserpersonnel/{user}',[AuthController::class,'updateUserPersonnelAdministratif']);
//modifier personnel via sa table
Route::put('/modifierpersonnel/{id}',[AuthController::class,'updatePersonnelAdministratif']);
//supprimer personnel dans sa table
Route::delete('/supprimerpersonnel/{personneladministratif}', [AuthController::class, 'supprimerPersonnelAdministratif']);
//supprimer personnel dans la table user
Route::delete('/supprimeruserpersonnel/{user}', [AuthController::class, 'supprimerUserPersonnelAdministratif']);
//archiver ou desactiver un personnel via sa table
Route::post('archiverpersonnel/{personneladministratif}',[AuthController::class,'archiverPersonnelAdministratif']);

//--------------------gestion classe-----------------------
Route::post('ajouter/classe', [ClasseController::class, 'storeClasse']);
//lister les classes
Route::get('classe/lister', [ClasseController::class, 'indexClasse']);
//afficher classe
Route::get('classe/detail/{id}', [ClasseController::class, 'showClasse']);
//modifier une classe
Route::put('classe/edit/{id}', [ClasseController::class, 'updateClasse']);
//supprimer un classe
Route::delete('classe/supprimer/{id}', [ClasseController::class, 'destroy']);

//------------------gestion salle-------------------------
Route::post('ajouter/salle', [SalleController::class, 'storeSalle']);
//lister les salles
Route::get('salle/lister', [SalleController::class, 'indexSalle']);
//afficher salle
Route::get('salle/detail/{id}', [SalleController::class, 'showSalle']);
//modifier une salle
Route::put('salle/edit/{id}', [SalleController::class, 'updateSalle']);
//supprimer un salle
Route::delete('salle/supprimer/{id}', [SalleController::class, 'destroySalle']);

//-------------gestion employer------------------------------
Route::post('employe/create', [EmployeController::class, 'store']);
//modifier employe
Route::put('employe/edit/{id}', [EmployeController::class, 'update']);
//supprimer  employe
Route::delete('employe/supprimer/{id}', [EmployeController::class, 'destroy']);
//lister employes
Route::get('employe/lister', [EmployeController::class, 'index']);
//afficher employe
Route::get('employe/detail/{id}', [EmployeController::class, 'show']);
//gestion cours-----------------------------------------------
Route::post('cours/create', [CoursController::class, 'store']);
//modifier cours
Route::put('cours/edit/{id}', [CoursController::class, 'update']);
//supprimer  cours
Route::delete('cours/supprimer/{id}', [CoursController::class, 'destroy']);
//lister cours
Route::get('cours/lister', [CoursController::class, 'index']);
//afficher cours
Route::get('cours/detail/{id}', [CoursController::class, 'show']);
//gestion enseignant_classe-------------------------------------------
Route::post('enseignantclasse/create', [EnseignantClasseController::class, 'store']);
//modifier cours
Route::put('enseignantclasse/edit/{id}', [EnseignantClasseController::class, 'update']);
//supprimer  cours
Route::delete('enseignantclasse/supprimer/{id}', [EnseignantClasseController::class, 'destroy']);
//lister cours
Route::get('enseignantclasse/lister', [EnseignantClasseController::class, 'index']);
//afficher cours
Route::get('enseignantclasse/detail/{id}', [EnseignantClasseController::class, 'show']);
//gestion planifiercour------------------------------------
Route::post('planifiercour/create', [PlanifiercourController::class, 'store']);
//modifier cours
Route::put('planifiercour/edit/{id}', [PlanifiercourController::class, 'update']);
//supprimer  cours
Route::delete('planifiercour/supprimer/{id}', [PlanifiercourController::class, 'destroy']);
//lister cours
Route::get('planifiercour/lister', [PlanifiercourController::class, 'index']);
//afficher cours
Route::get('planifiercour/detail/{id}', [PlanifiercourController::class, 'show']);
//gestion classeassociation-----------------
Route::post('classeassocier/create', [ClasseAssociationController::class, 'store']);
//modifier cours
Route::put('classeassocier/edit/{id}', [ClasseAssociationController::class, 'update']);
//supprimer  cours
Route::delete('classeassocier/supprimer/{id}', [ClasseAssociationController::class, 'destroy']);
//lister cours
Route::get('classeassocier/lister', [ClasseAssociationController::class, 'index']);
//afficher cours
Route::get('classeassocier/detail/{id}', [ClasseAssociationController::class, 'show']);
//gestion EnseignantClasse-----------------
Route::post('enseignantclasse/create', [EnseignantClasseController::class, 'store']);
//modifier enseignantclasse
Route::put('enseignantclasse/edit/{id}', [EnseignantClasseController::class, 'update']);
//supprimer  enseignantclasse
Route::delete('enseignantclasse/supprimer/{id}', [EnseignantClasseController::class, 'destroy']);
//lister enseignantclasse
Route::get('enseignantclasse/lister', [EnseignantClasseController::class, 'index']);
//afficher enseignantclasse
Route::get('enseignantclasse/detail/{id}', [EnseignantClasseController::class, 'show']);
//gestion Evaluation-----------------
Route::post('evaluation/create', [EvaluationController::class, 'store']);
//modifier evaluation
Route::put('evaluation/edit/{id}', [EvaluationController::class, 'update']);
//supprimer  evaluation
Route::delete('evaluation/supprimer/{id}', [EvaluationController::class, 'destroy']);
//lister evaluation
Route::get('evaluation/lister', [EvaluationController::class, 'index']);
//afficher evaluation
Route::get('evaluation/detail/{id}', [EvaluationController::class, 'show']);
//gestion Note-----------------
Route::post('note/create', [NoteController::class, 'store']);
//modifier note
Route::put('note/edit/{id}', [NoteController::class, 'update']);
//supprimer  note
Route::delete('note/supprimer/{id}', [NoteController::class, 'destroy']);
//lister note
Route::get('note/lister', [NoteController::class, 'index']);
//afficher note
Route::get('note/detail/{id}', [NoteController::class, 'show']);
//afficher note pour un apprenant
Route::get('/apprenants/note/{apprenantId}', [NoteController::class, 'showNotesByApprenant']);
//afficher les notes dune classe
Route::get('/classes/notes/{classeId}', [NoteController::class, 'showNotesByClasse']);
//gestion Parcours-----------------
Route::post('parcours/create', [ParcoursController::class, 'store']);
//modifier parcours
Route::put('parcours/edit/{id}', [ParcoursController::class, 'update']);
//supprimer  parcours
Route::delete('parcours/supprimer/{id}', [ParcoursController::class, 'destroy']);
//lister parcours
Route::get('parcours/lister', [ParcoursController::class, 'index']);
//afficher parcours
Route::get('parcours/detail/{id}', [ParcoursController::class, 'show']);
//gestion Programme-----------------
Route::post('programme/create', [ProgrammeController::class, 'store']);
//modifier programme
Route::put('programme/edit/{id}', [ProgrammeController::class, 'update']);
//supprimer  programme
Route::delete('programme/supprimer/{id}', [ProgrammeController::class, 'destroy']);
//lister programme
Route::get('programme/lister', [ProgrammeController::class, 'index']);
//afficher programme
Route::get('programme/detail/{id}', [ProgrammeController::class, 'show']);
//gestion PresenceAbsence-----------------
Route::post('presenceabsence/create', [PresenceAbsenceController::class, 'store']);
//modifier presenceabsence
Route::put('presenceabsence/edit/{id}', [PresenceAbsenceController::class, 'update']);
//supprimer  presenceabsence
Route::delete('presenceabsence/supprimer/{id}', [PresenceAbsenceController::class, 'destroy']);
//lister presenceabsence
Route::get('presenceabsence/lister', [PresenceAbsenceController::class, 'index']);
//afficher presenceabsence
Route::get('presenceabsence/detail/{id}', [PresenceAbsenceController::class, 'show']);
//afficher present
Route::get('present/detail/{id}', [PresenceAbsenceController::class, 'showpresent']);
//afficher absent
Route::get('absent/detail/{id}', [PresenceAbsenceController::class, 'showabsent']);
//lister tous les absent
Route::get('absent/lister', [PresenceAbsenceController::class, 'indexabsent']);
//lister tous les present
Route::get('present/lister', [PresenceAbsenceController::class, 'indexpresent']);
//gestion ApprenantClasse-----------------
Route::post('apprenantclasse/create', [ApprenantClasseController::class, 'store']);
//modifier apprenantclasse
Route::put('apprenantclasse/edit/{id}', [ApprenantClasseController::class, 'update']);
//supprimer  apprenantclasse
Route::delete('apprenantclasse/supprimer/{id}', [ApprenantClasseController::class, 'destroy']);
//lister apprenantclasse
Route::get('apprenantclasse/lister', [ApprenantClasseController::class, 'index']);
//afficher apprenantclasse
Route::get('apprenantclasse/detail/{id}', [ApprenantClasseController::class, 'show']);
//gestion Ecole-----------------
Route::post('ecole/create', [EcoleController::class, 'store']);
//modifier ecole
Route::put('ecole/edit/{id}', [EcoleController::class, 'update']);
//supprimer  ecole
Route::delete('ecole/supprimer/{id}', [EcoleController::class, 'destroy']);
//lister ecole
Route::get('ecole/lister', [EcoleController::class, 'index']);
//afficher ecole
Route::get('ecole/detail/{id}', [EcoleController::class, 'show']);
//lister les ecoles par niveau
Route::get('/ecoles/niveau', [EcoleController::class, 'indexByNiveau']);
//gestion niveau---------------------------------------------
Route::post('niveau/create', [NiveauController::class, 'store']);
//modifier niveau
Route::put('niveau/edit/{id}', [NiveauController::class, 'update']);
//supprimer  niveau
Route::delete('niveau/supprimer/{id}', [NiveauController::class, 'destroy']);
//lister niveau
Route::get('niveau/lister', [NiveauController::class, 'index']);
//afficher niveau
Route::get('niveau/detail/{id}', [NiveauController::class, 'show']);
//gestion niveauEcole---------------------------------------------
Route::post('niveauecole/create', [NiveauEcoleController::class, 'store']);
//modifier niveauecole
Route::put('niveauecole/edit/{id}', [NiveauEcoleController::class, 'update']);
//supprimer  niveauecole
Route::delete('niveauecole/supprimer/{id}', [NiveauEcoleController::class, 'destroy']);
//lister niveauecole
Route::get('niveauecole/lister', [NiveauEcoleController::class, 'index']);
//afficher niveauecole
Route::get('niveauecole/detail/{id}', [NiveauEcoleController::class, 'show']);
