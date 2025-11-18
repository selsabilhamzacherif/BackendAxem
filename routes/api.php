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
/**
 * Utilisateurs
 */
Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
Route::post('/utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
Route::get('/utilisateurs/{id}', [UtilisateurController::class, 'show'])->name('utilisateurs.show');
Route::put('/utilisateurs/{id}', [UtilisateurController::class, 'update'])->name('utilisateurs.update');
Route::delete('/utilisateurs/{id}', [UtilisateurController::class, 'destroy'])->name('utilisateurs.destroy');

/**
 * Groupes
 */
Route::get('/groupes', [GroupeController::class, 'index'])->name('groupes.index');
Route::post('/groupes', [GroupeController::class, 'store'])->name('groupes.store');
Route::get('/groupes/{id}', [GroupeController::class, 'show'])->name('groupes.show');
Route::put('/groupes/{id}', [GroupeController::class, 'update'])->name('groupes.update');
Route::delete('/groupes/{id}', [GroupeController::class, 'destroy'])->name('groupes.destroy');

/**
 * Étudiants
 */
Route::get('/etudiants', [EtudiantController::class, 'index'])->name('etudiants.index');
Route::post('/etudiants', [EtudiantController::class, 'store'])->name('etudiants.store');
Route::get('/etudiants/{id}', [EtudiantController::class, 'show'])->name('etudiants.show');
Route::put('/etudiants/{id}', [EtudiantController::class, 'update'])->name('etudiants.update');
Route::delete('/etudiants/{id}', [EtudiantController::class, 'destroy'])->name('etudiants.destroy');

/**
 * Enseignants
 */
Route::get('/enseignants', [EnseignantController::class, 'index'])->name('enseignants.index');
Route::post('/enseignants', [EnseignantController::class, 'store'])->name('enseignants.store');
Route::get('/enseignants/{id}', [EnseignantController::class, 'show'])->name('enseignants.show');
Route::put('/enseignants/{id}', [EnseignantController::class, 'update'])->name('enseignants.update');
Route::delete('/enseignants/{id}', [EnseignantController::class, 'destroy'])->name('enseignants.destroy');

/**
 * Examens
 */
Route::get('/examens', [ExamenController::class, 'index'])->name('examens.index');
Route::post('/examens', [ExamenController::class, 'store'])->name('examens.store');
Route::get('/examens/{id}', [ExamenController::class, 'show'])->name('examens.show');
Route::put('/examens/{id}', [ExamenController::class, 'update'])->name('examens.update');
Route::delete('/examens/{id}', [ExamenController::class, 'destroy'])->name('examens.destroy');

/**
 * Modules
 */
Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
Route::get('/modules/{id}', [ModuleController::class, 'show'])->name('modules.show');
Route::put('/modules/{id}', [ModuleController::class, 'update'])->name('modules.update');
Route::delete('/modules/{id}', [ModuleController::class, 'destroy'])->name('modules.destroy');

/**
 * Notifications
 */
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
Route::put('/notifications/{id}', [NotificationController::class, 'update'])->name('notifications.update');
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

/**
 * Salles
 */
Route::get('/salles', [SalleController::class, 'index'])->name('salles.index');
Route::post('/salles', [SalleController::class, 'store'])->name('salles.store');
Route::get('/salles/{id}', [SalleController::class, 'show'])->name('salles.show');
Route::put('/salles/{id}', [SalleController::class, 'update'])->name('salles.update');
Route::delete('/salles/{id}', [SalleController::class, 'destroy'])->name('salles.destroy');

/**
 * Chef de Département
 */
Route::get('/chefs-departement', [ChefDepartementController::class, 'index'])->name('chefs-departement.index');
Route::post('/chefs-departement', [ChefDepartementController::class, 'store'])->name('chefs-departement.store');
Route::get('/chefs-departement/{id}', [ChefDepartementController::class, 'show'])->name('chefs-departement.show');
Route::put('/chefs-departement/{id}', [ChefDepartementController::class, 'update'])->name('chefs-departement.update');
Route::delete('/chefs-departement/{id}', [ChefDepartementController::class, 'destroy'])->name('chefs-departement.destroy');

/**
 * Responsable Planification
 */
Route::get('/responsables-planification', [ResponsablePlanificationController::class, 'index'])->name('responsables-planification.index');
Route::post('/responsables-planification', [ResponsablePlanificationController::class, 'store'])->name('responsables-planification.store');
Route::get('/responsables-planification/{id}', [ResponsablePlanificationController::class, 'show'])->name('responsables-planification.show');
Route::put('/responsables-planification/{id}', [ResponsablePlanificationController::class, 'update'])->name('responsables-planification.update');
Route::delete('/responsables-planification/{id}', [ResponsablePlanificationController::class, 'destroy'])->name('responsables-planification.destroy');


/**
 * Contraintes
 */
Route::get('/contraintes', [ContrainteController::class, 'index'])->name('contraintes.index');        // Liste des contraintes
Route::post('/contraintes', [ContrainteController::class, 'store'])->name('contraintes.store');       // Créer une contrainte
Route::get('/contraintes/{id}', [ContrainteController::class, 'show'])->name('contraintes.show');     // Afficher une contrainte
Route::put('/contraintes/{id}', [ContrainteController::class, 'update'])->name('contraintes.update'); // Mettre à jour une contrainte
Route::delete('/contraintes/{id}', [ContrainteController::class, 'destroy'])->name('contraintes.destroy'); // Supprimer une contrainte

// --------------------------------------------------------------------------
// Routes pour les opérations métier spécifiques au Chef de Département
// --------------------------------------------------------------------------

Route::prefix('chefs-departements/{chefId}')->group(function () {
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

