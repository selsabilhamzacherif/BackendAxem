<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;

class ChefDepartement extends Utilisateur
{
    // Méthode pour valider un plan d'examens
    public function validerPlan($examens = null)
    {
        DB::beginTransaction();
        try {
            // Si aucun examen spécifié, prendre tous les examens du département en brouillon
            if ($examens === null) {
                $examens = Examen::whereHas('module', function($query) {
                    $query->where('departement', $this->departement);
                })
                ->where('statut', 'brouillon')
                ->get();
            }

            $resultats = [
                'valides' => [],
                'rejetes' => [],
                'conflits' => []
            ];

            foreach ($examens as $examen) {
                // Vérifier les conflits
                $conflits = $examen->detecterConflit();

                if (empty($conflits)) {
                    $examen->statut = 'validé';
                    $examen->save();
                    $resultats['valides'][] = $examen->id;
                } else {
                    $resultats['rejetes'][] = [
                        'examen_id' => $examen->id,
                        'conflits' => $conflits
                    ];

                    // Notifier le responsable planning
                    Notification::notifierConflitDetecte($examen, $conflits);
                }
            }

            // Si tous validés, notifier le département
            if (count($resultats['rejetes']) === 0 && count($resultats['valides']) > 0) {
                Notification::notifierValidationPlan($this->departement);
            }

            DB::commit();

            return [
                'success' => true,
                'resultats' => $resultats,
                'message' => count($resultats['valides']) . " examen(s) validé(s), " .
                            count($resultats['rejetes']) . " rejeté(s)"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Méthode pour publier le plan validé
    public function publierPlan()
    {
        DB::beginTransaction();
        try {
            // Récupérer tous les examens validés du département
            $examens = Examen::whereHas('module', function($query) {
                $query->where('departement', $this->departement);
            })
            ->where('statut', 'validé')
            ->get();

            if ($examens->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucun examen validé à publier'
                ];
            }

            // Publier chaque examen
            foreach ($examens as $examen) {
                $examen->statut = 'publié';
                $examen->save();

                // Notifier tous les étudiants du groupe
                Notification::notifierExamenCree($examen);
            }

            // Créer un résumé du planning publié
            $resume = $this->genererResumePlanning($examens);

            // Notifier tous les utilisateurs concernés
            $this->notifierPublication($examens);

            DB::commit();

            return [
                'success' => true,
                'examens_publies' => $examens->count(),
                'resume' => $resume
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Méthode pour consulter les examens de son département
    public function consulterExamensDepartement()
    {
        return Examen::whereHas('module', function($query) {
            $query->where('departement', $this->departement);
        })
        ->with(['module', 'salle', 'groupe', 'superviseur'])
        ->orderBy('date', 'asc')
        ->orderBy('heure', 'asc')
        ->get();
    }


    // Méthode pour annuler la validation d'un examen
    public function annulerValidation($examenId, $motif)
    {
        try {
            $examen = Examen::findOrFail($examenId);

            // Vérifier que l'examen appartient au département
            if ($examen->module->departement !== $this->departement) {
                return [
                    'success' => false,
                    'message' => 'Cet examen n\'appartient pas à votre département'
                ];
            }

            $examen->statut = 'brouillon';
            $examen->save();

            // Notifier le responsable planning
            $responsable = Utilisateur::where('role', 'responsable_plan')->first();
            if ($responsable) {
                Notification::create([
                    'destinataire_id' => $responsable->id,
                    'message' => "Validation annulée pour l'examen de {$examen->module->nomModule}. Motif: {$motif}",
                    'date' => now(),
                    'type' => 'alerte',
                    'metadata' => [
                        'examen_id' => $examen->id,
                        'motif' => $motif
                    ]
                ])->envoyer();
            }

            return [
                'success' => true,
                'message' => 'Validation annulée avec succès'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function genererResumePlanning($examens)
    {
        return [
            'periode' => [
                'debut' => $examens->min('date'),
                'fin' => $examens->max('date')
            ],
            'statistiques' => [
                'total_examens' => $examens->count(),
                'modules_concernes' => $examens->pluck('module_id')->unique()->count(),
                'groupes_concernes' => $examens->pluck('groupe_id')->unique()->count(),
                'salles_utilisees' => $examens->pluck('salle_id')->unique()->count()
            ]
        ];
    }

    private function notifierPublication($examens)
    {
        // Notifier tous les enseignants du département
        $enseignants = Utilisateur::where('role', 'enseignant')
                                   ->where('departement', $this->departement)
                                   ->get();

        foreach ($enseignants as $enseignant) {
            Notification::create([
                'destinataire_id' => $enseignant->id,
                'message' => "Le planning d'examens a été publié avec {$examens->count()} examens",
                'date' => now(),
                'type' => 'info'
            ])->envoyer();
        }
    }
}
