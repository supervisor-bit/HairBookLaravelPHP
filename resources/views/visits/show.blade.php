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
            @if(request()->get('from') === 'finance')
                <a href="{{ route('finance.index') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                    ← Zpět na Finance
                </a>
                <a href="{{ route('dashboard', ['section' => 'clients', 'client' => $visit->client_id]) }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                    👤 Profil klienta
                </a>
                <button onclick="window.print()" class="ml-auto px-4 py-2 rounded-lg bg-emerald-500 text-white hover:bg-emerald-400 transition font-semibold">
                    🖨️ Tisknout
                </button>
            @else
                <a href="{{ route('dashboard', ['section' => 'clients', 'client' => $visit->client_id]) }}" class="px-4 py-2 rounded-lg bg-emerald-500 text-white hover:bg-emerald-400 transition font-semibold">
                    ← Zpět na klienta
                </a>
                @if($visit->status !== 'closed')
                    <button onclick="showCloseModal()" class="ml-auto px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-400 transition font-semibold">
                        🔒 Uzavřít a odepsat
                    </button>
                @endif
            @endif
        </div>

        {{-- Účtenka/Detail --}}
        <div class="glass border border-slate-700 rounded-2xl p-8 space-y-6" style="font-family: 'Courier New', monospace;">
            @if(request()->get('from') === 'finance')
                <div class="text-center border-b border-slate-700 pb-4">
                    <h1 class="text-3xl font-bold mb-2">ÚČTENKA</h1>
                    <div class="text-slate-400 text-sm">HairBook Salon</div>
                </div>
            @else
                <div class="text-center border-b border-slate-700 pb-4">
                    <h1 class="text-3xl font-bold mb-2">Detail návštěvy</h1>
                </div>
            @endif
            
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
            
            {{-- Ceny --}}
            <div class="border-t-2 border-slate-600 pt-4 space-y-2">
                @if($visit->retail_price)
                <div class="flex justify-between items-center">
                    <span class="text-lg">Celkem za produkty:</span>
                    <span class="text-xl font-semibold text-sky-400">{{ number_format($visit->retail_price, 0, ',', ' ') }} Kč</span>
                </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-lg">Celkem za návštěvu:</span>
                    <span class="text-xl font-semibold">{{ number_format($visit->total_price, 0, ',', ' ') }} Kč</span>
                </div>
                <div class="flex justify-between items-center border-t-2 border-slate-500 pt-2">
                    <span class="text-xl font-bold">CELKEM ZA OBOJÍ:</span>
                    <span class="text-3xl font-bold text-emerald-400">{{ number_format($visit->total_price + ($visit->retail_price ?? 0), 0, ',', ' ') }} Kč</span>
                </div>
            </div>
            
            {{-- Uzavřeno info --}}
            @if($visit->closed_at)
            <div class="text-center text-sm text-slate-400 border-t border-slate-700 pt-4">
                <div>Uzavřeno: {{ \Carbon\Carbon::parse($visit->closed_at)->format('d.m.Y H:i') }}</div>
            </div>
            @endif
            
            @if(request()->get('from') === 'finance')
                <div class="text-center text-sm text-slate-500 pt-4">
                    Děkujeme za návštěvu!
                </div>
            @else
                <div class="text-center text-sm border-t border-slate-700 pt-4">
                    @if($visit->status === 'closed')
                        <div class="flex items-center justify-center gap-2 text-emerald-400">
                            <span class="text-xl">✓</span>
                            <span class="font-semibold">Návštěva uzavřena, materiál odepsán</span>
                        </div>
                    @else
                        <div class="flex items-center justify-center gap-2 text-amber-400">
                            <span class="text-xl">⏳</span>
                            <span class="font-semibold">Návštěva otevřená (koncept)</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Modal pro uzavření návštěvy --}}
    <div id="closeModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4 text-emerald-400">Uzavření návštěvy</h2>
            
            <div class="space-y-4 mb-6">
                <p class="text-slate-300">Tato akce uzavře návštěvu a odepíše použitý materiál ze skladu:</p>
                
                @if($visit->services->count() > 0)
                <div class="glass border border-slate-700 rounded-lg p-4">
                    <h3 class="font-semibold mb-3 text-emerald-300">📦 Materiál k odpisu:</h3>
                    <div class="space-y-2">
                        @foreach($visit->services as $service)
                            @if($service->products->count() > 0)
                                <div class="text-sm">
                                    <div class="font-semibold text-slate-200">{{ $service->title }}</div>
                                    @foreach($service->products as $product)
                                        @if($product->product)
                                        <div class="ml-4 text-slate-400">
                                            • {{ $product->product->name }}: 
                                            <span class="text-red-400 font-semibold">-{{ number_format($product->deducted_units, 3) }} ks</span>
                                            ({{ $product->used_grams }} g)
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="glass border border-amber-600 rounded-lg p-4 bg-amber-500/10">
                    <p class="text-amber-300 font-semibold">⚠️ Upozornění:</p>
                    <p class="text-sm text-slate-300 mt-1">Po uzavření již nebude možné návštěvu upravovat a materiál bude trvale odepsán ze skladu.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="hideCloseModal()" class="flex-1 px-4 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition">
                    Zrušit
                </button>
                <form action="{{ route('visits.close', $visit) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-400 transition font-semibold">
                        🔒 Uzavřít a odepsat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showCloseModal() {
        document.getElementById('closeModal').classList.remove('hidden');
    }
    
    function hideCloseModal() {
        document.getElementById('closeModal').classList.add('hidden');
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideCloseModal();
        }
    });
    
    // Close modal on backdrop click
    document.getElementById('closeModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            hideCloseModal();
        }
    });
</script>
</body>
</html>
