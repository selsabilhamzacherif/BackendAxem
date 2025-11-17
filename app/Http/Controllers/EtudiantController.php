<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Etudiant;
use Barryvdh\DomPDF\Facade\Pdf;

class EtudiantController extends Controller
{
    /**
     * Afficher la liste des étudiants.
     */
    public function index()
    {
        $etudiants = Etudiant::all();
        return response()->json($etudiants);
    }

    /**
     * Créer un nouvel étudiant.
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
            'matricule' => 'required|string|unique:etudiants,matricule',
            'groupe_id' => 'nullable|exists:groupes,id',
        ]);

        $etudiant = Etudiant::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'motDePasse' => bcrypt($request->motDePasse),
            'role' => $request->role,
            'specialite' => $request->specialite,
            'departement' => $request->departement,
            'matricule' => $request->matricule,
            'groupe_id' => $request->groupe_id,
        ]);

        return response()->json([
            'message' => 'Étudiant créé avec succès',
            'data' => $etudiant
        ], 201);
    }

    /**
     * Afficher un étudiant spécifique.
     */
    public function show(string $id)
    {
        $etudiant = Etudiant::findOrFail($id);
        return response()->json($etudiant);
    }

    /**
     * Mettre à jour un étudiant.
     */
    public function update(Request $request, string $id)
    {
        $etudiant = Etudiant::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email,' . $etudiant->id,
            'motDePasse' => 'nullable|string|min:6',
            'role' => 'required|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
            'matricule' => 'required|string|unique:etudiants,matricule,' . $etudiant->id,
            'groupe_id' => 'nullable|exists:groupes,id',
        ]);

        $data = $request->only([
            'nom','prenom','email','role','specialite','departement','matricule','groupe_id'
        ]);

        if ($request->filled('motDePasse')) {
            $data['motDePasse'] = bcrypt($request->motDePasse);
        }

        $etudiant->update($data);

        return response()->json([
            'message' => 'Étudiant mis à jour avec succès',
            'data' => $etudiant
        ]);
    }

    /**
     * Supprimer un étudiant.
     */
    public function destroy(string $id)
    {
        $etudiant = Etudiant::findOrFail($id);
        $etudiant->delete();

        return response()->json([
            'message' => 'Étudiant supprimé avec succès'
        ]);
    }

    public function consulterExams()
{
    //$etudiant = Auth::user();
     $etudiant=Etudiant::findOrFail(request()->id_etudiant);
    return response()->json($etudiant->consulterExams());
}

    public function telechargerPlanning()
{
    //$etudiant = Auth::user(); // étudiant connecté
    $etudiant=Etudiant::findOrFail(request()->id_etudiant);
    $planning = $etudiant->telechargerPlanning();

    // On renvoie les données en JSON
    return response()->json([
        'planning' => $planning
    ], 200);
}
public function consulterGroupe()
{      //$etudiant = Auth::user();
     $etudiant=Etudiant::findOrFail(request()->id_etudiant);
    return response()->json($etudiant->consulterGroupe());
}

}
