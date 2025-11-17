<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'date',
        'destinataire_id',
        'type', // info, alerte, modification, validation
        'lu',
        'metadata' // JSON pour données additionnelles
    ];

    protected $casts = [
        'date' => 'datetime',
        'lu' => 'boolean',
        'metadata' => 'array'
    ];

    // Relations
    public function destinataire()
    {
        return $this->belongsTo(Utilisateur::class, 'destinataire_id');
    }

    // Méthodes
    public function envoyer()
    {
        try {
            // 1. Notification dans la base (déjà créée)

            // 2. Notification email
            $this->envoyerEmail();

            // 3. Notification push (optionnel)
            // $this->envoyerPush();

            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur envoi notification: ' . $e->getMessage());
            return false;
        }
    }

    private function envoyerEmail()
    {
        if ($this->destinataire && $this->destinataire->email) {
            Mail::raw($this->message, function ($mail) {
                $mail->to($this->destinataire->email)
                     ->subject('Notification - Gestion des Examens');
            });
        }
    }

    public function marquerCommeLue()
    {
        $this->lu = true;
        $this->save();
    }

    // Méthodes statiques pour création rapide
    public static function notifierExamenCree($examen)
    {
        $etudiants = $examen->groupe->etudiants;

        foreach ($etudiants as $etudiant) {
            $notification = self::create([
                'destinataire_id' => $etudiant->id,
                'message' => "Nouvel examen planifié : {$examen->module->nomModule} le {$examen->date->format('d/m/Y')} à {$examen->heure->format('H:i')}",
                'date' => now(),
                'type' => 'info',
                'metadata' => [
                    'examen_id' => $examen->id,
                    'action' => 'creation'
                ]
            ]);

            $notification->envoyer();
        }

        // Notifier le superviseur
        if ($examen->superviseur) {
            $notification = self::create([
                'destinataire_id' => $examen->superviseur_id,
                'message' => "Vous supervisez l'examen de {$examen->module->nomModule} le {$examen->date->format('d/m/Y')}",
                'date' => now(),
                'type' => 'alerte',
                'metadata' => [
                    'examen_id' => $examen->id,
                    'action' => 'assignation'
                ]
            ]);

            $notification->envoyer();
        }
    }

    public static function notifierConflitDetecte($examen, $conflits)
    {
        // Notifier le responsable planning
        $responsable = Utilisateur::where('role', 'responsable_plan')->first();

        if ($responsable) {
            $messageConflits = implode(', ', array_column($conflits, 'message'));

            $notification = self::create([
                'destinataire_id' => $responsable->id,
                'message' => "Conflit détecté pour l'examen de {$examen->module->nomModule} : {$messageConflits}",
                'date' => now(),
                'type' => 'alerte',
                'metadata' => [
                    'examen_id' => $examen->id,
                    'conflits' => $conflits
                ]
            ]);

            $notification->envoyer();
        }
    }

    public static function notifierValidationPlan($departement)
    {
        // Notifier tous les enseignants du département
        $enseignants = Utilisateur::where('role', 'enseignant')
                                   ->where('departement', $departement)
                                   ->get();

        foreach ($enseignants as $enseignant) {
            $notification = self::create([
                'destinataire_id' => $enseignant->id,
                'message' => "Le planning d'examens du département {$departement} a été validé",
                'date' => now(),
                'type' => 'validation'
            ]);

            $notification->envoyer();
        }
    }

    // Scopes
    public function scopeNonLues($query)
    {
        return $query->where('lu', false);
    }

    public function scopeRecentes($query)
    {
        return $query->where('date', '>=', now()->subDays(7));
    }
}
