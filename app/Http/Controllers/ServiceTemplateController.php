<?php

namespace App\Http\Controllers;

use App\Models\ServiceTemplate;
use Illuminate\Http\Request;

class ServiceTemplateController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $template = ServiceTemplate::create($validated);

        return response()->json($template);
    }

    public function update(Request $request, ServiceTemplate $serviceTemplate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $serviceTemplate->update($validated);

        return response()->json($serviceTemplate);
    }

    public function destroy(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->delete();

        return response()->json(['success' => true]);
    }
}
