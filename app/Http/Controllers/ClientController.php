<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        Client::create($data);

        return redirect()->back()->with('status', 'Klient přidán.');
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $client->update($data);

        return redirect()->back()->with('status', 'Klient upraven.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('dashboard', ['section' => 'clients'])->with('status', 'Klient smazán.');
    }
}
