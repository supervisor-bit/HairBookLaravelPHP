<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showSetup()
    {
        // Check if password already exists
        $passwordExists = DB::table('app_settings')
            ->where('key', 'app_password')
            ->exists();
        
        if ($passwordExists) {
            return redirect()->route('auth.login');
        }
        
        return view('auth.setup');
    }
    
    public function storeSetup(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:4', 'confirmed']
        ]);
        
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'app_password'],
            ['value' => Hash::make($request->password), 'updated_at' => now()]
        );
        
        session(['authenticated' => true]);
        
        return redirect()->route('dashboard')->with('status', 'Heslo bylo úspěšně nastaveno!');
    }
    
    public function showLogin()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string']
        ]);
        
        $passwordHash = DB::table('app_settings')
            ->where('key', 'app_password')
            ->value('value');
        
        if (!$passwordHash || !Hash::check($request->password, $passwordHash)) {
            return back()->withErrors(['password' => 'Nesprávné heslo']);
        }
        
        session(['authenticated' => true]);
        
        return redirect()->route('home');
    }
    
    public function logout()
    {
        session()->forget('authenticated');
        return redirect()->route('login');
    }
}
