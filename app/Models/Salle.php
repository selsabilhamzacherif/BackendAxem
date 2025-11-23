<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $fillable = ['nomSalle', 'capacite', 'typeSalle'];//enum

     public function verifierDisponibilite($date, $heure)
    {
        return !$this->examens()
            ->where('date', $date)
            ->where('heure', $heure)
            ->exists();
    }
}
