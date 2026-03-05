# Underground Psy — CLAUDE.md

## Стек и окружение
- **Laravel 11** + PHP 8.4, MySQL, Apache, OpenServer (Windows)
- Рабочая директория: `c:\OSPanel\home\underground-psy`
- Запуск миграций: `php artisan migrate --force`
- Старый MVC сохранён в `_old_mvc/` только для справки — не трогать

## Архитектура Laravel

### Ключевые файлы
- `routes/web.php` — все роуты
- `app/Models/` — Eloquent-модели
- `app/Http/Controllers/` — контроллеры
- `app/Http/Middleware/EnsureRole.php` — проверка роли (alias: `role`)
- `app/Http/Middleware/EnsureNotBlocked.php` — глобальный, блокирует `is_blocked=1`
- `resources/views/layouts/main.blade.php` — основной layout (`@yield('content')`)

### Роли пользователей
`users.role` ∈ {`CLIENT`, `PSYCHOLOGIST`, `ADMIN`}

Хелперы на User-модели: `isClient()`, `isPsychologist()`, `isAdmin()`

## Роуты (сводка)

### Публичные (без auth)
- `GET /` `GET /about` — HomeController
- `GET /psychologists` `GET /psychologists/{id}` — PsychologistController
- `GET /events` `GET /events/{event}` — EventController
- `GET /questions` `GET /ask` `POST /ask` — QuestionController

### Auth (middleware: `auth`)
- `GET /dashboard` — DashboardController
- `GET /settings` `POST /settings/timezone` `POST /settings/gender` — SettingsController

### CLIENT (prefix: `client/`, name: `client.`)
- cases CRUD: index, create, store, show, close, acceptResponse

### PSYCHOLOGIST (prefix: `psychologist/`, name: `psychologist.`)
- cases: index, show, respond
- `GET /intervisions` — DashboardController@intervisionStatus
- `GET /questions` `POST /questions/{question}/answer` — QuestionController
- `GET /events` `GET /events/create` `POST /events` — EventController
- `GET /profile/edit` `POST /profile/update` — PsychologistController

### ADMIN (prefix: `admin/`, name: `admin.`)
- `GET /` — Admin\DashboardController@index
- `GET /users` `POST /users/{user}/block` `POST /users/{user}/unblock` — Admin\UserController
- `GET /verification` `POST /verification/{profile}/approve|reject` — Admin\VerificationController
- `/intervision/groups/*` `/intervision/sessions/*` — Admin\IntervisionController

## Модели Eloquent

| Модель | Таблица | Ключевые связи |
|--------|---------|----------------|
| User | users | psychologistProfile, cases, caseResponses, intervisionParticipations |
| PsychologistProfile | psychologist_profiles | user, specializations, problemTypes |
| ClientCase | client_cases | — |
| PublicQuestion | public_questions | answers (hasMany PublicAnswer) |
| PublicAnswer | public_answers | psychologist (belongsTo User) |
| Event | events | organizer (belongsTo User) |

### User fillable
`name, email, password, role, is_blocked, blocked_reason, timezone, gender`

### PsychologistProfile fillable
`user_id, bio, methods_description, education, experience_description, hourly_rate_min, hourly_rate_max, is_published, diploma_scan_url, diploma_verified`

### Event — константы
`Event::TYPES` (GROUP_THERAPY, SUPPORT_GROUP, SEMINAR, TRAINING, WEBINAR)
`Event::FORMATS` (ONLINE, OFFLINE)
Скоупы: `active()`, `upcoming()`

## Паттерны и соглашения

### Blade-шаблоны
```blade
@extends('layouts.main')
@section('title', 'Заголовок — Underground Psy')
@section('content')
...
@endsection
```

### Flash-сообщения
```php
// Контроллер
return redirect()->route('...')->with('success', 'Текст');
// Шаблон
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
```

### Route model binding
Работает — использовать `Model $model` в методах контроллера.

### Пагинация
```php
->paginate(25)->withQueryString()
// В шаблоне:
{{ $items->links() }}
```

### Миграции для существующих таблиц
Использовать `Schema::hasColumn()` перед `addColumn`, чтобы не падать на существующей БД.

## Что НЕ делать
- Не трогать `_old_mvc/` — только читать для справки
- Не использовать `Core\Model` или `Core\Database` — только Eloquent
- Не коммитить без явной просьбы пользователя
