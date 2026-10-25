<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $meeting->title }}
            </h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('meetings.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                    &larr; Back to meetings
                </a>
                @if($meeting->status === 'active')
                    <a href="{{ route('rooms.live', $meeting->room_code) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow transition">
                        Enter Room
                    </a>
                    @if($meeting->user_id === Auth::id())
                        <form method="POST" action="{{ route('meetings.end', $meeting) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition">
                                End Meeting
                            </button>
                        </form>
                    @endif
                @elseif($meeting->status === 'scheduled')
                    @if($meeting->user_id === Auth::id())
                        <form method="POST" action="{{ route('meetings.start', $meeting) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-bold transition">
                                Start Now
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Meeting Overview & Notes -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Meeting Details Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center justify-between border-b pb-4">
                            <div>
                                <span class="text-xs uppercase font-bold text-gray-400">Status</span>
                                <div class="mt-1">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $meeting->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($meeting->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                        {{ ucfirst($meeting->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs uppercase font-bold text-gray-400">Host</span>
                                <div class="text-sm font-semibold text-gray-800">{{ $meeting->host->name }}</div>
                            </div>
                        </div>

                        <div>
                            <span class="text-xs uppercase font-bold text-gray-400">Shareable Invite Link</span>
                            <div class="mt-1 flex items-center space-x-2">
                                <input type="text" readonly value="{{ url('/room/' . $meeting->room_code . '/join') }}" id="invite_link" class="w-full text-xs font-mono bg-gray-50 border-gray-200 rounded-lg text-gray-700">
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('invite_link').value); alert('Invite link copied!');" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition whitespace-nowrap">
                                    Copy Link
                                </button>
                            </div>
                        </div>

                        @if($meeting->description)
                            <div>
                                <span class="text-xs uppercase font-bold text-gray-400">Description / Agenda</span>
                                <p class="text-sm text-gray-700 mt-1 whitespace-pre-line">{{ $meeting->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Meeting Notes / Minutes -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                        <h3 class="font-bold text-gray-900 text-base">Meeting Notes & Summary</h3>

                        @if($meeting->notes->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($meeting->notes as $note)
                                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-1">
                                        <div class="flex justify-between items-center text-xs font-bold text-gray-700">
                                            <span>{{ $note->title }}</span>
                                            <span class="text-gray-400 font-normal">{{ $note->created_at->format('M d, H:i') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 whitespace-pre-line">{{ $note->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('meetings.notes.store', $meeting) }}" class="space-y-3 pt-2">
                            @csrf
                            <input type="text" name="title" required placeholder="Note Topic (e.g. Action Items)" class="w-full text-xs border-gray-300 rounded-lg">
                            <textarea name="content" rows="4" required placeholder="Type meeting takeaways or decisions here..." class="w-full text-xs border-gray-300 rounded-lg"></textarea>
                            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold transition">
                                Add Meeting Note
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Host Controls Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                        <h4 class="font-bold text-gray-900 text-sm border-b pb-2">Room Settings</h4>

                        <div class="text-xs space-y-3">
                            <div>
                                <span class="text-gray-400 block">Room Code:</span>
                                <span class="font-mono font-bold text-teal-700 text-sm">{{ $meeting->room_code }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Passcode:</span>
                                <span class="font-semibold text-gray-700">{{ $meeting->passcode ?: 'None (Open)' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Max Participants:</span>
                                <span class="font-semibold text-gray-700">{{ $meeting->max_participants }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Room Lock:</span>
                                <span class="font-semibold {{ $meeting->is_locked ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $meeting->is_locked ? 'Locked (No new entrants)' : 'Unlocked' }}
                                </span>
                            </div>
                        </div>

                        @if($meeting->user_id === Auth::id())
                            <div class="pt-3 border-t">
                                <form method="POST" action="{{ route('meetings.toggle-lock', $meeting) }}">
                                    @csrf
                                    <button type="submit" class="w-full py-2 px-3 rounded-lg text-xs font-semibold border {{ $meeting->is_locked ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-rose-300 bg-rose-50 text-rose-800' }} transition">
                                        {{ $meeting->is_locked ? 'Unlock Room' : 'Lock Room' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Participant Roster History -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-3">
                        <h4 class="font-bold text-gray-900 text-sm border-b pb-2">Participants ({{ $meeting->participants->count() }})</h4>
                        @if($meeting->participants->isEmpty())
                            <p class="text-xs text-gray-400">No participants recorded yet.</p>
                        @else
                            <div class="space-y-2 text-xs divide-y divide-gray-100">
                                @foreach($meeting->participants as $p)
                                    <div class="pt-2 flex justify-between items-center">
                                        <div>
                                            <span class="font-semibold text-gray-800">{{ $p->participant_name }}</span>
                                            <span class="text-gray-400">({{ $p->role }})</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $p->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $p->status }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
