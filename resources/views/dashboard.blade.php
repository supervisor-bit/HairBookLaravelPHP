<!doctype html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HairBook</title>
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
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.4/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Manrope', system-ui, -apple-system, sans-serif; }
        .glass { background: linear-gradient(135deg, rgba(33, 39, 55, 0.85), rgba(21, 27, 43, 0.82)); backdrop-filter: blur(16px); }
        [x-cloak] { display: none; }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .toast-enter { animation: slideInRight 0.3s ease-out; }
        .toast-exit { animation: slideOutRight 0.3s ease-in; }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 1s linear infinite; }
        .ts-wrapper { width: 100% !important; }
        .ts-control { 
            background: rgba(15, 23, 42, 0.6) !important; 
            border: 1px solid rgb(51 65 85) !important; 
            color: rgb(226 232 240) !important;
            min-height: 38px !important;
            padding: 0.5rem 0.75rem !important;
        }
        .ts-control input { 
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
        }
        .ts-wrapper.single .ts-control {
            display: flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
        }
        .ts-wrapper.single .ts-control::after {
            border-color: rgb(148 163 184) transparent transparent transparent !important;
        }
        .ts-dropdown { 
            background: rgba(15, 23, 42, 0.95) !important; 
            border: 1px solid rgb(51 65 85) !important; 
            color: rgb(226 232 240) !important;
            backdrop-filter: blur(8px) !important;
        }
        .ts-dropdown .option { padding: 0.5rem 0.75rem; }
        .ts-dropdown .option.active { background: rgb(30 41 59) !important; }
        
        /* Datalist custom styling */
        input[list]::-webkit-calendar-picker-indicator {
            filter: brightness(0) saturate(100%) invert(71%) sepia(56%) saturate(464%) hue-rotate(101deg) brightness(96%) contrast(89%);
            cursor: pointer;
        }
    </style>
</head>
<body class="h-screen overflow-hidden bg-slate-950 text-slate-100">
<div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(90,204,255,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,117,215,0.12),transparent_25%),radial-gradient(circle_at_50%_80%,rgba(120,178,255,0.10),transparent_25%)] pointer-events-none"></div>

{{-- Loading Overlay --}}
<div x-show="loading" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
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

