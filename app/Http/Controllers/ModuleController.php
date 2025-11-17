<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;

class ModuleController extends Controller
{
    /**
     * Afficher la liste des modules.
     */
    public function index()
    {
        $modules = Module::all();
        return response()->json($modules);
    }

    /**
     * Créer un nouveau module.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomModule' => 'required|string|max:255',
            'semestre' => 'required|string|max:50',
        ]);

        $module = Module::create($request->all());

        return response()->json([
            'message' => 'Module créé avec succès',
            'data' => $module
        ], 201);
    }

    /**
     * Afficher un module spécifique.
     */
    public function show(string $id)
    {
        $module = Module::findOrFail($id);
        return response()->json($module);
    }

    /**
     * Mettre à jour un module.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nomModule' => 'required|string|max:255',
            'semestre' => 'required|string|max:50',
        ]);

        $module = Module::findOrFail($id);
        $module->update($request->all());

        return response()->json([
            'message' => 'Module mis à jour avec succès',
            'data' => $module
        ]);
    }

    /**
     * Supprimer un module.
     */
    public function destroy(string $id)
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return response()->json([
            'message' => 'Module supprimé avec succès'
        ]);
    }
}
