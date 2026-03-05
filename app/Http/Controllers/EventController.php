<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * GET /events — публичный каталог мероприятий
     */
    public function index(Request $request)
    {
        $events = Event::active()->upcoming()
            ->with('organizer.psychologistProfile')
            ->when($request->event_type, fn ($q) => $q->where('event_type', $request->event_type))
            ->when($request->format, fn ($q) => $q->where('format', $request->format))
            ->orderBy('scheduled_at')
            ->get();

        return view('events.index', [
            'events'  => $events,
            'filters' => $request->only('event_type', 'format'),
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }

    /**
     * GET /events/{event} — детальная страница
     */
    public function show(Event $event)
    {
        abort_if($event->status !== 'ACTIVE', 404);

        return view('events.show', [
            'event'   => $event->load('organizer.psychologistProfile'),
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }

    /**
     * GET /psychologist/events/create — форма создания
     */
    public function create()
    {
        return view('psychologist.events.create', [
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }

    /**
     * POST /psychologist/events — сохранить мероприятие
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:200',
            'event_type'  => 'required|in:' . implode(',', array_keys(Event::TYPES)),
            'format'      => 'required|in:ONLINE,OFFLINE',
            'scheduled_at' => 'required|date|after:now',
            'city'        => 'required_if:format,OFFLINE|nullable|string|max:100',
            'meeting_link' => 'nullable|url|max:500',
            'price'       => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:2',
            'duration_minutes' => 'nullable|integer|min:30',
        ], [
            'title.required'       => 'Укажите название мероприятия',
            'event_type.required'  => 'Выберите тип мероприятия',
            'event_type.in'        => 'Недопустимый тип мероприятия',
            'scheduled_at.required' => 'Укажите дату и время',
            'scheduled_at.after'   => 'Дата должна быть в будущем',
            'city.required_if'     => 'Для офлайн-мероприятия укажите город',
            'meeting_link.url'     => 'Введите корректный URL ссылки',
        ]);

        Event::create([
            'organizer_id'     => auth()->id(),
            'title'            => $request->title,
            'description'      => $request->description,
            'event_type'       => $request->event_type,
            'format'           => $request->format,
            'city'             => $request->format === 'OFFLINE' ? $request->city : null,
            'meeting_link'     => $request->format === 'ONLINE' ? $request->meeting_link : null,
            'price'            => $request->price ?: null,
            'max_participants' => $request->max_participants ?: null,
            'scheduled_at'     => $request->scheduled_at,
            'duration_minutes' => $request->duration_minutes ?? 60,
            'status'           => 'ACTIVE',
        ]);

        return redirect()->route('events.index')
            ->with('success', 'Мероприятие создано и опубликовано!');
    }

    /**
     * GET /psychologist/events — мои мероприятия
     */
    public function myEvents()
    {
        $events = Event::where('organizer_id', auth()->id())
            ->orderByDesc('scheduled_at')
            ->get();

        return view('psychologist.events.my', [
            'events'  => $events,
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }
}