<div id="main-app" class="relative h-full flex" x-data="{
        toasts: [],
        toastId: 0,
        loading: false,
        loadingMessage: 'Načítání...',
        showToast(message, type = 'info', duration = 3000) {
            const id = ++this.toastId;
            this.toasts.push({ id, message, type });
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, duration);
        },
        setLoading(isLoading, message = 'Načítání...') {
            this.loading = isLoading;
            this.loadingMessage = message;
        },
        deleteModal: false,
        productDeleteModal: false,
        groupDeleteModal: false,
        groupDeleteUrl: '',
        groupDeleteName: '',
        duplicateModal: false,
        duplicateUrl: '',
        duplicatePrice: '',
        duplicateClose: false,
        visitSearch: '',
        visitStatusFilter: 'all',
        stockModal: false,
        stockMode: 'in',
        stockProduct: {name: '', stock: 0, url: '', usage: ''},
        stockReason: 'work',
        stockQty: '',
        stockNote: '',
        bulkModal: false,
        clientModal: false,
        clientMode: 'create',
        clientForm: { id: '', name: '', phone: '', note: '' },
        productModal: false,
        productMode: 'create',
        productForm: { id: '', name: '', sku: '', product_group_id: '', usage_type: 'both', package_size_grams: '', stock_units: '0', min_units: '', notes: '', is_active: true },
        receiptData: {
            show: false,
            clientName: '',
            date: '',
            time: '',
            services: [],
            retail: [],
            totalPrice: 0
        },
            getInitials(name) {
                if (!name) return '?';
                const parts = name.trim().split(' ');
                if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
                return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
            },
            getAvatarColor(name) {
                const colors = [
                    'bg-emerald-500', 'bg-sky-500', 'bg-purple-500', 'bg-pink-500',
                    'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-indigo-500',
                    'bg-teal-500', 'bg-orange-500'
                ];
                const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
                return colors[hash % colors.length];
            },
            init() {
                // Reset receipt modal při načtení stránky
                this.receiptData.show = false;
                
                @if(session('status'))
                this.showToast('{{ session('status') }}', 'success');
                @endif
                @if(session('error'))
                this.showToast('{{ session('error') }}', 'error');
                @endif
            },
            openStock(detail) {
                this.stockMode = detail.mode || 'in';
                this.stockProduct = detail.product || {name: '', stock: 0, url: '', usage: ''};
                this.stockReason = 'work';
                this.stockQty = '';
            this.stockNote = '';
            this.stockModal = true;
            requestAnimationFrame(() => {
                const input = document.querySelector('[data-stock-qty]');
                input?.focus();
                input?.select();
            });
        },
        openBulk() {
            this.bulkModal = true;
        },
        projectedStock() {
            const current = Number(this.stockProduct.stock || 0);
            const qty = Number(this.stockQty || 0);
            const delta = (this.stockMode === 'in' ? 1 : -1) * qty;
            return current + delta;
        },
        openEditProduct(product) {
            this.productMode = 'edit';
            this.productForm = {
                id: product.id,
                name: product.name,
                sku: product.sku || '',
                product_group_id: product.product_group_id || '',
                usage_type: product.usage_type,
                package_size_grams: product.package_size_grams || '',
                stock_units: '0',
                min_units: product.min_units || '',
                notes: product.notes || '',
                is_active: product.is_active
            };
            this.productModal = true;
        }
     }"
     x-on:open-delete-modal.window="deleteModal = true"
     x-on:open-product-delete-modal.window="productDeleteModal = true"
     x-on:open-group-delete-modal.window="
        groupDeleteModal = true;
        groupDeleteUrl = $event.detail.url;
        groupDeleteName = $event.detail.name;
     "
     x-on:open-stock-modal.window="openStock($event.detail)"
     x-on:open-bulk-modal.window="openBulk()"
     x-on:open-duplicate-modal.window="
        duplicateModal = true;
        duplicateUrl = $event.detail.url;
        duplicatePrice = $event.detail.price || 0;
        duplicateClose = true;
     ">
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
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition @if($section==='clients') bg-slate-800 text-white @else text-slate-300 hover:bg-slate-800/60 @endif">
                <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_6px_rgba(16,185,129,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">Klienti</div>
                    <div class="text-xs text-slate-400">Historie návštěv, uzávěrky</div>
                </div>
            </a>
            <a href="{{ route('dashboard', ['section' => 'products']) }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl transition @if($section==='products') bg-slate-800 text-white @else text-slate-300 hover:bg-slate-800/60 @endif">
                <span class="h-2 w-2 rounded-full bg-sky-400 shadow-[0_0_0_6px_rgba(56,189,248,0.15)]"></span>
                <div>
                    <div class="text-sm font-semibold">Produkty</div>
                    <div class="text-xs text-slate-400">Sklad ks + odpis v gramech</div>
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
                @if (session('status'))
                    <div class="text-slate-100 font-medium">{{ session('status') }}</div>
                @endif
            </div>
        </div>
    </aside>

    <main class="flex-1 grid @if($section==='products') grid-cols-3 @else grid-cols-2 @endif gap-4 p-4 overflow-hidden">
        {{-- Sloupec 1: Skupiny produktů (jen pro produkty) --}}
        @if($section === 'products')
        <section class="glass border border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col">
                <div class="p-4 flex items-center justify-between border-b border-slate-800">
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Skupiny</div>
                        <div class="text-lg font-semibold">Produkty podle skupin</div>
                    </div>
                    <form method="POST" action="{{ route('product-groups.store') }}" class="flex items-center gap-2">
                        @csrf
                        <input name="name" type="text" placeholder="Nová skupina" class="w-32 text-sm bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none">
                        <button class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 text-sm font-semibold hover:bg-sky-400">Přidat</button>
                    </form>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <ul class="divide-y divide-slate-800">
                        <li>
                            <a href="{{ route('dashboard', ['section' => 'products', 'group' => 'all']) }}"
                               class="flex items-center justify-between px-4 py-3 hover:bg-slate-800/60 transition @if($selectedGroupId === null) bg-slate-800/80 @endif">
                                <div class="flex items-center gap-3">
                                    <span class="h-3 w-3 rounded-full bg-slate-400"></span>
                                    <div>
                                        <div class="font-semibold text-sm">Všechny skupiny</div>
                                        <div class="text-xs text-slate-400">{{ $products->count() }} produktů</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @forelse($groups as $group)
                            <li x-data="{ edit: false }" class="group">
                                <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-800/60 transition @if($selectedGroupId === $group->id && $section === 'products') bg-slate-800/80 @endif">
                                    <a href="{{ route('dashboard', ['section' => 'products', 'group' => $group->id]) }}" class="flex items-center gap-3 flex-1">
                                        <span class="h-3 w-3 rounded-full" style="background: {{ $group->accent_color ?: '#7dd3fc' }}"></span>
                                        <div>
                                            <div class="font-semibold text-sm">{{ $group->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $group->products->count() }} produktů</div>
                                        </div>
                                    </a>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-1 rounded-full bg-slate-800 text-slate-300">pořadí: {{ $group->display_order }}</span>
                                        <button type="button" @click="edit = !edit" class="text-xs text-slate-300 px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700">Upravit</button>
                                        <button type="button"
                                                @click="$dispatch('open-group-delete-modal', {url: '{{ route('product-groups.destroy', $group) }}', name: '{{ $group->name }}'})"
                                                class="text-xs text-red-200 px-2 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30">Smazat</button>
                                    </div>
                                </div>
                                <div x-cloak x-show="edit" class="px-4 pb-4 space-y-2 bg-slate-900/60 border-t border-slate-800">
                                    <form method="POST" action="{{ route('product-groups.update', $group) }}" class="flex flex-col gap-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-2">
                                            <input name="name" value="{{ $group->name }}" class="bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="Název">
                                            <input name="display_order" value="{{ $group->display_order }}" type="number" min="0" class="bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="Pořadí">
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <label class="flex items-center gap-2 text-xs text-slate-300 bg-slate-900/60 border border-slate-700 rounded-lg px-2 py-2">
                                                <span>Barva</span>
                                                <input type="color" name="accent_color" value="{{ $group->accent_color ?: '#38bdf8' }}" class="w-10 h-8 bg-transparent border-none p-0">
                                                <input type="text" name="accent_color" value="{{ $group->accent_color }}" placeholder="#hex" class="bg-slate-900/0 border-none focus:ring-0 text-sm text-slate-100 w-20">
                                            </label>
                                            <button class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 text-xs font-semibold hover:bg-sky-400">Uložit</button>
                                        </div>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="px-4 py-4 text-slate-400 text-sm">Zatím žádné skupiny.</li>
                        @endforelse
                    </ul>
                </div>
        </section>
        @endif

        {{-- Sloupec 1/2: Seznam + vyhledávání (Klienti / Produkty) --}}
        <section class="glass border border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Seznam</div>
                        <div class="text-lg font-semibold">
                            {{ $section === 'clients' ? 'Klienti' : 'Produkty' }}
                        </div>
                    </div>
                    @if($section === 'clients')
                        <button type="button" @click="clientMode='create'; clientForm = { id: '', name: '', phone: '', note: '' }; clientModal=true" class="px-3 py-2 rounded-lg bg-emerald-500 text-slate-950 text-sm font-semibold hover:bg-emerald-400">Nový klient</button>
                    @endif
                    @if($section === 'products')
                        <button type="button"
                                @click="productMode='create'; productForm = { id: '', name: '', sku: '', product_group_id: '', usage_type: 'both', package_size_grams: '', stock_units: '0', min_units: '', notes: '', is_active: true }; productModal=true"
                                class="px-3 py-2 rounded-lg bg-emerald-500 text-slate-950 text-sm font-semibold hover:bg-emerald-400">
                            Nový produkt
                        </button>
                    @endif
                </div>
                @if($section === 'products')
                    <div class="flex items-center justify-end gap-2">
                        <form method="GET" action="{{ route('dashboard') }}">
                            <input type="hidden" name="section" value="products">
                            @if(!is_null($selectedGroupId)) <input type="hidden" name="group" value="{{ $selectedGroupId }}"> @endif
                            @if($selectedProduct) <input type="hidden" name="product" value="{{ $selectedProduct->id }}"> @endif
                            <input type="hidden" name="low_stock" value="{{ $lowStockOnly ? 0 : 1 }}">
                            <button type="submit"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium transition
                                    {{ $lowStockOnly ? 'bg-amber-500/20 border-amber-400/60 text-amber-100' : 'bg-slate-900/60 border-slate-700 text-slate-200 hover:border-slate-500' }}">
                                <span class="h-2 w-2 rounded-full {{ $lowStockOnly ? 'bg-amber-400' : 'bg-slate-500' }}"></span>
                                <span>Pod minimem</span>
                            </button>
                        </form>
                        <a href="{{ route('products.bulk-receipt') }}"
                           class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 text-sm font-semibold hover:bg-sky-400 inline-flex items-center gap-2">
                            📦 Hromadný příjem
                        </a>
                    </div>
                @endif
                <div>
                    <input x-data x-on:input="let q = $event.target.value.toLowerCase(); document.querySelectorAll('[data-filter-item]').forEach(el => { const text = (el.dataset.name + ' ' + (el.dataset.sku || '')).toLowerCase(); el.classList.toggle('hidden', !text.includes(q)); });"
                           type="search" placeholder="Hledat (název / SKU)" class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" data-filter-container>
                <div class="divide-y divide-slate-800">
                    @if($section === 'clients')
                        @foreach($clients as $client)
                            <a data-filter-item data-name="{{ $client->name }}"
                               href="{{ route('dashboard', ['section' => 'clients', 'client' => $client->id]) }}"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800/60 transition @if($selectedClient && $selectedClient->id === $client->id) bg-slate-800/80 @endif">
                                <div :class="getAvatarColor('{{ $client->name }}')" class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                    <span x-text="getInitials('{{ $client->name }}')"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold truncate">{{ $client->name }}</div>
                                    <div class="text-xs text-slate-400 truncate">
                                        {{ $client->phone ?? 'telefon neuveden' }}
                                        @if($client->visits->count())
                                            • {{ $client->visits->count() }} návštěv
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-slate-400 flex-shrink-0">{{ optional($client->visits->first())->occurred_at?->format('d.m.Y') }}</div>
                            </a>
                        @endforeach
                    @else
                        @php
                            $visibleProducts = $selectedGroupId
                                ? $products->where('product_group_id', $selectedGroupId)
                                : $products;
                            if ($lowStockOnly) {
                                $visibleProducts = $visibleProducts->filter(fn($p) => $p->min_units > 0 && $p->stock_units < $p->min_units);
                            }
                        @endphp
                        @foreach($visibleProducts as $product)
                            @php
                                $low = $product->min_units > 0 && $product->stock_units < $product->min_units;
                                $adjustUrl = route('products.adjust', $product);
                            @endphp
                            <div data-filter-item data-name="{{ $product->name }}" data-sku="{{ $product->sku }}"
                                 class="flex items-center justify-between px-4 py-3 hover:bg-slate-800/60 transition @if($selectedProduct && $selectedProduct->id === $product->id) bg-slate-800/80 @endif">
                                <a href="{{ route('dashboard', ['section' => 'products', 'product' => $product->id, 'group' => $selectedGroupId, 'product_mode' => 'edit']) }}"
                                   class="flex items-center gap-2 flex-1 min-w-0">
                                    @if($low)
                                        <span class="text-red-300 text-xs font-bold">!</span>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="font-semibold truncate">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-400 truncate">
                                            {{ $product->group?->name ?? 'bez skupiny' }} • {{ $product->usage_type }}
                                        </div>
                                    </div>
                                </a>
                                <div class="flex items-center gap-2 ml-3">
                                    @if($low)
                                        <span class="text-[10px] px-2 py-1 rounded-full bg-red-500/20 text-red-200">pod min</span>
                                    @endif
                                    <button type="button"
                                            class="text-xs px-2 py-1 rounded-lg bg-emerald-500/20 text-emerald-100 hover:bg-emerald-500/30"
                                            @click.stop="window.dispatchEvent(new CustomEvent('open-stock-modal', { detail: { mode: 'in', product: @js(['name' => $product->name, 'stock' => $product->stock_units, 'url' => route('products.adjust', $product), 'usage' => $product->usage_type]) } }))">
                                        Příjem
                                    </button>
                                    <button type="button"
                                            class="text-xs px-2 py-1 rounded-lg bg-amber-500/20 text-amber-100 hover:bg-amber-500/30"
                                            @click.stop="window.dispatchEvent(new CustomEvent('open-stock-modal', { detail: { mode: 'out', product: @js(['name' => $product->name, 'stock' => $product->stock_units, 'url' => route('products.adjust', $product), 'usage' => $product->usage_type]) } }))">
                                        Výdej
                                    </button>
                                    <div class="text-xs px-2 py-1 rounded-full bg-slate-800 text-slate-300">
                                        {{ number_format($product->stock_units, 2) }} ks
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </section>

        {{-- Sloupec 2/2: Detail --}}
        <section class="glass border border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col min-h-0">
            @if($section === 'clients')
                @if($selectedClient)
                    <div class="p-4 border-b border-slate-800">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-3">Detail klienta</div>
                        <div class="flex items-start gap-4">
                            <div :class="getAvatarColor('{{ $selectedClient->name }}')" class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                                <span x-text="getInitials('{{ $selectedClient->name }}')"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-2xl font-semibold">{{ $selectedClient->name }}</div>
                                <div class="text-lg font-semibold text-emerald-400 mt-1">📞 {{ $selectedClient->phone ?? 'telefon neuveden' }}</div>
                                @if($selectedClient->note)
                                    <div class="mt-2 text-sm text-slate-300 bg-slate-800/50 rounded-lg px-3 py-2">{{ $selectedClient->note }}</div>
                                @endif
                                <div class="flex items-center gap-2 mt-3">
                                    <button type="button"
                                            @click="clientMode='edit'; clientForm = { id: '{{ $selectedClient->id }}', name: '{{ $selectedClient->name }}', phone: '{{ $selectedClient->phone ?? '' }}', note: '{{ $selectedClient->note ?? '' }}' }; clientModal=true"
                                            class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 font-semibold hover:bg-sky-400 text-sm">Upravit</button>
                                    <button type="button" @click="$dispatch('open-delete-modal')" class="px-3 py-2 rounded-lg bg-red-500/20 text-red-200 hover:bg-red-500/30 text-sm">Smazat</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-b border-slate-800">
                        <a href="{{ route('visits.create', $selectedClient) }}" 
                           class="block w-full px-4 py-3 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400 text-center transition-colors">
                            + Nová návštěva
                        </a>
                    </div>

                    <div class="flex-1 flex flex-col min-h-0 p-4" x-data="clientNotesComponent({
                        notes: @js($selectedClient->notes->map(function($note) {
                            return [
                                'id' => $note->id,
                                'body' => $note->body,
                                'created_at_formatted' => optional($note->created_at)->format('d.m.Y H:i'),
                                'urls' => [
                                    'update' => route('clients.notes.update', $note),
                                    'delete' => route('clients.notes.destroy', $note),
                                ],
                            ];
                        })->values()),
                        storeUrl: '{{ route('clients.notes.store', $selectedClient) }}',
                        csrf: '{{ csrf_token() }}',
                    })">
                        <div class="flex flex-col gap-4 min-h-0">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                                    <div class="text-xs text-slate-400">Návštěv celkem</div>
                                    <div class="text-xl font-semibold">{{ $clientStats['count'] }}</div>
                                </div>
                                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                                    <div class="text-xs text-slate-400">Utraceno celkem</div>
                                    <div class="text-xl font-semibold">{{ number_format($clientStats['total'], 2) }} Kč</div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="tab='history'"
                                            :class="['px-3 py-1 rounded-lg text-sm', tab==='history' ? 'bg-slate-700 text-white' : 'bg-slate-900/60 text-slate-300']">
                                        Historie návštěv
                                    </button>
                                    <button type="button"
                                            @click="tab='notes'"
                                            :class="['px-3 py-1 rounded-lg text-sm', tab==='notes' ? 'bg-slate-700 text-white' : 'bg-slate-900/60 text-slate-300']">
                                        Poznámky
                                    </button>
                                </div>

                                <div x-show="tab==='history'" class="flex items-center gap-2">
                                    <input type="text" 
                                           x-model="visitSearch" 
                                           placeholder="Hledat v návštěvách..."
                                           class="flex-1 bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                    <select x-model="visitStatusFilter" 
                                            class="bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                        <option value="all">Všechny</option>
                                        <option value="draft">Otevřené</option>
                                        <option value="closed">Uzavřené</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex-1 min-h-0 overflow-y-auto space-y-4">
                                <div x-cloak x-show="tab==='history'" class="space-y-4">
                                    @php
                                        $visitsJson = $selectedClient->visits->map(function($visit) {
                                            return [
                                                'id' => $visit->id,
                                                'occurred_at' => $visit->occurred_at?->format('d.m.Y H:i') ?? 'Bez data',
                                                'occurred_at_raw' => $visit->occurred_at?->format('Y-m-d H:i:s'),
                                                'total_price' => $visit->total_price,
                                                'status' => $visit->status,
                                                'note' => $visit->note,
                                                'services' => $visit->services->map(function($s) {
                                                    return [
                                                        'title' => $s->title,
                                                        'note' => $s->note,
                                                        'products' => $s->products->map(function($p) {
                                                            return [
                                                                'name' => $p->product?->name,
                                                                'used_grams' => $p->used_grams,
                                                                'deducted_units' => $p->deducted_units
                                                            ];
                                                        })
                                                    ];
                                                }),
                                                'retail' => $visit->retailItems->map(function($r) {
                                                    return [
                                                        'name' => $r->product?->name,
                                                        'quantity_units' => $r->quantity_units
                                                    ];
                                                }),
                                                'duplicate_url' => route('visits.duplicate', $visit),
                                                'close_url' => route('visits.close', $visit)
                                            ];
                                        });
                                    @endphp
                                    <div x-data="{ 
                                        visits: @js($visitsJson),
                                        getStatusText(status) {
                                            return status === 'closed' ? 'Uzavřeno' : 'Otevřeno';
                                        },
                                        get filteredVisits() {
                                            return this.visits.filter(visit => {
                                                // Status filter
                                                if (visitStatusFilter !== 'all' && visit.status !== visitStatusFilter) {
                                                    return false;
                                                }
                                                
                                                // Search filter
                                                if (visitSearch.trim() !== '') {
                                                    const search = visitSearch.toLowerCase();
                                                    const searchIn = [
                                                        visit.occurred_at,
                                                        visit.note || '',
                                                        visit.total_price.toString(),
                                                        ...visit.services.map(s => s.title + ' ' + (s.note || '')),
                                                        ...visit.services.flatMap(s => s.products.map(p => p.name))
                                                    ].join(' ').toLowerCase();
                                                    
                                                    return searchIn.includes(search);
                                                }
                                                
                                                return true;
                                            });
                                        }
                                    }">
                                        <template x-if="filteredVisits.length === 0">
                                            <div class="text-sm text-slate-400 text-center py-8">
                                                <span x-show="visitSearch || visitStatusFilter !== 'all'">Žádné návštěvy nevyhovují filtru</span>
                                                <span x-show="!visitSearch && visitStatusFilter === 'all'">Zatím žádné návštěvy</span>
                                            </div>
                                        </template>
                                        
                                        <template x-for="visit in filteredVisits" :key="visit.id">
                                            <div class="border border-slate-800 rounded-xl p-4 bg-slate-900/40 space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-lg font-semibold" x-text="visit.occurred_at"></div>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="printVisitReceipt(visit)" class="px-3 py-1 rounded-lg bg-sky-500/20 border border-sky-500/40 text-sky-300 text-xs hover:bg-sky-500/30">
                                                            🖨️ Účtenka
                                                        </button>
                                                        <form method="POST" :action="visit.duplicate_url" class="inline">
                                                            @csrf
                                                            <button type="submit" class="px-3 py-1 rounded-lg bg-slate-800 text-slate-200 text-xs hover:bg-slate-700">
                                                                Duplikovat
                                                            </button>
                                                        </form>
                                                        <span class="text-xs px-2 py-1 rounded-full"
                                                              :class="visit.status === 'closed' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-amber-500/20 text-amber-200'"
                                                              x-text="getStatusText(visit.status)"></span>
                                                        <span class="text-sm text-slate-200 font-semibold" x-text="Number(visit.total_price).toFixed(2) + ' Kč'"></span>
                                                    </div>
                                                </div>
                                                <div x-show="visit.note" class="text-sm text-slate-300" x-text="visit.note"></div>

                                                <template x-for="service in visit.services" :key="service.title">
                                                    <div class="mt-2">
                                                        <div class="font-semibold text-slate-100" x-text="service.title"></div>
                                                        <div x-show="service.note" class="text-xs text-slate-400" x-text="service.note"></div>
                                                        <div class="mt-1 space-y-1">
                                                            <template x-for="prod in service.products" :key="prod.name">
                                                                <div class="flex items-center justify-between text-sm text-slate-300">
                                                                    <div x-text="prod.name"></div>
                                                                    <div class="text-xs text-slate-400" x-text="prod.used_grams + ' g (' + Number(prod.deducted_units).toFixed(3) + ' ks)'"></div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>

                                                <div x-show="visit.retail.length > 0" class="mt-2">
                                                    <div class="font-semibold text-slate-100">Prodej domů</div>
                                                    <template x-for="item in visit.retail" :key="item.name">
                                                        <div class="flex items-center justify-between text-sm text-slate-300">
                                                            <div x-text="item.name"></div>
                                                            <div class="text-xs text-slate-400" x-text="item.quantity_units + ' ks'"></div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <form x-show="visit.status === 'draft'" method="POST" :action="visit.close_url" class="pt-2">
                                                    @csrf
                                                    <button class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 text-sm font-semibold hover:bg-sky-400">Uzavřít a odepsat sklad</button>
                                                </form>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div x-cloak x-show="tab==='notes'" class="space-y-4">
                                    <template x-if="notes.length === 0">
                                        <div class="text-sm text-slate-400">Zatím žádné poznámky.</div>
                                    </template>

                                    <template x-for="note in notes" :key="note.id">
                                        <div class="border border-slate-800 rounded-xl p-3 bg-slate-900/40">
                                            <div class="flex items-center justify-between text-xs text-slate-400">
                                                <span x-text="note.created_at_formatted"></span>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="startEdit(note)" class="px-2 py-1 rounded bg-slate-800 text-slate-200 hover:bg-slate-700">Upravit</button>
                                                    <button type="button" @click="destroy(note)" class="px-2 py-1 rounded bg-red-500/20 text-red-200 hover:bg-red-500/30">Smazat</button>
                                                </div>
                                            </div>
                                            <div x-show="editingId !== note.id" class="text-sm text-slate-200 mt-1 whitespace-pre-wrap" x-text="note.body"></div>
                                            <div x-show="editingId === note.id" class="space-y-2 mt-2">
                                                <textarea x-model="editingBody" rows="2" required class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none"></textarea>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="cancelEdit()" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                                                    <button type="button" @click="saveEdit(note)" class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 text-sm font-semibold hover:bg-sky-400" :disabled="loading">Uložit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <form @submit.prevent="addNote" class="space-y-2 bg-slate-900/40 rounded-xl border border-slate-800 p-3">
                                        <div class="text-sm font-semibold text-slate-200">Nová poznámka</div>
                                        <textarea x-model="newBody" rows="2" required class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="Text poznámky"></textarea>
                                        <button class="px-3 py-2 rounded-lg bg-emerald-500 text-slate-950 text-sm font-semibold hover:bg-emerald-400" :disabled="loading">Přidat poznámku</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-6 text-slate-400">Vyberte klienta vlevo.</div>
                @endif
            @else
                @if($selectedProduct)
                    <div class="p-4 border-b border-slate-800">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Detail produktu</div>
                        <div class="flex items-center justify-between">
                            <div class="text-2xl font-semibold">{{ $selectedProduct->name }}</div>
                            <div class="text-xs px-3 py-1 rounded-full bg-slate-800 text-slate-200">{{ $selectedProduct->usage_type }}</div>
                        </div>
                        <div class="text-sm text-slate-400">{{ $selectedProduct->group?->name ?? 'Bez skupiny' }}</div>
                        <div class="mt-2 flex items-center gap-2">
                            <button type="button"
                                    @click="openEditProduct({ id: '{{ $selectedProduct->id }}', name: {{ json_encode($selectedProduct->name) }}, sku: {{ json_encode($selectedProduct->sku ?? '') }}, product_group_id: '{{ $selectedProduct->product_group_id ?? '' }}', usage_type: '{{ $selectedProduct->usage_type }}', package_size_grams: '{{ $selectedProduct->package_size_grams ?? '' }}', min_units: '{{ $selectedProduct->min_units ?? '' }}', notes: {{ json_encode($selectedProduct->notes ?? '') }}, is_active: {{ $selectedProduct->is_active ? 'true' : 'false' }} })"
                                    class="px-3 py-2 rounded-lg bg-sky-500 text-slate-950 font-semibold hover:bg-sky-400 text-sm">Upravit</button>
                            <button type="button"
                                    @click="$dispatch('open-product-delete-modal')"
                                    class="px-3 py-2 rounded-lg bg-red-500/20 text-red-200 hover:bg-red-500/30 text-sm">Smazat</button>
                        </div>
                    </div>
                    <div class="p-4 flex-1 min-h-0 flex flex-col gap-3">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                                <div class="text-xs text-slate-400">Stav (ks)</div>
                                <div class="text-xl font-semibold">{{ number_format($selectedProduct->stock_units, 3) }}</div>
                            </div>
                            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                                <div class="text-xs text-slate-400">Minimální zásoba</div>
                                <div class="text-xl font-semibold">{{ number_format($selectedProduct->min_units, 3) }}</div>
                            </div>
                            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                                <div class="text-xs text-slate-400">Velikost balení</div>
                                <div class="text-xl font-semibold">{{ number_format($selectedProduct->package_size_grams, 2) }} g/ml</div>
                            </div>
                        </div>
                        @if($selectedProduct->notes)
                            <div class="text-sm text-slate-300">{{ $selectedProduct->notes }}</div>
                        @endif
                        <div class="text-xs text-slate-400">Uzávěrka návštěvy přepočítá gramy na ks a zapíše do skladových pohybů.</div>

                        <div class="pt-3 border-t border-slate-800 flex-1 min-h-0 flex flex-col space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-slate-200">Pohyby produktu</div>
                                <div class="text-xs text-slate-400">Posledních 15</div>
                            </div>
                            <div class="space-y-2 overflow-y-auto min-h-0 pr-1">
                                @forelse($productMovements as $move)
                                    <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/50 px-3 py-2">
                                        <div>
                                            <div class="text-sm text-slate-100">{{ $move['label'] }}</div>
                                            <div class="text-xs text-slate-400">{{ optional($move['date'])->format('d.m.Y H:i') }}</div>
                                        </div>
                                        <div class="text-sm font-semibold @if($move['delta'] < 0) text-red-300 @else text-emerald-300 @endif">
                                            {{ $move['delta'] > 0 ? '+' : '' }}{{ number_format($move['delta'], 3) }} ks
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-slate-400">Zatím žádné pohyby.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-6 text-slate-400">Vyberte produkt vlevo.</div>
                @endif
            @endif
        </section>
    </main>
