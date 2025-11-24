<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Examen extends Model
{
    use HasFactory;

    protected $table = 'examens';

    protected $fillable = [
        'date',
        'heure',
        'type',
        'niveau',
        'module_id',
        'groupe_id',
        'superviseur_id',
        'statut' // brouillon, validé, publié
       // 'reclamation_chef',
       // 'date_reclamation',
       // 'date_publication'

    ];

    protected $casts = [
        'date' => 'date',
        'heure' => 'datetime'
    ];

    // ---------------- Relations ----------------

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function superviseur()
    {
        return $this->belongsTo(Utilisateur::class, 'superviseur_id');
    }

    /**
     * Relation many-to-many avec Salle
     * Un examen peut se dérouler dans plusieurs salles.
     */
    public function salles()
    {
        return $this->belongsToMany(Salle::class, 'examen_salle');
    }

    /**
     * Détecter les conflits pour cet examen
     * Retourne un tableau de conflits détectés
     */
    public function detecterConflit()
    {
        $conflits = [];

        // 1. Conflit de salle
        foreach ($this->salles as $salle) {
            $conflitSalle = $salle->examens()
                ->where('date', $this->date)
                ->where('heure', $this->heure)
                ->where('id', '!=', $this->id ?? 0)
                ->exists();

            if ($conflitSalle) {
                $conflits[] = [
                    'type' => 'salle',
                    'message' => "La salle {$salle->nomSalle} est déjà réservée à cette date/heure"
                ];
            }

            // Vérifier capacité salle vs effectif groupe
            if ($this->groupe) {
                $effectifGroupe = $this->groupe->etudiants()->count();
                if ($effectifGroupe > $salle->capacite) {
                    $conflits[] = [
                        'type' => 'capacite',
                        'message' => "Capacité insuffisante ({$salle->capacite} places pour {$effectifGroupe} étudiants)"
                    ];
                }
            }
        }

        // 2. Conflit superviseur
        $conflitSuperviseur = Examen::where('superviseur_id', $this->superviseur_id)
            ->where('date', $this->date)
            ->where('heure', $this->heure)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($conflitSuperviseur) {
            $conflits[] = [
                'type' => 'superviseur',
                'message' => "Le superviseur est déjà assigné à un autre examen"
            ];
        }

        // 3. Conflit groupe
        $conflitGroupe = Examen::where('groupe_id', $this->groupe_id)
            ->where('date', $this->date)
            ->where('heure', $this->heure)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($conflitGroupe) {
            $conflits[] = [
                'type' => 'groupe',
                'message' => "Le groupe a déjà un examen prévu à cette date/heure"
            ];
        }

        // 4. Contraintes enseignant
        if ($this->superviseur_id) {
            $contraintes = Contrainte::where('enseignant_id', $this->superviseur_id)
                ->where('date', $this->date)
                ->get();

            foreach ($contraintes as $contrainte) {
                $heureExamen = Carbon::parse($this->heure);
                $heureContrainte = Carbon::parse($contrainte->heure);

                if ($heureExamen->equalTo($heureContrainte)) {
                    $conflits[] = [
                        'type' => 'contrainte',
                        'message' => "Contrainte enseignant : {$contrainte->motif}"
                    ];
                }
            }
        }

        return $conflits;
    }

    /**
     * Modifier un examen avec vérification des conflits
     */
    public function modifier(array $data)
    {
        DB::beginTransaction();
        try {
            $anciennesDonnees = $this->getAttributes();
            $this->fill($data);

            $conflits = $this->detecterConflit();

            if (!empty($conflits)) {
                DB::rollBack();
                // Restaurer anciennes données en mémoire
                $this->fill($anciennesDonnees);
                return [
                    'success' => false,
                    'conflits' => $conflits
                ];
            }

            $this->save();
            $this->notifierModification($anciennesDonnees);

            DB::commit();
            return [
                'success' => true,
                'examen' => $this->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->fill($anciennesDonnees);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Notifier les étudiants et superviseur en cas de modification
     */
    private function notifierModification($anciennesDonnees)
    {
        // Notifier les étudiants du groupe
        if ($this->groupe) {
            foreach ($this->groupe->etudiants as $etudiant) {
                Notification::create([
                    'destinataire_id' => $etudiant->id,
                    'message' => "L'examen de {$this->module->nomModule} a été modifié",
                    'date' => now()
                ]);
            }
        }

        // Notifier le superviseur si changé
        if ($this->superviseur_id != $anciennesDonnees['superviseur_id']) {
            Notification::create([
                'destinataire_id' => $this->superviseur_id,
                'message' => "Vous avez été assigné à un nouvel examen : {$this->module->nomModule}",
                'date' => now()
            ]);
        }
    }

    // ---------------- Scopes ----------------

    public function scopePlanifies($query)
    {
        return $query->whereIn('statut', ['validé', 'publié']);
    }

    public function scopeAVenir($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }
}
