<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContrainteController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\AuthController;

/**
 * Utilisateurs
 */

//Route::get('/test', function() { return 'ok'; });


// --------------------------------------------------------------------------

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);

// Routes protégées par JWT
Route::middleware(['jwt.auth'])->group(function() {
    Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
    Route::post('/utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
    Route::get('/utilisateurs/{id}', [UtilisateurController::class, 'show'])->name('utilisateurs.show');
    Route::put('/utilisateurs/{id}', [UtilisateurController::class, 'update'])->name('utilisateurs.update');
    Route::delete('/utilisateurs/{id}', [UtilisateurController::class, 'destroy'])->name('utilisateurs.destroy');


    // Etudiant
    Route::prefix('etudiant/{id}')->group(function () {
        Route::get('/planning', [UtilisateurController::class, 'consulterExams']);
        Route::get('/planning/telecharger', [UtilisateurController::class, 'telechargerPlanning']);
        Route::get('/groupe', [UtilisateurController::class, 'consulterGroupe']);
    });
    // Enseignant
    Route::prefix('enseignant/{id}')->group(function () {
        Route::post('/proposer-creneaux', [UtilisateurController::class, 'proposerCreneau']);
        Route::post('/signaler-contrainte', [UtilisateurController::class, 'signalerContrainte']);
        Route::get('/planning', [UtilisateurController::class, 'consulterPlanningEnseignant']);
    });

    // Responsable Planification
    Route::prefix('responsable/{id}')->group(function () {
        Route::post('/gerer-comptes', [UtilisateurController::class, 'gererComptes']);
        Route::post('/gerer-salles', [UtilisateurController::class, 'gererSalles']);
        Route::post('/planifier', [UtilisateurController::class, 'planifierAutomatiquement']);
        Route::post('/planifier-niveau/{id_groupe}/{niveau}', [UtilisateurController::class, 'planifierNiveauAutomatiquement']);

    });
    //Route::post('responsable/gerer-comptes', [UtilisateurController::class, 'gererComptes']);

    // Chef de Département
    Route::prefix('chef/{id}')->group(function () {
        Route::post('/valider-examens', [UtilisateurController::class, 'validerExamens']);
        Route::post('/publier-plan', [UtilisateurController::class, 'publierPlanParNiveau']);
        Route::post('/refuser-examens', [UtilisateurController::class, 'refuserExamensAvecReclamation']);


    });

    /**
     * Groupes
     */
    Route::get('/groupes', [GroupeController::class, 'index'])->name('groupes.index');
    Route::post('/groupes', [GroupeController::class, 'store'])->name('groupes.store');
    Route::get('/groupes/{id}', [GroupeController::class, 'show'])->name('groupes.show');
    Route::put('/groupes/{id}', [GroupeController::class, 'update'])->name('groupes.update');
    Route::delete('/groupes/{id}', [GroupeController::class, 'destroy'])->name('groupes.destroy');



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
     * Contraintes
     */
    Route::get('/contraintes', [ContrainteController::class, 'index'])->name('contraintes.index');        // Liste des contraintes
    Route::post('/contraintes', [ContrainteController::class, 'store'])->name('contraintes.store');       // Créer une contrainte
    Route::get('/contraintes/{id}', [ContrainteController::class, 'show'])->name('contraintes.show');     // Afficher une contrainte
    Route::put('/contraintes/{id}', [ContrainteController::class, 'update'])->name('contraintes.update'); // Mettre à jour une contrainte
    Route::delete('/contraintes/{id}', [ContrainteController::class, 'destroy'])->name('contraintes.destroy'); // Supprimer une contrainte
    /**
     * liste des chefs departements enseignants et etudiants
     */
    Route::get('/chefs', [UtilisateurController::class, 'getChefsDepartement']);
    Route::get('/enseignants', [UtilisateurController::class, 'getEnseignants']);
    Route::get('/etudiants', [UtilisateurController::class, 'getEtudiants']);


    //salle disponibilite
    Route::get('/salles/{id}/disponibilite', [SalleController::class, 'checkDisponibilite']);
    // --------------------------------------------------------------------------

    // Routes pour les opérations métier spécifiques aux Examens
    // --------------------------------------------------------------------------
    Route::get('/examens/{id}/detecter-conflits', [ExamenController::class, 'detecterConflits']);
    Route::post('/examens/valider', [ExamenController::class, 'valider']);
    Route::post('/examens/publier', [ExamenController::class, 'publier']);
    Route::post('/logout', [AuthController::class, 'logout']);




});