@if($section === 'clients' && $selectedClient)
    <div x-show="deleteModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="deleteModal=false">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="text-lg font-semibold text-white">Smazat klienta?</div>
            <div class="text-sm text-slate-300">Tento krok odstraní klienta <span class="font-semibold">{{ $selectedClient->name }}</span> včetně jeho návštěv. Akci nejde vrátit.</div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="deleteModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                <form method="POST" action="{{ route('clients.destroy', $selectedClient) }}">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-400">Smazat</button>
                </form>
            </div>
        </div>
    </div>
@endif

@if($section === 'products' && $selectedProduct)
    <div x-show="productDeleteModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="productDeleteModal=false">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="text-lg font-semibold text-white">Smazat produkt?</div>
            <div class="text-sm text-slate-300">Produkt <span class="font-semibold">{{ $selectedProduct->name }}</span> bude odstraněn. Pokud je použit v návštěvách, mazání může selhat.</div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="productDeleteModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                <form method="POST" action="{{ route('products.destroy', $selectedProduct) }}">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-400">Smazat</button>
                </form>
            </div>
        </div>
    </div>
@endif

<div x-show="groupDeleteModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="groupDeleteModal=false">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
        <div class="text-lg font-semibold text-white">Smazat skupinu?</div>
        <div class="text-sm text-slate-300">Skupina <span class="font-semibold" x-text="groupDeleteName"></span> bude odstraněna. Produkty zůstanou, ale bez přiřazení.</div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="groupDeleteModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                <form method="POST" :action="groupDeleteUrl">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-400">Smazat</button>
                </form>
            </div>
        </div>
    </div>

