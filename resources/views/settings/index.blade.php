<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nastavení - HairBook</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.4/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .glass { background: linear-gradient(135deg, rgba(33, 39, 55, 0.85), rgba(21, 27, 43, 0.82)); backdrop-filter: blur(16px); }
        [x-cloak] { display: none; }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 1s linear infinite; }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast-enter { animation: slideInRight 0.3s ease-out; }
    </style>
</head>
<body class="h-screen overflow-hidden bg-slate-950 text-slate-100" x-data="settingsApp()" x-init="init()">
<div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(90,204,255,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,117,215,0.12),transparent_25%),radial-gradient(circle_at_50%_80%,rgba(120,178,255,0.10),transparent_25%)] pointer-events-none"></div>

{{-- Loading Overlay --}}
<div x-show="loading" 
     x-transition
     class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-[100]" 
     style="display: none;">
    <div class="glass border border-slate-700 rounded-2xl p-8 flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full spinner"></div>
        <div class="text-slate-200 font-medium" x-text="loadingMessage"></div>
    </div>
</div>

{{-- Toast Container --}}
<div class="fixed top-4 right-4 z-[100] space-y-2" style="max-width: 400px;">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast-enter glass border rounded-lg shadow-2xl p-4 flex items-start gap-3"
             :class="{
                 'border-emerald-500/50': toast.type === 'success',
                 'border-red-500/50': toast.type === 'error',
                 'border-blue-500/50': toast.type === 'info'
             }">
            <div class="text-2xl" x-show="toast.type === 'success'">✅</div>
            <div class="text-2xl" x-show="toast.type === 'error'">❌</div>
            <div class="text-2xl" x-show="toast.type === 'info'">ℹ️</div>
            <div class="flex-1">
                <div class="font-semibold text-slate-100" x-text="toast.message"></div>
            </div>
            <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-slate-400 hover:text-slate-200">×</button>
        </div>
    </template>
</div>

