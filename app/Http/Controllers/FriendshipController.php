<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function sendRequest(Request $request, User $user, UserNotificationService $notifications): RedirectResponse
    {
        $viewer = $request->user();
        $viewerId = (int) $viewer->id;
        $otherId = (int) $user->id;

        if ($viewerId === $otherId) {
            return back()->with('error', 'You cannot send a friend request to yourself.');
        }

        [$lowId, $highId] = Friendship::normalizedPair($viewerId, $otherId);

        $existing = Friendship::query()
            ->where('user_low_id', $lowId)
            ->where('user_high_id', $highId)
            ->first();

        if ($existing) {
            if ($existing->status === Friendship::STATUS_ACCEPTED) {
                return back()->with('status', 'You are already friends.');
            }

            if ((int) $existing->requester_id === $viewerId) {
                return back()->with('status', 'Friend request is already pending.');
            }

            return back()->with('status', 'This user already sent you a friend request.');
        }

        Friendship::create([
            'requester_id' => $viewerId,
            'addressee_id' => $otherId,
            'user_low_id' => $lowId,
            'user_high_id' => $highId,
            'status' => Friendship::STATUS_PENDING,
        ]);

        $notifications->notifyUser(
            $otherId,
            'friend_request_received',
            'New friend request',
            "{$viewer->name} sent you a friend request.",
            [
                'requester_id' => $viewerId,
                'requester_name' => $viewer->name,
            ]
        );

        return back()->with('status', 'Friend request sent.');
    }

    public function cancelRequest(Request $request, User $user): RedirectResponse
    {
        $viewerId = (int) $request->user()->id;
        $otherId = (int) $user->id;

        $friendship = Friendship::query()
            ->betweenUsers($viewerId, $otherId)
            ->where('status', Friendship::STATUS_PENDING)
            ->where('requester_id', $viewerId)
            ->first();

        if (! $friendship) {
            return back()->with('error', 'No outgoing friend request found.');
        }

        $friendship->delete();

        return back()->with('status', 'Friend request cancelled.');
    }

    public function acceptRequest(Request $request, User $user, UserNotificationService $notifications): RedirectResponse
    {
        $viewer = $request->user();
        $viewerId = (int) $viewer->id;
        $otherId = (int) $user->id;

        $friendship = Friendship::query()
            ->betweenUsers($viewerId, $otherId)
            ->where('status', Friendship::STATUS_PENDING)
            ->where('requester_id', $otherId)
            ->where('addressee_id', $viewerId)
            ->first();

        if (! $friendship) {
            return back()->with('error', 'No incoming friend request found.');
        }

        $friendship->update([
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        $this->markIncomingRequestNotificationsRead($viewerId, $otherId);

        $notifications->notifyUser(
            $otherId,
            'friend_request_accepted',
            'Friend request accepted',
            "{$viewer->name} accepted your friend request.",
            [
                'acceptor_id' => $viewerId,
                'acceptor_name' => $viewer->name,
            ]
        );

        return back()->with('status', 'Friend request accepted.');
    }

    public function declineRequest(Request $request, User $user): RedirectResponse
    {
        $viewerId = (int) $request->user()->id;
        $otherId = (int) $user->id;

        $friendship = Friendship::query()
            ->betweenUsers($viewerId, $otherId)
            ->where('status', Friendship::STATUS_PENDING)
            ->where('requester_id', $otherId)
            ->where('addressee_id', $viewerId)
            ->first();

        if (! $friendship) {
            return back()->with('error', 'No incoming friend request found.');
        }

        $friendship->delete();

        $this->markIncomingRequestNotificationsRead($viewerId, $otherId);

        return back()->with('status', 'Friend request declined.');
    }

    public function unfriend(Request $request, User $user): RedirectResponse
    {
        $viewerId = (int) $request->user()->id;
        $otherId = (int) $user->id;

        $friendship = Friendship::query()
            ->betweenUsers($viewerId, $otherId)
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->first();

        if (! $friendship) {
            return back()->with('error', 'You are not friends with this user.');
        }

        $friendship->delete();

        return back()->with('status', 'User removed from friends.');
    }

    private function markIncomingRequestNotificationsRead(int $recipientId, int $requesterId): void
    {
        UserNotification::query()
            ->where('user_id', $recipientId)
            ->where('type', 'friend_request_received')
            ->whereNull('read_at')
            ->where('data->requester_id', $requesterId)
            ->update(['read_at' => now()]);
    }
}
