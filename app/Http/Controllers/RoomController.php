<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingMessage;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function joinForm(Request $request, string $room_code): View|RedirectResponse
    {
        $meeting = Meeting::where('room_code', $room_code)->firstOrFail();

        if ($meeting->status === 'ended') {
            return redirect()->route('dashboard')->with('error', 'This meeting has already ended.');
        }

        return view('rooms.join', compact('meeting'));
    }

    public function join(Request $request, string $room_code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $room_code)->firstOrFail();

        if ($meeting->status === 'ended') {
            return redirect()->route('dashboard')->with('error', 'This meeting has ended.');
        }

        if ($meeting->is_locked && $meeting->user_id !== $request->user()->id) {
            return back()->with('error', 'Meeting room is currently locked by the host.');
        }

        if ($meeting->passcode && $meeting->user_id !== $request->user()->id) {
            $request->validate([
                'passcode' => 'required|string',
            ]);

            if ($request->input('passcode') !== $meeting->passcode) {
                return back()->with('error', 'Incorrect meeting passcode.');
            }
        }

        $user = $request->user();

        // Check capacity
        if ($meeting->activeParticipants()->count() >= $meeting->max_participants) {
            return back()->with('error', 'Meeting has reached maximum participant capacity.');
        }

        // Create or reactivate participant record
        $role = ($meeting->user_id === $user->id) ? 'host' : 'participant';

        Participant::updateOrCreate(
            ['meeting_id' => $meeting->id, 'user_id' => $user->id],
            [
                'participant_name' => $user->name,
                'role' => $role,
                'status' => 'active',
                'joined_at' => now(),
                'left_at' => null,
            ]
        );

        if ($meeting->status === 'scheduled') {
            $meeting->update(['status' => 'active']);
        }

        return redirect()->route('rooms.live', $meeting->room_code);
    }

    public function live(Request $request, string $room_code): View|RedirectResponse
    {
        $meeting = Meeting::where('room_code', $room_code)->firstOrFail();
        $user = $request->user();

        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$participant) {
            return redirect()->route('rooms.join.form', $meeting->room_code);
        }

        $participants = $meeting->activeParticipants()->get();
        $messages = $meeting->messages()->latest()->take(30)->get()->reverse();

        return view('rooms.live', compact('meeting', 'participant', 'participants', 'messages'));
    }

    public function leave(Request $request, string $room_code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $room_code)->firstOrFail();
        $user = $request->user();

        Participant::where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => 'left',
                'left_at' => now(),
            ]);

        return redirect()->route('dashboard')->with('success', 'You left the meeting.');
    }

    public function toggleMedia(Request $request, string $room_code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $room_code)->firstOrFail();
        $user = $request->user();

        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($request->has('toggle_mute')) {
            $participant->update(['is_muted' => !$participant->is_muted]);
        }

        if ($request->has('toggle_camera')) {
            $participant->update(['is_camera_on' => !$participant->is_camera_on]);
        }

        if ($request->has('toggle_screen')) {
            $participant->update(['is_screen_sharing' => !$participant->is_screen_sharing]);
        }

        return back()->with('status', 'Media state updated.');
    }

    public function sendMessage(Request $request, string $room_code): RedirectResponse
    {
        $meeting = Meeting::where('room_code', $room_code)->firstOrFail();
        $user = $request->user();

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        MeetingMessage::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'sender_name' => $user->name,
            'message' => $validated['message'],
        ]);

        return back();
    }
}
