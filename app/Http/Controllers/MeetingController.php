<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->hostedMeetings()->withCount('participants')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('room_code', 'like', "%{$search}%");
            });
        }

        $meetings = $query->paginate(10)->withQueryString();

        return view('meetings.index', compact('meetings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'passcode' => 'nullable|string|max:50',
            'max_participants' => 'nullable|integer|min:2|max:500',
            'scheduled_at' => 'nullable|date',
            'is_instant' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $isInstant = $request->boolean('is_instant');

        $meeting = Meeting::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'room_code' => Str::lower(Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3)),
            'description' => $validated['description'] ?? null,
            'passcode' => !empty($validated['passcode']) ? $validated['passcode'] : null,
            'max_participants' => $validated['max_participants'] ?? 50,
            'status' => $isInstant ? 'active' : 'scheduled',
            'scheduled_at' => $validated['scheduled_at'] ?? now(),
        ]);

        if ($isInstant) {
            return redirect()->route('rooms.live', $meeting->room_code)->with('success', 'Instant meeting launched!');
        }

        return redirect()->route('meetings.show', $meeting)->with('success', 'Meeting scheduled successfully.');
    }

    public function show(Request $request, Meeting $meeting): View
    {
        $meeting->load(['host', 'participants', 'notes', 'messages']);
        $user = $request->user();

        return view('meetings.show', compact('meeting'));
    }

    public function start(Request $request, Meeting $meeting): RedirectResponse
    {
        if ($meeting->user_id !== $request->user()->id) {
            abort(403, 'Only the host can start this meeting.');
        }

        $meeting->update([
            'status' => 'active',
        ]);

        return redirect()->route('rooms.live', $meeting->room_code)->with('success', 'Meeting started!');
    }

    public function end(Request $request, Meeting $meeting): RedirectResponse
    {
        if ($meeting->user_id !== $request->user()->id) {
            abort(403, 'Only the host can end this meeting.');
        }

        $meeting->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        // Mark all participants left
        $meeting->participants()->where('status', 'active')->update([
            'status' => 'left',
            'left_at' => now(),
        ]);

        return redirect()->route('meetings.show', $meeting)->with('success', 'Meeting ended.');
    }

    public function toggleLock(Request $request, Meeting $meeting): RedirectResponse
    {
        if ($meeting->user_id !== $request->user()->id) {
            abort(403);
        }

        $meeting->update([
            'is_locked' => !$meeting->is_locked,
        ]);

        return back()->with('success', $meeting->is_locked ? 'Meeting room locked.' : 'Meeting room unlocked.');
    }

    public function destroy(Request $request, Meeting $meeting): RedirectResponse
    {
        if ($meeting->user_id !== $request->user()->id) {
            abort(403);
        }

        $meeting->delete();

        return redirect()->route('meetings.index')->with('success', 'Meeting deleted.');
    }
}
