<!DOCTYPE html>
<html lang="cs" class="h-full" x-data="{ theme: localStorage.getItem('theme') || 'dark' }" :class="theme"
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nová návštěva - {{ $client->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.4/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(68%) sepia(46%) saturate(458%) hue-rotate(110deg) brightness(95%) contrast(92%);
        }
        
        /* Styling for number input spinners */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
            filter: invert(68%) sepia(46%) saturate(458%) hue-rotate(110deg) brightness(95%) contrast(92%);
            margin-left: 8px;
        }
        
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        .glass {
            background: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(8px) !important;
        }
        
        .glass {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
        }
        
        input[type="number"]::-webkit-inner-spin-button {
            height: 32px;
        }
        
        /* Toast animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .toast-enter {
            animation: slideInRight 0.3s ease-out;
        }
        
        .toast-exit {
            animation: slideOutRight 0.3s ease-in;
        }
        
        /* Loading spinner */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #printContent, #printContent * {
                visibility: visible;
            }
            #printContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 h-full overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(90,204,255,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(255,117,215,0.12),transparent_25%),radial-gradient(circle_at_50%_80%,rgba(120,178,255,0.10),transparent_25%)] pointer-events-none"></div>


    
    <div class="relative h-full flex flex-col" x-data="visitEditor()">
        
        {{-- Header s informacemi o návštěvě --}}
        <header class="bg-slate-900/60 backdrop-blur-sm border-b border-slate-800 px-6 py-4 flex-shrink-0">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard', ['section' => 'clients', 'client' => $client->id]) }}" 
                       class="text-slate-400 hover:text-white transition-colors">
                        ← Zpět
                    </a>
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Nová návštěva</div>
                        <div class="text-2xl font-semibold">{{ $client->name }}</div>
                    </div>
                    
                    {{-- Quick Stats --}}
                    <div class="flex items-center gap-4 text-sm" x-show="services.length > 0 || retail.length > 0">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/50 border border-slate-700" x-show="services.length > 0">
                            <span class="text-slate-400">Úkony:</span>
                            <span class="font-semibold text-slate-100" x-text="stats.servicesCount"></span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/50 border border-slate-700" x-show="stats.totalGrams > 0">
                            <span class="text-slate-400">Materiál:</span>
                            <span class="font-semibold text-slate-100" x-text="stats.totalGrams.toFixed(0) + ' g'"></span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/50 border border-slate-700" x-show="stats.totalRetailUnits > 0">
                            <span class="text-slate-400">Prodej:</span>
                            <span class="font-semibold text-slate-100" x-text="stats.totalRetailUnits.toFixed(1) + ' ks'"></span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-500/20 border border-red-500/50" x-show="stats.productsWithLowStock.length > 0">
                            <span class="text-red-200">⚠️ Nedostatek:</span>
                            <span class="font-semibold text-red-100" x-text="stats.productsWithLowStock.length"></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-300 flex items-center gap-2">
                        <input type="checkbox" x-model="closeNow" class="rounded border-slate-700 bg-slate-900/60">
                        Uzavřít hned (odepsat sklad)
                    </label>
                    <button type="button" @click="showImportModal = true; console.log('Previous visits:', previousVisits)" 
                            class="px-4 py-2.5 rounded-lg glass border border-slate-700 text-slate-200 font-medium hover:bg-slate-700/70 transition-all">
                        📥 Import z historie
                    </button>
                    <button type="button" @click="showPrintModal()" 
                            class="px-4 py-2.5 rounded-lg glass border border-slate-700 text-slate-200 font-medium hover:bg-slate-700/70 transition-all">
                        📄 Náhled
                    </button>
                    <button type="button" @click="saveVisit()" 
                            class="px-6 py-3 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-950 font-semibold hover:from-emerald-400 hover:to-emerald-500 transition-all shadow-lg shadow-emerald-500/25">
                        Uložit návštěvu
                    </button>
                </div>
            </div>
            
            {{-- Základní info o návštěvě --}}
            <div class="grid grid-cols-3 gap-4">
                <label class="text-sm text-slate-300 space-y-1">
                    <span>Datum a čas</span>
                    <input type="datetime-local" x-model="occurredAt" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </label>
                <label class="text-sm text-slate-300 space-y-1">
                    <span>Celková cena návštěvy</span>
                    <input type="number" step="0.01" x-model="totalPrice" placeholder="0" x-ref="totalPriceInput"
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </label>
                <label x-show="retail.some(r => r.product_id)" class="text-sm text-slate-300 space-y-1">
                    <span>Cena za prodej domů (volitelné)</span>
                    <input type="number" step="0.01" x-model="retailPrice" placeholder="0"
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </label>
                <label class="text-sm text-slate-300 space-y-1">
                    <span>Poznámka k návštěvě</span>
                    <input type="text" x-model="note" placeholder="Krátký popis návštěvy" 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </label>
            </div>
        </header>

        {{-- 4 sloupce --}}
        <main class="flex-1 grid grid-cols-4 gap-0 border-t border-slate-800 overflow-hidden min-h-0">
            
            {{-- 1. SLOUPEC: Šablony úkonů --}}
            <section class="border-r border-slate-800 flex flex-col glass min-h-0">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between glass flex-shrink-0">
                    <h2 class="font-semibold text-slate-200">Šablony úkonů</h2>
                    <button type="button" @click="showTemplateForm = true" 
                            class="text-xs px-3 py-1.5 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-950 font-semibold hover:from-emerald-400 hover:to-emerald-500 transition-all shadow-lg shadow-emerald-500/25">
                        + Nová
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-2 min-h-0">
                    <template x-for="template in templates" :key="template.id">
                        <div class="group relative glass border border-slate-700 rounded-lg p-3 hover:border-emerald-500/50 transition-all"
                             draggable="true"
                             @dragstart="onTemplateDragStart(template)"
                             @dragend="onTemplateDragEnd()"
                             @click="addServiceFromTemplate(template)"
                             style="cursor: grab;"
                             :style="draggingTemplate?.id === template.id ? 'opacity: 0.5; cursor: grabbing;' : ''">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500 text-sm">☰</span>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-slate-100" x-text="template.name"></div>
                                    <div class="text-xs text-slate-400 mt-1" x-text="template.note" x-show="template.note"></div>
                                </div>
                            </div>
                            
                            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click.stop="editTemplate(template)" 
                                        class="text-xs px-2 py-1 rounded bg-slate-700 text-slate-200 hover:bg-slate-600">
                                    ✎
                                </button>
                                <button type="button" @click.stop="deleteTemplate(template)" 
                                        class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-200 hover:bg-red-500/30">
                                    ×
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="templates.length === 0">
                        <div class="text-sm text-slate-400 text-center py-8">
                            Zatím žádné šablony.<br>Vytvořte první šablonu úkonu.
                        </div>
                    </template>
                </div>
            </section>

            {{-- 2. SLOUPEC: Aktivní úkony této návštěvy --}}
            <section class="border-r border-slate-800 flex flex-col glass min-h-0"
                     @dragover.prevent
                     @drop="draggingTemplate ? onDropTemplateToServices($event) : onDropToService($event)"
                     :class="(draggingProduct || draggingTemplate) ? 'ring-2 ring-blue-400/50 ring-inset' : ''">
                <div class="p-4 border-b border-slate-800 glass flex-shrink-0">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="font-semibold text-slate-200">Úkony návštěvy</h2>
                        <button type="button" @click="confirmDelete('services')" 
                                x-show="services.length > 0"
                                class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-200 hover:bg-red-500/30">
                            Vymazat vše
                        </button>
                    </div>
                    <div class="text-xs" :class="(draggingProduct || draggingTemplate) ? 'text-blue-300' : 'text-slate-400'">
                        <span x-show="!draggingProduct && !draggingTemplate">Klikněte na šablonu vlevo</span>
                        <span x-show="draggingProduct" class="font-medium">👆 Přetažením sem přidáte k úkonu</span>
                        <span x-show="draggingTemplate" class="font-medium">👆 Přetažením sem přidáte úkon</span>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-3 min-h-0" x-ref="servicesContainer">
                    <template x-for="(service, sIndex) in services" :key="service.tempId">
                        <div class="glass border rounded-xl p-3 space-y-3 transition-all"
                             :class="{
                                 'border-emerald-400 ring-2 ring-emerald-400/30': activeServiceIndex === sIndex,
                                 'border-slate-700 hover:border-slate-600': activeServiceIndex !== sIndex,
                                 'opacity-50': draggingServiceIndex === sIndex
                             }"
                             draggable="true"
                             @dragstart="onServiceDragStart(sIndex)"
                             @dragend="onServiceDragEnd()"
                             @dragover="onServiceDragOver($event, sIndex)"
                             @click="activeServiceIndex = sIndex"
                             style="cursor: grab;"
                             :style="draggingServiceIndex === sIndex ? 'cursor: grabbing;' : ''">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-lg">☰</span>
                                    <div class="text-base font-semibold text-slate-100" x-text="service.title"></div>
                                </div>
                                <button type="button" @click="services.splice(sIndex, 1)" 
                                        class="text-xs px-3 py-1.5 rounded-lg bg-red-500/20 text-red-200 hover:bg-red-500/30">
                                    Odebrat
                                </button>
                            </div>
                            
                            {{-- Materiály pro tento úkon --}}
                            <div class="border-t border-slate-700 pt-2">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-xs uppercase tracking-wider text-slate-400">Použitý materiál (g)</div>
                                    <div class="text-xs text-slate-400">Klikněte na produkt vpravo →</div>
                                </div>
                                
                                <div class="space-y-2">
                                    <template x-for="(prod, pIndex) in service.products" :key="prod.tempId">
                                        <div class="bg-slate-800/70 backdrop-blur-sm rounded-lg p-2 space-y-2 border border-slate-700/50">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-slate-200" x-text="prod.name"></div>
                                                    <div class="text-xs mt-0.5" :class="getRemainingStock(prod.product_id) < 0 ? 'text-red-400' : 'text-slate-500'">
                                                        <span>Zbude: </span>
                                                        <span x-text="getRemainingStock(prod.product_id).toFixed(3)"></span>
                                                        <span> ks</span>
                                                        <span x-show="getRemainingStock(prod.product_id) < 0" class="font-semibold"> ⚠️</span>
                                                    </div>
                                                </div>
                                                <button type="button" @click="service.products.splice(pIndex, 1)" 
                                                        class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-200 hover:bg-red-500/30">
                                                    ×
                                                </button>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" step="1" x-model="prod.used_grams" placeholder="Gramy" 
                                                       :id="'gram-input-' + prod.tempId"
                                                       class="flex-1 bg-slate-800 border border-slate-600 rounded pl-3 pr-8 py-2 text-sm text-right text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none">
                                                <button type="button" @click="prod.used_grams = 15" class="px-2 py-1.5 text-xs rounded bg-slate-700 text-slate-300 hover:bg-emerald-500 hover:text-slate-950">15g</button>
                                                <button type="button" @click="prod.used_grams = 30" class="px-2 py-1.5 text-xs rounded bg-slate-700 text-slate-300 hover:bg-emerald-500 hover:text-slate-950">30g</button>
                                                <button type="button" @click="prod.used_grams = 60" class="px-2 py-1.5 text-xs rounded bg-slate-700 text-slate-300 hover:bg-emerald-500 hover:text-slate-950">60g</button>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <template x-if="service.products.length === 0">
                                        <div class="text-xs text-slate-500 text-center py-2">Bez materiálu</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="services.length === 0">
                        <div class="text-sm text-slate-400 text-center py-8">
                            Přidejte úkon kliknutím<br>na šablonu vlevo.
                        </div>
                    </template>
                </div>
            </section>

            {{-- 3. SLOUPEC: Prodej domů (retail) --}}
            <section class="border-r border-slate-800 flex flex-col glass min-h-0"
                     @dragover.prevent
                     @drop="onDropToRetail($event)"
                     :class="draggingProduct ? 'ring-2 ring-emerald-400/50 ring-inset' : ''">
                <div class="p-4 border-b border-slate-800 glass flex-shrink-0">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="font-semibold text-slate-200">Prodej domů</h2>
                        <button type="button" @click="confirmDelete('retail')" 
                                x-show="retail.length > 0"
                                class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-200 hover:bg-red-500/30">
                            Vymazat vše
                        </button>
                    </div>
                    <div class="text-xs" :class="draggingProduct ? 'text-emerald-300' : 'text-slate-400'">
                        <span x-show="!draggingProduct">Produkty pro klienta (ks)</span>
                        <span x-show="draggingProduct" class="font-medium">👆 Přetažením sem přidáte do prodeje</span>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-2 min-h-0" x-ref="retailContainer">
                    <template x-for="(item, rIndex) in retail" :key="item.tempId">
                        <div class="flex items-center gap-2 glass border border-slate-700 rounded-lg p-3">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-slate-200" x-text="item.name"></div>
                                <div class="text-xs mt-0.5 space-y-0.5">
                                    <div class="text-slate-400">Sklad: <span x-text="Number(item.stock).toFixed(3)"></span> ks</div>
                                    <div :class="getRemainingStock(item.product_id) < 0 ? 'text-red-400' : 'text-slate-500'">
                                        <span>Zbude: </span>
                                        <span x-text="getRemainingStock(item.product_id).toFixed(3)"></span>
                                        <span> ks</span>
                                        <span x-show="getRemainingStock(item.product_id) < 0" class="font-semibold"> ⚠️</span>
                                    </div>
                                </div>
                            </div>
                            <input type="number" step="1" x-model="item.quantity_units" placeholder="ks" 
                                   :id="'retail-input-' + item.tempId"
                                   class="w-24 bg-slate-800 border border-slate-600 rounded pl-2 pr-6 py-1.5 text-sm text-right text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 focus:outline-none">
                            <button type="button" @click="retail.splice(rIndex, 1)" 
                                    class="text-xs px-2 py-1.5 rounded bg-red-500/20 text-red-200 hover:bg-red-500/30">
                                ×
                            </button>
                        </div>
                    </template>
                    
                    <template x-if="retail.length === 0">
                        <div class="text-sm text-slate-400 text-center py-8">
                            Žádný prodej domů.<br>Klikněte na produkt vpravo.
                        </div>
                    </template>
                </div>
            </section>

            {{-- 4. SLOUPEC: Katalog produktů --}}
            <section class="flex flex-col glass min-h-0">
                <div class="p-4 border-b border-slate-800 glass flex-shrink-0 space-y-3">
                    <h2 class="font-semibold text-slate-200">Katalog produktů</h2>
                    
                    {{-- Filtrování podle skupin --}}
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="selectedGroupId = null" 
                                :class="selectedGroupId === null ? 'bg-emerald-500 text-slate-950' : 'bg-slate-700 text-slate-300 hover:bg-slate-600'"
                                class="text-xs px-2.5 py-1 rounded-md font-medium transition-colors">
                            Vše
                        </button>
                        @foreach($productGroups as $group)
                        <button type="button" @click="selectedGroupId = {{ $group->id }}" 
                                :class="selectedGroupId === {{ $group->id }} ? 'bg-emerald-500 text-slate-950' : 'bg-slate-700 text-slate-300 hover:bg-slate-600'"
                                class="text-xs px-2.5 py-1 rounded-md font-medium transition-colors">
                            {{ $group->name }}
                        </button>
                        @endforeach
                    </div>
                    
                    <input type="text" x-model="productSearch" placeholder="Hledat produkt..." 
                           class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    
                    {{-- Bulk akce --}}
                    <div x-show="selectedProductIds.length > 0" class="flex gap-2">
                        <div class="text-xs text-slate-400 flex items-center">
                            <span x-text="selectedProductIds.length"></span> vybráno
                        </div>
                        <button type="button" @click="addSelectedToService()" 
                                class="flex-1 text-xs px-3 py-1.5 rounded bg-blue-500/20 text-blue-200 hover:bg-blue-500/30 font-medium">
                            → K úkonu
                        </button>
                        <button type="button" @click="addSelectedToRetail()" 
                                class="flex-1 text-xs px-3 py-1.5 rounded bg-emerald-500/20 text-emerald-200 hover:bg-emerald-500/30 font-medium">
                            → Prodej domů
                        </button>
                        <button type="button" @click="selectedProductIds = []" 
                                class="text-xs px-2 py-1.5 rounded bg-slate-700 text-slate-300 hover:bg-slate-600">
                            ×
                        </button>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-1.5 min-h-0">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="group glass border rounded-lg p-3 transition-all"
                             :class="selectedProductIds.includes(product.id) ? 'border-emerald-400 bg-emerald-500/10' : 'border-slate-700 hover:border-emerald-500/50'"
                             draggable="true"
                             @dragstart="onDragStart(product)"
                             @dragend="onDragEnd()"
                             @click="toggleProductSelection(product.id)"
                             style="cursor: grab;"
                             :style="draggingProduct?.id === product.id ? 'opacity: 0.5; cursor: grabbing;' : ''">
                            <div class="flex items-start gap-2">
                                <input type="checkbox" :checked="selectedProductIds.includes(product.id)" 
                                       class="mt-0.5 rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0"
                                       @click.stop="toggleProductSelection(product.id)">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-slate-100 truncate" x-text="product.name"></div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        <span x-text="product.sku || 'bez SKU'"></span> • 
                                        <span x-text="Number(product.stock_units).toFixed(3)"></span> ks
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="filteredProducts.length === 0">
                        <div class="text-sm text-slate-400 text-center py-8">
                            Žádné produkty.
                        </div>
                    </template>
                </div>
            </section>
        </main>

        {{-- Modal pro přidání/úpravu šablony --}}
        <template x-if="showTemplateForm">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click.self="showTemplateForm = false">
                <div class="glass border border-slate-700 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Šablona úkonu</div>
                            <div class="text-lg font-semibold text-white" x-text="editingTemplate ? 'Upravit šablonu' : 'Nová šablona'"></div>
                        </div>
                        <button type="button" @click="showTemplateForm = false" class="text-slate-400 hover:text-white">×</button>
                    </div>
                    
                    <div class="space-y-3">
                        <label class="block text-sm text-slate-300 space-y-1">
                            <span>Název šablony</span>
                            <input type="text" x-model="templateForm.name" placeholder="např. Melír, Střih, Barvení..." 
                                   class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </label>
                        <label class="block text-sm text-slate-300 space-y-1">
                            <span>Poznámka (volitelné)</span>
                            <textarea x-model="templateForm.note" rows="2" placeholder="Krátký popis šablony" 
                                      class="w-full bg-slate-900/60 border border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none"></textarea>
                        </label>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showTemplateForm = false" 
                                class="px-4 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">
                            Zrušit
                        </button>
                        <button type="button" @click="saveTemplate()" 
                                class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 text-slate-950 font-semibold hover:from-emerald-400 hover:to-emerald-500 transition-all shadow-lg shadow-emerald-500/25">
                            Uložit
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Confirmation modal před uložením návštěvy --}}
        <template x-if="confirmOpen">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-400">Souhrn návštěvy</div>
                            <div class="text-lg font-semibold text-white">Před uložením</div>
                        </div>
                        <button type="button" @click="confirmOpen = false" class="text-slate-400 hover:text-white">×</button>
                    </div>
                    
                    <div class="space-y-3 max-h-[50vh] overflow-y-auto">
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-slate-200">Úkony — materiál k odepsání</div>
                            <template x-if="summary.services.length === 0">
                                <div class="text-sm text-slate-400">Žádný materiál.</div>
                            </template>
                            <template x-for="line in summary.services" :key="line.id">
                                <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2">
                                    <div class="text-sm text-slate-100" x-text="line.name"></div>
                                    <div class="text-xs text-slate-300" x-text="line.grams !== null ? Number(line.grams).toFixed(2) + ' g' : 'bez materiálu'"></div>
                                </div>
                            </template>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="text-sm font-semibold text-slate-200">Prodej domů</div>
                            <template x-if="summary.retail.length === 0">
                                <div class="text-sm text-slate-400">Žádný prodej.</div>
                            </template>
                            <template x-for="line in summary.retail" :key="line.id">
                                <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2">
                                    <div class="text-sm text-slate-100" x-text="line.name"></div>
                                    <div class="text-xs text-slate-300" x-text="Number(line.units).toFixed(3) + ' ks'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center gap-2 pt-2" x-show="validationErrors.length === 0">
                        <button type="button" @click="printReceipt()" 
                                class="px-4 py-2 rounded-lg bg-sky-500/20 border border-sky-500/40 text-sky-300 hover:bg-sky-500/30">
                            🖨️ Vytisknout účtenku
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="confirmOpen = false" 
                                    class="px-4 py-2 rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700">
                                Zrušit
                            </button>
                            <button type="button" @click="submitVisit()" 
                                    class="px-4 py-2 rounded-lg bg-emerald-500 text-slate-950 font-semibold hover:bg-emerald-400">
                                Potvrdit a uložit
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-xs text-amber-200" x-show="validationErrors.length">
                        <div class="bg-amber-500/10 border border-amber-500/40 rounded-lg px-3 py-2 space-y-1">
                            <div class="font-semibold">Oprav prosím:</div>
                            <ul class="list-disc list-inside space-y-0.5">
                                <template x-for="(err, idx) in validationErrors" :key="idx">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Print Preview Modal --}}
        <template x-if="showPrintPreview">
            <div class="fixed inset-0 bg-slate-950/90 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="showPrintPreview = false">
                <div class="bg-white text-slate-900 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col" @click.stop>
                    {{-- Header --}}
                    <div class="bg-slate-100 px-6 py-4 border-b border-slate-300 flex items-center justify-between print:hidden">
                        <h3 class="text-lg font-semibold">Náhled návštěvy</h3>
                        <div class="flex gap-2">
                            <button type="button" @click="printVisit()" 
                                    class="px-4 py-2 rounded-lg bg-emerald-500 text-white font-medium hover:bg-emerald-600">
                                🖨️ Tisknout / PDF
                            </button>
                            <button type="button" @click="showPrintPreview = false" 
                                    class="px-3 py-2 rounded-lg bg-slate-300 text-slate-700 hover:bg-slate-400">
                                ×
                            </button>
                        </div>
                    </div>
                    
                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto p-8 print:p-0" id="printContent">
                        <div class="max-w-3xl mx-auto space-y-6">
                            {{-- Hlavička --}}
                            <div class="border-b-2 border-slate-300 pb-4">
                                <h1 class="text-3xl font-bold text-slate-900">Návštěva</h1>
                                <div class="mt-2 text-lg font-semibold text-slate-700">{{ $client->name }}</div>
                            </div>
                            
                            {{-- Základní informace --}}
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-semibold text-slate-600">Datum a čas:</span>
                                    <span class="text-slate-900" x-text="new Date(occurredAt).toLocaleString('cs-CZ')"></span>
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-600">Celková cena:</span>
                                    <span class="text-slate-900 font-bold" x-text="(totalPrice || '0') + ' Kč'"></span>
                                </div>
                                <div class="col-span-2" x-show="note">
                                    <span class="font-semibold text-slate-600">Poznámka:</span>
                                    <span class="text-slate-900" x-text="note"></span>
                                </div>
                            </div>
                            
                            {{-- Úkony --}}
                            <div x-show="services.length > 0">
                                <h2 class="text-xl font-bold text-slate-900 mb-3 border-b border-slate-300 pb-2">Úkony</h2>
                                <div class="space-y-4">
                                    <template x-for="(service, idx) in services" :key="service.tempId">
                                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                            <div class="font-semibold text-slate-900 mb-1">
                                                <span x-text="(idx + 1) + '. '"></span>
                                                <span x-text="service.title"></span>
                                            </div>
                                            <div class="text-sm text-slate-600 mb-2" x-show="service.note" x-text="service.note"></div>
                                            
                                            <div class="space-y-1 ml-4" x-show="service.products.length > 0">
                                                <div class="text-xs font-semibold text-slate-600 uppercase">Použitý materiál:</div>
                                                <template x-for="prod in service.products" :key="prod.tempId">
                                                    <div class="text-sm text-slate-700 flex justify-between">
                                                        <span x-text="prod.name"></span>
                                                        <span class="font-medium" x-text="Number(prod.used_grams || 0).toFixed(1) + ' g'"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            {{-- Prodej domů --}}
                            <div x-show="retail.length > 0">
                                <h2 class="text-xl font-bold text-slate-900 mb-3 border-b border-slate-300 pb-2">Prodej domů</h2>
                                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-300">
                                                <th class="text-left py-2 text-slate-600 font-semibold">Produkt</th>
                                                <th class="text-right py-2 text-slate-600 font-semibold">Množství</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="item in retail" :key="item.tempId">
                                                <tr class="border-b border-slate-200 last:border-0">
                                                    <td class="py-2 text-slate-900" x-text="item.name"></td>
                                                    <td class="py-2 text-right font-medium text-slate-900" x-text="Number(item.quantity_units || 0).toFixed(2) + ' ks'"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            {{-- Prázdný stav --}}
                            <div x-show="services.length === 0 && retail.length === 0" class="text-center py-8 text-slate-500">
                                Žádné úkony ani prodej.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Alert Modal --}}
        <template x-if="alertModal.open">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click="alertModal.open = false">
                <div class="glass border rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4" 
                     :class="{
                         'border-red-500/50': alertModal.type === 'error',
                         'border-emerald-500/50': alertModal.type === 'success',
                         'border-slate-700': alertModal.type === 'info'
                     }"
                     @click.stop>
                    <div class="flex items-start gap-3">
                        <div class="text-2xl" x-show="alertModal.type === 'error'">⚠️</div>
                        <div class="text-2xl" x-show="alertModal.type === 'success'">✅</div>
                        <div class="text-2xl" x-show="alertModal.type === 'info'">ℹ️</div>
                        <div class="flex-1">
                            <div class="text-base font-semibold text-slate-100" x-text="alertModal.message"></div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" @click="alertModal.open = false" 
                                class="px-4 py-2 rounded-lg font-medium transition-colors"
                                :class="{
                                    'bg-red-500/20 text-red-200 hover:bg-red-500/30': alertModal.type === 'error',
                                    'bg-emerald-500/20 text-emerald-200 hover:bg-emerald-500/30': alertModal.type === 'success',
                                    'bg-slate-700 text-slate-200 hover:bg-slate-600': alertModal.type === 'info'
                                }">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Delete Confirmation Modal --}}
        <template x-if="deleteConfirm.open">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50" @click="deleteConfirm.open = false">
                <div class="glass border border-red-500/50 rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4" @click.stop>
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-slate-100">Potvrdit smazání</h3>
                        <p class="text-slate-300" x-text="deleteConfirm.message"></p>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="deleteConfirm.open = false" 
                                class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 font-medium">
                            Zrušit
                        </button>
                        <button type="button" @click="executeDelete()" 
                                class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-medium">
                            Smazat
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Import from History Modal --}}
        <template x-if="showImportModal">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="showImportModal = false">
                <div class="glass border border-slate-700 rounded-xl shadow-2xl w-full max-w-2xl max-h-[80vh] flex flex-col" @click.stop>
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-100">Import z minulých návštěv</h3>
                        <button type="button" @click="showImportModal = false" 
                                class="text-slate-400 hover:text-slate-200 text-2xl leading-none">
                            ×
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6">
                        <div x-show="!previousVisits || previousVisits.length === 0" class="text-center py-8 text-slate-400">
                            Žádné předchozí návštěvy
                        </div>
                        <div x-show="previousVisits && previousVisits.length > 0" class="space-y-3">
                            <template x-for="visit in previousVisits" :key="visit.id">
                                <button type="button" @click="importFromVisit(visit.id)" 
                                        class="w-full text-left bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg p-4 transition-colors">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="font-semibold text-slate-100" x-text="new Date(visit.occurred_at).toLocaleDateString('cs-CZ', {day: 'numeric', month: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})"></div>
                                        <div class="text-emerald-400 font-semibold" x-text="visit.total_price ? visit.total_price + ' Kč' : ''"></div>
                                    </div>
                                    <div x-show="visit.note" class="text-sm text-slate-400 mb-2" x-text="visit.note"></div>
                                    <div class="flex gap-4 text-xs text-slate-500">
                                        <div x-show="visit.services_count > 0">
                                            <span x-text="visit.services_count"></span> úkon<span x-text="visit.services_count > 1 ? 'y' : ''"></span>
                                        </div>
                                        <div x-show="visit.retail_items_count > 0">
                                            <span x-text="visit.retail_items_count"></span> prodej
                                        </div>
                                        <div class="ml-auto" x-text="visit.status === 'closed' ? '✓ Uzavřeno' : 'Otevřeno'"></div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
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
                <div :class="'toast-enter'" 
                     class="glass border rounded-lg shadow-2xl p-4 flex items-start gap-3"
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
                    <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-200">×</button>
                </div>
            </template>
        </div>
        
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
                    
                    <div class="border-t border-gray-300 pt-3 text-right space-y-1">
                        <template x-if="receiptData.retailPrice > 0">
                            <div class="text-base text-gray-700">
                                Celkem za produkty: <span x-text="Number(receiptData.retailPrice).toFixed(2)"></span> Kč
                            </div>
                        </template>
                        <div class="text-base text-gray-700">
                            Celkem za návštěvu: <span x-text="Number(receiptData.totalPrice).toFixed(2)"></span> Kč
                        </div>
                        <div class="text-xl font-bold text-gray-900 border-t border-gray-400 pt-2 mt-2">
                            CELKEM ZA OBOJÍ: <span x-text="(Number(receiptData.totalPrice) + Number(receiptData.retailPrice || 0)).toFixed(2)"></span> Kč
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
        function visitEditor() {
            return {
                // Data
                templates: @json($serviceTemplates),
                products: @json($products),
                services: [],
                retail: [],
                
                // Form fields
                occurredAt: '{{ now()->format('Y-m-d\TH:i') }}',
                totalPrice: '',
                retailPrice: '',
                note: '',
                closeNow: false,
                
                // Init function to load duplicated visit
                init() {
                    @if($duplicatedVisit ?? false)
                    // Load duplicated visit data
                    const duplicatedVisit = @json($duplicatedVisit);
                    console.log('Loading duplicated visit:', duplicatedVisit);
                    
                    // Set basic info
                    this.totalPrice = duplicatedVisit.total_price || '';
                    this.note = duplicatedVisit.note || '';
                    
                    // Load services with products
                    if (duplicatedVisit.services) {
                        duplicatedVisit.services.forEach(service => {
                            const newService = {
                                tempId: Date.now() + Math.random(),
                                title: service.title,
                                note: service.note || '',
                                products: service.products ? service.products.map(p => ({
                                    tempId: Date.now() + Math.random(),
                                    product_id: p.product_id,
                                    name: p.product.name,
                                    used_grams: p.used_grams
                                })) : []
                            };
                            this.services.push(newService);
                        });
                    }
                    
                    this.showAlert('Návštěva byla duplikována - můžete ji upravit', 'success');
                    @endif
                },
                
                // UI state
                showTemplateForm: false,
                editingTemplate: null,
                templateForm: { name: '', note: '' },
                productSearch: '',
                selectedGroupId: null,
                selectedProductIds: [],
                draggingProduct: null,
                draggingServiceIndex: null,
                draggingTemplate: null,
                productMenuOpen: null,
                confirmOpen: false,
                validationErrors: [],
                activeServiceIndex: null,
                showPrintPreview: false,
                showImportModal: false,
                previousVisits: @json($previousVisits),
                deleteConfirm: {
                    open: false,
                    type: '', // 'services' or 'retail'
                    message: ''
                },
                
                // Receipt modal
                receiptData: {
                    show: false,
                    clientName: '',
                    date: '',
                    time: '',
                    services: [],
                    retail: [],
                    totalPrice: 0,
                    retailPrice: 0
                },
                
                // Loading state
                loading: false,
                loadingMessage: 'Načítání...',
                
                // Toast notifications
                toasts: [],
                toastId: 0,
                
                // Alert modal state
                alertModal: {
                    open: false,
                    message: '',
                    type: 'info' // 'info', 'error', 'success'
                },
                
                showAlert(message, type = 'info') {
                    this.alertModal = { open: true, message, type };
                },
                
                // Toast notification helper
                showToast(message, type = 'info', duration = 3000) {
                    const id = ++this.toastId;
                    this.toasts.push({ id, message, type });
                    
                    setTimeout(() => {
                        this.removeToast(id);
                    }, duration);
                },
                
                removeToast(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                },
                
                // Loading helper
                setLoading(isLoading, message = 'Načítání...') {
                    this.loading = isLoading;
                    this.loadingMessage = message;
                },
                
                // Multi-select functions
                toggleProductSelection(productId) {
                    const idx = this.selectedProductIds.indexOf(productId);
                    if (idx === -1) {
                        this.selectedProductIds.push(productId);
                    } else {
                        this.selectedProductIds.splice(idx, 1);
                    }
                },
                
                addSelectedToService() {
                    if (this.selectedProductIds.length === 0) {
                        this.showAlert('Nejprve označte produkty', 'error');
                        return;
                    }
                    
                    this.selectedProductIds.forEach(productId => {
                        const product = this.products.find(p => p.id === productId);
                        if (product) {
                            this.addProductToActiveService(product);
                        }
                    });
                    
                    this.selectedProductIds = [];
                },
                
                addSelectedToRetail() {
                    if (this.selectedProductIds.length === 0) {
                        this.showAlert('Nejprve označte produkty', 'error');
                        return;
                    }
                    
                    this.selectedProductIds.forEach(productId => {
                        const product = this.products.find(p => p.id === productId);
                        if (product && !this.retail.find(r => r.product_id === productId)) {
                            this.addProductToRetail(product);
                        }
                    });
                    
                    this.selectedProductIds = [];
                },
                
                // Drag and drop functions
                onDragStart(product) {
                    this.draggingProduct = product;
                },
                
                onDragEnd() {
                    this.draggingProduct = null;
                },
                
                onDropToService(event) {
                    event.preventDefault();
                    if (this.draggingProduct) {
                        this.addProductToActiveService(this.draggingProduct);
                        this.draggingProduct = null;
                    }
                },
                
                onDropToRetail(event) {
                    event.preventDefault();
                    if (this.draggingProduct) {
                        this.addProductToRetail(this.draggingProduct);
                        this.draggingProduct = null;
                    }
                },
                
                // Service drag and drop
                onServiceDragStart(index) {
                    this.draggingServiceIndex = index;
                },
                
                onServiceDragEnd() {
                    this.draggingServiceIndex = null;
                },
                
                onServiceDragOver(event, targetIndex) {
                    event.preventDefault();
                    if (this.draggingServiceIndex !== null && this.draggingServiceIndex !== targetIndex) {
                        // Swap services
                        const draggedService = this.services[this.draggingServiceIndex];
                        this.services.splice(this.draggingServiceIndex, 1);
                        this.services.splice(targetIndex, 0, draggedService);
                        this.draggingServiceIndex = targetIndex;
                        
                        // Update active index if needed
                        if (this.activeServiceIndex === this.draggingServiceIndex) {
                            this.activeServiceIndex = targetIndex;
                        }
                    }
                },
                
                // Template drag and drop
                onTemplateDragStart(template) {
                    this.draggingTemplate = template;
                },
                
                onTemplateDragEnd() {
                    this.draggingTemplate = null;
                },
                
                onDropTemplateToServices(event) {
                    event.preventDefault();
                    if (this.draggingTemplate) {
                        this.addServiceFromTemplate(this.draggingTemplate);
                        this.draggingTemplate = null;
                    }
                },
                
                // Computed
                get filteredProducts() {
                    let filtered = this.products;
                    
                    // Filtr podle skupiny
                    if (this.selectedGroupId !== null) {
                        filtered = filtered.filter(p => p.product_group_id === this.selectedGroupId);
                    }
                    
                    // Filtr podle vyhledávání
                    if (this.productSearch) {
                        const search = this.productSearch.toLowerCase();
                        filtered = filtered.filter(p => 
                            p.name.toLowerCase().includes(search) || 
                            (p.sku && p.sku.toLowerCase().includes(search))
                        );
                    }
                    
                    return filtered;
                },
                
                get stats() {
                    let totalGrams = 0;
                    let totalRetailUnits = 0;
                    let productsWithLowStock = [];
                    
                    // Count grams from services
                    this.services.forEach(service => {
                        service.products.forEach(prod => {
                            if (prod.used_grams) {
                                totalGrams += parseFloat(prod.used_grams);
                                const remaining = this.getRemainingStock(prod.product_id);
                                if (remaining < 0 && !productsWithLowStock.find(p => p.id === prod.product_id)) {
                                    productsWithLowStock.push({ id: prod.product_id, name: prod.name });
                                }
                            }
                        });
                    });
                    
                    // Count retail units
                    this.retail.forEach(item => {
                        if (item.quantity_units) {
                            totalRetailUnits += parseFloat(item.quantity_units);
                            const remaining = this.getRemainingStock(item.product_id);
                            if (remaining < 0 && !productsWithLowStock.find(p => p.id === item.product_id)) {
                                productsWithLowStock.push({ id: item.product_id, name: item.name });
                            }
                        }
                    });
                    
                    return {
                        servicesCount: this.services.length,
                        totalGrams: totalGrams,
                        totalRetailUnits: totalRetailUnits,
                        productsWithLowStock: productsWithLowStock
                    };
                },
                
                get summary() {
                    const serviceLines = [];
                    const retailLines = [];
                    
                    // Zobraz úkony (i bez produktů)
                    this.services.forEach(service => {
                        if (service.products.length === 0) {
                            // Úkon bez produktů - zobraz název úkonu
                            serviceLines.push({
                                id: service.tempId,
                                name: service.title,
                                grams: null  // žádný materiál
                            });
                        } else {
                            // Úkon s produkty - zobraz produkty
                            service.products.forEach(prod => {
                                serviceLines.push({
                                    id: prod.tempId,
                                    name: prod.name,
                                    grams: prod.used_grams || 0
                                });
                            });
                        }
                    });
                    
                    this.retail.forEach(item => {
                        retailLines.push({
                            id: item.tempId,
                            name: item.name,
                            units: item.quantity_units || 0
                        });
                    });
                    
                    return { services: serviceLines, retail: retailLines };
                },
                
                // Template management
                addServiceFromTemplate(template) {
                    this.services.push({
                        tempId: Date.now() + Math.random(),
                        title: template.name,
                        note: template.note || '',
                        products: []
                    });
                    
                    // Nastav nový úkon jako aktivní
                    this.activeServiceIndex = this.services.length - 1;
                    
                    // Scroll dolů v kontejneru úkonů
                    this.$nextTick(() => {
                        if (this.$refs.servicesContainer) {
                            this.$refs.servicesContainer.scrollTop = this.$refs.servicesContainer.scrollHeight;
                        }
                    });
                },
                
                addEmptyService() {
                    this.services.push({
                        tempId: Date.now() + Math.random(),
                        title: '',
                        note: '',
                        products: []
                    });
                },
                
                editTemplate(template) {
                    this.editingTemplate = template;
                    this.templateForm = { name: template.name, note: template.note || '' };
                    this.showTemplateForm = true;
                },
                
                async saveTemplate() {
                    if (!this.templateForm.name.trim()) {
                        this.showToast('Název šablony je povinný', 'error');
                        return;
                    }
                    
                    const url = this.editingTemplate 
                        ? `/service-templates/${this.editingTemplate.id}`
                        : '/service-templates';
                    const method = this.editingTemplate ? 'PUT' : 'POST';
                    
                    this.setLoading(true, 'Ukládání šablony...');
                    
                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.templateForm)
                        });
                        
                        if (response.ok) {
                            const saved = await response.json();
                            if (this.editingTemplate) {
                                const idx = this.templates.findIndex(t => t.id === this.editingTemplate.id);
                                if (idx !== -1) this.templates[idx] = saved;
                            } else {
                                this.templates.push(saved);
                            }
                            this.showTemplateForm = false;
                            this.editingTemplate = null;
                            this.templateForm = { name: '', note: '' };
                            this.showToast('Šablona byla uložena', 'success');
                        } else {
                            const errorData = await response.text();
                            console.error('Error response:', errorData);
                            this.showToast('Chyba při ukládání šablony', 'error');
                        }
                    } catch (err) {
                        console.error('Fetch error:', err);
                        this.showToast('Chyba při ukládání šablony', 'error');
                    } finally {
                        this.setLoading(false);
                    }
                },
                
                async deleteTemplate(template) {
                    if (!confirm(`Smazat šablonu "${template.name}"?`)) return;
                    
                    this.setLoading(true, 'Mazání šablony...');
                    
                    try {
                        const response = await fetch(`/service-templates/${template.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        
                        if (response.ok) {
                            this.templates = this.templates.filter(t => t.id !== template.id);
                            this.showToast('Šablona byla smazána', 'success');
                        } else {
                            this.showToast('Chyba při mazání šablony', 'error');
                        }
                    } catch (err) {
                        this.showToast('Chyba při mazání šablony', 'error');
                    } finally {
                        this.setLoading(false);
                    }
                },
                
                // Product management
                addProductToActiveService(product) {
                    if (this.services.length === 0) {
                        this.showAlert('Nejprve přidejte úkon', 'error');
                        return;
                    }
                    
                    // Pokud není žádný označený, vezmi poslední
                    if (this.activeServiceIndex === null) {
                        this.activeServiceIndex = this.services.length - 1;
                    }
                    
                    // Přidá k označenému úkonu
                    const activeService = this.services[this.activeServiceIndex];
                    const tempId = Date.now() + Math.random();
                    activeService.products.push({
                        tempId: tempId,
                        product_id: product.id,
                        name: product.name,
                        used_grams: ''
                    });
                    
                    this.productMenuOpen = null;
                    
                    // Scroll dolů v kontejneru úkonů
                    this.$nextTick(() => {
                        if (this.$refs.servicesContainer) {
                            this.$refs.servicesContainer.scrollTop = this.$refs.servicesContainer.scrollHeight;
                        }
                        
                        // Auto-focus na input po přidání
                        const input = document.getElementById('gram-input-' + tempId);
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    });
                },
                
                addProductToRetail(product) {
                    // Check if already added
                    if (this.retail.find(r => r.product_id === product.id)) {
                        this.showAlert('Tento produkt je již v prodeji domů', 'error');
                        return;
                    }
                    
                    const tempId = Date.now() + Math.random();
                    this.retail.push({
                        tempId: tempId,
                        product_id: product.id,
                        name: product.name,
                        stock: product.stock_units,
                        quantity_units: ''
                    });
                    
                    this.productMenuOpen = null;
                    
                    // Scroll dolů a focus na input
                    this.$nextTick(() => {
                        if (this.$refs.retailContainer) {
                            this.$refs.retailContainer.scrollTop = this.$refs.retailContainer.scrollHeight;
                        }
                        
                        const input = document.getElementById('retail-input-' + tempId);
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    });
                },
                
                // Calculate remaining stock after deduction
                getRemainingStock(productId) {
                    const product = this.products.find(p => p.id === productId);
                    if (!product) return 0;
                    
                    let totalGrams = 0;
                    let totalUnits = 0;
                    
                    // Sum all grams from services
                    this.services.forEach(service => {
                        service.products.forEach(prod => {
                            if (prod.product_id === productId && prod.used_grams) {
                                totalGrams += parseFloat(prod.used_grams);
                            }
                        });
                    });
                    
                    // Sum all units from retail
                    this.retail.forEach(item => {
                        if (item.product_id === productId && item.quantity_units) {
                            totalUnits += parseFloat(item.quantity_units);
                        }
                    });
                    
                    // Convert grams to units (1000g = 1 unit)
                    const gramsAsUnits = totalGrams / 1000;
                    const totalDeducted = gramsAsUnits + totalUnits;
                    
                    return product.stock_units - totalDeducted;
                },
                
                // Delete confirmation
                confirmDelete(type) {
                    const count = type === 'services' ? this.services.length : this.retail.length;
                    const itemType = type === 'services' ? 'úkonů' : 'produktů';
                    this.deleteConfirm = {
                        open: true,
                        type: type,
                        message: `Opravdu chcete smazat všech ${count} ${itemType}?`
                    };
                },
                
                executeDelete() {
                    if (this.deleteConfirm.type === 'services') {
                        this.services = [];
                        this.activeServiceIndex = null;
                    } else if (this.deleteConfirm.type === 'retail') {
                        this.retail = [];
                    }
                    this.deleteConfirm.open = false;
                },
                
                // Import from previous visit
                async importFromVisit(visitId) {
                    this.setLoading(true, 'Načítání návštěvy...');
                    
                    try {
                        console.log('Importing visit:', visitId);
                        const response = await fetch(`/visits/${visitId}`);
                        
                        if (!response.ok) {
                            console.error('Response not OK:', response.status);
                            throw new Error('Načítání selhalo');
                        }
                        
                        const data = await response.json();
                        console.log('Loaded data:', data);
                        
                        let importedServices = 0;
                        let importedRetail = 0;
                        
                        // Import services
                        if (data.services && Array.isArray(data.services)) {
                            data.services.forEach(service => {
                                const newService = {
                                    tempId: Date.now() + Math.random(),
                                    title: service.title,
                                    note: service.note || '',
                                    products: Array.isArray(service.products) ? service.products.map(p => ({
                                        tempId: Date.now() + Math.random(),
                                        product_id: p.product_id,
                                        name: p.name,
                                        used_grams: p.used_grams
                                    })) : []
                                };
                                this.services.push(newService);
                                importedServices++;
                            });
                        }
                        
                        // Import retail
                        if (data.retail && Array.isArray(data.retail)) {
                            data.retail.forEach(item => {
                                this.retail.push({
                                    tempId: Date.now() + Math.random(),
                                    product_id: item.product_id,
                                    name: item.name,
                                    quantity_units: item.quantity_units,
                                    unit_price: item.unit_price
                                });
                                importedRetail++;
                            });
                        }
                        
                        this.showImportModal = false;
                        
                        console.log(`Imported ${importedServices} services and ${importedRetail} retail items`);
                        
                        if (importedServices > 0 || importedRetail > 0) {
                            this.showToast(`Naimportováno: ${importedServices} úkonů, ${importedRetail} produktů`, 'success');
                        } else {
                            this.showToast('Návštěva neobsahovala žádná data', 'info');
                        }
                        
                        // Scroll to services
                        this.$nextTick(() => {
                            const container = this.$refs.servicesContainer;
                            if (container) container.scrollTop = 0;
                        });
                    } catch (error) {
                        console.error('Import error:', error);
                        this.showToast('Chyba při načítání návštěvy', 'error');
                    } finally {
                        this.setLoading(false);
                    }
                },
                
                // Show print preview modal
                showPrintModal() {
                    this.showPrintPreview = true;
                },
                
                // Print/Export to PDF
                printVisit() {
                    window.print();
                },
                
                // Save visit
                saveVisit() {
                    this.validationErrors = [];
                    let hasPriceError = false;
                    
                    // Kontrola ceny
                    if (!this.totalPrice || this.totalPrice <= 0) {
                        this.validationErrors.push('Zadejte celkovou cenu návštěvy');
                        hasPriceError = true;
                    }
                    
                    if (this.services.length === 0 && this.retail.length === 0) {
                        this.validationErrors.push('Přidejte alespoň jeden úkon nebo prodej');
                    }
                    
                    this.services.forEach((s, idx) => {
                        if (!s.title.trim()) {
                            this.validationErrors.push(`Úkon ${idx + 1}: chybí název`);
                        }
                    });
                    
                    // Kontrola skladu při uzavření
                    if (this.closeNow) {
                        const stockRequirements = {};
                        
                        // Sečti materiál z úkonů
                        this.services.forEach(service => {
                            service.products.forEach(prod => {
                                if (prod.used_grams > 0 && prod.product_id) {
                                    const product = this.products.find(p => p.id === prod.product_id);
                                    if (product && product.package_size_grams > 0) {
                                        const deductedUnits = prod.used_grams / product.package_size_grams;
                                        stockRequirements[prod.product_id] = (stockRequirements[prod.product_id] || 0) + deductedUnits;
                                    }
                                }
                            });
                        });
                        
                        // Sečti prodej domů
                        this.retail.forEach(item => {
                            if (item.quantity_units > 0 && item.product_id) {
                                stockRequirements[item.product_id] = (stockRequirements[item.product_id] || 0) + parseFloat(item.quantity_units);
                            }
                        });
                        
                        // Zkontroluj dostupnost skladu
                        Object.keys(stockRequirements).forEach(productId => {
                            const product = this.products.find(p => p.id == productId);
                            if (product) {
                                const needed = stockRequirements[productId];
                                const after = product.stock_units - needed;
                                if (after < 0) {
                                    this.validationErrors.push(`${product.name}: nedostatek skladu (chybí ${Math.abs(after).toFixed(3)} ks)`);
                                }
                            }
                        });
                    }
                    
                    // Pokud je chyba s cenou, neotevírej modal a nastav focus na pole ceny
                    if (hasPriceError) {
                        requestAnimationFrame(() => {
                            if (this.$refs.totalPriceInput) {
                                this.$refs.totalPriceInput.focus();
                                this.$refs.totalPriceInput.select();
                            }
                        });
                        return;
                    }
                    
                    this.confirmOpen = true;
                },
                
                printReceipt() {
                    const client = '{{ $client->name }}';
                    const date = new Date(this.occurredAt).toLocaleDateString('cs-CZ');
                    const time = new Date(this.occurredAt).toLocaleTimeString('cs-CZ', {hour: '2-digit', minute: '2-digit'});
                    
                    // Připrav data pro služby
                    const servicesData = this.services.map(service => ({
                        title: service.title
                    }));
                    
                    // Připrav data pro prodej domů
                    const retailData = this.retail.map(item => {
                        const product = this.products.find(p => p.id === item.product_id);
                        return {
                            name: product ? product.name : 'Neznámý produkt',
                            quantity_units: item.quantity_units || 0
                        };
                    });
                    
                    // Nastav data pro modal
                    this.receiptData.show = true;
                    this.receiptData.clientName = client;
                    this.receiptData.date = date;
                    this.receiptData.time = time;
                    this.receiptData.services = servicesData;
                    this.receiptData.retail = retailData;
                    this.receiptData.totalPrice = this.totalPrice || 0;
                    this.receiptData.retailPrice = this.retailPrice || 0;
                },
                
                async submitVisit() {
                    if (this.validationErrors.length > 0) return;
                    
                    this.setLoading(true, 'Ukládání návštěvy...');
                    
                    const formData = {
                        client_id: {{ $client->id }},
                        occurred_at: this.occurredAt,
                        total_price: this.totalPrice || 0,
                        retail_price: this.retailPrice || null,
                        note: this.note,
                        close_now: this.closeNow ? 1 : 0,
                        services: this.services.map(s => ({
                            title: s.title,
                            note: s.note,
                            products: s.products.map(p => ({
                                product_id: p.product_id,
                                used_grams: p.used_grams || 0
                            }))
                        })),
                        retail: this.retail.map(r => ({
                            product_id: r.product_id,
                            quantity_units: r.quantity_units || 0
                        }))
                    };
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('visits.store') }}';
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);
                    
                    for (const [key, value] of Object.entries(formData)) {
                        if (key === 'services' || key === 'retail') {
                            value.forEach((item, idx) => {
                                for (const [k, v] of Object.entries(item)) {
                                    if (k === 'products') {
                                        v.forEach((p, pIdx) => {
                                            for (const [pk, pv] of Object.entries(p)) {
                                                const input = document.createElement('input');
                                                input.type = 'hidden';
                                                input.name = `${key}[${idx}][${k}][${pIdx}][${pk}]`;
                                                input.value = pv;
                                                form.appendChild(input);
                                            }
                                        });
                                    } else {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = `${key}[${idx}][${k}]`;
                                        input.value = v;
                                        form.appendChild(input);
                                    }
                                }
                            });
                        } else {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }
                    }
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</body>
</html>
