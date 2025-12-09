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
        
        return view('finance.index', compact('visits', 'totalRevenue', 'period'));
    }
}
