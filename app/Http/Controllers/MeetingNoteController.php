<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MeetingNoteController extends Controller
{
    public function store(Request $request, Meeting $meeting): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        MeetingNote::create([
            'meeting_id' => $meeting->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Meeting notes saved successfully.');
    }
}