<div class="relative h-full flex flex-col">
    {{-- Header --}}
    <header class="glass border-b border-slate-800 px-6 py-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
                    ← Zpět na dashboard
                </a>
                <div>
                    <h1 class="text-2xl font-semibold">⚙️ Nastavení</h1>
                    <p class="text-sm text-slate-400">Konfigurace aplikace a správa dat</p>
                </div>
            </div>
        </div>
    </header>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- Salon Info --}}
            <form @submit.prevent="saveSettings()" class="glass border border-slate-700 rounded-2xl p-6 space-y-4">
                <h2 class="text-lg font-semibold mb-4">Informace o salonu</h2>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Název salonu</label>
                    <input type="text" 
                           x-model="salonName"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                           placeholder="např. Salon Beautiful">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Adresa</label>
                    <input type="text" 
                           x-model="salonAddress"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                           placeholder="např. Hlavní 123, Praha">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Telefon</label>
                        <input type="tel" 
                               x-model="salonPhone"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                               placeholder="+420 xxx xxx xxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">E-mail</label>
                        <input type="email" 
                               x-model="salonEmail"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                               placeholder="info@salon.cz">
                    </div>
                </div>

                <button type="submit" 
                        :disabled="loading"
                        class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-950 font-semibold py-3 rounded-lg hover:from-emerald-400 hover:to-emerald-500 transition-all shadow-lg shadow-emerald-500/25 disabled:opacity-50">
                    💾 Uložit informace
                </button>
            </form>

            {{-- Database Backup/Restore --}}
            <div class="glass border border-slate-700 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">Záloha a obnova databáze</h2>
                
                <div class="space-y-4">
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                        <p class="text-sm text-blue-200">
                            💡 Pravidelně zálohujte data! Doporučujeme vytvořit zálohu před důležitými změnami.
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <a href="{{ route('settings.backup') }}" 
                           class="flex-1 bg-blue-500 text-white font-semibold py-3 rounded-lg hover:bg-blue-600 transition-colors text-center">
                            📥 Stáhnout zálohu
                        </a>
                        
                        <button @click="$refs.restoreInput.click()" 
                                type="button"
                                class="flex-1 bg-amber-500 text-slate-950 font-semibold py-3 rounded-lg hover:bg-amber-600 transition-colors">
                            📤 Obnovit ze zálohy
                        </button>
                    </div>

                    <form @submit.prevent="restoreDatabase()" x-ref="restoreForm" class="hidden">
                        @csrf
                        <input type="file" 
                               x-ref="restoreInput"
                               @change="restoreDatabase()"
                               name="backup_file" 
                               accept=".sqlite,.db">
                    </form>

                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                        <p class="text-sm text-red-200">
                            ⚠️ Obnova databáze přepíše všechna současná data! Ujistěte se, že máte aktuální zálohu.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Import produktů --}}
            <div class="glass border border-slate-700 rounded-2xl p-6">
                <h2 class="text-lg font-semibold mb-4">Hromadný import produktů</h2>
                
                <div class="space-y-4">
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                        <p class="text-sm text-blue-200 mb-2">
                            💡 Import produktů z CSV souboru. Stáhněte si šablonu, vyplňte data a nahrajte zpět.
                        </p>
                        <ul class="text-xs text-blue-300 space-y-1 list-disc list-inside">
                            <li>Formát: CSV se středníkem (;) jako oddělovačem</li>
                            <li>Kódování: UTF-8 nebo Windows-1250</li>
                            <li>Skupiny se vytvoří automaticky, pokud neexistují</li>
                        </ul>
                    </div>

                    <div class="flex gap-4">
                        <a href="{{ route('settings.template') }}" 
                           class="flex-1 bg-emerald-500 text-slate-950 font-semibold py-3 rounded-lg hover:bg-emerald-600 transition-colors text-center">
                            📥 Stáhnout šablonu CSV
                        </a>
                        
                        <button @click="$refs.importInput.click()" 
                                type="button"
                                class="flex-1 bg-purple-500 text-white font-semibold py-3 rounded-lg hover:bg-purple-600 transition-colors">
                            📤 Nahrát CSV s produkty
                        </button>
                    </div>

                    <form @submit.prevent="importProducts()" x-ref="importForm" class="hidden">
                        @csrf
                        <input type="file" 
                               x-ref="importInput"
                               @change="importProducts()"
                               name="import_file" 
                               accept=".csv,.txt">
                    </form>

                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4">
                        <p class="text-sm text-amber-200">
                            ⚠️ Import nepřepíše existující produkty, pouze přidá nové. Duplikáty musíte smazat ručně.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function settingsApp() {
        return {
            // Loading & Toast
            loading: false,
            loadingMessage: 'Načítání...',
            toasts: [],
            toastId: 0,
            
            // Settings
            salonName: '{{ $salonName }}',
            salonAddress: '{{ $salonAddress }}',
            salonPhone: '{{ $salonPhone }}',
            salonEmail: '{{ $salonEmail }}',
            
            init() {
                @if(session('status'))
                this.showToast('{{ session('status') }}', 'success');
                @endif
                @if(session('error'))
                this.showToast('{{ session('error') }}', 'error');
                @endif
            },
            
            showToast(message, type = 'info', duration = 3000) {
                const id = ++this.toastId;
                this.toasts.push({ id, message, type });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, duration);
            },
            
            async saveSettings() {
                this.loading = true;
                this.loadingMessage = 'Ukládání...';
                
                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('salon_name', this.salonName);
                    formData.append('salon_address', this.salonAddress);
                    formData.append('salon_phone', this.salonPhone);
                    formData.append('salon_email', this.salonEmail);
                    
                    const response = await fetch('{{ route('settings.update') }}', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        this.showToast('Nastavení uloženo', 'success');
                    } else {
                        this.showToast('Chyba při ukládání', 'error');
                    }
                } catch (error) {
                    this.showToast('Chyba při ukládání', 'error');
                } finally {
                    this.loading = false;
                }
            },
            
            async restoreDatabase() {
                const file = this.$refs.restoreInput.files[0];
                if (!file) return;
                
                if (!confirm('Opravdu chcete obnovit databázi? Všechna současná data budou ztracena!')) {
                    this.$refs.restoreInput.value = '';
                    return;
                }
                
                this.loading = true;
                this.loadingMessage = 'Obnovování databáze...';
                
                const formData = new FormData(this.$refs.restoreForm);
                
                try {
                    const response = await fetch('{{ route('settings.restore') }}', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        this.showToast('Databáze obnovena', 'success', 5000);
                        setTimeout(() => {
                            window.location.href = '{{ route('dashboard') }}';
                        }, 2000);
                    } else {
                        this.showToast('Chyba při obnově databáze', 'error');
                    }
                } catch (error) {
                    this.showToast('Chyba při obnově databáze', 'error');
                } finally {
                    this.loading = false;
                    this.$refs.restoreInput.value = '';
                }
            },
            
            async importProducts() {
                const file = this.$refs.importInput.files[0];
                if (!file) return;
                
                if (!confirm('Importovat produkty z CSV souboru?')) {
                    this.$refs.importInput.value = '';
                    return;
                }
                
                this.loading = true;
                this.loadingMessage = 'Importování produktů...';
                
                const formData = new FormData(this.$refs.importForm);
                
                try {
                    const response = await fetch('{{ route('settings.import') }}', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.showToast(data.message || 'Produkty importovány', 'success', 5000);
                        setTimeout(() => {
                            window.location.href = '{{ route('dashboard', ['section' => 'products']) }}';
                        }, 2000);
                    } else {
                        this.showToast(data.message || 'Chyba při importu', 'error');
                    }
                } catch (error) {
                    this.showToast('Chyba při importu produktů', 'error');
                } finally {
                    this.loading = false;
                    this.$refs.importInput.value = '';
                }
            }
        }
    }
</script>
</body>
</html>
