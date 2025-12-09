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
            <div class="grid grid-cols-3 gap-6">
                <div class="glass border border-slate-700 rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Celkové příjmy</div>
                    <div class="text-3xl font-bold text-emerald-400">{{ number_format($totalRevenue, 0, ',', ' ') }} Kč</div>
                </div>
                <div class="glass border border-slate-700 rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Počet návštěv</div>
                    <div class="text-3xl font-bold text-sky-400">{{ $visits->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">{{ $visits->unique('client_id')->count() }} různých klientů</div>
                </div>
                <div class="glass border border-slate-700 rounded-xl p-4">
                    <div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Průměr / návštěva</div>
                    <div class="text-3xl font-bold text-purple-400">
                        {{ $visits->count() > 0 ? number_format($totalRevenue / $visits->count(), 0, ',', ' ') : 0 }} Kč
                    </div>
                </div>
            </div>
        </div>

        {{-- Seznam návštěv --}}
        <div class="flex-1 overflow-y-auto px-6 py-4">
            @if($visits->isEmpty())
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-slate-400">
                        <div class="text-6xl mb-4">📊</div>
                        <div class="text-xl font-semibold mb-2">Žádné návštěvy</div>
                        <div class="text-sm">V tomto období nebyly uzavřeny žádné návštěvy</div>
                    </div>
                </div>
            @else
                <div class="glass border border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Datum</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Čas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Klient</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-300 uppercase tracking-wider">Cena</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-300 uppercase tracking-wider">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($visits as $visit)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-4 py-3 text-sm text-slate-200">
                                        {{ \Carbon\Carbon::parse($visit->occurred_at)->format('d.m.Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300">
                                        {{ \Carbon\Carbon::parse($visit->occurred_at)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('dashboard', ['section' => 'clients', 'client' => $visit->client_id]) }}" 
                                           class="text-sm font-medium text-emerald-400 hover:text-emerald-300">
                                            {{ $visit->client->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-lg font-semibold text-emerald-400">
                                            {{ number_format($visit->total_price, 0, ',', ' ') }} Kč
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('visits.show', $visit) }}" 
                                           class="inline-block px-3 py-1 rounded-lg bg-slate-700 text-slate-300 text-xs hover:bg-slate-600 transition">
                                            Zobrazit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
</div>
</body>
</html>
