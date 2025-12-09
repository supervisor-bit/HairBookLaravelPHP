<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail návštěvy - HairBook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .glass { background: linear-gradient(135deg, rgba(33, 39, 55, 0.85), rgba(21, 27, 43, 0.82)); backdrop-filter: blur(16px); }
        @media print {
            body { background: white; color: black; }
            .no-print { display: none !important; }
            .glass { background: white; border: 1px solid #ddd; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
<div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(90,204,255,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,117,215,0.12),transparent_25%),radial-gradient(circle_at_50%_80%,rgba(120,178,255,0.10),transparent_25%)] pointer-events-none no-print"></div>

<div class="relative min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-2xl">
        {{-- Tlačítka nahoře --}}
        <div class="flex gap-3 mb-6 no-print">
            <a href="{{ route('finance.index') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                ← Zpět na Finance
            </a>
            <a href="{{ route('dashboard', ['section' => 'clients', 'client' => $visit->client_id]) }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                👤 Profil klienta
            </a>
            <button onclick="window.print()" class="ml-auto px-4 py-2 rounded-lg bg-emerald-500 text-white hover:bg-emerald-400 transition font-semibold">
                🖨️ Tisknout
            </button>
        </div>

        {{-- Účtenka --}}
        <div class="glass border border-slate-700 rounded-2xl p-8 space-y-6" style="font-family: 'Courier New', monospace;">
            <div class="text-center border-b border-slate-700 pb-4">
                <h1 class="text-3xl font-bold mb-2">ÚČTENKA</h1>
                <div class="text-slate-400 text-sm">HairBook Salon</div>
            </div>
            
            {{-- Info o návštěvě --}}
            <div class="border-b border-slate-700 pb-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-400">Klient:</span>
                    <span class="font-semibold">{{ $visit->client->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Datum:</span>
                    <span>{{ \Carbon\Carbon::parse($visit->occurred_at)->format('d.m.Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Čas:</span>
                    <span>{{ \Carbon\Carbon::parse($visit->occurred_at)->format('H:i') }}</span>
                </div>
                @if($visit->note)
                <div class="flex justify-between">
                    <span class="text-slate-400">Poznámka:</span>
                    <span class="text-right max-w-xs">{{ $visit->note }}</span>
                </div>
                @endif
            </div>
            
            {{-- Úkony --}}
            @if($visit->services->isNotEmpty())
            <div class="space-y-3">
                <div class="font-bold text-lg border-b border-slate-700 pb-2">Úkony:</div>
                @foreach($visit->services as $service)
                <div class="pl-4 space-y-1">
                    <div class="font-semibold">{{ $service->title }}</div>
                    @if($service->note)
                    <div class="text-sm text-slate-400 italic">{{ $service->note }}</div>
                    @endif
                    @if($service->products->isNotEmpty())
                    <div class="text-xs text-slate-500 pl-4">
                        @foreach($service->products as $sp)
                            <div>• {{ $sp->product->name }}: {{ $sp->used_grams }}g</div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-slate-500 pl-4 italic">bez materiálu</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
            
            {{-- Prodej domů --}}
            @if($visit->retailItems->isNotEmpty())
            <div class="space-y-3">
                <div class="font-bold text-lg border-b border-slate-700 pb-2">Prodej domů:</div>
                @foreach($visit->retailItems as $item)
                <div class="pl-4 flex justify-between">
                    <span>{{ $item->product->name }}</span>
                    <span>{{ $item->quantity_units }} ks</span>
                </div>
                @endforeach
            </div>
            @endif
            
            {{-- Celková cena --}}
            <div class="border-t-2 border-slate-600 pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold">CELKEM:</span>
                    <span class="text-3xl font-bold text-emerald-400">{{ number_format($visit->total_price, 0, ',', ' ') }} Kč</span>
                </div>
            </div>
            
            {{-- Uzavřeno info --}}
            @if($visit->closed_at)
            <div class="text-center text-sm text-slate-400 border-t border-slate-700 pt-4">
                <div>Uzavřeno: {{ \Carbon\Carbon::parse($visit->closed_at)->format('d.m.Y H:i') }}</div>
            </div>
            @endif
            
            <div class="text-center text-sm text-slate-500 pt-4">
                Děkujeme za návštěvu!
            </div>
        </div>
    </div>
</div>
</body>
</html>