{{-- Modal pro klienty --}}
@if($section === 'clients')
<div x-show="clientModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="clientModal=false">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="text-lg font-semibold text-white" x-text="clientMode === 'edit' ? 'Upravit klienta' : 'Nový klient'"></div>
            <button type="button" @click="clientModal=false" class="text-slate-400 hover:text-white text-sm">✕</button>
        </div>
        
        <form method="POST" :action="clientMode === 'edit' ? '/clients/' + clientForm.id : '{{ route('clients.store') }}'" class="space-y-3">
            @csrf
            <template x-if="clientMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Jméno a příjmení *</label>
                    <input name="name" x-model="clientForm.name" required placeholder="např. Jana Nová" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Telefon</label>
                    <input name="phone" x-model="clientForm.phone" placeholder="např. 777 123 456" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Poznámka</label>
                    <input name="note" x-model="clientForm.note" placeholder="např. Alergie" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-700">
                <button type="button" @click="clientModal=false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700 text-sm">Zrušit</button>
                <button type="submit" class="px-5 py-2 rounded-lg text-slate-950 font-semibold text-sm" 
                        :class="clientMode === 'edit' ? 'bg-sky-500 hover:bg-sky-400' : 'bg-emerald-500 hover:bg-emerald-400'"
                        x-text="clientMode === 'edit' ? 'Uložit změny' : 'Přidat klienta'"></button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal pro produkty --}}
