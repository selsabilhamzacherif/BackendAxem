<?php
namespace App\Models;

class Enseignant extends Utilisateur
{
    public function proposerCreneau(array $creneaux)
{
    $propositions = [];

    foreach ($creneaux as $data) {
        $examen = new Examen($data);
        $examen->enseignant_id = $this->id;

        // Vérifier disponibilité de la salle
        if (!$examen->salle->verifierDisponibilite($examen->date, $examen->heure)) {
            $propositions[] = [
                'status' => 'error',
                'message' => "Salle non disponible pour le créneau {$examen->date} {$examen->heure}"
            ];
        } else {
            $examen->save();
            $propositions[] = [
                'status' => 'success',
                'message' => "Créneau proposé avec succès",
                'examen' => $examen
            ];
        }
    }

    return $propositions;
}
public function signalerContrainte($data)
{
    return $this->contraintes()->create([
        'date' => $data['date'],
        'heure' => $data['heure'],
        'motif' => $data['motif']
    ]);
}

public function contraintes()
{
    return $this->hasMany(Contrainte::class);
}

    public function consulterPlanning()
{
    return Examen::where('enseignant_id', $this->id)
                 ->orderBy('date', 'asc')
                 ->orderBy('heure', 'asc')
                 ->get();
}

}
