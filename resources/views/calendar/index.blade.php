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
    <main class="flex-1 p-8 overflow-y-auto h-screen" x-data="{
        previewModal: { show: false, appointment: null },
        editModal: { show: false, appointment: null, defaultTime: null },
        repeatModal: { show: false, appointment: null, mode: 'weeks', selectedDate: null, repeatWeeks: 1, alternatives: [], checking: false },
        selectedDate: '{{ $selectedDate->format('Y-m-d') }}',
        selectedClient: null,
        searchQuery: '',
        showDropdown: false,
        clients: {{ json_encode($clients) }},
        openPreview(appointment) {
            this.previewModal.appointment = appointment;
            this.previewModal.show = true;
        },
        openEditModal(appointment = null, time = null) {
            this.previewModal.show = false;
            this.editModal.appointment = appointment;
            this.editModal.defaultTime = time;
            this.selectedClient = null;
            this.searchQuery = '';
            this.showDropdown = false;
            this.editModal.show = true;
        },
        openNewAppointment(time = null) {
            this.editModal.appointment = null;
            this.editModal.defaultTime = time;
            this.selectedClient = null;
            this.searchQuery = '';
            this.showDropdown = false;
            this.editModal.show = true;
        },
        selectClient(clientId) {
            const client = this.clients.find(c => c.id == clientId);
            if(client) {
                this.selectedClient = client;
                this.searchQuery = client.name;
                this.showDropdown = false;
                document.querySelector('input[name=client_id]').value = client.id;
                const nameParts = client.name.split(' ');
                document.querySelector('input[name=first_name]').value = nameParts[0] || '';
                document.querySelector('input[name=last_name]').value = nameParts.slice(1).join(' ') || '';
                document.querySelector('input[name=phone]').value = client.phone || '';
            } else {
                this.selectedClient = null;
                this.searchQuery = '';
                document.querySelector('input[name=client_id]').value = '';
                document.querySelector('input[name=first_name]').value = '';
                document.querySelector('input[name=last_name]').value = '';
                document.querySelector('input[name=phone]').value = '';
            }
        },
        filterClients(query) {
            if(!query || query.length === 0) return [];
            const lowerQuery = query.toLowerCase();
            return this.clients.filter(c => 
                c.name.toLowerCase().includes(lowerQuery)
            );
        },
        changeDate(direction) {
            const date = new Date(this.selectedDate);
            date.setDate(date.getDate() + direction);
            window.location.href = '{{ route('calendar.index') }}?date=' + date.toISOString().split('T')[0];
        },
        goToToday() {
            const today = new Date();
            window.location.href = '{{ route('calendar.index') }}?date=' + today.toISOString().split('T')[0];
        },
        deleteAppointment() {
            if(confirm('Opravdu smazat tuto rezervaci?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/appointments') }}/' + this.previewModal.appointment.id;
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(token);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        },
        openRepeatModal() {
            this.repeatModal.appointment = this.previewModal.appointment;
            this.repeatModal.mode = 'weeks';
            this.repeatModal.selectedDate = null;
            this.repeatModal.repeatWeeks = 1;
            this.repeatModal.alternatives = [];
            this.previewModal.show = false;
            this.repeatModal.show = true;
        },
        async checkAvailability(date) {
            if (!date) return;
            
            this.repeatModal.checking = true;
            this.repeatModal.alternatives = [];
            
            const startTime = date + ' ' + this.repeatModal.appointment.start_time + ':00';
            const endTime = date + ' ' + this.repeatModal.appointment.end_time + ':00';
            
            const response = await fetch('{{ route('appointments.check-availability') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    date: date,
                    start_time: startTime,
                    end_time: endTime
                })
            });
            
            const data = await response.json();
            this.repeatModal.checking = false;
            
            if (data.available) {
                this.createRepeatAppointment(date, this.repeatModal.appointment.start_time);
            } else {
                this.repeatModal.alternatives = data.alternatives;
            }
        },
        createFutureAppointment() {
            // Spočítat datum za N týdnů od původní rezervace
            // Extrahuj jen datum část (prvních 10 znaků: YYYY-MM-DD)
            const dateOnly = this.repeatModal.appointment.date.substring(0, 10);
            const [year, month, day] = dateOnly.split('-').map(Number);
            
            // Vytvoř datum s místním časem (ne UTC)
            const originalDate = new Date(year, month - 1, day);
            
            // Přičti týdny (7 dní na týden)
            const daysToAdd = this.repeatModal.repeatWeeks * 7;
            const futureDate = new Date(originalDate.getTime() + daysToAdd * 24 * 60 * 60 * 1000);
            
            // Formátuj jako YYYY-MM-DD
            const dateString = futureDate.getFullYear() + '-' + 
                              String(futureDate.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(futureDate.getDate()).padStart(2, '0');
            
            this.repeatModal.selectedDate = dateString;
            console.log('Původní datum:', dateOnly, 'Budoucí datum:', dateString, 'Týdnů:', this.repeatModal.repeatWeeks);
            
            // Nejdřív zkontrolovat dostupnost
            this.checkAvailability(dateString);
        },
        createRepeatAppointment(date, time, endTime = null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('appointments.store') }}';
            
            const fields = {
                '_token': '{{ csrf_token() }}',
                'client_id': this.repeatModal.appointment.client_id || '',
                'first_name': this.repeatModal.appointment.first_name,
                'last_name': this.repeatModal.appointment.last_name,
                'phone': this.repeatModal.appointment.phone || '',
                'date': date,
                'start_time': time,
                'end_time': endTime || this.repeatModal.appointment.end_time,
                'notes': this.repeatModal.appointment.notes || '',
                'redirect_to_date': date
            };
            
            Object.keys(fields).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">Kalendář</h2>
                    <p class="text-slate-400 mt-1">Denní rozvrh rezervací</p>
                </div>
                <button @click="openNewAppointment()" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium transition-colors">
                    + Nová rezervace
                </button>
            </div>

            <!-- Date Navigation -->
            <div class="glass rounded-xl p-6 mb-6 border border-slate-700/50 sticky top-0 z-10">
                <div class="flex items-center justify-between gap-4">
                    <button @click="changeDate(-1)" class="px-4 py-2 hover:bg-slate-700/50 rounded-lg transition-colors">
                        ← Předchozí
                    </button>
                    <button @click="goToToday()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors">
                        Dnes
                    </button>
                    <div class="text-center flex-1">
                        <h3 class="text-2xl font-bold text-white">{{ $selectedDate->format('d.m.Y') }}</h3>
                        <p class="text-slate-400 text-sm">{{ $selectedDate->locale('cs')->translatedFormat('l') }}</p>
                    </div>
                    <button @click="changeDate(1)" class="px-4 py-2 hover:bg-slate-700/50 rounded-lg transition-colors">
                        Následující →
                    </button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="glass rounded-xl border border-slate-700/50 overflow-hidden">
                <div class="grid grid-cols-[80px_1fr] border-b border-slate-700/50">
                    <div class="p-4 bg-slate-800/50 border-r border-slate-700/50">
                        <span class="text-sm font-medium text-slate-400">Čas</span>
                    </div>
                    <div class="p-4 bg-slate-800/50">
                        <span class="text-sm font-medium text-emerald-400">Rezervace</span>
                    </div>
                </div>

                <!-- Time slots (8:00 - 20:00) -->
                @php
                    $hours = range(8, 20);
                @endphp

                @foreach($hours as $hour)
                <div class="grid grid-cols-[80px_1fr] border-b border-slate-700/50 min-h-[80px]">
                    <div class="p-4 bg-slate-800/30 border-r border-slate-700/50">
                        <span class="text-sm text-slate-400">{{ sprintf('%02d:00', $hour) }}</span>
                    </div>
                    
                    <!-- Appointments -->
                    <div class="p-2 relative bg-slate-800/10 hover:bg-slate-700/20 transition-colors cursor-pointer" 
                         @click="openNewAppointment('{{ sprintf('%02d:00', $hour) }}')">
                        @foreach($appointments as $appointment)
                            @php
                                $startTime = substr($appointment->start_time, 11, 5); // HH:MM z datetime
                                $endTime = substr($appointment->end_time, 11, 5);
                                $startHour = (int)substr($startTime, 0, 2);
                                $startMin = (int)substr($startTime, 3, 2);
                                $endHour = (int)substr($endTime, 0, 2);
                                $endMin = (int)substr($endTime, 3, 2);
                            @endphp
                            @if($startHour == $hour)
                            <div class="absolute top-2 left-2 right-2 bg-emerald-500/20 border border-emerald-500/50 rounded-lg p-3 cursor-pointer hover:bg-emerald-500/30 transition-colors"
                                 style="height: {{ (($endHour - $startHour) * 80 + (($endMin - $startMin) / 60) * 80) - 8 }}px"
                                 @click.stop="openPreview({{ json_encode($appointment) }})">
                                <div class="text-sm font-medium text-white">{{ $appointment->first_name }} {{ $appointment->last_name }}</div>
                                <div class="text-xs text-emerald-300 mt-1">{{ $startTime }} - {{ $endTime }}</div>
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

        <!-- Preview Modal -->
        <template x-if="previewModal.show">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                 @click.self="previewModal.show = false">
                <div class="glass rounded-2xl p-6 w-full max-w-md border border-slate-700/50">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-2xl font-bold text-white" x-text="previewModal.appointment?.first_name + ' ' + previewModal.appointment?.last_name"></h3>
                        <button @click="previewModal.show = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4 mb-6">
                        <!-- Čas -->
                        <div class="flex items-center gap-3 text-slate-300">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="previewModal.appointment?.start_time + ' - ' + previewModal.appointment?.end_time"></span>
                        </div>

                        <!-- Datum -->
                        <div class="flex items-center gap-3 text-slate-300">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="previewModal.appointment?.date"></span>
                        </div>

                        <!-- Telefon -->
                        <template x-if="previewModal.appointment?.phone">
                            <div class="flex items-center gap-3 text-slate-300">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span x-text="previewModal.appointment?.phone"></span>
                            </div>
                        </template>

                        <!-- Poznámka -->
                        <template x-if="previewModal.appointment?.notes">
                            <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-700/50">
                                <div class="text-sm font-medium text-slate-400 mb-2">Poznámka</div>
                                <p class="text-slate-300" x-text="previewModal.appointment?.notes"></p>
                            </div>
                        </template>

                        <!-- Status -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-slate-400">Status:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium"
                                  :class="{
                                      'bg-blue-500/20 text-blue-300': previewModal.appointment?.status == 'scheduled',
                                      'bg-green-500/20 text-green-300': previewModal.appointment?.status == 'completed',
                                      'bg-red-500/20 text-red-300': previewModal.appointment?.status == 'cancelled'
                                  }"
                                  x-text="{
                                      'scheduled': 'Naplánováno',
                                      'completed': 'Dokončeno',
                                      'cancelled': 'Zrušeno'
                                  }[previewModal.appointment?.status]">
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mb-3">
                        <button @click="openEditModal(previewModal.appointment)" 
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Upravit
                        </button>
                        <button @click="deleteAppointment()" 
                                class="px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Smazat
                        </button>
                    </div>
                    <button @click="openRepeatModal()" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Opakovat rezervaci
                    </button>
                </div>
            </div>
        </template>

        <!-- Edit Modal -->
        <template x-if="editModal.show">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                 @click.self="editModal.show = false">
                <div class="glass rounded-2xl p-6 w-full max-w-md border border-slate-700/50">
                    <h3 class="text-xl font-bold text-white mb-4" x-text="editModal.appointment ? 'Upravit rezervaci' : 'Nová rezervace'"></h3>
                    
                    <form :action="editModal.appointment ? '{{ url('/appointments') }}/' + editModal.appointment.id : '{{ route('appointments.store') }}'" method="POST">
                        @csrf
                        <template x-if="editModal.appointment">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4">
                            <!-- Volba klienta -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Vybrat klienta (volitelné)</label>
                                <input type="hidden" name="client_id">
                                <div class="relative">
                                    <input type="text" 
                                           x-model="searchQuery"
                                           @focus="showDropdown = true"
                                           @click.away="showDropdown = false"
                                           @input="showDropdown = true"
                                           placeholder="Začni psát jméno nebo vyber klienta..."
                                           class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    
                                    <div x-show="showDropdown && searchQuery.length > 0" 
                                         class="absolute z-10 w-full mt-1 bg-slate-800 border border-slate-700 rounded-lg max-h-60 overflow-y-auto shadow-xl">
                                        <template x-for="client in filterClients(searchQuery)" :key="client.id">
                                            <div @click="selectClient(client.id)"
                                                 class="px-4 py-2 hover:bg-slate-700 cursor-pointer text-white">
                                                <span x-text="client.name"></span>
                                                <span x-show="client.phone" x-text="' - ' + client.phone" class="text-slate-400 text-sm"></span>
                                            </div>
                                        </template>
                                        <div x-show="filterClients(searchQuery).length === 0" class="px-4 py-2 text-slate-400 text-sm">
                                            Žádný klient nenalezen
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Jméno -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Jméno *</label>
                                <input type="text" name="first_name" required 
                                       :value="editModal.appointment?.first_name || ''"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Příjmení -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Příjmení *</label>
                                <input type="text" name="last_name" required 
                                       :value="editModal.appointment?.last_name || ''"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Telefon -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Telefon</label>
                                <input type="tel" name="phone" 
                                       :value="editModal.appointment?.phone || ''"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Datum -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Datum *</label>
                                <input type="date" name="date" required 
                                       :value="editModal.appointment?.date || '{{ $selectedDate->format('Y-m-d') }}'"
                                       class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <!-- Čas od-do -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Čas od *</label>
                                    <input type="time" name="start_time" required 
                                           x-init="$el.value = editModal.appointment?.start_time ? editModal.appointment.start_time.substring(0, 5) : (editModal.defaultTime || '')"
                                           class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Čas do *</label>
                                    <input type="time" name="end_time" required 
                                           x-init="$el.value = editModal.appointment?.end_time ? editModal.appointment.end_time.substring(0, 5) : ''"
                                           class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>

                            <!-- Poznámky -->
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Poznámky</label>
                                <textarea name="notes" rows="3" 
                                          x-text="editModal.appointment?.notes || ''"
                                          class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>

                            <!-- Status (pouze pro existující) -->
                            <template x-if="editModal.appointment">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                                    <select name="status" class="w-full bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white">
                                        <option value="scheduled" :selected="editModal.appointment?.status == 'scheduled'">Naplánováno</option>
                                        <option value="completed" :selected="editModal.appointment?.status == 'completed'">Dokončeno</option>
                                        <option value="cancelled" :selected="editModal.appointment?.status == 'cancelled'">Zrušeno</option>
                                    </select>
                                </div>
                            </template>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 mt-6">
                            <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium transition-colors">
                                <span x-text="editModal.appointment ? 'Uložit' : 'Vytvořit'"></span>
                            </button>
                            <button type="button" @click="editModal.show = false" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition-colors">
                                Zrušit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Repeat Modal -->
        <template x-if="repeatModal.show">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                 @click.self="repeatModal.show = false">
                <div class="glass rounded-2xl p-6 w-full max-w-lg border border-slate-700/50">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-white">Opakovat rezervaci</h3>
                            <p class="text-slate-400 mt-1" x-text="repeatModal.appointment?.first_name + ' ' + repeatModal.appointment?.last_name"></p>
                        </div>
                        <button @click="repeatModal.show = false" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Původní čas -->
                    <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-700/50 mb-6">
                        <div class="text-sm font-medium text-slate-400 mb-2">Původní rezervace</div>
                        <div class="flex items-center gap-3 text-white">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="repeatModal.appointment?.start_time + ' - ' + repeatModal.appointment?.end_time"></span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-400 mt-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="repeatModal.appointment?.date"></span>
                        </div>
                    </div>

                    <!-- Za kolik týdnů -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Za kolik týdnů?</label>
                        <div class="flex items-center gap-4">
                            <input type="number" 
                                   x-model.number="repeatModal.repeatWeeks"
                                   min="1"
                                   max="52"
                                   class="flex-1 bg-slate-800/60 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                   placeholder="např. 6">
                            <span class="text-slate-400 text-sm" x-text="repeatModal.repeatWeeks === 1 ? '1 týden' : repeatModal.repeatWeeks + ' týdnů'"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            Vytvoří se 1 rezervace za <span x-text="repeatModal.repeatWeeks"></span> 
                            <span x-text="repeatModal.repeatWeeks === 1 ? 'týden' : (repeatModal.repeatWeeks < 5 ? 'týdny' : 'týdnů')"></span>
                            (stejný den a čas)
                        </p>
                    </div>

                    <!-- Checking spinner -->
                    <template x-if="repeatModal.checking">
                        <div class="flex items-center justify-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
                            <span class="ml-3 text-slate-300">Kontroluji dostupnost...</span>
                        </div>
                    </template>

                    <!-- Alternativní časy -->
                    <template x-if="repeatModal.alternatives.length > 0">
                        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-3 mb-4">
                                <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <div class="font-medium text-yellow-300 mb-1">Původní čas není volný</div>
                                    <div class="text-sm text-slate-300">Vyberte jeden z dostupných termínů:</div>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <template x-for="alt in repeatModal.alternatives" :key="alt.start_time">
                                    <button @click="createRepeatAppointment(repeatModal.selectedDate, alt.start_time, alt.end_time)"
                                            class="w-full bg-slate-700 hover:bg-slate-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-between">
                                        <span x-text="alt.start_time + ' - ' + alt.end_time"></span>
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Buttons -->
                    <div class="flex gap-3 mt-6">
                        <button type="button" 
                                @click="repeatModal.show = false" 
                                class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-medium transition-colors">
                            Zrušit
                        </button>
                        <button type="button" 
                                @click="createFutureAppointment()"
                                :disabled="!repeatModal.repeatWeeks || repeatModal.checking"
                                :class="(!repeatModal.repeatWeeks || repeatModal.checking) ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-medium transition-colors"
                                x-text="repeatModal.checking ? 'Kontroluji...' : 'Vytvořit rezervaci'">
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </main>
</div>

</body>
</html>
