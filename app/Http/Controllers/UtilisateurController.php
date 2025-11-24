<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    /*-----------------------------------------*
     | CRUD UTILISATEURS                        |
     *-----------------------------------------*/

    public function index()
    {
        return response()->json(Utilisateur::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:utilisateurs',
            'motDePasse' => 'required|string|min:6',
            'role' => 'required|string',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
            'matricule' => 'nullable|string',
            'groupe_id' => 'nullable|integer',
        ]);

        $data['motDePasse'] = Hash::make($data['motDePasse']);

        $user = Utilisateur::create($data);
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = Utilisateur::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);
        $data = $request->all();

        if(isset($data['motDePasse'])){
            $data['motDePasse'] = Hash::make($data['motDePasse']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = Utilisateur::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }

    public function getByRole($role)
    {
        $users = Utilisateur::where('role', $role)->get();
        return response()->json($users);
    }


    /*-----------------------------------------*
     | ACTIONS POUR ETUDIANT                    |
     *-----------------------------------------*/

    public function consulterExams($id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEtudiant()) return response()->json(['error' => 'Non autorisé'], 403);

        return response()->json($user->etudiant_consulterExams());
    }

    public function telechargerPlanning($id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEtudiant()) return response()->json(['error' => 'Non autorisé'], 403);

        return response()->json($user->etudiant_telechargerPlanning());
    }

    public function consulterGroupe($id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEtudiant()) return response()->json(['error' => 'Non autorisé'], 403);

        return response()->json($user->etudiant_consulterGroupe());
    }


    /*-----------------------------------------*
     | ACTIONS POUR ENSEIGNANT                  |
     *-----------------------------------------*/

    public function proposerCreneau(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEnseignant()) return response()->json(['error' => 'Non autorisé'], 403);

        $creneaux = $request->input('creneaux', []);
        return response()->json($user->enseignant_proposerCreneau($creneaux));
    }

    public function signalerContrainte(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEnseignant()) return response()->json(['error' => 'Non autorisé'], 403);

        return response()->json($user->enseignant_signalerContrainte($request->all()));
    }

    public function consulterPlanningEnseignant($id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEnseignant()) return response()->json(['error' => 'Non autorisé'], 403);

        return response()->json($user->enseignant_consulterPlanning());
    }


    /*-----------------------------------------*
     | ACTIONS POUR RESPONSABLE PLANIFICATION  |
     *-----------------------------------------*/

    public function gererComptes(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isResponsable()) return response()->json(['error' => 'Non autorisé'], 403);

        $action = $request->input('action', 'create');
        $data = $request->all();

        return response()->json($user->resp_gererComptes($data, $action));
    }

    public function gererSalles(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isResponsable()) return response()->json(['error' => 'Non autorisé'], 403);

        $action = $request->input('action', 'create');
        $data = $request->all();

        return response()->json($user->resp_gererSalles($data, $action));
    }

    public function planifierAutomatiquement(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isResponsable()) return response()->json(['error' => 'Non autorisé'], 403);

        $data = $request->all();

        return response()->json($user->resp_planifierAutomatiquement($data));
    }


    /*-----------------------------------------*
     | ACTIONS POUR CHEF DEPARTEMENT           |
     *-----------------------------------------*/

    public function validerExamens(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isChef()) return response()->json(['error' => 'Non autorisé'], 403);

        $niveau = $request->input('niveau');
        $examens = $request->input('examens', null);

        return response()->json($user->chef_validerExamens($niveau, $examens));
    }
}
        
