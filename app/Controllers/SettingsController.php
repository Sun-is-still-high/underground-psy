<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Request;
use App\Models\User;

class SettingsController extends Controller
{
    private User $userModel;

    private const TIMEZONES = [
        'Europe/Minsk'     => 'Минск (UTC+3)',
        'Europe/Moscow'    => 'Москва (UTC+3)',
        'Europe/Kiev'      => 'Киев (UTC+2/+3)',
        'Europe/Riga'      => 'Рига (UTC+2/+3)',
        'Europe/Vilnius'   => 'Вильнюс (UTC+2/+3)',
        'Europe/Tallinn'   => 'Таллин (UTC+2/+3)',
        'Europe/Chisinau'  => 'Кишинёв (UTC+2/+3)',
        'Europe/Samara'    => 'Самара (UTC+4)',
        'Asia/Yekaterinburg' => 'Екатеринбург (UTC+5)',
        'Asia/Tashkent'    => 'Ташкент (UTC+5)',
        'Asia/Almaty'      => 'Алма-Ата (UTC+5/+6)',
        'Asia/Bishkek'     => 'Бишкек (UTC+6)',
        'Asia/Novosibirsk' => 'Новосибирск (UTC+7)',
        'Asia/Krasnoyarsk' => 'Красноярск (UTC+7)',
        'Asia/Irkutsk'     => 'Иркутск (UTC+8)',
        'Asia/Vladivostok' => 'Владивосток (UTC+10)',
    ];

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $user = $this->userModel->getUserById(Session::userId());

        $this->view('settings/index', [
            'user'      => $user,
            'timezones' => self::TIMEZONES,
        ]);
    }

    public function saveTimezone(): void
    {
        $this->requireAuth();
        $request  = new Request();
        $timezone = $request->input('timezone', 'Europe/Moscow');

        if (!array_key_exists($timezone, self::TIMEZONES)) {
            $timezone = 'Europe/Moscow';
        }

        $this->userModel->updateTimezone(Session::userId(), $timezone);
        Session::flash('success', 'Часовой пояс обновлён');
        $this->redirect('/settings');
    }
}
