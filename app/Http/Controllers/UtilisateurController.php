<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;

class UtilisateurController extends Controller
{
    /**
     * Afficher la liste des utilisateurs.
     */
    public function index()
    {
        $utilisateurs = Utilisateur::all();
        return response()->json($utilisateurs);
    }

    /**
     * Créer un nouvel utilisateur.
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

        $utilisateur = Utilisateur::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'motDePasse' => bcrypt($request->motDePasse),
            'role' => $request->role,
            'specialite' => $request->specialite,
            'departement' => $request->departement,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'data' => $utilisateur
        ], 201);
    }

    /**
     * Afficher un utilisateur spécifique.
     */
    public function show(string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        return response()->json($utilisateur);
    }

    /**
     * Mettre à jour un utilisateur.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email,' . $id,
            'motDePasse' => 'nullable|string|min:6',
            'role' => 'required|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
        ]);

        $utilisateur = Utilisateur::findOrFail($id);

        $data = $request->only(['nom','prenom','email','role','specialite','departement']);
        if ($request->filled('motDePasse')) {
            $data['motDePasse'] = bcrypt($request->motDePasse);
        }

        $utilisateur->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $utilisateur
        ]);
    }

    /**
     * Supprimer un utilisateur.
     */
    public function destroy(string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}
