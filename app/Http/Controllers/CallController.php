<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAnswered;
use App\Events\CallRejected;
use App\Events\CallEnded;
use App\Events\WebRtcSdpOffer;
use App\Events\WebRtcSdpAnswer;
use App\Events\WebRtcIceCandidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CallController extends Controller
{
    /**
     * Display the call page for initiating/receiving calls
     */
    public function show($receiverId)
    {
        $currentUser = Auth::user();
        $receiver = User::findOrFail($receiverId);

        // Security: Ensure call is between authenticated users
        if (!$currentUser || $currentUser->id === $receiver->id) {
            abort(403, 'Invalid call recipient');
        }

        return view('call.room', [
            'receiver' => $receiver,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Initiate a new call
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id|different:user',
            'call_type' => 'required|in:audio,video',
        ]);

        $currentUser = Auth::user();
        $receiver = User::findOrFail($validated['receiver_id']);

        // Create new call record
        $call = Call::create([
            'initiator_id' => $currentUser->id,
            'receiver_id' => $receiver->id,
            'call_type' => $validated['call_type'],
            'status' => 'initiated',
            'initiated_at' => now(),
        ]);

        // Broadcast call initiated event
        broadcast(new CallInitiated($call))->toOthers();

        return response()->json([
            'success' => true,
            'call_id' => $call->id,
            'message' => 'Call initiated',
        ]);
    }

    /**
     * Answer a call
     */
    public function answer(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:calls,id',
        ]);

        $call = Call::findOrFail($validated['call_id']);
        $currentUser = Auth::user();

        // Security: Only receiver can answer
        if ($call->receiver_id !== $currentUser->id) {
            abort(403, 'You are not the receiver of this call');
        }

        // Mark as answered
        $call->markAsAnswered();

        // Broadcast answer event to initiator
        broadcast(new CallAnswered($call))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Call answered',
        ]);
    }

    /**
     * Reject a call
     */
    public function reject(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:calls,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $call = Call::findOrFail($validated['call_id']);
        $currentUser = Auth::user();

        // Security: Only receiver can reject
        if ($call->receiver_id !== $currentUser->id) {
            abort(403, 'You are not the receiver of this call');
        }

        // Mark as rejected
        $call->markAsRejected($validated['reason'] ?? null);

        // Broadcast rejection event
        broadcast(new CallRejected($call, $validated['reason'] ?? null))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Call rejected',
        ]);
    }

    /**
     * End a call
     */
    public function end(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:calls,id',
        ]);

        $call = Call::findOrFail($validated['call_id']);
        $currentUser = Auth::user();

        // Security: Only participants can end the call
        if ($call->initiator_id !== $currentUser->id && $call->receiver_id !== $currentUser->id) {
            abort(403, 'You are not a participant of this call');
        }

        // Mark as ended
        $call->markAsEnded();

        // Broadcast end event to both participants
        broadcast(new CallEnded($call))->toOthers();

        return response()->json([
            'success' => true,
            'call_duration' => $call->duration_seconds,
            'message' => 'Call ended',
        ]);
    }

    /**
     * Handle WebRTC SDP offer
     */
    public function sendSdpOffer(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:calls,id',
            'sdp' => 'required|string',
        ]);

        $call = Call::findOrFail($validated['call_id']);
        $currentUser = Auth::user();

        // Security: Only initiator can send offer
        if ($call->initiator_id !== $currentUser->id) {
            abort(403, 'Only the call initiator can send SDP offer');
        }

        // Broadcast SDP offer to receiver
        broadcast(new WebRtcSdpOffer(
            $call->id,
            $call->receiver_id,
            $validated['sdp'],
            $currentUser->id
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'SDP offer sent',
        ]);
    }

    /**
     * Handle WebRTC SDP answer
     */
    public function sendSdpAnswer(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:calls,id',
            'sdp' => 'required|string',
        ]);

        $call = Call::findOrFail($validated['call_id']);
        $currentUser = Auth::user();

        // Security: Only receiver can send answer
        if ($call->receiver_id !== $currentUser->id) {
            abort(403, 'Only the call receiver can send SDP answer');
        }

        // Broadcast SDP answer to initiator
        broadcast(new WebRtcSdpAnswer(
            $call->id,
            $call->initiator_id,
            $validated['sdp'],
            $currentUser->id
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'SDP answer sent',
        ]);
    }

    /**
     * Handle WebRTC ICE candidates
     */
    public function sendIceCandidate(Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required|exists:calls,id',
            'recipient_id' => 'required|exists:users,id',
            'candidate' => 'required|array',
        ]);

        $call = Call::findOrFail($validated['call_id']);
        $currentUser = Auth::user();

        // Security: User must be a participant in the call
        if (!in_array($currentUser->id, [$call->initiator_id, $call->receiver_id])) {
            abort(403, 'You are not a participant of this call');
        }

        // Broadcast ICE candidate
        broadcast(new WebRtcIceCandidate(
            $call->id,
            $validated['recipient_id'],
            $validated['candidate'],
            $currentUser->id
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'ICE candidate sent',
        ]);
    }

    /**
     * Get call history for a user
     */
    public function history()
    {
        $currentUser = Auth::user();

        $calls = Call::where(function ($query) use ($currentUser) {
            $query->where('initiator_id', $currentUser->id)
                  ->orWhere('receiver_id', $currentUser->id);
        })
        ->with(['initiator', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return response()->json([
            'success' => true,
            'calls' => $calls,
        ]);
    }

    /**
     * Get recent calls with a specific user
     */
    public function getCallsWith($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        $calls = Call::where(function ($query) use ($currentUser, $otherUser) {
            $query->where(function ($q) use ($currentUser, $otherUser) {
                $q->where('initiator_id', $currentUser->id)
                  ->where('receiver_id', $otherUser->id);
            })->orWhere(function ($q) use ($currentUser, $otherUser) {
                $q->where('initiator_id', $otherUser->id)
                  ->where('receiver_id', $currentUser->id);
            });
        })
        ->with(['initiator', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'calls' => $calls,
        ]);
    }

    /**
     * Check for incoming calls (used for polling)
     */
    public function checkIncomingCalls()
    {
        $currentUser = Auth::user();

        $incomingCalls = Call::where('receiver_id', $currentUser->id)
            ->where('status', 'initiated')
            ->with('initiator')
            ->get()
            ->map(function ($call) {
                return [
                    'call_id' => $call->id,
                    'initiator_id' => $call->initiator_id,
                    'initiator_name' => $call->initiator->name,
                    'call_type' => $call->call_type,
                ];
            });

        return response()->json([
            'success' => true,
            'calls' => $incomingCalls,
        ]);
    }
}
