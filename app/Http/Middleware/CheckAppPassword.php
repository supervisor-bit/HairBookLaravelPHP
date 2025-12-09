<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckAppPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if password is set in database
        $passwordHash = DB::table('app_settings')
            ->where('key', 'app_password')
            ->value('value');
        
        // If no password set, redirect to setup
        if (!$passwordHash) {
            if (!$request->is('auth/setup') && !$request->is('auth/setup-store')) {
                return redirect()->route('auth.setup');
            }
            return $next($request);
        }
        
        // If password is set, check if user is logged in
        if (!session('authenticated')) {
            if (!$request->is('auth/login') && !$request->is('auth/login-store')) {
                return redirect()->route('login');
            }
        }
        
        return $next($request);
    }
}
