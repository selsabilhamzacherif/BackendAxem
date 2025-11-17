<?php

namespace App\Http\Controllers;

use App\Models\ChefDepartement;
use App\Models\Examen;
use Illuminate\Http\Request;

class ChefDepartementController extends Controller
{
    /**
     * Afficher la liste des chefs de département
     */
    public function index()
    {
        return response()->json(ChefDepartement::all(), 200);
    }

    /**
     * Créer un chef de département
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'email' => 'required|email|unique:utilisateurs,email',
            'password' => 'required|string|min_length:6',
            'departement' => 'required|string'
        ]);

        $data['role'] = 'chef_departement';

        $chef = ChefDepartement::create($data);

        return response()->json([
            'success' => true,
            'chef' => $chef
        ], 201);
    }

    /**
     * Afficher un chef
     */
    public function show($id)
    {
        $chef = ChefDepartement::findOrFail($id);
        return response()->json($chef, 200);
    }

    /**
     * Modifier un chef
     */
    public function update(Request $request, $id)
    {
        $chef = ChefDepartement::findOrFail($id);

        $chef->update($request->all());

        return response()->json([
            'success' => true,
            'chef' => $chef
        ], 200);
    }

    /**
     * Supprimer un chef
     */
    public function destroy($id)
    {
        $chef = ChefDepartement::findOrFail($id);
        $chef->delete();

        return response()->json(['success' => true], 200);
    }

    /* --------------------------------------------------------------------------
       -------------------- MÉTHODES MÉTIER SPÉCIALES ---------------------------
       -------------------------------------------------------------------------- */

    /**
     * Valider un plan d'examens (du département du chef)
     */
    public function validerPlan(Request $request, $chefId)
    {
        $chef = ChefDepartement::findOrFail($chefId);

        $examens = $request->has('examens')
            ? Examen::whereIn('id', $request->examens)->get()
            : null;

        $result = $chef->validerPlan($examens);

        return response()->json($result);
    }

    /**
     * Publier le plan validé
     */
    public function publierPlan($chefId)
    {
        $chef = ChefDepartement::findOrFail($chefId);
        $result = $chef->publierPlan();

        return response()->json($result);
    }

    /**
     * Consulter les examens du département
     */
    public function consulterExamensDepartement($chefId)
    {
        $chef = ChefDepartement::findOrFail($chefId);
        $examens = $chef->consulterExamensDepartement();

        return response()->json($examens);
    }

    /**
     * Annuler la validation d’un examen
     */
    public function annulerValidation(Request $request, $chefId)
    {
        $chef = ChefDepartement::findOrFail($chefId);

        $data = $request->validate([
            'examen_id' => 'required|integer',
            'motif' => 'required|string'
        ]);

        $result = $chef->annulerValidation($data['examen_id'], $data['motif']);

        return response()->json($result);
    }
}