@if($section === 'products')
<div x-show="productModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="productModal=false">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between sticky top-0 bg-slate-900 pb-2 border-b border-slate-700">
            <div class="text-lg font-semibold text-white" x-text="productMode === 'edit' ? 'Upravit produkt' : 'Nový produkt'"></div>
            <button type="button" @click="productModal=false" class="text-slate-400 hover:text-white text-sm">✕</button>
        </div>
        
        <form method="POST" :action="productMode === 'edit' ? '/products/' + productForm.id : '{{ route('products.store') }}'" class="space-y-4">
            @csrf
            <template x-if="productMode === 'edit'">
                <input type="hidden" name="_method" value="PUT">
            </template>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Název produktu *</label>
                    <input name="name" x-model="productForm.name" required placeholder="např. Barva na vlasy - Hnědá" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">SKU / Kód</label>
                    <input name="sku" x-model="productForm.sku" placeholder="např. BVH-001" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Skupina</label>
                    <select name="product_group_id" x-model="productForm.product_group_id" class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                        <option value="">Bez skupiny</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Použití *</label>
                    <select name="usage_type" x-model="productForm.usage_type" class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                        <option value="service">Do služby (g → ks)</option>
                        <option value="retail">Prodej domů (ks)</option>
                        <option value="both">Obojí</option>
                    </select>
                </div>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Velikost balení (g/ml)</label>
                    <input name="package_size_grams" x-model="productForm.package_size_grams" type="number" step="0.01" min="0" placeholder="např. 100" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                    <div class="text-xs text-slate-500 mt-1">Pro balené produkty (ks)</div>
                </div>
                
                <template x-if="productMode === 'create'">
                    <div>
                        <label class="text-xs text-slate-400 mb-1 block">Počáteční stav (ks)</label>
                        <input name="stock_units" x-model="productForm.stock_units" type="number" step="0.001" min="0" 
                               class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                    </div>
                </template>
                
                <div>
                    <label class="text-xs text-slate-400 mb-1 block">Minimální stav (ks)</label>
                    <input name="min_units" x-model="productForm.min_units" type="number" step="0.001" min="0" placeholder="např. 5" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                    <div class="text-xs text-slate-500 mt-1">Upozornění při nízkém stavu</div>
                </div>
                
                <div class="col-span-2">
                    <label class="text-xs text-slate-400 mb-1 block">Poznámka</label>
                    <textarea name="notes" x-model="productForm.notes" rows="2" placeholder="Volitelná poznámka..." 
                              class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none resize-none"></textarea>
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" id="productActive" x-model="productForm.is_active"
                           class="w-4 h-4 rounded bg-slate-900/60 border-slate-700 text-sky-500 focus:ring-2 focus:ring-sky-400">
                    <label for="productActive" class="text-sm text-slate-300">Aktivní produkt</label>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-700">
                <button type="button" @click="productModal=false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700 text-sm">Zrušit</button>
                <button type="submit" class="px-5 py-2 rounded-lg text-slate-950 font-semibold text-sm" 
                        :class="productMode === 'edit' ? 'bg-sky-500 hover:bg-sky-400' : 'bg-emerald-500 hover:bg-emerald-400'"
                        x-text="productMode === 'edit' ? 'Uložit změny' : 'Přidat produkt'"></button>
            </div>
        </form>
    </div>
</div>
@endif

<template x-if="duplicateModal">
    <div x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Duplikovat návštěvu</div>
                    <div class="text-lg font-semibold text-white">Zadat cenu a odepsat sklad</div>
                </div>
                <button type="button" @click="duplicateModal=false" class="text-slate-400 hover:text-white text-sm">Zavřít</button>
            </div>
            <form method="POST" :action="duplicateUrl" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs text-slate-400">Cena za návštěvu</label>
                    <input type="number" name="total_price" step="0.01" min="0" x-model="duplicatePrice" class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-200">
                    <input type="checkbox" name="close_now" value="1" x-model="duplicateClose" class="rounded border-slate-700 bg-slate-900/60">
                    Uzavřít duplikovanou návštěvu (jinak se otevře k úpravě)
                </label>
                <div class="text-xs text-slate-400 bg-slate-900/60 border border-slate-800 rounded-lg px-3 py-2">
                    Zkopírují se pouze úkony s materiálem. Prodej domů se nezkopíruje. Pokud necháte odškrtnuté, otevře se stránka s novou návštěvou k úpravě.
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="duplicateModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                    <button class="px-3 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400">Duplikovat</button>
                </div>
            </form>
        </div>
    </div>
</template>

<template x-if="stockModal">
    <div x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400" x-text="stockMode === 'in' ? 'Příjem' : 'Výdej'"></div>
                    <div class="text-lg font-semibold text-white" x-text="stockProduct.name"></div>
                </div>
                <button type="button" @click="stockModal=false" class="text-slate-400 hover:text-white text-sm">Zavřít</button>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                    <div class="text-xs text-slate-400">Aktuálně</div>
                    <div class="text-lg font-semibold" x-text="Number(stockProduct.stock || 0).toFixed(3) + ' ks'"></div>
                </div>
                <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                    <div class="text-xs text-slate-400">Po zapsání</div>
                    <div class="text-lg font-semibold"
                         x-text="Math.max(projectedStock(), 0).toFixed(3) + ' ks'"></div>
                </div>
                <template x-if="projectedStock() < 0">
                    <div class="col-span-2 text-sm text-amber-300 bg-amber-500/10 border border-amber-500/30 rounded-lg px-3 py-2">
                        Výdej by snížil stav pod nulu. Uprav množství.
                    </div>
                </template>
            </div>

            <form method="POST" :action="stockProduct.url || '#'" class="space-y-3">
                @csrf
                <input type="hidden" name="direction" :value="stockMode">
                <input type="hidden" name="reason_type" :value="stockMode === 'out' ? stockReason : ''">

                <div class="grid grid-cols-2 gap-3">
                    <label class="space-y-1 text-sm text-slate-300 col-span-2">
                        <span>Množství (ks)</span>
                        <input name="quantity"
                               x-model="stockQty"
                               type="number"
                               step="0.001"
                               min="0.001"
                               required
                               data-stock-qty
                               class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                    </label>
                    <template x-if="stockMode === 'out'">
                        <label class="space-y-1 text-sm text-slate-300">
                            <span>Typ výdeje</span>
                            <select x-model="stockReason" class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                <option value="work">Výdej na práci</option>
                                <option value="retail">Výdej - prodej klientovi domů</option>
                            </select>
                        </label>
                    </template>
                    <label class="space-y-1 text-sm text-slate-300 col-span-2">
                        <span>Poznámka (volitelné)</span>
                        <input name="note"
                               x-model="stockNote"
                               placeholder="Např. dodávka, oprava inventury..."
                               class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="stockModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                    <button type="submit"
                            :disabled="projectedStock() < 0"
                            :class="[
                                'px-4 py-2 rounded-lg font-semibold',
                                projectedStock() < 0 ? 'bg-emerald-500/40 text-slate-300 cursor-not-allowed' : 'bg-emerald-500 text-slate-950 hover:bg-emerald-400'
                            ]"
                            x-text="stockMode === 'in' ? 'Zapsat příjem' : 'Zapsat výdej'"></button>
                </div>
            </form>
        </div>
    </div>
</template>

