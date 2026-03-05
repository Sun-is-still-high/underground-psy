<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const TIMEZONES = [
        'Europe/Minsk'       => 'Минск (UTC+3)',
        'Europe/Moscow'      => 'Москва (UTC+3)',
        'Europe/Kiev'        => 'Киев (UTC+2/+3)',
        'Europe/Riga'        => 'Рига (UTC+2/+3)',
        'Europe/Vilnius'     => 'Вильнюс (UTC+2/+3)',
        'Europe/Tallinn'     => 'Таллин (UTC+2/+3)',
        'Europe/Chisinau'    => 'Кишинёв (UTC+2/+3)',
        'Europe/Samara'      => 'Самара (UTC+4)',
        'Asia/Yekaterinburg' => 'Екатеринбург (UTC+5)',
        'Asia/Tashkent'      => 'Ташкент (UTC+5)',
        'Asia/Almaty'        => 'Алма-Ата (UTC+5/+6)',
        'Asia/Bishkek'       => 'Бишкек (UTC+6)',
        'Asia/Novosibirsk'   => 'Новосибирск (UTC+7)',
        'Asia/Krasnoyarsk'   => 'Красноярск (UTC+7)',
        'Asia/Irkutsk'       => 'Иркутск (UTC+8)',
        'Asia/Vladivostok'   => 'Владивосток (UTC+10)',
    ];

    private const GENDERS = [
        'MALE'   => 'Мужской',
        'FEMALE' => 'Женский',
        'OTHER'  => 'Другой',
    ];

    /**
     * GET /settings
     */
    public function index()
    {
        return view('settings.index', [
            'user'      => auth()->user(),
            'timezones' => self::TIMEZONES,
            'genders'   => self::GENDERS,
        ]);
    }

    /**
     * POST /settings/timezone
     */
    public function saveTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|in:' . implode(',', array_keys(self::TIMEZONES)),
        ]);

        auth()->user()->update(['timezone' => $request->timezone]);

        return redirect()->route('settings.index')
            ->with('success', 'Часовой пояс обновлён');
    }

    /**
     * POST /settings/gender
     */
    public function saveGender(Request $request)
    {
        $request->validate([
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
        ]);

        auth()->user()->update(['gender' => $request->gender ?: null]);

        return redirect()->route('settings.index')
            ->with('success', 'Пол обновлён');
    }
}
