<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // Získat všechny události pro daný den
        $appointments = Appointment::with('client')
            ->whereDate('date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        // Rozdělit události do 2 kolejí
        $lane1 = $appointments->where('lane', 1);
        $lane2 = $appointments->where('lane', 2);

        $clients = Client::orderBy('last_name')->get();

        return view('calendar.index', compact('appointments', 'lane1', 'lane2', 'selectedDate', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'duration' => 'nullable|integer',
            'notes' => 'nullable|string',
            'lane' => 'required|integer|in:1,2',
            'repeat_weeks' => 'nullable|integer|min:0',
        ]);

        // Vytvořit hlavní událost
        $appointment = Appointment::create($validated);

        // Pokud má opakování, vytvořit další události
        if ($validated['repeat_weeks'] > 0) {
            $this->createRecurringAppointments($appointment, $validated['repeat_weeks']);
        }

        return redirect()->route('calendar.index', ['date' => $validated['date']])
            ->with('success', 'Událost byla vytvořena.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'duration' => 'nullable|integer',
            'notes' => 'nullable|string',
            'lane' => 'required|integer|in:1,2',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $appointment->update($validated);

        return redirect()->route('calendar.index', ['date' => $validated['date']])
            ->with('success', 'Událost byla aktualizována.');
    }

    public function destroy(Appointment $appointment)
    {
        $date = $appointment->date;
        $appointment->delete();

        return redirect()->route('calendar.index', ['date' => $date])
            ->with('success', 'Událost byla smazána.');
    }

    private function createRecurringAppointments(Appointment $parentAppointment, int $weeks)
    {
        for ($i = 1; $i <= $weeks; $i++) {
            $newDate = Carbon::parse($parentAppointment->date)->addWeeks($i);
            
            Appointment::create([
                'client_id' => $parentAppointment->client_id,
                'first_name' => $parentAppointment->first_name,
                'last_name' => $parentAppointment->last_name,
                'date' => $newDate,
                'start_time' => $parentAppointment->start_time,
                'end_time' => $parentAppointment->end_time,
                'duration' => $parentAppointment->duration,
                'notes' => $parentAppointment->notes,
                'lane' => $parentAppointment->lane,
                'repeat_weeks' => 0,
                'parent_appointment_id' => $parentAppointment->id,
                'status' => 'scheduled',
            ]);
        }
    }
}
