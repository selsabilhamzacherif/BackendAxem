<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Utilisateur extends Authenticatable
{
    use HasFactory;

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


    /*-----------------------------------------------*
     |        METHODES POUR ETUDIANT                 |
     *-----------------------------------------------*/

    public function etudiant_consulterExams()
    {
        if (!$this->isEtudiant()) return null;

        return Examen::where('groupe_id', $this->groupe_id)
            ->orderBy('date')
            ->orderBy('heure')
            ->get();
    }

    public function etudiant_telechargerPlanning()
    {
        return $this->etudiant_consulterExams();
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
            $examen = new Examen($data);
            $examen->enseignant_id = $this->id;

            if (!$examen->salle->verifierDisponibilite($examen->date, $examen->heure)) {
                $propositions[] = [
                    'status' => 'error',
                    'message' => "Salle non dispo {$examen->date} {$examen->heure}"
                ];
            } else {
                $examen->save();
                $propositions[] = [
                    'status' => 'success',
                    'message' => "Créneau proposé",
                    'examen' => $examen
                ];
            }
        }

        return $propositions;
    }

    public function enseignant_signalerContrainte($data)
    {
        if (!$this->isEnseignant()) return null;

        return $this->contraintes()->create([
            'date' => $data['date'],
            'heure' => $data['heure'],
            'motif' => $data['motif']
        ]);
    }

    public function enseignant_consulterPlanning()
    {
        if (!$this->isEnseignant()) return null;

        return Examen::where('enseignant_id', $this->id)
            ->orderBy('date')
            ->orderBy('heure')
            ->get();
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

            case 'create': return Salle::create($data);

            case 'update':
                $salle = Salle::find($data['id']);
                if ($salle) { $salle->update($data); return $salle; }
                break;

            case 'delete':
                $salle = Salle::find($data['id']);
                if ($salle) { $salle->delete(); return true; }
                break;
        }

        return false;
    }

    public function resp_planifierAutomatiquement(array $data)
    {
        if (!$this->isResponsable()) return null;

        $examen = new Examen($data);
        $conflits = $examen->detecterConflit();

        if (empty($conflits)) {
            $examen->save();
            return ['success' => true, 'examen' => $examen];
        }

        foreach ($conflits as $c) {

            switch ($c['type']) {

                case 'salle':
                    $salleLibre = Salle::where('capacite', '>=', $examen->groupe->etudiants()->count())
                        ->get()
                        ->first(fn($s) => $s->verifierDisponibilite($examen->date, $examen->heure));
                    if ($salleLibre) $examen->salle_id = $salleLibre->id;
                    break;

                case 'superviseur':
                case 'contrainte':
                    $superviseurLibre = Utilisateur::where('role', self::ROLE_ENSEIGNANT)
                        ->get()
                        ->first(function($ens) use ($examen) {
                            return !$ens->examens()
                                ->where('date', $examen->date)
                                ->where('heure', $examen->heure)
                                ->exists()
                                && !Contrainte::where('enseignant_id', $ens->id)
                                    ->where('date', $examen->date)
                                    ->where('heure', $examen->heure)
                                    ->exists();
                        });
                    if ($superviseurLibre) $examen->superviseur_id = $superviseurLibre->id;
                    break;

                case 'groupe':
                    $examen->heure = Carbon::parse($examen->heure)->addHours(2);
                    break;

                case 'capacite':
                    $salleGrande = Salle::where('capacite', '>=', $examen->groupe->etudiants()->count())
                        ->get()
                        ->first(fn($s) => $s->verifierDisponibilite($examen->date, $examen->heure));
                    if ($salleGrande) $examen->salle_id = $salleGrande->id;
                    break;
            }
        }

        $reCheck = $examen->detecterConflit();

        if (empty($reCheck)) {
            $examen->save();
            return ['success' => true, 'examen' => $examen];
        }

        return ['success' => false, 'conflits' => $reCheck];
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
}

