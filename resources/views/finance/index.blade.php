<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance - HairBook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Manrope', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .glass { background: linear-gradient(135deg, rgba(33, 39, 55, 0.85), rgba(21, 27, 43, 0.82)); backdrop-filter: blur(16px); }
    </style>
</head>
<body class="h-screen overflow-hidden bg-slate-950 text-slate-100">
<div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(90,204,255,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,117,215,0.12),transparent_25%),radial-gradient(circle_at_50%_80%,rgba(120,178,255,0.10),transparent_25%)] pointer-events-none"></div>

<div class="relative h-full flex">
    {{-- Sidebar --}}
    <aside class="w-64 border-r border-slate-800 glass flex flex-col">
        <div class="p-5">
            <div class="text-xs uppercase tracking-[0.3em] text-slate-400">HairBook</div>
            <div class="text-2xl font-semibold">Salon OS</div>
        </div>
        <nav class="px-3 space-y-2">
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
                    <div class="text-sm font-semibold">📅 Kalendář</div>
                    <div class="text-xs text-slate-400">Denní rozvrh</div>
                </div>
            </a>
            <a href="{{ route('finance.index') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition bg-slate-800 text-white">
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

    {{-- Main content --}}
    <main class="flex-1 flex flex-col overflow-hidden">
        {{-- Header --}}
        <header class="bg-slate-900/60 backdrop-blur-sm border-b border-slate-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Finance</div>
                    <div class="text-2xl font-semibold">Přehled příjmů</div>
                </div>
                
                {{-- Filtry období --}}
                <div class="flex gap-2">
                    <a href="{{ route('finance.index', ['period' => 'today']) }}" 
                       class="px-4 py-2 rounded-lg transition @if($period === 'today') bg-amber-500 text-white @else bg-slate-800 text-slate-300 hover:bg-slate-700 @endif">
                        Dnes
                    </a>
                    <a href="{{ route('finance.index', ['period' => 'week']) }}" 
                       class="px-4 py-2 rounded-lg transition @if($period === 'week') bg-amber-500 text-white @else bg-slate-800 text-slate-300 hover:bg-slate-700 @endif">
                        Týden
                    </a>
                    <a href="{{ route('finance.index', ['period' => 'month']) }}" 
                       class="px-4 py-2 rounded-lg transition @if($period === 'month') bg-amber-500 text-white @else bg-slate-800 text-slate-300 hover:bg-slate-700 @endif">
                        Měsíc
                    </a>
                    <a href="{{ route('finance.index', ['period' => 'year']) }}" 
                       class="px-4 py-2 rounded-lg transition @if($period === 'year') bg-amber-500 text-white @else bg-slate-800 text-slate-300 hover:bg-slate-700 @endif">
                        Rok
                    </a>
                    <a href="{{ route('finance.index', ['period' => 'all']) }}" 
                       class="px-4 py-2 rounded-lg transition @if($period === 'all') bg-amber-500 text-white @else bg-slate-800 text-slate-300 hover:bg-slate-700 @endif">
                        Vše
                    </a>
                </div>
            </div>
        </header>

        {{-- Stats --}}
        <div class="px-6 py-4 bg-slate-900/40 border-b border-slate-800">
            <div class="grid grid-cols-4 gap-4">
                <div class="glass border border-slate-700 rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Služby</div>
                    <div class="text-2xl font-bold text-emerald-400">{{ number_format($totalRevenue, 0, ',', ' ') }} Kč</div>
                </div>
                <div class="glass border border-slate-700 rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Prodej domů</div>
                    <div class="text-2xl font-bold text-sky-400">{{ number_format($totalRetail, 0, ',', ' ') }} Kč</div>
                </div>
                <div class="glass border border-emerald-600 rounded-xl p-4 bg-emerald-500/10">
                    <div class="text-xs uppercase tracking-wider text-emerald-400 mb-1">Celkem</div>
                    <div class="text-2xl font-bold text-emerald-300">{{ number_format($totalCombined, 0, ',', ' ') }} Kč</div>
                </div>
                <div class="glass border border-slate-700 rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Návštěvy</div>
                    <div class="text-2xl font-bold text-purple-400">{{ $visits->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">{{ $visits->unique('client_id')->count() }} klientů</div>
                </div>
            </div>
        </div>

        {{-- Obsah --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Statistika po měsících --}}
            @if($monthlySales->isNotEmpty())
            <div class="px-6 py-4">
                <div class="text-lg font-semibold text-slate-200 mb-4">📊 Statistika po měsících</div>
                <div class="glass border border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Měsíc</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Služby</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Prodej domů</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Celkem</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider">Návštěv</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($monthlySales as $sale)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-4 py-3 text-base font-semibold text-slate-200">
                                        {{ \Carbon\Carbon::parse($sale->month . '-01')->locale('cs')->isoFormat('MMMM YYYY') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-base text-emerald-400 font-semibold">
                                        {{ number_format($sale->total_services, 0, ',', ' ') }} Kč
                                    </td>
                                    <td class="px-4 py-3 text-right text-base text-sky-400 font-semibold">
                                        {{ number_format($sale->total_retail ?? 0, 0, ',', ' ') }} Kč
                                    </td>
                                    <td class="px-4 py-3 text-right text-xl text-emerald-300 font-bold">
                                        {{ number_format(($sale->total_services + ($sale->total_retail ?? 0)), 0, ',', ' ') }} Kč
                                    </td>
                                    <td class="px-4 py-3 text-center text-base text-slate-300 font-medium">
                                        {{ $sale->visit_count }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Seznam návštěv --}}
            @if($visits->isNotEmpty())
            <div class="px-6 pb-4" x-data="{ expandedMonths: {} }">
                <div class="text-lg font-semibold text-slate-200 mb-4">📋 Detail návštěv</div>
                <div class="space-y-4">
                    @php
                        $groupedVisits = $visits->groupBy(function($visit) {
                            return \Carbon\Carbon::parse($visit->occurred_at)->format('Y-m');
                        });
                    @endphp
                    
                    @foreach($groupedVisits as $month => $monthVisits)
                        <div class="glass border border-slate-700 rounded-xl overflow-hidden" x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }">
                            {{-- Nadpis měsíce - klikací --}}
                            <button @click="expanded = !expanded" class="w-full bg-slate-800/70 px-4 py-3 border-b border-slate-700 flex items-center justify-between hover:bg-slate-800 transition">
                                <h3 class="text-base font-bold text-emerald-400">
                                    {{ \Carbon\Carbon::parse($month . '-01')->locale('cs')->isoFormat('MMMM YYYY') }}
                                    <span class="text-sm text-slate-400 font-normal ml-2">({{ $monthVisits->count() }} návštěv)</span>
                                </h3>
                                <svg x-show="!expanded" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                <svg x-show="expanded" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            </button>
                            
                            {{-- Tabulka návštěv - rozbalovací --}}
                            <div x-show="expanded" x-collapse>
                            <table class="w-full">
                                <thead class="bg-slate-800/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Datum</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Čas</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Klient</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Cena</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Prodej domů</th>
                                        <th class="px-4 py-2 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider">Akce</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    @foreach($monthVisits as $visit)
                                        <tr class="hover:bg-slate-800/30 transition">
                                            <td class="px-4 py-2 text-sm text-slate-200">
                                                {{ \Carbon\Carbon::parse($visit->occurred_at)->format('d.m.Y') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-slate-300">
                                                {{ \Carbon\Carbon::parse($visit->occurred_at)->format('H:i') }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <a href="{{ route('dashboard', ['section' => 'clients', 'client' => $visit->client_id]) }}" 
                                                   class="text-sm font-medium text-emerald-400 hover:text-emerald-300">
                                                    {{ $visit->client->name }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <span class="text-base font-semibold text-emerald-400">
                                                    {{ number_format($visit->total_price, 0, ',', ' ') }} Kč
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                @if($visit->retail_price)
                                                    <span class="text-sm font-medium text-sky-400">
                                                        {{ number_format($visit->retail_price, 0, ',', ' ') }} Kč
                                                    </span>
                                                @else
                                                    <span class="text-sm text-slate-500">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <a href="{{ route('visits.show', ['visit' => $visit, 'from' => 'finance']) }}" 
                                                   class="inline-block px-3 py-1 rounded-lg bg-slate-700 text-slate-300 text-xs hover:bg-slate-600 transition">
                                                    Zobrazit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </main>
</div>
</body>
</html>
