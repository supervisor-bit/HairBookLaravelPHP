<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hromadný příjem - HairBook</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.4/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
    </style>
</head>
<body class="h-screen overflow-hidden text-slate-200">
    <div x-data="bulkReceipt()" x-init="init()" @keydown.escape.window="goBack()" class="h-full flex flex-col">
        
        <!-- Fixed Header -->
        <div class="bg-slate-900/50 backdrop-blur-sm border-b border-slate-700/50 px-8 py-6 flex-shrink-0">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">📦 Hromadný příjem</h1>
                        <p class="text-slate-400">Naskenujte nebo zadejte produkty a jejich množství</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('dashboard', ['section' => 'products']) }}" 
                           class="px-4 py-2 rounded-lg bg-slate-800/60 border border-slate-700 text-slate-300 hover:bg-slate-700/60 transition">
                            ← Zpět
                        </a>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="bg-slate-800/40 backdrop-blur-sm border border-slate-700/50 rounded-xl p-6">
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <div class="text-sm text-slate-400 mb-1">Počet položek</div>
                            <div class="text-2xl font-bold text-white" x-text="validRowsCount"></div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400 mb-1">Celkové množství</div>
                            <div class="text-2xl font-bold text-emerald-400" x-text="totalQuantity.toFixed(3) + ' ks'"></div>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400 mb-1">Stav</div>
                            <div class="text-lg text-slate-300" x-text="validRowsCount > 0 ? 'Připraveno k uložení' : 'Prázdný seznam'"></div>
                        </div>
                    </div>
                </div>

                @if(session('status'))
                <div class="mt-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl p-4 text-emerald-400">
                    {{ session('status') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto px-8 py-6" x-ref="scrollContainer">
            <div class="max-w-7xl mx-auto">
                <!-- Form -->
                <form method="POST" action="{{ route('products.adjust-batch') }}" @submit="return validateForm()">
                    @csrf

                    <!-- Table Header -->
                    <div class="mb-4 grid grid-cols-12 gap-4 px-4 text-sm font-semibold text-slate-400">
                        <div class="col-span-1">#</div>
                        <div class="col-span-5">Produkt</div>
                        <div class="col-span-2">SKU</div>
                        <div class="col-span-2">Aktuální sklad</div>
                        <div class="col-span-2">Množství (ks)</div>
                    </div>

                    <!-- Rows Container -->
                    <div class="space-y-3 mb-6" x-ref="rowsContainer">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="bg-slate-800/40 backdrop-blur-sm border rounded-xl p-4 transition"
                         :class="row.product_id ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-slate-700/50'">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            
                            <!-- Row Number -->
                            <div class="col-span-1 text-slate-400 font-mono text-sm" x-text="index + 1"></div>
                            
                            <!-- Product Input with Datalist -->
                            <div class="col-span-5">
                                <input type="text" 
                                       :list="'products-' + index" 
                                       x-model="row.product_name"
                                       @input="updateProduct(row, $event.target.value)"
                                       @keydown.enter="$event.preventDefault(); focusQuantity(index)"
                                       @change="updateProduct(row, $event.target.value)"
                                       placeholder="Začněte psát název nebo naskenujte SKU..."
                                       class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                       :class="row.product_id ? 'border-emerald-500/50' : ''">
                                <datalist :id="'products-' + index">
                                    <template x-for="p in products">
                                        <option :value="p.name" x-text="`${p.name} - ${p.sku || 'bez SKU'}`"></option>
                                    </template>
                                </datalist>
                                <input type="hidden" :name="'rows[' + index + '][product_id]'" x-model="row.product_id">
                            </div>

                            <!-- SKU Display -->
                            <div class="col-span-2">
                                <div class="text-sm text-slate-400 font-mono" x-text="row.sku || '-'"></div>
                            </div>

                            <!-- Current Stock Display -->
                            <div class="col-span-2">
                                <div class="text-sm font-semibold" 
                                     :class="row.product_id ? 'text-sky-400' : 'text-slate-500'"
                                     x-text="row.current_stock !== null ? Number(row.current_stock).toFixed(3) + ' ks' : '-'"></div>
                            </div>

                            <!-- Quantity Input -->
                            <div class="col-span-2 flex gap-2 items-center">
                                <input type="number" 
                                       step="0.001" 
                                       min="0.001"
                                       x-model="row.qty"
                                       :name="'rows[' + index + '][quantity]'"
                                       @input="handleQuantityInput(row, index)"
                                       @keydown.enter.prevent="handleEnterOnQuantity(index)"
                                       :x-ref="'qty-' + index"
                                       placeholder="0.000"
                                       class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                       :class="row.qty ? 'border-emerald-500/50 font-semibold' : ''">
                                <button type="button" 
                                        @click="removeRow(index)" 
                                        x-show="rows.length > 1"
                                        class="px-3 py-2 rounded-lg bg-slate-700/50 text-slate-400 hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/30 border border-slate-600 transition text-sm">
                                    ×
                                </button>
                            </div>
                        </div>

                        <!-- Product Not Found Warning -->
                        <div x-show="row.product_name && !row.product_id" 
                             class="mt-3 flex items-center gap-3">
                            <span class="text-sm text-amber-400 flex items-center gap-2">
                                <span>⚠️</span>
                                <span>Produkt nebyl nalezen</span>
                            </span>
                            <button type="button" 
                                    @click="openNewProductModal(row.product_name, index)"
                                    class="text-xs px-3 py-1.5 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/30">
                                + Vytvořit nový produkt
                            </button>
                        </div>
                    </div>
                    </template>
                </div>

                <!-- Add Row Button -->
                <button type="button" 
                        @click="addRow()" 
                        class="w-full py-3 rounded-lg bg-slate-800/60 border border-slate-700 text-slate-300 hover:bg-slate-700/60 hover:border-emerald-500/30 hover:text-emerald-400 transition mb-6">
                    + Přidat řádek
                </button>

                <!-- Keyboard Shortcuts Help -->
                <div class="bg-slate-800/20 border border-slate-700/30 rounded-xl p-4 mb-6">
                    <div class="text-sm text-slate-400">
                        <strong class="text-slate-300">Klávesové zkratky:</strong>
                        <span class="ml-4">Enter na produktu → přesun na množství</span>
                        <span class="ml-4">•</span>
                        <span class="ml-2">Enter na množství → nový řádek</span>
                        <span class="ml-4">•</span>
                        <span class="ml-2">Esc → zpět na dashboard</span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Fixed Footer -->
    <div class="bg-slate-900/50 backdrop-blur-sm border-t border-slate-700/50 px-8 py-6 flex-shrink-0">
        <div class="max-w-7xl mx-auto flex gap-4">
            <button type="button"
                    @click="document.querySelector('form').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))"
                    :disabled="validRowsCount === 0"
                    class="flex-1 py-4 rounded-xl font-semibold transition shadow-lg"
                    :class="validRowsCount > 0 
                        ? 'bg-emerald-500 text-white hover:bg-emerald-600' 
                        : 'bg-slate-700 text-slate-500 cursor-not-allowed'">
                <span x-show="validRowsCount === 0">Žádné položky k uložení</span>
                <span x-show="validRowsCount > 0" x-text="'Uložit příjem (' + validRowsCount + ' položek)'"></span>
            </button>
            <button type="button" 
                    @click="resetForm()" 
                    class="px-6 py-4 rounded-xl bg-slate-800/60 border border-slate-700 text-slate-300 hover:bg-slate-700/60 transition">
                Vymazat vše
            </button>
        </div>
    </div>

        <!-- New Product Modal -->
        <div x-show="newProductModal" 
             x-cloak
             @click.self="newProductModal = false"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-slate-700 flex justify-between items-center sticky top-0 bg-slate-800 z-10">
                    <h3 class="text-xl font-semibold text-white">Vytvořit nový produkt</h3>
                    <button type="button" @click="newProductModal = false" class="text-slate-400 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                
                <form method="POST" action="{{ route('products.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_bulk" value="1">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="text-xs text-slate-400 mb-1 block">Název produktu *</label>
                            <input type="text" name="name" x-model="newProductName" required placeholder="např. Barva na vlasy - Hnědá"
                                   class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                        
                        <div>
                            <label class="text-xs text-slate-400 mb-1 block">SKU / Kód</label>
                            <input type="text" name="sku" x-model="newProductSku" placeholder="např. BVH-001"
                                   class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>
                        
                        <div>
                            <label class="text-xs text-slate-400 mb-1 block">Skupina</label>
                            <select name="product_group_id" x-model="newProductGroupId"
                                    class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="">Bez skupiny</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="text-xs text-slate-400 mb-1 block">Použití *</label>
                            <select name="usage_type" x-model="newProductUsage"
                                    class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="service">Do služby (g → ks)</option>
                                <option value="retail">Prodej domů (ks)</option>
                                <option value="both" selected>Obojí</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="text-xs text-slate-400 mb-1 block">Velikost balení (g/ml)</label>
                            <input type="number" step="0.01" min="0" name="package_size_grams" x-model="newProductPackage" placeholder="např. 100"
                                   class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            <div class="text-xs text-slate-500 mt-1">Pro balené produkty (ks)</div>
                        </div>
                        
                        <div>
                            <label class="text-xs text-slate-400 mb-1 block">Minimální stav (ks)</label>
                            <input type="number" step="0.001" min="0" name="min_units" x-model="newProductMinUnits" placeholder="např. 5"
                                   class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            <div class="text-xs text-slate-500 mt-1">Upozornění při nízkém stavu</div>
                        </div>
                        
                        <div class="col-span-2">
                            <label class="text-xs text-slate-400 mb-1 block">Poznámka</label>
                            <textarea name="notes" x-model="newProductNotes" rows="2" placeholder="Volitelná poznámka..."
                                      class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none resize-none"></textarea>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                   class="w-4 h-4 rounded bg-slate-900/60 border-slate-700 text-emerald-500 focus:ring-2 focus:ring-emerald-400">
                            <label for="is_active" class="text-sm text-slate-300">Aktivní produkt</label>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-700">
                        <button type="button" @click="newProductModal = false" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700 text-sm">
                            Zrušit
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold text-sm hover:bg-emerald-400">
                            Přidat produkt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.productDataset = @json($productDataset);

        function bulkReceipt() {
            return {
                rows: [
                    {product_id: '', product_name: '', sku: '', current_stock: null, qty: ''}
                ],
                products: [],
                newProductModal: false,
                newProductName: '',
                newProductSku: '',
                newProductGroupId: '',
                newProductUsage: 'both',
                newProductPackage: '',
                newProductStock: '',
                newProductMinUnits: '',
                newProductNotes: '',
                pendingRowIndex: null,

                init() {
                    this.products = window.productDataset || [];
                    // Focus first input
                    this.$nextTick(() => {
                        const firstInput = document.querySelector('input[type="text"]');
                        if (firstInput) firstInput.focus();
                    });
                },

                updateProduct(row, value) {
                    const product = this.products.find(p => 
                        p.name === value || p.sku === value
                    );
                    
                    if (product) {
                        row.product_id = product.id;
                        row.product_name = product.name;
                        row.sku = product.sku;
                        row.current_stock = product.stock;
                    } else {
                        row.product_id = '';
                        row.sku = '';
                        row.current_stock = null;
                    }
                },

                focusQuantity(index) {
                    this.$refs['qty-' + index]?.focus();
                },

                handleQuantityInput(row, index) {
                    // Auto-add row when both product and quantity are filled on last row
                    if (row.product_id && row.qty && index === this.rows.length - 1) {
                        this.addRow();
                    }
                },

                scrollToInput(input) {
                    if (!input) return;
                    
                    requestAnimationFrame(() => {
                        input.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'nearest'
                        });
                    });
                },

                handleEnterOnQuantity(index) {
                    if (index === this.rows.length - 1) {
                        this.addRow();
                    } else {
                        // Focus next row's product input
                        const nextInput = document.querySelectorAll('input[type="text"]')[index + 1];
                        if (nextInput) {
                            this.scrollToInput(nextInput);
                            setTimeout(() => nextInput.focus({preventScroll: true}), 100);
                        }
                    }
                },

                addRow() {
                    this.rows.push({product_id: '', product_name: '', sku: '', current_stock: null, qty: ''});
                    this.$nextTick(() => {
                        setTimeout(() => {
                            // Focus the new row's product input
                            const inputs = document.querySelectorAll('input[type="text"]');
                            const newInput = inputs[inputs.length - 1];
                            if (newInput) {
                                this.scrollToInput(newInput);
                                // Focus without scrolling
                                setTimeout(() => newInput.focus({preventScroll: true}), 100);
                            }
                        }, 100);
                    });
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                resetForm() {
                    if (confirm('Opravdu chcete vymazat všechny řádky?')) {
                        this.rows = [{product_id: '', product_name: '', sku: '', current_stock: null, qty: ''}];
                        this.$nextTick(() => {
                            document.querySelector('input[type="text"]')?.focus();
                        });
                    }
                },

                openNewProductModal(productName, rowIndex) {
                    this.newProductName = productName || '';
                    this.newProductSku = '';
                    this.newProductGroupId = '';
                    this.newProductUsage = 'both';
                    this.newProductPackage = '';
                    this.newProductStock = '';
                    this.newProductMinUnits = '';
                    this.newProductNotes = '';
                    this.pendingRowIndex = rowIndex;
                    this.newProductModal = true;
                },

                validateForm() {
                    if (this.validRowsCount === 0) {
                        alert('Zadejte alespoň jeden produkt s množstvím.');
                        return false;
                    }
                    return true;
                },

                goBack() {
                    if (this.validRowsCount > 0) {
                        if (confirm('Máte neuložené změny. Opravdu chcete odejít?')) {
                            window.location.href = '{{ route('dashboard', ['section' => 'products']) }}';
                        }
                    } else {
                        window.location.href = '{{ route('dashboard', ['section' => 'products']) }}';
                    }
                },

                get validRowsCount() {
                    return this.rows.filter(r => r.product_id && r.qty > 0).length;
                },

                get totalQuantity() {
                    return this.rows
                        .filter(r => r.product_id && r.qty > 0)
                        .reduce((sum, r) => sum + parseFloat(r.qty || 0), 0);
                }
            }
        }
    </script>
</body>
</html>
