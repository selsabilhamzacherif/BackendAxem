<?php
namespace App\Models;
use Carbon\Carbon;

class ResponsablePlanification extends Utilisateur
{
    public function gererComptes(array $data, string $action = 'create')
{
    switch ($action) {
        case 'create':
            // Création d’un nouvel utilisateur
            return Utilisateur::create($data);

        case 'update':
            // Mise à jour d’un utilisateur existant
            $user = Utilisateur::find($data['id']);
            if ($user) {
                $user->update($data);
                return $user;
            }
            break;

        case 'delete':
            // Suppression d’un utilisateur
            $user = Utilisateur::find($data['id']);
            if ($user) {
                $user->delete();
                return true;
            }
            break;
    }

    return false;
}


    public function gererSalles(array $data, string $action = 'create')
{
    switch ($action) {
        case 'create':
            return Salle::create($data);

        case 'update':
            $salle = Salle::find($data['id']);
            if ($salle) {
                $salle->update($data);
                return $salle;
            }
            break;

        case 'delete':
            $salle = Salle::find($data['id']);
            if ($salle) {
                $salle->delete();
                return true;
            }
            break;
    }

    return false;
}

    public function planifierAutomatiquement(array $data)
{
    $examen = new Examen($data);

    // Vérifier les conflits initiaux
    $conflits = $examen->detecterConflit();

    if (empty($conflits)) {
        $examen->save();
        return ['success' => true, 'examen' => $examen];
    }

    // Résolution automatique
    foreach ($conflits as $conflit) {
        switch ($conflit['type']) {
            case 'salle':
                // Trouver une autre salle disponible
                $salleLibre = Salle::where('capacite', '>=', $examen->groupe->etudiants()->count())
                    ->get()
                    ->first(fn($salle) => $salle->verifierDisponibilite($examen->date, $examen->heure));
                if ($salleLibre) {
                    $examen->salle_id = $salleLibre->id;
                }
                break;

            case 'superviseur':
            case 'contrainte':
                // Trouver un autre superviseur disponible
                $superviseurLibre = Utilisateur::where('role', 'enseignant')
                    ->get()
                    ->first(function($enseignant) use ($examen) {
                        return !$enseignant->examens()
                            ->where('date', $examen->date)
                            ->where('heure', $examen->heure)
                            ->exists()
                            && !Contrainte::where('enseignant_id', $enseignant->id)
                                ->where('date', $examen->date)
                                ->where('heure', $examen->heure)
                                ->exists();
                    });
                if ($superviseurLibre) {
                    $examen->superviseur_id = $superviseurLibre->id;
                }
                break;

            case 'groupe':
                // Décaler l'heure (exemple simple : +2h)
                $examen->heure = Carbon::parse($examen->heure)->addHours(2);
                break;

            case 'capacite':
                // Choisir une salle plus grande
                $salleGrande = Salle::where('capacite', '>=', $examen->groupe->etudiants()->count())
                    ->get()
                    ->first(fn($salle) => $salle->verifierDisponibilite($examen->date, $examen->heure));
                if ($salleGrande) {
                    $examen->salle_id = $salleGrande->id;
                }
                break;
        }
    }

    // Vérifier à nouveau
    $conflitsFinal = $examen->detecterConflit();
    if (empty($conflitsFinal)) {
        $examen->save();
        return ['success' => true, 'examen' => $examen];
    }

    return ['success' => false, 'conflits' => $conflitsFinal];
}

}
