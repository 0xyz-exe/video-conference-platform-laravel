<x-app-layout>
    <div class="h-[calc(100vh-4rem)] flex flex-col bg-gray-900 text-white overflow-hidden">
        <!-- Top Conference Bar -->
        <div class="h-14 bg-gray-800/80 backdrop-blur px-6 flex items-center justify-between border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h1 class="text-sm font-bold text-gray-100 tracking-wide">{{ $meeting->title }}</h1>
                <span class="text-xs font-mono text-gray-400 bg-gray-700/60 px-2 py-0.5 rounded">{{ $meeting->room_code }}</span>
            </div>
            <div class="flex items-center space-x-3 text-xs">
                <span class="text-gray-300">{{ $participants->count() }} connected</span>
                <a href="{{ route('meetings.show', $meeting) }}" class="text-gray-400 hover:text-white underline">
                    Meeting Info
                </a>
            </div>
        </div>

        <!-- Middle: Video Grid & Sidebars -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Video Grid Area -->
            <div class="flex-1 p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 overflow-y-auto">
                @foreach($participants as $p)
                    <div class="relative bg-gray-800 rounded-2xl border {{ $p->user_id === Auth::id() ? 'border-teal-500/80 shadow-teal-500/10 shadow-lg' : 'border-gray-700' }} flex flex-col items-center justify-center p-6 min-h-[220px]">
                        @if($p->is_camera_on)
                            <!-- Simulated active video stream -->
                            <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-teal-500 to-indigo-600 flex items-center justify-center text-3xl font-extrabold text-white shadow-inner">
                                {{ strtoupper(substr($p->participant_name, 0, 1)) }}
                            </div>
                        @else
                            <!-- Camera off placeholder -->
                            <div class="w-24 h-24 rounded-full bg-gray-700 flex items-center justify-center text-gray-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                        @endif

                        <!-- Name & Badge -->
                        <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur px-2.5 py-1 rounded-lg text-xs font-semibold flex items-center space-x-2">
                            <span>{{ $p->participant_name }} {{ $p->user_id === Auth::id() ? '(You)' : '' }}</span>
                            @if($p->role === 'host')
                                <span class="text-[10px] bg-teal-600 px-1.5 py-0.2 rounded font-bold">Host</span>
                            @endif
                        </div>

                        <!-- Mic / Screen status badges -->
                        <div class="absolute top-3 right-3 flex items-center space-x-1.5">
                            @if($p->is_screen_sharing)
                                <span class="bg-blue-600/80 text-white px-2 py-0.5 rounded text-[10px] font-bold">Sharing</span>
                            @endif
                            <span class="p-1 rounded-full {{ $p->is_muted ? 'bg-rose-600 text-white' : 'bg-gray-700 text-gray-300' }}">
                                @if($p->is_muted)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 19L5 5m14 0L5 19" />
                                    </svg>
                                @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- In-Meeting Chat Sidebar -->
            <div class="w-80 bg-gray-800 border-l border-gray-700 flex flex-col hidden lg:flex">
                <div class="p-4 border-b border-gray-700 text-xs font-bold uppercase tracking-wider text-gray-400">
                    Meeting Chat
                </div>

                <div class="flex-1 p-4 space-y-3 overflow-y-auto">
                    @if($messages->isEmpty())
                        <div class="text-xs text-gray-500 text-center py-10">
                            No messages yet. Say hello to participants!
                        </div>
                    @else
                        @foreach($messages as $msg)
                            <div class="text-xs space-y-0.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="font-bold text-teal-400">{{ $msg->sender_name }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                                <p class="text-gray-200 bg-gray-700/60 p-2 rounded-lg">{{ $msg->message }}</p>
                            </div>
                        @endforeach
                    @endif
                </div>

                <form method="POST" action="{{ route('rooms.messages', $meeting->room_code) }}" class="p-3 border-t border-gray-700 flex space-x-2">
                    @csrf
                    <input type="text" name="message" required placeholder="Type a message..." class="flex-1 text-xs bg-gray-900 border-gray-700 rounded-lg text-white focus:ring-teal-500 focus:border-teal-500">
                    <button type="submit" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 rounded-lg text-xs font-bold text-white transition">
                        Send
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Controller Dock -->
        <div class="h-18 bg-gray-950 px-6 py-3 flex items-center justify-between border-t border-gray-800">
            <div class="text-xs text-gray-400 hidden sm:block">
                <span>Microphone: <strong class="{{ $participant->is_muted ? 'text-rose-400' : 'text-emerald-400' }}">{{ $participant->is_muted ? 'Muted' : 'Active' }}</strong></span>
                <span class="ml-3">Camera: <strong class="{{ $participant->is_camera_on ? 'text-emerald-400' : 'text-rose-400' }}">{{ $participant->is_camera_on ? 'On' : 'Off' }}</strong></span>
            </div>

            <div class="flex items-center space-x-3 mx-auto">
                <!-- Toggle Mute -->
                <form method="POST" action="{{ route('rooms.media', $meeting->room_code) }}">
                    @csrf
                    <input type="hidden" name="toggle_mute" value="1">
                    <button type="submit" class="p-3 rounded-full {{ $participant->is_muted ? 'bg-rose-600 hover:bg-rose-700' : 'bg-gray-800 hover:bg-gray-700' }} text-white transition" title="{{ $participant->is_muted ? 'Unmute' : 'Mute' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </button>
                </form>

                <!-- Toggle Camera -->
                <form method="POST" action="{{ route('rooms.media', $meeting->room_code) }}">
                    @csrf
                    <input type="hidden" name="toggle_camera" value="1">
                    <button type="submit" class="p-3 rounded-full {{ !$participant->is_camera_on ? 'bg-rose-600 hover:bg-rose-700' : 'bg-gray-800 hover:bg-gray-700' }} text-white transition" title="{{ $participant->is_camera_on ? 'Stop Video' : 'Start Video' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </form>

                <!-- Toggle Screen Share -->
                <form method="POST" action="{{ route('rooms.media', $meeting->room_code) }}">
                    @csrf
                    <input type="hidden" name="toggle_screen" value="1">
                    <button type="submit" class="p-3 rounded-full {{ $participant->is_screen_sharing ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-700' }} text-white transition" title="Share Screen">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </button>
                </form>

                <!-- Leave Meeting -->
                <form method="POST" action="{{ route('rooms.leave', $meeting->room_code) }}">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Leave</span>
                    </button>
                </form>
            </div>

            <div class="text-xs text-gray-500 hidden sm:block">
                MeetStream Secure Session
            </div>
        </div>
    </div>
</x-app-layout>
