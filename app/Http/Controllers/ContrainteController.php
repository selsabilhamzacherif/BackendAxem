<?php
namespace App\Http\Controllers;

use App\Models\Contrainte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContrainteController extends Controller
{
    // Ajouter une contrainte
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'heure' => 'required',
            'motif' => 'nullable|string'
        ]);

        $contrainte = Contrainte::create([
            'enseignant_id' => Auth::id(),
            'date' => $request->date,
            'heure' => $request->heure,
            'motif' => $request->motif,
        ]);

        return response()->json([
            'message' => 'Contrainte signalée avec succès',
            'contrainte' => $contrainte
        ], 201);
    }

    // Lister les contraintes de l’enseignant connecté
    public function index()
    {
        $contraintes = Contrainte::where('enseignant_id', Auth::id())->get();

        return response()->json($contraintes);
    }

    // Supprimer une contrainte
    public function destroy($id)
    {
        $contrainte = Contrainte::where('enseignant_id', Auth::id())->findOrFail($id);
        $contrainte->delete();

        return response()->json(['message' => 'Contrainte supprimée avec succès']);
    }
}
