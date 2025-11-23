<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Utilisateur extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nom', 'prenom', 'email', 'motDePasse', 'role', 'specialite', 'departement' , 'matricule','groupe_id'
    ];

    protected $hidden = ['motDePasse'];

    // Relation avec Notification
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }

    // Relation avec Examens (pour enseignant, étudiant, etc.)
    public function examens()
    {
        return $this->hasMany(Examen::class, 'superviseur_id'); // selon rôle
    }
}
