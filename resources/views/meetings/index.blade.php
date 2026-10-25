<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Meetings & Conferences') }}
            </h2>
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
                <!-- Schedule Form -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 text-lg mb-4">Schedule a Meeting</h3>
                    <form method="POST" action="{{ route('meetings.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="title" class="block text-xs font-semibold uppercase text-gray-600 mb-1">Meeting Title <span class="text-rose-500">*</span></label>
                            <input type="text" id="title" name="title" required placeholder="e.g. Weekly Engineering Sync" class="w-full text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-xs font-semibold uppercase text-gray-600 mb-1">Scheduled Date & Time</label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="w-full text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        <div>
                            <label for="passcode" class="block text-xs font-semibold uppercase text-gray-600 mb-1">Passcode (Optional)</label>
                            <input type="text" id="passcode" name="passcode" placeholder="Optional security code" class="w-full text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        <div>
                            <label for="max_participants" class="block text-xs font-semibold uppercase text-gray-600 mb-1">Max Capacity</label>
                            <input type="number" id="max_participants" name="max_participants" value="50" min="2" max="500" class="w-full text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-semibold uppercase text-gray-600 mb-1">Agenda / Description</label>
                            <textarea id="description" name="description" rows="3" placeholder="Meeting objectives, links, or notes" class="w-full text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg text-sm shadow transition">
                            Schedule Meeting
                        </button>
                    </form>
                </div>

                <!-- Meetings List -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <form method="GET" action="{{ route('meetings.index') }}" class="w-full flex gap-3">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or room code..." class="flex-1 text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                            <select name="status" class="text-sm border-gray-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                                <option value="">All Statuses</option>
                                <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                                <option value="active" @selected(request('status') === 'active')>Active</option>
                                <option value="ended" @selected(request('status') === 'ended')>Ended</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Filter</button>
                        </form>
                    </div>

                    @if($meetings->isEmpty())
                        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center text-gray-500 text-sm">
                            No meetings found.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($meetings as $m)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:border-teal-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('meetings.show', $m) }}" class="font-bold text-gray-900 hover:text-teal-600">
                                                {{ $m->title }}
                                            </a>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $m->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($m->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                                {{ ucfirst($m->status) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            Room Code: <span class="font-mono font-semibold text-gray-700">{{ $m->room_code }}</span>
                                            &bull; Capacity: {{ $m->max_participants }}
                                            @if($m->passcode) &bull; <span class="text-amber-600">Protected</span> @endif
                                        </p>
                                        @if($m->scheduled_at)
                                            <p class="text-xs text-gray-400">Scheduled: {{ $m->scheduled_at->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        @if($m->status === 'active')
                                            <a href="{{ route('rooms.live', $m->room_code) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                                Join Live
                                            </a>
                                        @elseif($m->status === 'scheduled')
                                            <form method="POST" action="{{ route('meetings.start', $m) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-bold transition">
                                                    Start
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('meetings.show', $m) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition">
                                            Manage
                                        </a>
                                        <form method="POST" action="{{ route('meetings.destroy', $m) }}" onsubmit="return confirm('Delete this meeting?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-semibold px-2 py-1.5">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            {{ $meetings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
