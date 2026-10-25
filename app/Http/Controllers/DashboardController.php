<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'hosted_count' => $user->hostedMeetings()->count(),
            'active_count' => Meeting::where('status', 'active')->count(),
            'attended_count' => $user->participantSessions()->count(),
            'upcoming_count' => $user->hostedMeetings()->where('status', 'scheduled')->whereNotNull('scheduled_at')->count(),
        ];

        $myMeetings = $user->hostedMeetings()
            ->withCount('activeParticipants')
            ->latest()
            ->take(6)
            ->get();

        $recentAttended = $user->participantSessions()
            ->with('meeting')
            ->latest('joined_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'myMeetings', 'recentAttended'));
    }
}
