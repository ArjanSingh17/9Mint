<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $viewerId = (int) $request->user()->id;

        $notifications = UserNotification::query()
            ->where('user_id', $viewerId)
            ->latest('created_at')
            ->paginate(25);

        $requesterIds = $notifications->getCollection()
            ->filter(fn (UserNotification $notification) => ($notification->type ?? '') === 'friend_request_received')
            ->map(fn (UserNotification $notification) => (int) ($notification->data['requester_id'] ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $friendRequestStatusLookup = [];
        if (! empty($requesterIds)) {
            $friendships = Friendship::query()
                ->where('addressee_id', $viewerId)
                ->whereIn('requester_id', $requesterIds)
                ->get(['requester_id', 'status']);

            foreach ($friendships as $friendship) {
                $friendRequestStatusLookup[(int) $friendship->requester_id] = (string) $friendship->status;
            }
        }

        return view('notifications.index', [
            'notifications' => $notifications,
            'friendRequestStatusLookup' => $friendRequestStatusLookup,
        ]);
    }

    public function markAllRead(Request $request)
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'Notifications marked as read.');
    }
}
