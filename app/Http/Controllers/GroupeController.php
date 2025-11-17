<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Groupe;

class GroupeController extends Controller
{
    /**
     * Afficher la liste des groupes.
     */
    public function index()
    {
        $groupes = Groupe::all();
        return response()->json($groupes);
    }

    /**
     * Créer un nouveau groupe.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $groupe = Groupe::create($request->all());

        return response()->json([
            'message' => 'Groupe créé avec succès',
            'data' => $groupe
        ], 201);
    }

    /**
     * Afficher un groupe spécifique.
     */
    public function show(string $id)
    {
        $groupe = Groupe::findOrFail($id);
        return response()->json($groupe);
    }

    /**
     * Mettre à jour un groupe existant.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $groupe = Groupe::findOrFail($id);
        $groupe->update($request->all());

        return response()->json([
            'message' => 'Groupe mis à jour avec succès',
            'data' => $groupe
        ]);
    }

    /**
     * Supprimer un groupe.
     */
    public function destroy(string $id)
    {
        $groupe = Groupe::findOrFail($id);
        $groupe->delete();

        return response()->json([
            'message' => 'Groupe supprimé avec succès'
        ]);
    }
}
