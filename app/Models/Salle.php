<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $fillable = ['nomSalle', 'capacite', 'typeSalle']; // typeSalle peut être enum (amphi, salle TP, etc.)

    /**
     * Relation many-to-many avec Examen
     * Une salle peut accueillir plusieurs examens,
     * et un examen peut se dérouler dans plusieurs salles.
     */
    public function examens()
    {
        return $this->belongsToMany(Examen::class, 'examen_salle');
    }

    /**
     * Vérifie si la salle est disponible à une date et une heure données.
     * Retourne true si libre, false si occupée.
     */
    public function verifierDisponibilite($date, $heure)
    {
        return !$this->examens()
            ->where('date', $date)
            ->where('heure', $heure)
            ->exists();
    }
}
