<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conference Dashboard') }}
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('meetings.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                    View Schedule
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Quick Launch & Join Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Instant Meeting Card -->
                <div class="bg-gradient-to-br from-teal-600 to-emerald-700 rounded-2xl p-6 text-white shadow-md flex flex-col justify-between">
                    <div>
                        <div class="inline-flex p-3 bg-white/20 rounded-xl mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold">New Instant Meeting</h3>
                        <p class="text-teal-100 text-sm mt-1">Start a video conference immediately and invite participants via room link.</p>
                    </div>
                    <form method="POST" action="{{ route('meetings.store') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="title" value="Instant Room ({{ Auth::user()->name }})">
                        <input type="hidden" name="is_instant" value="1">
                        <button type="submit" class="w-full py-2.5 px-4 bg-white text-teal-800 font-bold rounded-xl text-sm shadow hover:bg-teal-50 transition">
                            Start Instant Meeting &rarr;
                        </button>
                    </form>
                </div>

                <!-- Join with Room Code -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex p-3 bg-indigo-50 text-indigo-600 rounded-xl mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Join a Meeting</h3>
                        <p class="text-gray-500 text-sm mt-1">Enter a room code (e.g. `abc-defg-hij`) shared with you.</p>
                    </div>
                    <form method="GET" action="" onsubmit="event.preventDefault(); const code = document.getElementById('join_room_code').value.trim(); if(code) window.location.href='/room/'+code+'/join';" class="mt-6 flex space-x-2">
                        <input type="text" id="join_room_code" placeholder="Enter code" class="flex-1 text-sm border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-indigo-500" required>
                        <button type="submit" class="py-2.5 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                            Join
                        </button>
                    </form>
                </div>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Hosted Meetings</span>
                    <div class="text-3xl font-extrabold text-teal-600 mt-2">{{ $stats['hosted_count'] }}</div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Live Conferences</span>
                    <div class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $stats['active_count'] }}</div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Upcoming</span>
                    <div class="text-3xl font-extrabold text-indigo-600 mt-2">{{ $stats['upcoming_count'] }}</div>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Attended Sessions</span>
                    <div class="text-3xl font-extrabold text-gray-900 mt-2">{{ $stats['attended_count'] }}</div>
                </div>
            </div>

            <!-- Recent Hosted Meetings -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-lg">My Created Meetings</h3>
                    <a href="{{ route('meetings.index') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-800">All Meetings &rarr;</a>
                </div>
                @if($myMeetings->isEmpty())
                    <div class="p-10 text-center text-gray-500 text-sm">
                        You have not scheduled any meetings yet.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($myMeetings as $m)
                            <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-3">
                                        <a href="{{ route('meetings.show', $m) }}" class="font-bold text-gray-900 hover:text-teal-600">
                                            {{ $m->title }}
                                        </a>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $m->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($m->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                            {{ ucfirst($m->status) }}
                                        </span>
                                        @if($m->is_locked)
                                            <span class="text-xs bg-rose-50 text-rose-700 px-2 py-0.5 rounded border border-rose-200">Locked</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center space-x-3">
                                        <span>Room: <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-800 font-mono">{{ $m->room_code }}</code></span>
                                        <span>&bull;</span>
                                        <span>{{ $m->active_participants_count }} online</span>
                                        <span>&bull;</span>
                                        <span>{{ $m->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($m->status === 'active')
                                        <a href="{{ route('rooms.live', $m->room_code) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                            Enter Live Room
                                        </a>
                                    @elseif($m->status === 'scheduled')
                                        <form method="POST" action="{{ route('meetings.start', $m) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold transition">
                                                Start Meeting
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('meetings.show', $m) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition">
                                        Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
