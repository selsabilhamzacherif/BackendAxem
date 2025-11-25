<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrainte extends Model
{
    protected $fillable = ['enseignant_id', 'motif', 'date', 'heure'];

    public function enseignant()
    {
        return $this->belongsTo(Utilisateur::class, 'enseignant_id');
    }
}
