<x-app-layout>
    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6">
            @if(session('error'))
                <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center space-y-6">
                <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-600">Meeting Lobby</span>
                    <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ $meeting->title }}</h2>
                    <p class="text-xs text-gray-500 mt-1">Host: {{ $meeting->host->name }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs text-gray-600 space-y-1">
                    <p>Room Code: <span class="font-mono font-bold text-gray-800">{{ $meeting->room_code }}</span></p>
                    <p>Participants Online: <span class="font-bold text-emerald-600">{{ $meeting->activeParticipants()->count() }}</span> / {{ $meeting->max_participants }}</p>
                </div>

                <form method="POST" action="{{ route('rooms.join', $meeting->room_code) }}" class="space-y-4">
                    @csrf
                    @if($meeting->passcode && $meeting->user_id !== Auth::id())
                        <div>
                            <label for="passcode" class="block text-xs font-semibold text-gray-700 uppercase text-left mb-1">Room Passcode Required</label>
                            <input type="password" id="passcode" name="passcode" required placeholder="Enter meeting passcode" class="w-full text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                        </div>
                    @endif

                    <button type="submit" class="w-full py-3 px-4 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow transition">
                        Join Call Now
                    </button>
                </form>

                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">Cancel and return to dashboard</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
