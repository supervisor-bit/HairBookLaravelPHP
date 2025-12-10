<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalendář - HairBook</title>
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
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen text-slate-200">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 border-r border-slate-800 glass flex flex-col overflow-hidden">
        <div class="p-5">
            <div class="text-xs uppercase tracking-[0.3em] text-slate-400">HairBook</div>
            <div class="text-2xl font-semibold">Salon OS</div>
        </div>
        <nav class="px-3 space-y-2 flex-1 overflow-y-auto">
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
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition bg-slate-800 text-white">
                <span class="h-2 w-2 rounded-full bg-indigo-400 shadow-[0_0_0_6px_rgba(129,140,248,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">📅 Kalendář</div>
                    <div class="text-xs text-slate-400">Denní rozvrh</div>
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
    <main class="flex-1 p-8" x-data="{
        appointmentModal: { show: false, appointment: null },
        selectedDate: '{{ $selectedDate->format('Y-m-d') }}',
        openAppointmentModal(appointment = null) {
            this.appointmentModal.appointment = appointment;
            this.appointmentModal.show = true;
        },
        changeDate(direction) {
            const date = new Date(this.selectedDate);
            date.setDate(date.getDate() + direction);
            window.location.href = '{{ route('calendar.index') }}?date=' + date.toISOString().split('T')[0];
        }
    }">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Kalendář</h2>
                    <p class="text-slate-400 mt-1">Denní rozvrh rezervací</p>
                </div>
                <button @click="openAppointmentModal()" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium transition-colors">
                    + Nová rezervace
                </button>
            </div>

            <!-- Date Navigation -->
            <div class="glass rounded-xl p-6 mb-6 border border-slate-700/50">
                <div class="flex items-center justify-between">
                    <button @click="changeDate(-1)" class="p-2 hover:bg-slate-700/50 rounded-lg transition-colors">
                        ← Předchozí den
                    </button>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-white">{{ $selectedDate->format('d.m.Y') }}</h3>
                        <p class="text-slate-400 text-sm">{{ $selectedDate->locale('cs')->translatedFormat('l') }}</p>
                    </div>
                    <button @click="changeDate(1)" class="p-2 hover:bg-slate-700/50 rounded-lg transition-colors">
                        Následující den →
                    </button>
                </div>
            </div>

            <!-- Calendar Grid (2 lanes) -->
            <div class="glass rounded-xl border border-slate-700/50 overflow-hidden">
                <div class="grid grid-cols-[80px_1fr_1fr] border-b border-slate-700/50">
                    <div class="p-4 bg-slate-800/50 border-r border-slate-700/50">
                        <span class="text-sm font-medium text-slate-400">Čas</span>
                    </div>
                    <div class="p-4 bg-slate-800/50 border-r border-slate-700/50">
                        <span class="text-sm font-medium text-emerald-400">Místo 1</span>
                    </div>
                    <div class="p-4 bg-slate-800/50">
                        <span class="text-sm font-medium text-blue-400">Místo 2</span>
                    </div>
                </div>

                <!-- Time slots (8:00 - 20:00) -->
                @php
                    $hours = range(8, 20);
                @endphp

                @foreach($hours as $hour)
                <div class="grid grid-cols-[80px_1fr_1fr] border-b border-slate-700/50 min-h-[80px]">
                    <div class="p-4 bg-slate-800/30 border-r border-slate-700/50">
                        <span class="text-sm text-slate-400">{{ sprintf('%02d:00', $hour) }}</span>
                    </div>
                    
                    <!-- Lane 1 -->
                    <div class="p-2 border-r border-slate-700/50 relative bg-slate-800/10 hover:bg-slate-700/20 transition-colors cursor-pointer" 
                         @click="openAppointmentModal()">
                        @foreach($lane1 as $appointment)
                            @php
                                $startHour = (int)substr($appointment->start_time, 0, 2);
                                $startMin = (int)substr($appointment->start_time, 3, 2);
                                $endHour = (int)substr($appointment->end_time, 0, 2);
                                $endMin = (int)substr($appointment->end_time, 3, 2);
                            @endphp
                            @if($startHour == $hour)
                            <div class="absolute top-2 left-2 right-2 bg-emerald-500/20 border border-emerald-500/50 rounded-lg p-3 cursor-pointer hover:bg-emerald-500/30 transition-colors"
                                 style="height: {{ (($endHour - $startHour) * 80 + (($endMin - $startMin) / 60) * 80) - 8 }}px"
                                 @click.stop="openAppointmentModal({{ json_encode($appointment) }})">
                                <div class="text-sm font-medium text-white">{{ $appointment->first_name }} {{ $appointment->last_name }}</div>
                                <div class="text-xs text-emerald-300 mt-1">{{ substr($appointment->start_time, 0, 5) }} - {{ substr($appointment->end_time, 0, 5) }}</div>
                                @if($appointment->notes)
                                <div class="text-xs text-slate-300 mt-1 line-clamp-2">{{ $appointment->notes }}</div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <!-- Lane 2 -->
                    <div class="p-2 relative bg-slate-800/10 hover:bg-slate-700/20 transition-colors cursor-pointer"
                         @click="openAppointmentModal()">
                        @foreach($lane2 as $appointment)
                            @php
                                $startHour = (int)substr($appointment->start_time, 0, 2);
                                $startMin = (int)substr($appointment->start_time, 3, 2);
                                $endHour = (int)substr($appointment->end_time, 0, 2);
                                $endMin = (int)substr($appointment->end_time, 3, 2);
                            @endphp
                            @if($startHour == $hour)
                            <div class="absolute top-2 left-2 right-2 bg-blue-500/20 border border-blue-500/50 rounded-lg p-3 cursor-pointer hover:bg-blue-500/30 transition-colors"
                                 style="height: {{ (($endHour - $startHour) * 80 + (($endMin - $startMin) / 60) * 80) - 8 }}px"
                                 @click.stop="openAppointmentModal({{ json_encode($appointment) }})">
                                <div class="text-sm font-medium text-white">{{ $appointment->first_name }} {{ $appointment->last_name }}</div>
                                <div class="text-xs text-blue-300 mt-1">{{ substr($appointment->start_time, 0, 5) }} - {{ substr($appointment->end_time, 0, 5) }}</div>
                                @if($appointment->notes)
                                <div class="text-xs text-slate-300 mt-1 line-clamp-2">{{ $appointment->notes }}</div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Appointment Modal -->
        <template x-if="appointmentModal.show">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                 @click.self="appointmentModal.show = false">
                <div class="glass rounded-2xl p-6 w-full max-w-md border border-slate-700/50">
                    <h3 class="text-xl font-bold text-white mb-4" x-text="appointmentModal.appointment ? 'Upravit rezervaci' : 'Nová rezervace'"></h3>
                    
                    <form :action="appointmentModal.appointment ? '{{ url('/appointments') }}/' + appointmentModal.appointment.id : '{{ route('appointments.store') }}'" method="POST">
                        @csrf
                        <template x-if="appointmentModal.appointment">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4">
                            <!-- Volba klienta -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Vybrat klienta (volitelné)</label>
                                <select name="client_id" class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white">
                                    <option value="">-- Nový klient --</option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->first_name }} {{ $client->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Jméno -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Jméno *</label>
                                <input type="text" name="first_name" required 
                                       :value="appointmentModal.appointment?.first_name || ''"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Příjmení -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Příjmení *</label>
                                <input type="text" name="last_name" required 
                                       :value="appointmentModal.appointment?.last_name || ''"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Datum -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Datum *</label>
                                <input type="date" name="date" required 
                                       :value="appointmentModal.appointment?.date || '{{ $selectedDate->format('Y-m-d') }}'"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Čas od-do -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Čas od *</label>
                                    <input type="time" name="start_time" required 
                                           :value="appointmentModal.appointment?.start_time ? appointmentModal.appointment.start_time.substring(0, 5) : ''"
                                           class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Čas do *</label>
                                    <input type="time" name="end_time" required 
                                           :value="appointmentModal.appointment?.end_time ? appointmentModal.appointment.end_time.substring(0, 5) : ''"
                                           class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>

                            <!-- Pracovní místo -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Pracovní místo *</label>
                                <select name="lane" required class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white">
                                    <option value="1" :selected="!appointmentModal.appointment || appointmentModal.appointment.lane == 1">Místo 1</option>
                                    <option value="2" :selected="appointmentModal.appointment?.lane == 2">Místo 2</option>
                                </select>
                            </div>

                            <!-- Poznámky -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Poznámky</label>
                                <textarea name="notes" rows="3" 
                                          x-text="appointmentModal.appointment?.notes || ''"
                                          class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>

                            <!-- Opakování (pouze pro nové) -->
                            <template x-if="!appointmentModal.appointment">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Opakovat na příštích X týdnů</label>
                                    <input type="number" name="repeat_weeks" min="0" max="52" value="0" 
                                           class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <p class="text-xs text-slate-400 mt-1">0 = neopakuje se</p>
                                </div>
                            </template>

                            <!-- Status (pouze pro existující) -->
                            <template x-if="appointmentModal.appointment">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                                    <select name="status" class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white">
                                        <option value="scheduled" :selected="appointmentModal.appointment?.status == 'scheduled'">Naplánováno</option>
                                        <option value="completed" :selected="appointmentModal.appointment?.status == 'completed'">Dokončeno</option>
                                        <option value="cancelled" :selected="appointmentModal.appointment?.status == 'cancelled'">Zrušeno</option>
                                    </select>
                                </div>
                            </template>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 mt-6">
                            <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium transition-colors">
                                <span x-text="appointmentModal.appointment ? 'Uložit' : 'Vytvořit'"></span>
                            </button>
                            <button type="button" @click="appointmentModal.show = false" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition-colors">
                                Zrušit
                            </button>
                            <template x-if="appointmentModal.appointment">
                                <button type="button" 
                                        @click="if(confirm('Opravdu smazat tuto rezervaci?')) { 
                                            const form = document.createElement('form'); 
                                            form.method = 'POST'; 
                                            form.action = '{{ url('/appointments') }}/' + appointmentModal.appointment.id;
                                            form.innerHTML = '@csrf @method('DELETE')';
                                            document.body.appendChild(form);
                                            form.submit();
                                        }"
                                        class="px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors">
                                    Smazat
                                </button>
                            </template>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </main>
</div>

</body>
</html>
