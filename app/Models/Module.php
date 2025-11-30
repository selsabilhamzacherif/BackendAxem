<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['nomModule', 'semestre','enseignant_id'];

    public function examens()
    {
        return $this->hasMany(Examen::class);
    }
        public function enseignant()
    {
        return $this->belongsTo(\App\Models\Utilisateur::class, 'enseignant_id');
    }
}
