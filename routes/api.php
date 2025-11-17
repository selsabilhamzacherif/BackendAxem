<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChefDepartementController;
use App\Http\Controllers\ContrainteController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ResponsablePlanificationController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\UtilisateurController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Exemple de route pour l'utilisateur authentifié (optionnel, si vous utilisez l'authentification)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --------------------------------------------------------------------------
// Routes pour la gestion  (CRUD basique)
// --------------------------------------------------------------------------

// Utilisation de Route::resource pour les opérations CRUD standard
Route::resource('chefs-departement', ChefDepartementController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('contrainte', ContrainteController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('enseignant', EnseignantController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('etudiant', EtudiantController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('examen', ExamenController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('groupe', GroupeController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('module', ModuleController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('notification', NotificationController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('responsableplan', ResponsablePlanificationController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('salle', SalleController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
Route::resource('utilisateur', UtilisateurController::class)->only([
    'index', 'store', 'show', 'update', 'destroy'
]);
// --------------------------------------------------------------------------
// Routes pour les opérations métier spécifiques au Chef de Département
// --------------------------------------------------------------------------

Route::prefix('chefs-departement/{chefId}')->group(function () {
    // POST /api/chefs-departement/{chefId}/valider-plan
    // Valider un ou tous les plans d'examens. Prend un tableau d'ID d'examens en option dans le corps de la requête.
    Route::post('/valider-plan', [ChefDepartementController::class, 'validerPlan']);

    // POST /api/chefs-departement/{chefId}/publier-plan
    // Publier tous les plans d'examens validés pour le département.
    Route::post('/publier-plan', [ChefDepartementController::class, 'publierPlan']);

    // GET /api/chefs-departement/{chefId}/examens
    // Consulter tous les examens du département.
    Route::get('/examens', [ChefDepartementController::class, 'consulterExamensDepartement']);

    // POST /api/chefs-departement/{chefId}/annuler-validation
    // Annuler la validation d'un examen spécifique. Nécessite 'examen_id' et 'motif' dans le corps.
    Route::post('/annuler-validation', [ChefDepartementController::class, 'annulerValidation']);


});
//salle disponibilite
 Route::get('/salles/{id}/disponibilite', [SalleController::class, 'checkDisponibilite']);
// --------------------------------------------------------------------------
// Routes pour les opérations métier spécifiques aux Étudiants
// --------------------------------------------------------------------------
 Route::get('/mon-planning', [EtudiantController::class, 'consulterExams']);      // Voir ses examens
 Route::get('/mon-planning/telecharger', [EtudiantController::class, 'telechargerPlanning']); // Télécharger planning
 Route::get('/mon-groupe', [EtudiantController::class, 'consulterGroupe']);      // Voir son groupe
 //-------------------------------------------------------------------------
 // Routes pour les opérations métier spécifiques aux Enseignants
 // --------------------------------------------------------------------------
 Route::post('/proposer-creneaux', [EnseignantController::class, 'proposerCreneau']); // Proposer créneaux
 Route::post('/signaler-contrainte', [EnseignantController::class, 'signalerContrainte']); // Signaler contrainte
 Route::get('/consulter-planning', [EnseignantController::class, 'consulterPlanning']); // Consulter planning
// --------------------------------------------------------------------------
// Routes pour les opérations métier spécifiques aux Examens
// --------------------------------------------------------------------------
Route::get('/examens/{id}/detecter-conflits', [ExamenController::class, 'detecterConflits']);
Route::post('/examens/valider', [ExamenController::class, 'valider']);
Route::post('/examens/publier', [ExamenController::class, 'publier']);
// --------------------------------------------------------------------------

