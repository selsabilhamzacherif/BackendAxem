<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Examen;

class ExamenController extends Controller
{
    // Lister tous les examens avec filtres et pagination
    public function index(Request $request)
    {
        $query = Examen::with(['module', 'salle', 'groupe', 'superviseur']);

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

        $examens = $query->orderBy('date')->orderBy('heure')->get();

        return response()->json([
            'success' => true,
            'data' => $examens
        ]);
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

    // Créer un examen (Responsable plan ou Enseignant ou Public pour debug)
    public function store(Request $request)
    {
        // Validation des champs requis
        $validated = $request->validate([
            'date' => 'required|date|after:today',
            'heure' => 'required',
            'type' => 'required|string',
            'niveau' => 'required|string',
            'module_id' => 'required|exists:modules,id',
            'salle_id' => 'required|exists:salles,id',
            'groupe_id' => 'required|exists:groupes,id',
            'superviseur_id' => 'required|exists:utilisateurs,id',
            'statut' => 'nullable|string',
            'reclamation_chef' => 'nullable|string',
            'date_publication' => 'nullable|date',
            'date_reclamation' => 'nullable|date'
        ]);

        try {
            // Créer l'examen directement
            $examen = Examen::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Examen créé avec succès',
                'data' => $examen
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 400);
        }
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

    // Supprimer un examen (seulement responsable plan)
    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role !== 'responsable_plan') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $examen = Examen::findOrFail($id);
        $examen->delete();

        return response()->json(['success' => true, 'message' => 'Examen supprimé']);
    }

    // Détecter conflits
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

    // Planning étudiant
    public function monPlanning()
    {
        $user = Auth::user();
        if ($user->role !== 'etudiant') {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $examens = $user->consulterExams(); // méthode à implémenter côté Étudiant

        return response()->json(['success' => true, 'data' => $examens]);
    }
}
