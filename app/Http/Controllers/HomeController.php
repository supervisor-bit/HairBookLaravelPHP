<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Visit;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Statistiky
        $totalClients = Client::count();
        $totalVisits = Visit::count();
        $openVisits = Visit::where('closed_at', null)->count();
        $closedVisits = Visit::whereNotNull('closed_at')->count();
        
        // Tržby
        $totalRevenue = Visit::whereNotNull('closed_at')->sum('total_price');
        $todayRevenue = Visit::whereNotNull('closed_at')
            ->whereDate('closed_at', today())
            ->sum('total_price');
        $monthRevenue = Visit::whereNotNull('closed_at')
            ->whereYear('closed_at', now()->year)
            ->whereMonth('closed_at', now()->month)
            ->sum('total_price');
        
        // Produkty s nízkým stavem (méně než 5)
        $lowStockProducts = Product::where('stock', '<', 5)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();
        
        // Poslední návštěvy
        $recentVisits = Visit::with('client')
            ->orderBy('occurred_at', 'desc')
            ->limit(5)
            ->get();
        
        // Top klienti podle počtu návštěv
        $topClients = Client::withCount('visits')
            ->orderBy('visits_count', 'desc')
            ->limit(5)
            ->get();
        
        return view('home', compact(
            'totalClients',
            'totalVisits',
            'openVisits',
            'closedVisits',
            'totalRevenue',
            'todayRevenue',
            'monthRevenue',
            'lowStockProducts',
            'recentVisits',
            'topClients'
        ));
    }
}
