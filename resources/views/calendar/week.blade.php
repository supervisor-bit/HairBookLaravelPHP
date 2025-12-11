<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Týdenní přehled - HairBook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Manrope', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.4/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .glass { background: linear-gradient(135deg, rgba(33, 39, 55, 0.85), rgba(21, 27, 43, 0.82)); backdrop-filter: blur(16px); }
        [x-cloak] { display: none; }
        input[type="time"], input[type="date"] { color-scheme: dark; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen text-slate-200">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 border-r border-slate-800 glass flex flex-col">
        <div class="p-5">
            <div class="text-xs uppercase tracking-[0.3em] text-slate-400">HairBook</div>
            <div class="text-2xl font-semibold">Salon OS</div>
        </div>
        <nav class="px-3 space-y-2 flex-1">
            <a href="{{ route('home') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-purple-400 shadow-[0_0_0_6px_rgba(192,132,252,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">🏠 Domů</div>
                    <div class="text-xs text-slate-400">Úvodní obrazovka</div>
                </div>
            </a>
            <a href="{{ route('dashboard', ['section' => 'clients']) }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_6px_rgba(16,185,129,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">Klienti</div>
                    <div class="text-xs text-slate-400">Historie návštěv, uzávěrky</div>
                </div>
            </a>
            <a href="{{ route('dashboard', ['section' => 'products']) }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-sky-400 shadow-[0_0_0_6px_rgba(56,189,248,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">Produkty</div>
                    <div class="text-xs text-slate-400">Sklad ks + odpis v gramech</div>
                </div>
            </a>
            <a href="{{ route('calendar.index') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-indigo-400 shadow-[0_0_0_6px_rgba(129,140,248,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">📅 Denní kalendář</div>
                    <div class="text-xs text-slate-400">Denní rozvrh</div>
                </div>
            </a>
            <a href="{{ route('calendar.week') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition bg-slate-800 text-white">
                <span class="h-2 w-2 rounded-full bg-violet-400 shadow-[0_0_0_6px_rgba(167,139,250,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">📆 Týdenní přehled</div>
                    <div class="text-xs text-slate-400">Celý týden</div>
                </div>
            </a>
            <a href="{{ route('finance.index') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-amber-400 shadow-[0_0_0_6px_rgba(251,191,36,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">💰 Finance</div>
                    <div class="text-xs text-slate-400">Přehled příjmů</div>
                </div>
            </a>
        </nav>
        <div class="mt-auto p-4 space-y-2">
            <a href="{{ route('settings.index') }}" class="block w-full px-3 py-2 rounded-lg bg-slate-800 text-slate-300 text-sm hover:bg-slate-700 transition-colors text-center">
                ⚙️ Nastavení
            </a>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500/10 text-red-400 text-sm hover:bg-red-500/20 transition-colors">
                    🚪 Odhlásit
                </button>
            </form>
            <div class="text-xs text-slate-400 space-y-1">
                <div>Stav: <span class="text-emerald-300">online</span></div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto h-screen" x-data="{
        previewModal: { show: false, appointment: null },
        clients: {{ json_encode($clients) }},
        goToPreviousWeek() {
            const current = new Date('{{ $weekStart->format('Y-m-d') }}');
            current.setDate(current.getDate() - 7);
            window.location.href = '/calendar/week?date=' + current.toISOString().split('T')[0];
        },
        goToNextWeek() {
            const current = new Date('{{ $weekStart->format('Y-m-d') }}');
            current.setDate(current.getDate() + 7);
            window.location.href = '/calendar/week?date=' + current.toISOString().split('T')[0];
        },
        goToToday() {
            const today = new Date().toISOString().split('T')[0];
            window.location.href = '/calendar/week?date=' + today;
        },
        openPreview(appointment) {
            this.previewModal = { show: true, appointment: appointment };
        },
        closePreview() {
            this.previewModal = { show: false, appointment: null };
        },
        deleteAppointment(id) {
            if (!confirm('Opravdu chcete smazat tuto událost?')) return;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/appointments/' + id;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }">
        <div class="max-w-[1800px] mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-violet-400 to-purple-300 bg-clip-text text-transparent">
                            Týdenní přehled
                        </h1>
                        <div class="text-slate-400 mt-1">
                            {{ $weekStart->locale('cs')->isoFormat('D. MMMM') }} – {{ $weekEnd->locale('cs')->isoFormat('D. MMMM YYYY') }}
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center gap-4">
                    <button @click="goToPreviousWeek()" 
                            class="px-5 py-2.5 rounded-xl glass border border-slate-700 hover:bg-slate-800/60 transition text-sm">
                        ← Předchozí týden
                    </button>
                    <button @click="goToToday()" 
                            class="px-5 py-2.5 rounded-xl bg-violet-500 hover:bg-violet-600 transition text-white text-sm font-medium">
                        Aktuální týden
                    </button>
                    <button @click="goToNextWeek()" 
                            class="px-5 py-2.5 rounded-xl glass border border-slate-700 hover:bg-slate-800/60 transition text-sm">
                        Následující týden →
                    </button>
                    <a href="{{ route('calendar.index', ['date' => $selectedDate->format('Y-m-d')]) }}" 
                       class="ml-auto px-5 py-2.5 rounded-xl glass border border-slate-700 hover:bg-slate-800/60 transition text-sm">
                        📅 Denní zobrazení
                    </a>
                </div>
            </div>

            <!-- Week Grid -->
            <div class="grid grid-cols-7 gap-4">
                @php
                    $dayNames = ['Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
                    $currentDay = $weekStart->copy();
                @endphp

                @foreach($dayNames as $index => $dayName)
                    @php
                        $dateKey = $currentDay->format('Y-m-d');
                        $dayAppointments = $appointmentsByDay[$dateKey] ?? collect();
                        $isToday = $currentDay->isToday();
                        $currentDay->addDay();
                    @endphp

                    <div class="glass rounded-xl border {{ $isToday ? 'border-violet-500' : 'border-slate-700' }} overflow-hidden">
                        <!-- Day Header -->
                        <div class="p-4 border-b border-slate-700 {{ $isToday ? 'bg-violet-500/10' : '' }}">
                            <div class="font-semibold {{ $isToday ? 'text-violet-300' : 'text-slate-300' }}">
                                {{ $dayName }}
                            </div>
                            <div class="text-sm text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($dateKey)->locale('cs')->isoFormat('D. M.') }}
                            </div>
                            <div class="text-xs text-slate-500 mt-2">
                                {{ $dayAppointments->count() }} {{ $dayAppointments->count() === 1 ? 'rezervace' : 'rezervací' }}
                            </div>
                        </div>

                        <!-- Appointments List -->
                        <div class="p-3 space-y-2 max-h-[600px] overflow-y-auto">
                            @forelse($dayAppointments as $appointment)
                                <div @click="openPreview({{ json_encode($appointment) }})"
                                     class="p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800 cursor-pointer transition border border-slate-700/50 hover:border-slate-600">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="text-xs font-mono text-violet-400">
                                            {{ substr($appointment->start_time, 0, 5) }}
                                        </div>
                                        @if($appointment->status === 'completed')
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">
                                                ✓
                                            </span>
                                        @elseif($appointment->status === 'cancelled')
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-red-500/20 text-red-300">
                                                ✗
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm font-medium text-slate-200">
                                        {{ $appointment->first_name }} {{ $appointment->last_name }}
                                    </div>
                                    @if($appointment->phone)
                                        <div class="text-xs text-slate-400 mt-1">
                                            📞 {{ $appointment->phone }}
                                        </div>
                                    @endif
                                    @if($appointment->notes)
                                        <div class="text-xs text-slate-400 mt-1 line-clamp-2">
                                            {{ $appointment->notes }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-8 text-slate-500 text-sm">
                                    Žádné rezervace
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Preview Modal -->
        <div x-show="previewModal.show" 
             x-cloak
             @click.self="closePreview()"
             class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
            <div @click.stop class="glass rounded-2xl border border-slate-700 w-full max-w-lg p-6 shadow-2xl">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-100" x-text="previewModal.appointment?.first_name + ' ' + previewModal.appointment?.last_name"></h3>
                        <div class="text-slate-400 mt-1 flex items-center gap-2">
                            <span x-text="previewModal.appointment?.start_time ? previewModal.appointment.start_time.substring(0, 5) : ''"></span>
                            <span>–</span>
                            <span x-text="previewModal.appointment?.end_time ? previewModal.appointment.end_time.substring(0, 5) : ''"></span>
                        </div>
                    </div>
                    <button @click="closePreview()" class="text-slate-400 hover:text-slate-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-slate-400 mb-1">Datum</div>
                        <div class="text-slate-200" x-text="previewModal.appointment?.date ? new Date(previewModal.appointment.date).toLocaleDateString('cs-CZ', { year: 'numeric', month: 'long', day: 'numeric' }) : ''"></div>
                    </div>

                    <div x-show="previewModal.appointment?.phone">
                        <div class="text-sm text-slate-400 mb-1">Telefon</div>
                        <div class="text-slate-200" x-text="previewModal.appointment?.phone"></div>
                    </div>

                    <div x-show="previewModal.appointment?.notes">
                        <div class="text-sm text-slate-400 mb-1">Poznámky</div>
                        <div class="text-slate-200" x-text="previewModal.appointment?.notes"></div>
                    </div>

                    <div>
                        <div class="text-sm text-slate-400 mb-1">Stav</div>
                        <div>
                            <span x-show="previewModal.appointment?.status === 'scheduled'" class="px-3 py-1 rounded-lg bg-blue-500/20 text-blue-300 text-sm">
                                Naplánováno
                            </span>
                            <span x-show="previewModal.appointment?.status === 'completed'" class="px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 text-sm">
                                Dokončeno
                            </span>
                            <span x-show="previewModal.appointment?.status === 'cancelled'" class="px-3 py-1 rounded-lg bg-red-500/20 text-red-300 text-sm">
                                Zrušeno
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <a :href="'/calendar?date=' + previewModal.appointment?.date.split(' ')[0]"
                       class="flex-1 px-4 py-2.5 rounded-xl bg-violet-500 hover:bg-violet-600 text-white font-medium transition text-center">
                        Zobrazit den
                    </a>
                    <button @click="deleteAppointment(previewModal.appointment?.id)" 
                            class="px-4 py-2.5 rounded-xl bg-red-500/20 text-red-400 hover:bg-red-500/30 transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>

</body>
</html>
