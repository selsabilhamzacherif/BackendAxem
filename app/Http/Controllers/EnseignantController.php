<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enseignant;
use Illuminate\Support\Facades\Auth;

class EnseignantController extends Controller
{
    /**
     * Afficher la liste des enseignants.
     */
    public function index()
    {
        $enseignants = Enseignant::all();
        return response()->json($enseignants);
    }

    /**
     * Créer un nouvel enseignant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'motDePasse' => 'required|string|min:6',
            'role' => 'required|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
        ]);

        $enseignant = Enseignant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'motDePasse' => bcrypt($request->motDePasse),
            'role' => $request->role,
            'specialite' => $request->specialite,
            'departement' => $request->departement,
        ]);

        return response()->json([
            'message' => 'Enseignant créé avec succès',
            'data' => $enseignant
        ], 201);
    }

    /**
     * Afficher un enseignant spécifique.
     */
    public function show(string $id)
    {
        $enseignant = Enseignant::findOrFail($id);
        return response()->json($enseignant);
    }

    /**
     * Mettre à jour un enseignant.
     */
    public function update(Request $request, string $id)
    {
        $enseignant = Enseignant::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email,' . $enseignant->id,
            'motDePasse' => 'nullable|string|min:6',
            'role' => 'required|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
        ]);

        $data = $request->only(['nom','prenom','email','role','specialite','departement']);
        if ($request->filled('motDePasse')) {
            $data['motDePasse'] = bcrypt($request->motDePasse);
        }

        $enseignant->update($data);

        return response()->json([
            'message' => 'Enseignant mis à jour avec succès',
            'data' => $enseignant
        ]);
    }

    /**
     * Supprimer un enseignant.
     */
    public function destroy(string $id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $enseignant->delete();

        return response()->json([
            'message' => 'Enseignant supprimé avec succès'
        ]);
    }
    public function proposerCreneau(Request $request)
{
    $enseignant = Auth::user(); // enseignant connecté
   //$enseignant=Enseignant::findOrFail($request->id_enseignant);

    // Le front doit envoyer un tableau de 3 créneaux
    $creneaux = $request->input('creneaux');

    $resultats = $enseignant->proposerCreneau($creneaux);

    return response()->json(['resultats' => $resultats], 201);
}
public function signalerContrainte(Request $request)
{
    $enseignant = Auth::user();
    //$enseignant=Enseignant::findOrFail($request->id_enseignant);
    $contrainte = $enseignant->signalerContrainte($request->all());

    return response()->json(['message' => 'Contrainte signalée avec succès', 'contrainte' => $contrainte], 201);
}
public function consulterPlanning()
{
    $enseignant = Auth::user();
   // $enseignant=Enseignant::findOrFail(request()->id_enseignant);
    $planning = $enseignant->consulterPlanning();

    return response()->json(['planning' => $planning], 200);
}



}

