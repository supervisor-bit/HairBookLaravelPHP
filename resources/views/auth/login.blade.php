<!DOCTYPE html>
<html lang="cs" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení - HairBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.4/dist/cdn.min.js"></script>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 1s linear infinite; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(16,185,129,0.15),transparent_50%),radial-gradient(circle_at_70%_70%,rgba(120,178,255,0.12),transparent_50%)] pointer-events-none"></div>
    
    <div class="relative h-full flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-400 to-sky-400 bg-clip-text text-transparent mb-2">
                    🎨 HairBook
                </h1>
                <p class="text-slate-400">Kadeřnický deník</p>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-sm border border-slate-800 rounded-2xl shadow-2xl p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold mb-2">Vítejte zpět</h2>
                    <p class="text-sm text-slate-400">Zadejte heslo pro přístup</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 mb-4">
                        <p class="text-red-200 text-sm">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.login.store') }}" class="space-y-4" 
                      x-data="{ loading: false }" 
                      @submit="loading = true">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Heslo</label>
                        <input type="password" 
                               name="password" 
                               required 
                               autofocus
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none"
                               placeholder="Zadejte heslo">
                    </div>

                    <button type="submit" 
                            :disabled="loading"
                            class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-950 font-semibold py-3 rounded-lg hover:from-emerald-400 hover:to-emerald-500 transition-all shadow-lg shadow-emerald-500/25 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span x-show="loading" class="w-5 h-5 border-2 border-slate-950/30 border-t-slate-950 rounded-full spinner"></span>
                        <span x-text="loading ? 'Přihlašování...' : 'Přihlásit se'"></span>
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-slate-500 mt-6">
                HairBook © 2025 - Profesionální správa kadeřnického salonu
            </p>
        </div>
    </div>
</body>
</html>
