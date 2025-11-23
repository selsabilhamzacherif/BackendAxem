<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etudiant extends Utilisateur
{
    // Include parent fields plus student-specific fields so mass-assignment works
    protected $fillable = [
        'nom', 'prenom', 'email', 'motDePasse', 'role', 'specialite', 'departement',
        'matricule', 'groupe_id'
    ];

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function consulterExams()
    {
        return Examen::where('groupe_id', $this->groupe_id)
                     ->orderBy('date', 'asc')
                     ->orderBy('heure', 'asc')
                     ->get();
    }
    public function telechargerPlanning()
{
    // On récupère les examens du groupe de l'étudiant
    return $this->consulterExams();
}

    public function consulterGroupe()
    {
        return $this->groupe()->first();
    }
}
