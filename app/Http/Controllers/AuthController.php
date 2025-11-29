<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;


/*    /**
 * @method middleware($name, $options = [])
 */

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        // Étudiant
        if ($request->has('matricule')) {
            $request->validate([
                'matricule' => 'required|string',
                'motDePasse' => 'required|string',
            ]);

            $user = Utilisateur::where('matricule', $request->matricule)->first();

            if (!$user || !Hash::check($request->motDePasse, $user->motDePasse)) {
                return response()->json(['error' => 'Matricule ou mot de passe incorrect'], 401);
            }

            if ($user->role !== 'etudiant') {
                return response()->json(['error' => 'Ce matricule n’appartient pas à un étudiant'], 403);
            }
        }
        // Enseignant / Chef / Responsable
        else {
            $request->validate([
                'email' => 'required|email',
                'motDePasse' => 'required|string',
            ]);

            $user = Utilisateur::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->motDePasse, $user->motDePasse)) {
                return response()->json(['error' => 'Email ou mot de passe incorrect'], 401);
            }

            if (!in_array($user->role, ['enseignant','chef_departement','responsable_plan'])) {
                return response()->json(['error' => 'Ce compte n’a pas accès à cette authentification'], 403);
            }
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json(['error' => 'Token manquant'], 400);
            }

            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token invalide ou expiré'], 401);
        }
    }



  /*   public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
 */

    // Signup (seulement responsable)
    public function signup(Request $request)
    {

        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'role' => 'required|string',
            'motDePasse' => 'required|string|min:6',
            'email' => 'nullable|email|unique:utilisateurs',
            'matricule' => 'nullable|string|unique:utilisateurs',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
            'groupe_id' => 'nullable|integer',
        ]);

       // $user = auth('api')->user();
      /*  if ($user->role !== 'responsable_plan') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }*/

        $data = $request->all();
        $data['motDePasse'] = Hash::make($data['motDePasse']);
        $nouveau = Utilisateur::create($data);

        return response()->json([
            'success' => true,
            'user' => $nouveau
        ]);
    }
   /*  public function signupetudiant(Request $request)
    {

        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'role' => 'required|string',
            'motDePasse' => 'required|string|min:6',
            'email' => 'nullable|email|unique:utilisateurs',
            'matricule' => 'nullable|string|unique:utilisateurs',
            'specialite' => 'nullable|string',
            'departement' => 'nullable|string',
            'groupe_id' => 'nullable|integer',
        ]);

        $user = auth('api')->user();
        if ($user->role !== 'responsable_plan') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $data = $request->all();
        $data['motDePasse'] = Hash::make($data['motDePasse']);
        $nouveau = Utilisateur::create($data);

        return response()->json([
            'success' => true,
            'user' => $nouveau
        ]);
    } */
}
