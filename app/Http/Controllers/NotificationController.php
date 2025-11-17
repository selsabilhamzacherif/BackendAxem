<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Afficher la liste des notifications.
     */
    public function index()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }

    /**
     * Créer une nouvelle notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'date' => 'required|date',
            'destinataire_id' => 'required|exists:utilisateurs,id',
        ]);

        $notification = Notification::create($request->all());

        return response()->json([
            'message' => 'Notification créée avec succès',
            'data' => $notification
        ], 201);
    }

    /**
     * Afficher une notification spécifique.
     */
    public function show(string $id)
    {
        $notification = Notification::findOrFail($id);
        return response()->json($notification);
    }

    /**
     * Mettre à jour une notification.
     */
    public function update(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);

        $request->validate([
            'message' => 'required|string',
            'date' => 'required|date',
            'destinataire_id' => 'required|exists:utilisateurs,id',
        ]);

        $notification->update($request->all());

        return response()->json([
            'message' => 'Notification mise à jour avec succès',
            'data' => $notification
        ]);
    }

    /**
     * Supprimer une notification.
     */
    public function destroy(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification supprimée avec succès'
        ]);
    }
}
