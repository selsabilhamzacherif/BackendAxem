<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Models\Contrainte;
use App\Models\Notification;
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
        'salle_id',
        'groupe_id',
        'superviseur_id',
        'statut' ,             // brouillon, validé, publié
        'reclamation_chef',
        'date_reclamation',
        'date_publication'

    ];

    protected $casts = [
        'date' => 'date',
        'heure' => 'datetime',
        'date_reclamation' => 'datetime',
        'date_publication' => 'datetime'
    ];

    // Relations
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function superviseur()
    {
        return $this->belongsTo(Utilisateur::class, 'superviseur_id');
    }

    // Méthodes métier
    public function detecterConflit()
    {
        $conflits = [];

        // 1. Conflit de salle
        $conflitSalle = Examen::where('salle_id', $this->salle_id)
            ->where('date', $this->date)
            ->where('heure', $this->heure)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($conflitSalle) {
            $conflits[] = [
                'type' => 'salle',
                'message' => "La salle {$this->salle->nomSalle} est déjà réservée à cette date/heure"
            ];
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

        // 5. Capacité salle vs effectif groupe
        if ($this->salle && $this->groupe) {
            $effectifGroupe = $this->groupe->etudiants()->count();
            if ($effectifGroupe > $this->salle->capacite) {
                $conflits[] = [
                    'type' => 'capacite',
                    'message' => "Capacité salle insuffisante ({$this->salle->capacite} places pour {$effectifGroupe} étudiants)"
                ];
            }
        }

        return $conflits;
    }

    public function modifier(array $data)
    {
        DB::beginTransaction();
        try {
            // Mise à jour temporaire pour vérification
            $anciennesDonnees = $this->getAttributes();
            $this->fill($data);

            // Détection des conflits
            $conflits = $this->detecterConflit();

            if (!empty($conflits)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'conflits' => $conflits
                ];
            }

            // Sauvegarde effective
            $this->save();

            // Notification des parties prenantes
            $this->notifierModification($anciennesDonnees);

            DB::commit();
            return [
                'success' => true,
                'examen' => $this->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function notifierModification($anciennesDonnees)
    {
        // Notifier les étudiants du groupe
        $etudiants = $this->groupe->etudiants;
        foreach ($etudiants as $etudiant) {
            Notification::create([
                'destinataire_id' => $etudiant->id,
                'message' => "L'examen de {$this->module->nomModule} a été modifié",
                'date' => now()
            ]);
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

    // Scopes utiles
    public function scopePlanifies($query)
    {
        return $query->where('statut', 'validé')
                     ->orWhere('statut', 'publié');
    }

    public function scopeAVenir($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }
    public static function publierPlanParNiveau($niveau)
{
    // Récupérer tous les examens validés du niveau
    $examens = self::where('niveau', $niveau)
                   ->where('statut', 'validé')
                   ->get();

    // Mettre à jour le statut et la date de publication
    foreach ($examens as $examen) {
        $examen->statut = 'publié';
        $examen->date_publication = now();
        $examen->save();
    }

    return $examens;
}













}
