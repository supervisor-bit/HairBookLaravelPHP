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

        $clients = Client::orderBy('last_name')->get();

        return view('calendar.index', compact('appointments', 'selectedDate', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'duration' => 'nullable|integer',
            'notes' => 'nullable|string',
            'repeat_weeks' => 'nullable|integer|min:0',
        ]);

        // Vytvořit hlavní událost
        $appointment = Appointment::create($validated);

        // Pokud má opakování, vytvořit další události
        if (isset($validated['repeat_weeks']) && $validated['repeat_weeks'] > 1) {
            $this->createRecurringAppointments($appointment, $validated['repeat_weeks']);
        }

        // Pokud je zadáno redirect_to_date, přesměrovat na toto datum (pro opakované rezervace)
        $redirectDate = $request->input('redirect_to_date', $validated['date']);

        return redirect()->route('calendar.index', ['date' => $redirectDate])
            ->with('success', 'Událost byla vytvořena.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'duration' => 'nullable|integer',
            'notes' => 'nullable|string',
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

    public function checkAvailability(Request $request)
    {
        $date = $request->input('date');
        $startTime = $request->input('start_time'); // datetime string
        $endTime = $request->input('end_time'); // datetime string

        // Extrahovat pouze čas z datetime
        $requestStartTime = substr($startTime, 11, 8); // HH:MM:SS
        $requestEndTime = substr($endTime, 11, 8);

        // Zkontrolovat překryvy - porovnat pouze časové části
        $conflicts = Appointment::whereDate('date', $date)
            ->get()
            ->filter(function($appointment) use ($requestStartTime, $requestEndTime) {
                // Časy jsou uložené jako "HH:MM", přidáme :00
                $appStart = strlen($appointment->start_time) <= 5 ? $appointment->start_time . ':00' : substr($appointment->start_time, 11, 8);
                $appEnd = strlen($appointment->end_time) <= 5 ? $appointment->end_time . ':00' : substr($appointment->end_time, 11, 8);
                
                // Kontrola překryvu
                return ($requestStartTime < $appEnd && $requestEndTime > $appStart);
            });

        $available = $conflicts->isEmpty();

        // Pokud není volno, najít alternativní termíny
        $alternatives = [];
        if (!$available) {
            $alternatives = $this->findAlternativeTimes($date, $requestStartTime, $requestEndTime);
        }

        return response()->json([
            'available' => $available,
            'conflicts' => $conflicts->values(),
            'alternatives' => $alternatives
        ]);
    }

    private function findAlternativeTimes($date, $startTime, $endTime)
    {
        $alternatives = [];
        $duration = strtotime($endTime) - strtotime($startTime);
        
        // Získat všechny události pro ten den
        $dayAppointments = Appointment::whereDate('date', $date)->get();
        
        // Zkusit najít volné sloty v rozmezí 8:00 - 20:00
        for ($hour = 8; $hour < 20; $hour++) {
            $testStart = sprintf('%02d:00:00', $hour);
            $testEnd = date('H:i:s', strtotime($testStart) + $duration);
            
            if (strtotime($testEnd) > strtotime('20:00:00')) continue;

            // Zkontrolovat překryvy s existujícími událostmi
            $hasConflict = $dayAppointments->filter(function($appointment) use ($testStart, $testEnd) {
                // Časy jsou uložené jako "HH:MM", přidáme :00
                $appStart = strlen($appointment->start_time) <= 5 ? $appointment->start_time . ':00' : substr($appointment->start_time, 11, 8);
                $appEnd = strlen($appointment->end_time) <= 5 ? $appointment->end_time . ':00' : substr($appointment->end_time, 11, 8);
                return ($testStart < $appEnd && $testEnd > $appStart);
            })->isNotEmpty();

            if (!$hasConflict) {
                $alternatives[] = [
                    'start_time' => substr($testStart, 0, 5),
                    'end_time' => substr($testEnd, 0, 5)
                ];
                
                if (count($alternatives) >= 3) break;
            }
        }

        return $alternatives;
    }

    private function createRecurringAppointments(Appointment $parentAppointment, int $weeks)
    {
        // $weeks = celkový počet týdnů (včetně první rezervace)
        // Vytvoříme tedy $weeks - 1 dalších rezervací
        for ($i = 1; $i < $weeks; $i++) {
            $newDate = Carbon::parse($parentAppointment->date)->addWeeks($i);
            
            Appointment::create([
                'client_id' => $parentAppointment->client_id,
                'first_name' => $parentAppointment->first_name,
                'last_name' => $parentAppointment->last_name,
                'phone' => $parentAppointment->phone,
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
