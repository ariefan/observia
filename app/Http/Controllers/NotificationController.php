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
