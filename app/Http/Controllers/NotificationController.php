<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    public function markAsRead(Notification $notification)
    {
        $this->authorizeAccess($notification);
        $notification->markAsRead();

        return response()->json(['status' => 'read']);
    }

    public function accept(Notification $notification)
    {
        $this->authorizeAction($notification);

        $notification->accept();

        // You can hook into actual logic here (e.g., join a farm)
        // FarmInvite::process($notification);

        return response()->json(['status' => 'accepted']);
    }

    public function reject(Notification $notification)
    {
        $this->authorizeAction($notification);

        $notification->reject();

        return response()->json(['status' => 'rejected']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'message' => 'required|string',
            'action_required' => 'boolean',
        ]);

        $notification = Notification::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'action_required' => $request->boolean('action_required', false),
            'action_status' => $request->boolean('action_required', false) ? 'pending' : null,
        ]);

        return response()->json($notification, 201);
    }

    public function destroy(Notification $notification)
    {
        $this->authorizeAccess($notification);
        
        $notification->delete();

        return response()->json(['status' => 'deleted']);
    }

    private function authorizeAccess(Notification $notification)
    {
        abort_if(auth()->id() !== $notification->user_id, 403, 'This isn’t your notification, my guy.');
    }

    private function authorizeAction(Notification $notification)
    {
        $this->authorizeAccess($notification);

        if (!$notification->requiresAction()) {
            abort(400, 'No action required or already acted upon.');
        }
    }
}