<template x-if="bulkModal">
    <div x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="bulkModal=false">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl p-6 space-y-4" x-data="bulkForm()">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Hromadný příjem</div>
                    <div class="text-lg font-semibold text-white">Zapiš více produktů najednou</div>
                </div>
                <button type="button" @click="bulkModal=false" class="text-slate-400 hover:text-white text-sm">Zavřít</button>
            </div>

            <!-- Modal pro vytvoření nového produktu -->
            <div x-show="newProductModal" x-cloak class="fixed inset-0 bg-slate-950/90 backdrop-blur-sm flex items-center justify-center z-[60]" @click.self="closeNewProductModal()" style="margin: -1.5rem;">
                <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="flex items-center justify-between sticky top-0 bg-slate-800 pb-2 border-b border-slate-700">
                        <div class="text-lg font-semibold text-white">Vytvořit nový produkt</div>
                        <button type="button" @click="closeNewProductModal()" class="text-slate-400 hover:text-white text-sm">✕</button>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="text-xs text-slate-400 mb-1 block">Název produktu *</label>
                                <input type="text" x-model="newProductName" required placeholder="např. Barva na vlasy - Hnědá" 
                                       class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 mb-1 block">SKU / Kód</label>
                                <input type="text" x-model="newProductSku" placeholder="např. BVH-001" 
                                       class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 mb-1 block">Skupina</label>
                                <select x-ref="newProductGroupSelect" x-model="newProductGroupId" 
                                        class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                    <option value="">Bez skupiny</option>
                                    <template x-for="g in groups" :key="g.id">
                                        <option :value="g.id" x-text="g.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 mb-1 block">Použití *</label>
                                <select x-model="newProductUsage" class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                    <option value="service">Do služby (g → ks)</option>
                                    <option value="retail">Prodej domů (ks)</option>
                                    <option value="both" selected>Obojí</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 mb-1 block">Velikost balení (g/ml)</label>
                                <input type="number" x-model="newProductPackage" step="0.01" min="0" placeholder="např. 100" 
                                       class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                <div class="text-xs text-slate-500 mt-1">Pro balené produkty (ks)</div>
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 mb-1 block">Minimální stav (ks)</label>
                                <input type="number" x-model="newProductMinUnits" step="0.001" min="0" placeholder="např. 5" 
                                       class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                <div class="text-xs text-slate-500 mt-1">Upozornění při nízkém stavu</div>
                            </div>
                            
                            <div class="col-span-2">
                                <label class="text-xs text-slate-400 mb-1 block">Poznámka</label>
                                <textarea x-model="newProductNotes" rows="2" placeholder="Volitelná poznámka..." 
                                          class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none resize-none"></textarea>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <input type="checkbox" x-model="newProductActive" id="newProductActive" 
                                       class="w-4 h-4 rounded bg-slate-900/60 border-slate-700 text-sky-500 focus:ring-2 focus:ring-sky-400">
                                <label for="newProductActive" class="text-sm text-slate-300">Aktivní produkt</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-700">
                        <button type="button" @click="closeNewProductModal()" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-200 hover:bg-slate-600 text-sm">Zrušit</button>
                        <button type="button" @click="createNewProduct()" class="px-5 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400 text-sm">Vytvořit produkt</button>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('products.adjust-batch') }}" class="space-y-3">
                @csrf
                <div class="max-h-[50vh] overflow-y-auto space-y-3 pr-1" data-bulk-scroll>
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="space-y-2 bg-slate-900/50 border border-slate-800 rounded-xl p-3">
                            <div class="grid grid-cols-4 gap-3 items-start">
                                <div class="col-span-3">
                                    <input type="text" 
                                           :list="`products-bulk-${index}`" 
                                           x-model="row.product_name"
                                           @input="updateBulkProductName(row, $event.target.value)"
                                           placeholder="Začněte psát název nebo SKU..."
                                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                    <datalist :id="`products-bulk-${index}`">
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.name" x-text="`${p.name} - ${p.sku || 'bez SKU'} (sklad: ${Number(p.stock).toFixed(3)} ks)`"></option>
                                        </template>
                                    </datalist>
                                    <input type="hidden" :name="`rows[${index}][product_id]`" x-model="row.product_id">
                                </div>
                                <div class="flex gap-2 items-center">
                                    <input type="number" step="0.001" min="0.001"
                                           x-model="row.qty"
                                           @input="if(row.qty && row.product_id && index === rows.length - 1) add()"
                                           :name="`rows[${index}][quantity]`"
                                           placeholder="Ks"
                                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-400 focus:outline-none"
                                           :data-bulk-qty="index === rows.length - 1">
                                    <button type="button" @click="remove(index)" class="text-xs px-3 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700">Odebrat</button>
                                </div>
                            </div>
                            <div x-show="row.product_name && !row.product_id" class="flex items-center gap-2">
                                <div class="flex-1 text-xs text-amber-400">⚠️ Produkt nebyl nalezen</div>
                                <button type="button" @click="showNewProductForm(row.product_name)" class="text-xs px-3 py-1.5 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/30">+ Vytvořit nový produkt</button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="add()" class="text-xs px-3 py-2 rounded-lg bg-slate-800 text-slate-100 hover:bg-slate-700">+ řádek</button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="bulkModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400">Zapsat příjem</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

    @if($section === 'products' && $selectedProduct)
        <div x-cloak x-show="productDeleteModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <div class="text-lg font-semibold text-white">Smazat produkt?</div>
                <div class="text-sm text-slate-300">Produkt <span class="font-semibold">{{ $selectedProduct->name }}</span> bude odstraněn. Pokud je použit v návštěvách, mazání může selhat.</div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="productDeleteModal=false" class="px-3 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">Zrušit</button>
                    <form method="POST" action="{{ route('products.destroy', $selectedProduct) }}">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-2 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-400">Smazat</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal pro účtenku --}}
    <div x-show="receiptData.show && receiptData.clientName" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="receiptData.show = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto" style="font-family: 'Courier New', monospace;">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-center flex-1 text-gray-900">ÚČTENKA</h2>
                    <button type="button" @click="receiptData.show = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                
                <div class="border-t border-b border-gray-300 py-3 space-y-1 text-gray-900">
                    <div><strong>Klient:</strong> <span x-text="receiptData.clientName"></span></div>
                    <div><strong>Datum:</strong> <span x-text="receiptData.date + ' ' + receiptData.time"></span></div>
                </div>
                
                <div x-show="receiptData.services?.length > 0" class="space-y-2">
                    <div class="font-bold text-gray-900">Úkony:</div>
                    <template x-for="service in receiptData.services" :key="service.title">
                        <div class="pl-4 text-gray-800" x-text="service.title"></div>
                    </template>
                </div>
                
                <div x-show="receiptData.retail?.length > 0" class="space-y-2">
                    <div class="font-bold text-gray-900">Prodej domů:</div>
                    <template x-for="item in receiptData.retail" :key="item.name">
                        <div class="pl-4 text-gray-800" x-text="item.name + ' - ' + item.quantity_units + ' ks'"></div>
                    </template>
                </div>
                
                <div class="border-t border-gray-300 pt-3 text-right">
                    <div class="text-xl font-bold text-gray-900">
                        Celkem: <span x-text="Number(receiptData.totalPrice).toFixed(0)"></span> Kč
                    </div>
                </div>
                
                <div class="text-center text-sm text-gray-600 pt-2">
                    Děkujeme za návštěvu!
                </div>
                
                <div class="flex gap-2 pt-4 border-t border-gray-200">
                    <button type="button" @click="receiptData.show = false" class="flex-1 px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">
                        Zavřít
                    </button>
                    <button type="button" @click="window.print()" class="flex-1 px-4 py-2 rounded-lg bg-emerald-500 text-white font-semibold hover:bg-emerald-400">
                        🖨️ Tisknout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.productDataset = window.productDataset ?? @json($productDataset);

    // Scroll to selected product on page load (instant, no animation)
    @if($section === 'products' && $selectedProduct)
        const selectedProductEl = document.querySelector('[data-filter-item].bg-slate-800\\/80');
        if (selectedProductEl) {
            const container = document.querySelector('[data-filter-container]');
            if (container) {
                const containerRect = container.getBoundingClientRect();
                const elementRect = selectedProductEl.getBoundingClientRect();
                const relativeTop = selectedProductEl.offsetTop;
                const offset = relativeTop - (containerRect.height / 2) + (elementRect.height / 2);
                
                // Instant scroll without animation
                container.scrollTop = offset;
            }
        }
    @endif

    // Initialize Tom Select
    window.initTomSelect = function(selectElement) {
        if (selectElement.tomselect) return; // Already initialized
        
        const products = window.productDataset || [];
        const options = products.map(p => ({
            value: p.id,
            text: p.name,
            sku: p.sku || 'bez SKU',
            stock: p.stock,
            group: p.group || 'bez skupiny'
        }));
        
        new TomSelect(selectElement, {
            options: options,
            create: false,
            sortField: 'text',
            maxOptions: 100,
            placeholder: 'Vyberte produkt',
            searchField: ['text', 'sku'],
            render: {
                option: function(data, escape) {
                    return `<div class="py-1">
                        <div class="font-medium">${escape(data.text)}</div>
                        <div class="text-xs text-slate-400">${escape(data.group)} • ${escape(data.sku)} • ${escape(data.stock)} ks</div>
                    </div>`;
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                }
            }
        });
    };

    function getScrollOffset() {
        const footer = document.querySelector('[data-visit-footer]');
        return footer ? footer.offsetHeight + 24 : 32;
    }

    function updateVisitScrollPadding() {
        const offset = getScrollOffset();
        document.documentElement.style.setProperty('--visit-scroll-offset', `${offset}px`);
    }

    window.addEventListener('load', updateVisitScrollPadding);
    window.addEventListener('resize', updateVisitScrollPadding);
    updateVisitScrollPadding();

    function scrollToLast(selector, offset = 0) {
        requestAnimationFrame(() => {
            const container = document.querySelector(selector);
            if (container && container.lastElementChild) {
                const target = container.scrollHeight - container.clientHeight + offset;
                container.scrollTo({ top: Math.max(target, 0), behavior: 'smooth' });
            }
        });
    }

    function productPicker(config) {
        console.log('productPicker initialized', config);
        return {
            products: config.products || window.productDataset || [],
            nameField: config.nameField,
            dropdownWidth: config.dropdownWidth || '100%',
            selectedId: config.initialId || '',
            search: '',
            open: false,
            modalOpen: false,
            modalSearch: '',
            highlighted: 0,
            scrollTarget: config.scrollTarget || '[data-visit-scroll]',
            scrollOffset: config.scrollOffset || getScrollOffset(),
            dropdownRect: {top: 0, left: 0, width: 0},
            get filtered() {
                const searchTerm = String(this.search || '').toLowerCase().trim();
                if (!searchTerm) {
                    return this.products.slice(0, 12);
                }
                const filtered = [];
                for (let i = 0; i < this.products.length && filtered.length < 12; i++) {
                    const p = this.products[i];
                    const name = String(p.name || '').toLowerCase();
                    const sku = String(p.sku || '').toLowerCase();
                    const group = String(p.group || '').toLowerCase();
                    if (name.includes(searchTerm) || sku.includes(searchTerm) || group.includes(searchTerm)) {
                        filtered.push(p);
                    }
                }
                return filtered;
            },
            modalFiltered() {
                const q = (this.modalSearch || '').toLowerCase().trim();
                if (!q) return this.products;
                return this.products.filter(p =>
                    (p.name && p.name.toLowerCase().includes(q)) ||
                    (p.sku && p.sku.toLowerCase().includes(q)) ||
                    (p.group && p.group.toLowerCase().includes(q))
                );
            },
            select(product) {
                this.selectedId = product.id;
                this.search = product.name;
                this.modalOpen = false;
                this.modalSearch = '';
                if (typeof config.onSelect === 'function') {
                    config.onSelect(product.id);
                }
                this.open = false;
                if (this.scrollTarget) {
                    setTimeout(() => scrollToLast(this.scrollTarget, this.scrollOffset), 0);
                }
            },
            clear() {
                this.selectedId = '';
                this.search = '';
                this.open = true;
                this.updateRect();
                this.highlighted = 0;
                this.$nextTick(() => {
                    this.$refs.input?.focus();
                });
            },
            handleBlur() {
                setTimeout(() => {
                    this.open = false;
                }, 150);
            },
            openModal() {
                this.modalOpen = true;
                this.modalSearch = '';
                this.$nextTick(() => {
                    const modalInput = this.$el.querySelector('[data-modal-search]');
                    if (modalInput) {
                        modalInput.focus();
                    }
                });
            },
            closeModal() {
                this.modalOpen = false;
                this.$refs.input?.focus();
            },
            updateRect() {
                if (!this.$refs.input) return;
                const rect = this.$refs.input.getBoundingClientRect();
                this.dropdownRect = {
                    top: rect.bottom + window.scrollY + 4,
                    left: rect.left + window.scrollX,
                    width: rect.width || this.parseWidth(this.dropdownWidth),
                };
            },
            widthCss() {
                const w = this.dropdownRect.width || this.parseWidth(this.dropdownWidth);
                return typeof w === 'number' ? `${w}px` : w;
            },
            parseWidth(value) {
                if (typeof value === 'number') return value;
                if (typeof value === 'string' && value.endsWith('px')) {
                    const parsed = parseFloat(value);
                    return isNaN(parsed) ? 0 : parsed;
                }
                return 0;
            },
            onKeydown(event) {
                if (!this.open && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
                    this.open = true;
                    this.$nextTick(() => this.updateRect());
                }
                if (event.key === 'ArrowDown') {
                    this.highlighted = (this.highlighted + 1) % Math.max(this.filtered.length, 1);
                    event.preventDefault();
                }
                if (event.key === 'ArrowUp') {
                    this.highlighted = (this.highlighted - 1 + this.filtered.length) % Math.max(this.filtered.length, 1);
                    event.preventDefault();
                }
                if (event.key === 'Enter' && this.filtered[this.highlighted]) {
                    this.select(this.filtered[this.highlighted]);
                    event.preventDefault();
                }
            },
            init() {
                if (this.selectedId) {
                    const found = this.products.find(p => p.id == this.selectedId);
                    if (found) {
                        this.search = found.name;
                    }
                }
                window.addEventListener('resize', () => this.open && this.updateRect());
                window.addEventListener('scroll', () => this.open && this.updateRect(), true);
            }
        }
    }

    function visitForm() {
        return {
            products: window.productDataset || [],
            services: [{
                title: 'Úkon 1',
                note: '',
                products: [{product_id: '', product_name: '', used_grams: ''}],
            }],
            retail: [],
            closeNow: false,
            confirmOpen: false,
            summary: {services: [], retail: []},
            validationErrors: [],
            addService() {
                this.services.push({
                    title: 'Úkon ' + (this.services.length + 1),
                    note: '',
                    products: [{product_id: '', used_grams: ''}],
                });
                setTimeout(() => {
                    scrollToLast('[data-visit-scroll]', getScrollOffset());
                    const last = document.querySelector('[data-services-scroll] [data-service-card]:last-child input[type="text"]');
                    last?.focus();
                }, 0);
            },
            addProduct(service) {
                service.products.push({product_id: '', product_name: '', used_grams: ''});
                setTimeout(() => {
                    scrollToLast('[data-visit-scroll]', getScrollOffset());
                }, 0);
            },
            addRetail() {
                this.retail.push({product_id: '', product_name: '', quantity_units: '', unit_price: ''});
                setTimeout(() => {
                    scrollToLast('[data-visit-scroll]', getScrollOffset());
                }, 0);
            },
            updateProductId(item, name) {
                const product = (window.productDataset || []).find(p => p.name === name);
                if (product) {
                    item.product_id = product.id;
                    item.product_name = product.name;
                } else {
                    item.product_id = '';
                }
            },
            productName(id) {
                const found = (window.productDataset || []).find(p => p.id == id);
                return found ? found.name : `Produkt #${id}`;
            },
            productData(id) {
                return (window.productDataset || []).find(p => p.id == id);
            },
            buildSummary() {
                const servicesMap = {};
                const errors = [];
                this.services.forEach(s => {
                    (s.products || []).forEach(p => {
                        const pid = p.product_id;
                        const grams = parseFloat(p.used_grams || 0);
                        // prázdný řádek: bez produktu i množství -> ignoruj
                        if (!pid && !(grams > 0)) {
                            return;
                        }
                        // množství, ale bez produktu
                        if (!pid && grams > 0) {
                            errors.push('Úkon: vyber produkt k ' + grams.toFixed(2) + ' g.');
                            return;
                        }
                        // produkt, ale množství <= 0
                        if (pid && !(grams > 0)) {
                            errors.push(this.productName(pid) + ': zadej množství v gramech.');
                            return;
                        }
                        servicesMap[pid] = (servicesMap[pid] || 0) + grams;
                    });
                });
                const retailMap = {};
                this.retail.forEach(r => {
                    const pid = r.product_id;
                    const units = parseFloat(r.quantity_units || 0);
                    if (!pid && !(units > 0)) {
                        return;
                    }
                    if (!pid && units > 0) {
                        errors.push('Prodej domů: vyber produkt k ' + units.toFixed(3) + ' ks.');
                        return;
                    }
                    if (pid && !(units > 0)) {
                        errors.push(this.productName(pid) + ': zadej množství ks (prodej domů).');
                        return;
                    }
                    retailMap[pid] = (retailMap[pid] || 0) + units;
                });
                this.summary = {
                    services: Object.entries(servicesMap).map(([id, grams]) => ({
                        id,
                        name: this.productName(id),
                        grams,
                    })),
                    retail: Object.entries(retailMap).map(([id, units]) => ({
                        id,
                        name: this.productName(id),
                        units,
                    })),
                };
                // Kontrola zásob (g -> ks)
                this.summary.services.forEach(line => {
                    const prod = this.productData(line.id);
                    if (!prod || !(prod.package_size_grams > 0)) {
                        return;
                    }
                    const neededUnits = line.grams / prod.package_size_grams;
                    const after = (prod.stock ?? 0) - neededUnits;
                    if (after < 0) {
                        errors.push(`${line.name}: potřeba ${neededUnits.toFixed(3)} ks, skladem ${Number(prod.stock ?? 0).toFixed(3)} ks`);
                    }
                });
                // Kontrola zásob retail (ks)
                this.summary.retail.forEach(line => {
                    const prod = this.productData(line.id);
                    const after = (prod?.stock ?? 0) - (line.units ?? 0);
                    if (after < 0) {
                        errors.push(`${line.name}: potřeba ${Number(line.units).toFixed(3)} ks, skladem ${Number(prod?.stock ?? 0).toFixed(3)} ks`);
                    }
                });
                this.validationErrors = errors;
            },
            openConfirm() {
                this.buildSummary();
                this.confirmOpen = true;
            },
            submitConfirmed() {
                this.confirmOpen = false;
                this.$refs.visitForm.submit();
            },
        }
    }

    function bulkForm() {
        return {
            products: window.productDataset || [],
            groups: @json($groups->map(fn($g) => ['id' => $g->id, 'name' => $g->name])),
            rows: [{product_id: '', product_name: '', qty: ''}],
            newProductModal: false,
            newProductName: '',
            newProductSku: '',
            newProductGroupId: '',
            newProductUsage: 'both',
            newProductPackage: 0,
            newProductMinUnits: 0,
            newProductActive: true,
            newProductNotes: '',
            groupTomSelect: null,
            scroll() {
                scrollToLast('[data-bulk-scroll]', 0);
            },
            add() {
                this.rows.push({product_id: '', product_name: '', qty: ''});
                requestAnimationFrame(() => {
                    this.scroll();
                });
            },
            remove(idx) {
                this.rows.splice(idx, 1);
                if (this.rows.length === 0) {
                    this.add();
                }
                requestAnimationFrame(() => this.scroll());
            },
            updateBulkProductName(row, name) {
                const product = (window.productDataset || []).find(p => 
                    p.name === name || p.sku === name
                );
                if (product) {
                    row.product_id = product.id;
                    row.product_name = product.name;
                } else {
                    row.product_id = '';
                }
            },

            showNewProductForm(name) {
                this.newProductName = name || '';
                this.newProductSku = '';
                this.newProductGroupId = '';
                this.newProductUsage = 'both';
                this.newProductPackage = 0;
                this.newProductMinUnits = 0;
                this.newProductActive = true;
                this.newProductNotes = '';
                this.newProductModal = true;
                
                // Initialize Tom Select after modal opens
                requestAnimationFrame(() => {
                    const select = this.$refs.newProductGroupSelect;
                    if (select && !this.groupTomSelect) {
                        const options = this.groups.map(g => ({
                            value: g.id,
                            text: g.name
                        }));
                        
                        this.groupTomSelect = new TomSelect(select, {
                            options: options,
                            create: true,
                            sortField: 'text',
                            placeholder: 'Vyberte nebo vytvořte skupinu',
                            onItemAdd: function() {
                                this.blur();
                            }
                        });
                    }
                });
            },
            closeNewProductModal() {
                this.newProductModal = false;
                if (this.groupTomSelect) {
                    this.groupTomSelect.destroy();
                    this.groupTomSelect = null;
                }
            },
            async createNewProduct() {
                if (!this.newProductName.trim()) {
                    alert('Zadejte název produktu');
                    return;
                }
                
                // Get group name from ID or new value (skupina není povinná)
                let groupName = '';
                let groupId = null;
                
                if (this.groupTomSelect) {
                    const value = this.groupTomSelect.getValue();
                    if (value) {
                        const option = this.groupTomSelect.options[value];
                        groupName = option ? option.text : value;
                        groupId = !isNaN(value) ? value : null;
                    }
                } else if (this.newProductGroupId) {
                    const group = this.groups.find(g => g.id == this.newProductGroupId);
                    groupName = group ? group.name : '';
                    groupId = this.newProductGroupId;
                }
                
                try {
                    const response = await fetch('{{ route('products.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: this.newProductName,
                            sku: this.newProductSku || null,
                            product_group_id: groupId,
                            group_name: groupName || null,
                            usage_type: this.newProductUsage,
                            package_size_grams: this.newProductPackage || 0,
                            initial_stock: 0,
                            min_units: this.newProductMinUnits || 0,
                            is_active: this.newProductActive,
                            notes: this.newProductNotes || null
                        })
                    });
                    
                    if (response.ok) {
                        const newProduct = await response.json();
                        
                        // Update both arrays
                        this.products = [...this.products, newProduct];
                        window.productDataset = [...window.productDataset, newProduct];
                        
                        this.closeNewProductModal();
                        
                        // Auto-fill first empty row
                        const emptyRow = this.rows.find(r => !r.product_id);
                        if (emptyRow) {
                            emptyRow.product_id = newProduct.id;
                            emptyRow.product_name = newProduct.name;
                        }
                        
                        alert(`Produkt "${newProduct.name}" byl vytvořen`);
                    } else {
                        const data = await response.json();
                        alert(data.message || 'Chyba při vytváření produktu');
                    }
                } catch (error) {
                    console.error(error);
                    alert('Chyba při vytváření produktu');
                }
            }
        }
    }

    function clientNotesComponent(config) {
        return {
            tab: 'history',
            notes: config.notes || [],
            storeUrl: config.storeUrl,
            csrf: config.csrf,
            newBody: '',
            editingId: null,
            editingBody: '',
            loading: false,
            async addNote() {
                if (!this.newBody.trim()) return;
                this.loading = true;
                try {
                    const res = await fetch(this.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({body: this.newBody}),
                    });
                    if (res.ok) {
                        const note = await res.json();
                        this.notes.unshift(note);
                        this.newBody = '';
                    }
                } finally {
                    this.loading = false;
                }
            },
            startEdit(note) {
                this.editingId = note.id;
                this.editingBody = note.body;
            },
            cancelEdit() {
                this.editingId = null;
                this.editingBody = '';
            },
            async saveEdit(note) {
                if (!this.editingBody.trim()) return;
                this.loading = true;
                try {
                    const res = await fetch(note.urls.update, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({body: this.editingBody}),
                    });
                    if (res.ok) {
                        const updated = await res.json();
                        Object.assign(note, updated);
                        this.cancelEdit();
                    }
                } finally {
                    this.loading = false;
                }
            },
            async destroy(note) {
                this.loading = true;
                try {
                    await fetch(note.urls.delete, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                    });
                    this.notes = this.notes.filter(n => n.id !== note.id);
                } finally {
                    this.loading = false;
                }
            },
        }
    }

    function printVisitReceipt(visit) {
        @if($selectedClient)
        const clientName = '{{ addslashes($selectedClient->name) }}';
        @else
        const clientName = 'Klient';
        @endif
        const date = new Date(visit.occurred_at_raw).toLocaleDateString('cs-CZ');
        const time = new Date(visit.occurred_at_raw).toLocaleTimeString('cs-CZ', {hour: '2-digit', minute: '2-digit'});
        
        // Použij Alpine.$data pomocí Alpine.js API
        const mainElement = document.getElementById('main-app');
        if (mainElement && typeof Alpine !== 'undefined') {
            const alpineData = Alpine.$data(mainElement);
            if (alpineData && alpineData.receiptData) {
                alpineData.receiptData.show = true;
                alpineData.receiptData.clientName = clientName;
                alpineData.receiptData.date = date;
                alpineData.receiptData.time = time;
                alpineData.receiptData.services = visit.services || [];
                alpineData.receiptData.retail = visit.retail || [];
                alpineData.receiptData.totalPrice = visit.total_price;
            }
        }
    }
</script>

</body>
</html>
