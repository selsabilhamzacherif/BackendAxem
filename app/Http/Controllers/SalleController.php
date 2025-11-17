<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salle;

class SalleController extends Controller
{
    /**
     * Afficher la liste des salles.
     */
    public function index()
    {
        $salles = Salle::all();
        return response()->json($salles);
    }

    /**
     * Créer une nouvelle salle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomSalle' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'typeSalle' => 'required|string|max:100',
        ]);

        $salle = Salle::create($request->all());

        return response()->json([
            'message' => 'Salle créée avec succès',
            'data' => $salle
        ], 201);
    }

    /**
     * Afficher une salle spécifique.
     */
    public function show(string $id)
    {
        $salle = Salle::findOrFail($id);
        return response()->json($salle);
    }

    /**
     * Mettre à jour une salle.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nomSalle' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'typeSalle' => 'required|string|max:100',
        ]);

        $salle = Salle::findOrFail($id);
        $salle->update($request->all());

        return response()->json([
            'message' => 'Salle mise à jour avec succès',
            'data' => $salle
        ]);
    }

    /**
     * Supprimer une salle.
     */
    public function destroy(string $id)
    {
        $salle = Salle::findOrFail($id);
        $salle->delete();

        return response()->json([
            'message' => 'Salle supprimée avec succès'
        ]);
    }
    public function checkDisponibilite(Request $request, $salleId)
{
    $salle = Salle::findOrFail($salleId);

    $disponible = $salle->verifierDisponibilite($request->date, $request->heure);

    if ($disponible) {
        return response()->json(['message' => 'Salle disponible'], 200);
    } else {
        return response()->json(['message' => 'Salle occupée'], 409);
    }
}
}
