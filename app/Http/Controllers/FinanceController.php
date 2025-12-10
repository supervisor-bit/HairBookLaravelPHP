<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // today, week, month, year, all
        
        $query = Visit::with('client')
            ->whereNotNull('closed_at')
            ->orderBy('occurred_at', 'desc');
        
        // Filtrování podle období
        $now = Carbon::now();
        switch ($period) {
            case 'today':
                $query->whereDate('occurred_at', $now->toDateString());
                break;
            case 'week':
                $query->whereBetween('occurred_at', [$now->startOfWeek(), $now->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('occurred_at', $now->month)
                      ->whereYear('occurred_at', $now->year);
                break;
            case 'year':
                $query->whereYear('occurred_at', $now->year);
                break;
            // 'all' - bez filtru
        }
        
        $visits = $query->get();
        $totalRevenue = $visits->sum('total_price');
        $totalRetail = $visits->sum('retail_price');
        $totalCombined = $totalRevenue + $totalRetail;
        
        // Statistika prodejů po měsících
        $monthlySales = Visit::selectRaw("
                strftime('%Y-%m', occurred_at) as month,
                SUM(total_price) as total_services,
                SUM(retail_price) as total_retail,
                COUNT(*) as visit_count
            ")
            ->whereNotNull('closed_at')
            ->when($period !== 'all', function($q) use ($now, $period) {
                switch ($period) {
                    case 'today':
                        return $q->whereDate('occurred_at', $now->toDateString());
                    case 'week':
                        return $q->whereBetween('occurred_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    case 'month':
                        return $q->whereMonth('occurred_at', $now->month)
                                 ->whereYear('occurred_at', $now->year);
                    case 'year':
                        return $q->whereYear('occurred_at', $now->year);
                }
            })
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();
        
        return view('finance.index', compact('visits', 'totalRevenue', 'totalRetail', 'totalCombined', 'period', 'monthlySales'));
    }
}
