<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;





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

    if (!$user->isEtudiant()) {
        return response()->json(['error' => 'Accès non autorisé'], 403);
    }

    $examens = $user->consulterExams();

    // Format propre
    $formatted = $examens->map(function($exam){
        return [
            'id' => $exam->id,
            'date' => $exam->date,
            'heure' => $exam->heure,
            'type' => $exam->type,
            'niveau' => $exam->niveau,
            'module' => $exam->module?->nomModule,
            'salle' => $exam->salle?->nomSalle
           // 'superviseur' => $exam->superviseur?->nom . ' ' . $exam->superviseur?->prenom,
        ];
    });

    return response()->json([
        'success' => true,
        'etudiant' => $user->nom . ' ' . $user->prenom,
        'groupe' => $user->groupe?->nomGroupe,
        'examens' => $formatted
    ]);
}


    public function telechargerPlanning($id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEtudiant()) return response()->json(['error' => 'Non autorisé'], 403);

        $examens = $user->telechargerPlanning();

        return response()->json([
            'examens' => $examens
        ]);
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

        // Accept lightweight proposals (only date + heure required).
        $validator = Validator::make($request->all(), [
            'creneaux' => 'required|array|min:1',
            'creneaux.*.date' => 'required|date',
            'creneaux.*.heure' => 'required',
            // optional: module_id, salle_id, groupe_id, type, niveau
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        $creneaux = $request->input('creneaux', []);
        try {
            $result = $user->enseignant_proposerCreneau($creneaux);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erreur proposerCreneau: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function signalerContrainte(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEnseignant()) return response()->json(['error' => 'Non autorisé'], 403);

        $data = $request->validate([
            'motif' => 'required|string',
            'date' => 'nullable|date',
            'heure' => ['nullable','regex:/^([01]\d|2[0-3]):([0-5]\d)(:([0-5]\d))?$/']
        ]);

        return response()->json($user->enseignant_signalerContrainte($data));
    }
        public function consulterPlanningEnseignant($id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isEnseignant())
            return response()->json(['error' => 'Non autorisé'], 403);

        $planning = $user->enseignant_consulterPlanning();
        return response()->json($planning);
    }


    public function consulterEmploiModules()
    {
        $user = auth('api')->user();

        // Vérification du rôle
        if ($user->role !== 'enseignant') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Charger les modules avec examens
        $modules = $user->modules() // relation enseignant -> modules
                        ->with(['examens' => function($query) {
                            $query->orderBy('date')->orderBy('heure');
                        }])
                        ->get();

        // Formater les données
        $result = $modules->map(function ($module) {
            return [
                'nomModule' => $module->nomModule,
                'examens' => $module->examens->map(function ($exam) use ($module) {
                    return [
                        'date' => $exam->date,
                        'heure' => $exam->heure,
                        'salle' => $exam->salle?->nomSalle,
                        'module' => $module->nomModule
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
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

 /*    public function planifierAutomatiquement(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isResponsable()) return response()->json(['error' => 'Non autorisé'], 403);

        $data = $request->all();

        return response()->json($user->resp_planifierAutomatiquement($data));
    }
 */
        public function planifierNiveauAutomatiquement($id, $niveau)
        {
            $user = Utilisateur::findOrFail($id);

            if (!$user->isResponsable())
                return response()->json(['error' => 'Non autorisé'], 403);

            return response()->json($user->resp_planifierAutomatiquementPourNiveau($niveau));
        }


         //FILTRAGE PAR ROLES


        public function getChefsDepartement()
        {
            $chefs = Utilisateur::where('role', Utilisateur::ROLE_CHEF)
            ->select('id', 'nom', 'prenom', 'email')
            ->get();
            return response()->json([
                'success' => true,
                'count' => $chefs->count(),
                'chefs' => $chefs
            ]);
        }

        public function getEnseignants()
        {
            $enseignants = Utilisateur::where('role', Utilisateur::ROLE_ENSEIGNANT)
            ->select('id', 'nom', 'prenom', 'email')
            ->get();
            return response()->json([
                'success' => true,
                'count' => $enseignants->count(),
                'enseignants' => $enseignants
            ]);
        }

        public function getEtudiants()
        {
            $etudiants = Utilisateur::where('role', Utilisateur::ROLE_ETUDIANT)
            ->select('id', 'nom', 'prenom', 'email', 'groupe_id')
            ->get();
            return response()->json([
                'success' => true,
                'count' => $etudiants->count(),
                'etudiants' => $etudiants
            ]);
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
    public function publierPlanParNiveau(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isChef()) return response()->json(['error' => 'Non autorisé'], 403);

        $niveau = $request->input('niveau');

        return response()->json($user->publierPlanParNiveau($niveau));
    }
    public function refuserExamensAvecReclamation(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        if(!$user->isChef()) return response()->json(['error' => 'Non autorisé'], 403);

        $niveau = $request->input('niveau');
        $reclamations = $request->input('reclamations', []);

        return response()->json($user->refuserExamensAvecReclamation($niveau, $reclamations));
    }
}

