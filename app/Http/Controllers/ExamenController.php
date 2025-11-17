<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Examen;

class ExamenController extends Controller
{

     public function index(Request $request)
    {
        $query = Examen::with(['module', 'salle', 'groupe', 'superviseur']);

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('date_debut')) {
            $query->where('date', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->where('date', '<=', $request->date_fin);
        }

        if ($request->has('groupe_id')) {
            $query->where('groupe_id', $request->groupe_id);
        }

        $examens = $query->orderBy('date')->orderBy('heure')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $examens
        ]);
    }

    // Créer un examen (Responsable Plan ou Enseignant)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|after:today',
            'heure' => 'required',
            'type' => 'required|string',
            'niveau' => 'required|string',
            'module_id' => 'required|exists:modules,id',
            'salle_id' => 'required|exists:salles,id',
            'groupe_id' => 'required|exists:groupes,id',
            'superviseur_id' => 'required|exists:utilisateurs,id'
        ]);

        $user = Auth::user();
       // $$examen = Examen::findOrFail($id);

        // Vérifier les permissions
        if (!in_array($user->role, ['responsable_plan', 'enseignant'])) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($user->role === 'responsable_plan') {
            $result = $user->planifierExam($validated);
        } else {
            // Enseignant propose un créneau
            $result = $user->proposerCreneau([$validated]);
        }

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    // Afficher un examen
    public function show($id)
    {
        $examen = Examen::with(['module', 'salle', 'groupe', 'superviseur'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $examen
        ]);
    }

    // Modifier un examen
    public function update(Request $request, $id)
    {
        $examen = Examen::findOrFail($id);

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'heure' => 'sometimes',
            'type' => 'sometimes|string',
            'niveau' => 'sometimes|string',
            'module_id' => 'sometimes|exists:modules,id',
            'salle_id' => 'sometimes|exists:salles,id',
            'groupe_id' => 'sometimes|exists:groupes,id',
            'superviseur_id' => 'sometimes|exists:utilisateurs,id'
        ]);

        $result = $examen->modifier($validated);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    // Supprimer un examen
    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role !== 'responsable_plan') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $examen = Examen::findOrFail($id);
        $examen->delete();

        return response()->json([
            'success' => true,
            'message' => 'Examen supprimé'
        ]);
    }

    // Détecter les conflits
    public function detecterConflits($id)
    {
        $examen = Examen::findOrFail($id);
        $conflits = $examen->detecterConflit();

        return response()->json([
            'success' => true,
            'conflits' => $conflits,
            'has_conflicts' => !empty($conflits)
        ]);
    }

    // Valider des examens (Chef Département)
    public function valider(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'chef_departement') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $examenIds = $request->input('examen_ids', null);
        $examens = $examenIds ? Examen::whereIn('id', $examenIds)->get() : null;

        $result = $user->validerPlan($examens);

        return response()->json($result);
    }

    // Publier le planning (Chef Département)
    public function publier()
    {
        $user = Auth::user();

        if ($user->role !== 'chef_departement') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $result = $user->publierPlan();

        return response()->json($result);
    }

    // Consulter le planning (Étudiant)
    public function monPlanning()
    {
        $user = Auth::user();

        if ($user->role !== 'etudiant') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $examens = $user->consulterExams();

        return response()->json([
            'success' => true,
            'data' => $examens
        ]);
    }

    // Statistiques
    public function statistiques()
    {
        $user = Auth::user();

        if ($user->role === 'chef_departement') {
            $stats = $user->obtenirStatistiques();
        } else {
            $stats = [
                'total' => Examen::count(),
                'publies' => Examen::where('statut', 'publié')->count(),
                'a_venir' => Examen::aVenir()->count()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
























}
