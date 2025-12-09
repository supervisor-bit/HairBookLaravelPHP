<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientNote;
use Illuminate\Http\Request;

class ClientNoteController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $client->notes()->create($data);
        $note = $client->notes()->latest()->first();

        if ($request->wantsJson()) {
            return response()->json($this->serialize($note), 201);
        }

        return redirect()->back()->with('status', 'Poznámka přidána.');
    }

    public function update(Request $request, ClientNote $note)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $note->update($data);

        if ($request->wantsJson()) {
            return response()->json($this->serialize($note));
        }

        return redirect()->back()->with('status', 'Poznámka upravena.');
    }

    public function destroy(ClientNote $note)
    {
        $note->delete();

        if (request()->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back()->with('status', 'Poznámka smazána.');
    }

    private function serialize(ClientNote $note): array
    {
        return [
            'id' => $note->id,
            'body' => $note->body,
            'created_at_formatted' => optional($note->created_at)->format('d.m.Y H:i'),
            'urls' => [
                'update' => route('clients.notes.update', $note),
                'delete' => route('clients.notes.destroy', $note),
            ],
        ];
    }
}
