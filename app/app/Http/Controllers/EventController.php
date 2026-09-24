<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $ym = $request->ym ?: now()->format('Y-m');
        $cursor = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();

        $monthStart = $cursor->copy()->startOfMonth();
        $monthEnd = $cursor->copy()->endOfMonth();

        // Rango visible de la grilla (semanas completas, lunes a domingo)
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $events = Event::whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('date')->get()
            ->groupBy(fn ($e) => $e->date->toDateString());

        // Construir semanas
        $weeks = [];
        $day = $gridStart->copy();
        while ($day <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $day->copy();
                $day->addDay();
            }
            $weeks[] = $week;
        }

        $upcoming = Event::whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')->limit(8)->get();

        $colors = Event::COLORS;

        return view('events.index', compact('cursor', 'monthStart', 'weeks', 'events', 'upcoming', 'colors', 'ym'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        Event::create($data);

        return redirect()->route('events.index', ['ym' => Carbon::parse($data['date'])->format('Y-m')])
            ->with('success', 'Evento agregado al calendario.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validateData($request));

        return redirect()->route('events.index', ['ym' => Carbon::parse($event->date)->format('Y-m')])
            ->with('success', 'Evento actualizado.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $ym = Carbon::parse($event->date)->format('Y-m');
        $event->delete();

        return redirect()->route('events.index', ['ym' => $ym])->with('success', 'Evento eliminado.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:date'],
            'type' => ['required', 'in:feriado,examen,reunion,actividad,civico,otro'],
        ]);
    }
}
