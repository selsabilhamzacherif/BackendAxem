<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = ['nomGroupe', 'niveau'];

    public function etudiants()

{
    return $this->hasMany(Utilisateur::class, 'groupe_id')
                ->where('role', 'etudiant');
}

    }
