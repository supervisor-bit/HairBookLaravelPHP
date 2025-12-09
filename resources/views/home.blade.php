<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Úvodní přehled - HairBook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .glass { 
            background: linear-gradient(135deg, rgba(33, 39, 55, 0.85), rgba(21, 27, 43, 0.82)); 
            backdrop-filter: blur(16px); 
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 h-screen overflow-hidden flex">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(90,204,255,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,117,215,0.12),transparent_25%),radial-gradient(circle_at_50%_80%,rgba(120,178,255,0.10),transparent_25%)] pointer-events-none"></div>

    <!-- Sidebar -->
    <aside class="w-64 border-r border-slate-800 glass flex flex-col relative z-10">
        <div class="p-5">
            <div class="text-xs uppercase tracking-[0.3em] text-slate-400">HairBook</div>
            <div class="text-2xl font-semibold">Salon OS</div>
        </div>
        <nav class="px-3 space-y-2">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl transition bg-slate-800 text-white">
                <span class="h-2 w-2 rounded-full bg-purple-400 shadow-[0_0_0_6px_rgba(192,132,252,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">🏠 Domů</div>
                    <div class="text-xs text-slate-400">Úvodní obrazovka</div>
                </div>
            </a>
            <a href="{{ route('dashboard', ['section' => 'clients']) }}" class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_6px_rgba(16,185,129,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">Klienti</div>
                    <div class="text-xs text-slate-400">Historie návštěv, uzávěrky</div>
                </div>
            </a>
            <a href="{{ route('dashboard', ['section' => 'products']) }}" class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
                <span class="h-2 w-2 rounded-full bg-sky-400 shadow-[0_0_0_6px_rgba(56,189,248,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">Produkty</div>
                    <div class="text-xs text-slate-400">Sklad ks + odpis v gramech</div>
                </div>
            </a>
            <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl transition text-slate-300 hover:bg-slate-800/60">
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
    <main class="flex-1 overflow-y-auto p-6 relative z-10">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-4xl font-bold mb-2">Vítejte v HairBook! 👋</h1>
                <p class="text-slate-400">Přehled vašeho salonu na jednom místě</p>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Clients -->
                <div class="glass border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25">
                            <span class="text-2xl">👥</span>
                        </div>
                        <span class="text-xs text-slate-400 uppercase tracking-wider">Celkem</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">{{ $totalClients }}</div>
                    <div class="text-sm text-slate-400">Klientů v databázi</div>
                </div>

                <!-- Total Visits -->
                <div class="glass border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
                            <span class="text-2xl">✂️</span>
                        </div>
                        <span class="text-xs text-slate-400 uppercase tracking-wider">Návštěvy</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">{{ $totalVisits }}</div>
                    <div class="text-sm text-slate-400">
                        <span class="text-emerald-400">{{ $closedVisits }} uzavřeno</span> · 
                        <span class="text-amber-400">{{ $openVisits }} otevřeno</span>
                    </div>
                </div>

                <!-- Today Revenue -->
                <div class="glass border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/25">
                            <span class="text-2xl">💰</span>
                        </div>
                        <span class="text-xs text-slate-400 uppercase tracking-wider">Dnes</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">{{ number_format($todayRevenue, 0, ',', ' ') }} Kč</div>
                    <div class="text-sm text-slate-400">Dnešní tržby</div>
                </div>

                <!-- Month Revenue -->
                <div class="glass border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/25">
                            <span class="text-2xl">📊</span>
                        </div>
                        <span class="text-xs text-slate-400 uppercase tracking-wider">Měsíc</span>
                    </div>
                    <div class="text-3xl font-bold mb-1">{{ number_format($monthRevenue, 0, ',', ' ') }} Kč</div>
                    <div class="text-sm text-slate-400">Tržby v {{ now()->locale('cs')->translatedFormat('F') }}</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Visits -->
                <div class="glass border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Poslední návštěvy</h2>
                        <a href="{{ route('dashboard', ['section' => 'clients']) }}" class="text-sm text-emerald-400 hover:text-emerald-300">Zobrazit vše →</a>
                    </div>
                    @if($recentVisits->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentVisits as $visit)
                            <div class="flex items-center justify-between py-3 border-b border-slate-700 last:border-0">
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $visit->client->name }}</div>
                                    <div class="text-sm text-slate-400">{{ $visit->occurred_at->locale('cs')->translatedFormat('d. M Y, H:i') }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">{{ number_format($visit->total_price, 0, ',', ' ') }} Kč</div>
                                    @if($visit->closed_at)
                                        <div class="text-xs text-emerald-400">Uzavřeno</div>
                                    @else
                                        <div class="text-xs text-amber-400">Otevřeno</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400">
                            <span class="text-4xl mb-4 block">📋</span>
                            Zatím žádné návštěvy
                        </div>
                    @endif
                </div>

                <!-- Top Clients -->
                <div class="glass border border-slate-700 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">Top klienti</h2>
                        <a href="{{ route('dashboard', ['section' => 'clients']) }}" class="text-sm text-emerald-400 hover:text-emerald-300">Zobrazit vše →</a>
                    </div>
                    @if($topClients->count() > 0)
                        <div class="space-y-4">
                            @foreach($topClients as $index => $client)
                            <div class="flex items-center gap-4 py-3 border-b border-slate-700 last:border-0">
                                <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center font-bold text-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $client->name }}</div>
                                    <div class="text-sm text-slate-400">{{ $client->visits_count }} {{ Str::plural('návštěva', $client->visits_count) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400">
                            <span class="text-4xl mb-4 block">👥</span>
                            Zatím žádní klienti
                        </div>
                    @endif
                </div>

                <!-- Low Stock Products -->
                <div class="glass border border-slate-700 rounded-2xl p-6 lg:col-span-2">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold">⚠️ Produkty s nízkým stavem</h2>
                        <a href="{{ route('dashboard', ['section' => 'products']) }}" class="text-sm text-emerald-400 hover:text-emerald-300">Zobrazit vše →</a>
                    </div>
                    @if($lowStockProducts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($lowStockProducts as $product)
                            <div class="bg-slate-800/50 border border-amber-500/30 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold">{{ $product->name }}</div>
                                    <div class="px-2 py-1 bg-amber-500/20 text-amber-400 text-xs font-bold rounded">
                                        {{ $product->stock }} {{ $product->unit }}
                                    </div>
                                </div>
                                <div class="text-sm text-slate-400">{{ $product->productGroup->name ?? 'Nezařazeno' }}</div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400">
                            <span class="text-4xl mb-4 block">✅</span>
                            Všechny produkty mají dostatečný stav
                        </div>
                    @endif
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="mt-6 glass border border-slate-700 rounded-2xl p-8 text-center">
                <div class="text-sm text-slate-400 uppercase tracking-wider mb-2">Celkové tržby (uzavřené návštěvy)</div>
                <div class="text-5xl font-bold bg-gradient-to-r from-emerald-400 to-emerald-600 bg-clip-text text-transparent">
                    {{ number_format($totalRevenue, 0, ',', ' ') }} Kč
                </div>
            </div>
        </div>
    </main>
</body>
</html>
