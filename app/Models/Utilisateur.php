<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Salle;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
//use Illuminate\Foundation\Auth\User as Authenticatable;




class Utilisateur extends Authenticatable implements JWTSubject
{

        public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    use /*HasApiTokens*/ HasFactory;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom', 'prenom', 'email', 'motDePasse', 'role',
        'specialite', 'departement', 'matricule', 'groupe_id'
    ];

    protected $hidden = ['motDePasse'];


    /*-----------------------------------------------*
     |                    ROLES                     |
     *-----------------------------------------------*/

    const ROLE_ETUDIANT = 'etudiant';
    const ROLE_ENSEIGNANT = 'enseignant';
    const ROLE_CHEF = 'chef_departement';
    const ROLE_RESPONSABLE = 'responsable_plan';


    /*-----------------------------------------------*
     |       HELPERS POUR ROLE (isEtudiant...)       |
     *-----------------------------------------------*/

    public function isEtudiant()       { return $this->role === self::ROLE_ETUDIANT; }
    public function isEnseignant()     { return $this->role === self::ROLE_ENSEIGNANT; }
    public function isChef()           { return $this->role === self::ROLE_CHEF; }
    public function isResponsable()    { return $this->role === self::ROLE_RESPONSABLE; }


    /*-----------------------------------------------*
     |                 RELATIONS                     |
     *-----------------------------------------------*/

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function contraintes()
    {
        return $this->hasMany(Contrainte::class, 'enseignant_id');
    }

    public function examens()
    {
        return $this->hasMany(Examen::class, 'superviseur_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }
    public function modules()
        {
            // un enseignant a plusieurs modules via la colonne modules.enseignant_id
            return $this->hasMany(Module::class, 'enseignant_id');
        }


    /*-----------------------------------------------*
     |        METHODES POUR ETUDIANT                 |
     *-----------------------------------------------*/

    public function consulterExams()
   {
    if (!$this->isEtudiant()) return null;

    return Examen::where('groupe_id', $this->groupe_id)
        ->where('statut', 'publié')
        ->with(['module', 'salle',/*'superviseur'*/])
        ->orderBy('date')
        ->orderBy('heure')
        ->get();
}


   /* public function etudiant_telechargerPlanning()
    {
     if (!$this->isEtudiant()) return null;

    return Examen::where('groupe_id', $this->groupe_id)
        ->where('statut', 'publié')
        ->with(['module', 'salle',/*'superviseur'*///])
      /*  ->orderBy('date')
        ->orderBy('heure')
        ->get();
    }*/

    /**
     * Alias pour compatibility (utilisé par EtudiantController)
     * Retourne uniquement les champs spécifiés
     */
    public function telechargerPlanning()
    {
        if (!$this->isEtudiant()) return null;

        $examens = Examen::where('groupe_id', $this->groupe_id)
            ->where('statut', 'publié')
            ->with(['module', 'salle'])
            ->orderBy('date')
            ->orderBy('heure')
            ->get();

        // Retourner uniquement les champs spécifiés
        return $examens->map(function($examen) {
            return [
                'id' => $examen->id,
                'date' => $examen->date,
                'heure' => $examen->heure,
                'type' => $examen->type,
                'niveau' => $examen->niveau,
                'module' => $examen->module ? ($examen->module->nomModule ?? null) : null,
                'salle' => $examen->salle ? ($examen->salle->nomSalle ?? null) : null
            ];
        })->toArray();
    }

    public function etudiant_consulterGroupe()
    {
        return $this->groupe;
    }


    /*-----------------------------------------------*
     |        METHODES POUR ENSEIGNANT               |
     *-----------------------------------------------*/

    public function enseignant_proposerCreneau(array $creneaux)
    {
        if (!$this->isEnseignant()) return null;

        $propositions = [];

        foreach ($creneaux as $data) {
            // Accept lightweight proposals (date+heure only).
            $requiredKeys = ['module_id', 'salle_id', 'groupe_id', 'type', 'niveau'];
            $hasAll = true;
            foreach ($requiredKeys as $k) {
                if (empty($data[$k])) { $hasAll = false; break; }
            }

            if (!$hasAll) {
                // Return a non-persisted proposal so the client can present it.
                $propositions[] = [
                    //'status' => 'proposed',
                   // 'message' => 'Créneau proposé (non persisté) — fournir module_id/salle_id/groupe_id/type/niveau pour persister',
                    'date' => $data['date'] ?? null,
                    'heure' => $data['heure'] ?? null
                ];
                continue;
            }

            $examen = new Examen($data);
            $examen->enseignant_id = $this->id;

            $salle = isset($examen->salle_id) ? Salle::find($examen->salle_id) : null;

            if (!$salle) {
                $propositions[] = [
                    'status' => 'error',
                    'message' => "Salle non spécifiée ou introuvable pour {$examen->date} {$examen->heure}"
                ];
                continue;
            }

            try {
                if (!$salle->verifierDisponibilite($examen->date, $examen->heure)) {
                    $propositions[] = [
                        'status' => 'error',
                        'message' => "Salle non dispo {$examen->date} {$examen->heure}"
                    ];
                    continue;
                }

                $examen->salle_id = $salle->id;
                $examen->save();
                $propositions[] = [
                    'status' => 'success',
                    'message' => "Créneau proposé",
                    'examen' => $examen
                ];
            } catch (\Exception $e) {
                $propositions[] = [
                    'status' => 'error',
                    'message' => "Erreur lors de la proposition: " . $e->getMessage()
                ];
            }
        }

        return $propositions;
    }


        public function enseignant_signalerContrainte($data)
        {
            if (!$this->isEnseignant()) return null;

            return $this->contraintes()->create([
                'motif' => $data['motif']
            ]);
        }

        public function enseignant_consulterPlanning()
        {
            if (!$this->isEnseignant()) return null;

            return Examen::where('superviseur_id', $this->id) // ou enseignant_id si tu utilises ça
                ->where('statut', 'publié')
                ->with(['module', 'salle', 'groupe'])
                ->orderBy('date')
                ->orderBy('heure')
                ->get()
                ->map(function($examen) {
                    return [
                        'id' => $examen->id,
                        'date' => $examen->date,
                        'heure' => $examen->heure,
                        'type' => $examen->type,
                        'niveau' => $examen->niveau,
                        'module' => $examen->module?->nomModule,
                        'salle' => $examen->salle?->nomSalle,
                        'groupe' => $examen->groupe?->nomGroupe,
                        'statut' => $examen->statut,
                        'date_publication' => $examen->date_publication,
                    ];
                });
        }
        public function enseignant_consulterExamensModules()
        {
            if (!$this->isEnseignant()) return null;

            // récupérer les IDs des modules que l’enseignant enseigne
            $modulesIds = $this->modulesEnseignant()->pluck('modules.id');

            // récupérer les examens liés à ces modules
            $examens = Examen::whereIn('module_id', $modulesIds)
                ->where('statut', 'publié')
                ->with(['module', 'salle', 'groupe'])
                ->orderBy('date')
                ->orderBy('heure')
                ->get();

            return $examens->map(function ($examen) {
                return [
                    'id' => $examen->id,
                    'date' => $examen->date,
                    'heure' => $examen->heure,
                    'type' => $examen->type,
                    'niveau' => $examen->niveau,
                    'module' => $examen->module?->nomModule,
                    'salle' => $examen->salle?->nomSalle,
                    'groupe' => $examen->groupe?->nomGroupe,
                    'statut' => $examen->statut,
                ];
            });
        }





    /*-----------------------------------------------*
     |   METHODES POUR RESPONSABLE PLANIFICATION     |
     *-----------------------------------------------*/

    public function resp_gererComptes(array $data, string $action = 'create')
    {
        if (!$this->isResponsable()) return null;
        switch ($action) {

            case 'create':
                return Utilisateur::create($data);

            case 'update':
                $user = Utilisateur::find($data['id']);
                if ($user) { $user->update($data); return $user; }
                break;

            case 'delete':
                $user = Utilisateur::find($data['id']);
                if ($user) { $user->delete(); return true; }
                break;
        }

        return false;
    }

    public function resp_gererSalles(array $data, string $action = 'create')
{
    if (!$this->isResponsable()) return null;

    switch ($action) {


        case 'create':
            return Salle::create($data);


        case 'update':
            $salle = Salle::find($data['id']);
            if ($salle) {
                $salle->update($data);
                return $salle;
            }
            return ['error' => 'Salle introuvable'];


        case 'delete':
            $salle = Salle::find($data['id']);
            if ($salle) {
                $salle->delete();
                return ['success' => true];
            }
            return ['error' => 'Salle introuvable'];
    }

    return ['error' => 'Action invalide'];
}


        public function resp_planifierAutomatiquementPourNiveau($niveau)
        {
            if (!$this->isResponsable()) return null;

            $examensCree = [];

            // Récupérer tous les modules du niveau
            $modules = Module::where('niveau', $niveau)->get();

            // Récupérer tous les groupes du niveau
            $groupes = Groupe::where('niveau', $niveau)->get();

            foreach ($groupes as $groupe) {
                foreach ($modules as $module) {

                    $examen = new Examen([
                        'module_id' => $module->id,
                        'groupe_id' => $groupe->id,
                        'type' => 'Final',
                        'statut' => 'brouillon'
                    ]);

                    // Date et heure automatiques : exemple = jour libre à 08:00
                    $examen->date = Carbon::now()->addDays(rand(1, 30))->toDateString();
                    $examen->heure = "08:00";

                    // Superviseur disponible
                    $enseignants = Utilisateur::where('role', Utilisateur::ROLE_ENSEIGNANT)->get();
                    foreach ($enseignants as $ens) {
                        if (!$ens->examens()->where('date', $examen->date)->where('heure', $examen->heure)->exists()) {
                            $examen->superviseur_id = $ens->id;
                            break;
                        }
                    }

                    // Salle disponible
                    $salles = Salle::all();
                    foreach ($salles as $salle) {
                        if ($salle->verifierDisponibilite($examen->date, $examen->heure) &&
                            $salle->capacite >= $groupe->etudiants()->count()
                        ) {
                            $examen->salle_id = $salle->id;
                            break;
                        }
                    }

                    // Vérifier les conflits
                    $conflits = $examen->detecterConflit();
                    if (empty($conflits)) {
                        $examen->save();
                        $examensCree[] = $examen;
                    }
                }
            }

            return ['success' => true, 'examens_crees' => $examensCree];
        }

        public function getChefsDepartement()
        {   if (!$this->isResponsable()) return ['success' => false, 'message' => 'Non autorisé'];
            $chefs = Utilisateur::where('role', Utilisateur::ROLE_CHEF)
            ->select('id', 'nom', 'prenom', 'email')
            ->get();

            return response()->json([
                'success' => true,
                'count' => $chefs->count(),
                'chefs' => $chefs
            ]);
        }

        public function getEtudiants()
        {   if (!$this->isResponsable()) return ['success' => false, 'message' => 'Non autorisé'];
            $etudiants = Utilisateur::where('role', Utilisateur::ROLE_ETUDIANT)
            ->select('id', 'nom', 'prenom', 'matricule', 'groupe_id')
            ->get();

            return response()->json([
                'success' => true,
                'count' => $etudiants->count(),
                'etudiants' => $etudiants
            ]);
        }

        public function getEnseignants()
        {   if (!$this->isResponsable()) return ['success' => false, 'message' => 'Non autorisé'];
            $enseignants = Utilisateur::where('role', Utilisateur::ROLE_ENSEIGNANT)
            ->select('id', 'nom', 'prenom', 'email')
            ->get();

            return response()->json([
                'success' => true,
                'count' => $enseignants->count(),
                'enseignants' => $enseignants
            ]);
        }







    /*-----------------------------------------------*
     |   METHODES POUR CHEF DEPARTEMENT             |
     *-----------------------------------------------*/

    public function chef_validerExamens($niveau, $examenIds = null)
    {
        if (!$this->isChef()) return null;

        DB::beginTransaction();

        try {
            $query = Examen::where('niveau', $niveau)
                ->where('statut', 'brouillon');

            if ($examenIds) $query->whereIn('id', $examenIds);

            $examens = $query->get();
            if ($examens->isEmpty()) return ['success' => false, 'message' => "Aucun examen"];

            foreach ($examens as $e) {
                $e->statut = 'validé';
                $e->save();
            }

            $resp = Utilisateur::where('role', self::ROLE_RESPONSABLE)->first();
            if ($resp) {
                Notification::create([
                    'destinataire_id' => $resp->id,
                    'message' => "Chef a validé {$examens->count()} examens",
                    'date' => now(),
                    'type' => 'validation'
                ])->envoyer();
            }

            DB::commit();

            return ['success' => true, 'count' => $examens->count()];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Publier tous les examens validés pour un niveau (Chef de Département)
     */
    public function publierPlanParNiveau($niveau)
    {
        if (!$this->isChef()) return ['success' => false, 'message' => 'Non autorisé'];

        DB::beginTransaction();
        try {
            $examens = Examen::where('niveau', $niveau)
                ->where('statut', 'validé')
                ->get();

            if ($examens->isEmpty()) {
                return ['success' => false, 'message' => "Aucun examen validé pour {$niveau}"];
            }

            foreach ($examens as $examen) {
                $examen->statut = 'publié';
                $examen->date_publication = now();
                $examen->save();
            }

            // Notifications groupées par groupe
        // Notifications groupées par groupe
            $examensParGroupe = $examens->groupBy('groupe_id');

            foreach ($examensParGroupe as $groupeId => $examsGroupe) {

                // Récupérer le groupe
                $groupe = Groupe::find($groupeId);
                if (!$groupe) continue;

                // Pour chaque étudiant du groupe
                foreach ($groupe->etudiants as $etudiant) {

                    Notification::create([
                        'destinataire_id' => $etudiant->id,
                        'message' => "Planning {$niveau} publié : {$examsGroupe->count()} examen(s)",
                        'date' => now(),
                        'type' => 'info'
                    ])->envoyer();
                }
            }




            DB::commit();

            return [
                'success' => true,
                'niveau' => $niveau,
                'examens_publies' => $examens->count(),
                'details' => $examens->map(fn($e) => [
                    'id' => $e->id,
                    'module' => $e->module?->nomModule,
                    'groupe' => $e->groupe?->nomGroupe,
                    'date' => $e->date,
                    'heure' => $e->heure
                ])
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * CONSULTER les examens d'un niveau
     */
    public function consulterExamensParNiveau($niveau)
    {
        return Examen::where('niveau', $niveau)
            ->with(['module', 'salle', 'groupe', 'superviseur'])
            ->orderBy('date')
            ->orderBy('heure')
            ->get();
    }

    /**
     * Refuser des examens avec réclamations (pour le Chef de Département)
     *
     * @param string $niveau
     * @param array $reclamations  // each item: ['examen_id' => id, 'message' => '...']
     * @return array
     */
    public function refuserExamensAvecReclamation($niveau, $reclamations)
    {
        if (!$this->isChef()) return ['success' => false, 'message' => 'Non autorisé'];

        DB::beginTransaction();
        try {
            if (empty($reclamations)) {
                return ['success' => false, 'message' => 'Aucune réclamation fournie'];
            }

            $examensRefuses = [];

            foreach ($reclamations as $reclamation) {
                $examen = Examen::find($reclamation['examen_id']);
                if (!$examen || $examen->niveau !== $niveau) continue;

                $examen->statut = 'à_modifier';
                $examen->reclamation_chef = $reclamation['message'] ?? null;
                $examen->date_reclamation = now();
                $examen->save();

                $examensRefuses[] = [
                    'id' => $examen->id,
                    'module' => $examen->module?->nomModule,
                    'groupe' => $examen->groupe?->nomGroupe,
                    'message' => $reclamation['message'] ?? null
                ];

                // Notifier superviseur
                if ($examen->superviseur_id) {
                    Notification::create([
                        'destinataire_id' => $examen->superviseur_id,
                        'message' => "Examen {$examen->module?->nomModule} refusé par le chef. Motif: {$reclamation['message']}",
                        'date' => now(),
                        'type' => 'alerte',
                        'metadata' => [
                            'examen_id' => $examen->id,
                            'reclamation' => $reclamation['message'] ?? null
                        ]
                    ])->envoyer();
                }
            }

            // Notifier responsable planning
            $responsable = Utilisateur::where('role', self::ROLE_RESPONSABLE)->first();
            if ($responsable && !empty($examensRefuses)) {
                Notification::create([
                    'destinataire_id' => $responsable->id,
                    'message' => "Le chef a refusé ".count($examensRefuses)." examen(s) pour {$niveau}. Modifications demandées.",
                    'date' => now(),
                    'type' => 'alerte',
                    'metadata' => [
                        'niveau' => $niveau,
                        'examens_refuses' => $examensRefuses
                    ]
                ])->envoyer();
            }

            DB::commit();

            return [
                'success' => true,
                'niveau' => $niveau,
                'examens_refuses' => count($examensRefuses),
                'details' => $examensRefuses
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

