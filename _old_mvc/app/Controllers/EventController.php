<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Request;
use App\Models\Event;
use App\Models\User;

class EventController extends Controller
{
    private Event $eventModel;

    public function __construct()
    {
        $this->eventModel = new Event();
    }

    /**
     * GET /events — публичный список мероприятий
     */
    public function index(): void
    {
        $request = new Request();
        $filters = [
            'event_type' => $request->get('event_type'),
            'format'     => $request->get('format'),
        ];

        $events = $this->eventModel->getUpcoming($filters);

        $this->view('events/index', [
            'events'     => $events,
            'filters'    => $filters,
            'types'      => Event::TYPES,
            'formats'    => Event::FORMATS,
        ]);
    }

    /**
     * GET /events/{id} — детальная страница
     */
    public function show(int $id): void
    {
        $event = $this->eventModel->getWithOrganizer($id);

        if (!$event || $event['status'] !== 'ACTIVE') {
            Session::flash('errors', ['Мероприятие не найдено']);
            $this->redirect('/events');
            return;
        }

        $this->view('events/show', [
            'event'   => $event,
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }

    /**
     * GET /psychologist/events/create — форма создания (только PSYCHOLOGIST)
     */
    public function create(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('psychologist/events/create', [
            'user'    => $user,
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }

    /**
     * POST /psychologist/events — сохранить мероприятие
     */
    public function store(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $request = new Request();

        $title          = trim($request->input('title', ''));
        $description    = trim($request->input('description', ''));
        $eventType      = $request->input('event_type', '');
        $format         = $request->input('format', 'ONLINE');
        $city           = trim($request->input('city', ''));
        $meetingLink    = trim($request->input('meeting_link', ''));
        $price          = $request->input('price') !== '' ? (float) $request->input('price') : null;
        $maxParticipants = $request->input('max_participants') !== '' ? (int) $request->input('max_participants') : null;
        $scheduledAt    = $request->input('scheduled_at', '');
        $durationMin    = (int) $request->input('duration_minutes', 60);

        $errors = [];
        if (empty($title))    $errors[] = 'Укажите название мероприятия';
        if (!array_key_exists($eventType, Event::TYPES)) $errors[] = 'Выберите тип мероприятия';
        if (empty($scheduledAt)) $errors[] = 'Укажите дату и время';
        if ($format === 'OFFLINE' && empty($city)) $errors[] = 'Для офлайн-мероприятия укажите город';

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->post());
            $this->redirect('/psychologist/events/create');
            return;
        }

        $this->eventModel->create([
            'organizer_id'    => $user['id'],
            'title'           => $title,
            'description'     => $description,
            'event_type'      => $eventType,
            'format'          => $format,
            'city'            => $city ?: null,
            'meeting_link'    => $meetingLink ?: null,
            'price'           => $price,
            'max_participants' => $maxParticipants,
            'scheduled_at'    => $scheduledAt,
            'duration_minutes' => $durationMin,
        ]);

        Session::flash('success', 'Мероприятие создано и опубликовано!');
        $this->redirect('/events');
    }

    /**
     * GET /psychologist/events — список мероприятий психолога
     */
    public function myEvents(): void
    {
        $this->requireAuth();
        $user   = $this->getUser();
        $events = $this->eventModel->getByOrganizer($user['id']);

        $this->view('psychologist/events/my', [
            'user'    => $user,
            'events'  => $events,
            'types'   => Event::TYPES,
            'formats' => Event::FORMATS,
        ]);
    }

    private function getUser(): array
    {
        $userModel = new User();
        $user = $userModel->getUserById(Session::userId());
        if (!$user) {
            Session::logout();
            $this->redirect('/login');
            exit;
        }
        return $user;
    }
}
