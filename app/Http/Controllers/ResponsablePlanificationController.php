<?php

namespace App\Http\Controllers;

use App\Models\ResponsablePlanification;
use Illuminate\Http\Request;

class ResponsablePlanificationController extends Controller
{
    // Liste tous les responsables de planification
    public function index()
    {
        $responsables = ResponsablePlanification::all();
        return response()->json($responsables);
    }

    // Affiche un responsable par son ID
    public function show($id)
    {
        $responsable = ResponsablePlanification::find($id);

        if (!$responsable) {
            return response()->json(['message' => 'Responsable non trouvé'], 404);
        }

        return response()->json($responsable);
    }

    // Crée un nouveau responsable
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'motDePasse' => 'required|string|min:6',
            'role' => 'required|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
        ]);

        $responsable = ResponsablePlanification::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'motDePasse' => bcrypt($validated['motDePasse']),
            'role' => $validated['role'],
            'specialite' => $validated['specialite'] ?? null,
            'departement' => $validated['departement'] ?? null,
        ]);

        return response()->json($responsable, 201);
    }

    // Met à jour un responsable
    public function update(Request $request, $id)
    {
        $responsable = ResponsablePlanification::find($id);

        if (!$responsable) {
            return response()->json(['message' => 'Responsable non trouvé'], 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $id,
            'motDePasse' => 'sometimes|string|min:6',
            'role' => 'sometimes|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
        ]);

        if (isset($validated['motDePasse'])) {
            $validated['motDePasse'] = bcrypt($validated['motDePasse']);
        }

        $responsable->update($validated);

        return response()->json($responsable);
    }

    // Supprime un responsable
    public function destroy($id)
    {
        $responsable = ResponsablePlanification::find($id);

        if (!$responsable) {
            return response()->json(['message' => 'Responsable non trouvé'], 404);
        }

        $responsable->delete();

        return response()->json(['message' => 'Responsable supprimé avec succès']);
    }
}
