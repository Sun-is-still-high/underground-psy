<?php

namespace App\Http\Controllers;

use App\Models\Method;
use App\Models\ProblemType;
use App\Models\PsychologistProfile;
use App\Models\PsychologistSpecialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PsychologistController extends Controller
{
    public function index(Request $request): View
    {
        $query = PsychologistProfile::with(['user', 'problemTypes', 'methods'])
            ->where('is_published', true)
            ->where('diploma_verified', true)
            ->where('can_consult', true)
            ->whereHas('user', fn($q) => $q->where('status', 'active'));

        if ($request->filled('specialization')) {
            $query->whereHas('problemTypes', fn($q) => $q->where('problem_types.id', $request->integer('specialization')));
        }

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', '%' . $request->input('search') . '%'));
        }

        if ($request->filled('gender') && in_array($request->input('gender'), ['male', 'female'])) {
            $query->whereHas('user', fn($q) => $q->where('gender', $request->input('gender')));
        }

        if ($request->filled('price_min')) {
            $query->where('hourly_rate_max', '>=', $request->integer('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('hourly_rate_min', '<=', $request->integer('price_max'));
        }

        if ($request->filled('method')) {
            $query->whereHas('methods', fn($q) => $q->where('methods.id', $request->integer('method')));
        }

        if ($request->filled('work_format') && in_array($request->input('work_format'), ['online', 'offline', 'both'])) {
            $format = $request->input('work_format');
            $query->where(function ($q) use ($format) {
                $q->where('work_format', $format)->orWhere('work_format', 'both');
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }

        if ($request->filled('language')) {
            $lang = $request->input('language');
            $query->whereRaw("JSON_CONTAINS(languages, ?)", [json_encode($lang)]);
        }

        $sort = $request->input('sort', 'activity');
        if ($sort === 'price') {
            $query->orderByRaw('COALESCE(hourly_rate_min, 999999)');
        } elseif ($sort === 'since') {
            $query->join('users as u_sort', 'u_sort.id', '=', 'psychologist_profiles.user_id')
                  ->orderBy('u_sort.created_at');
        } else {
            // Взвешенная сортировка: активность за 90 дней × 1.0, старше × 0.3
            $cutoff = now()->subDays(90)->toDateTimeString();
            $query->selectRaw("psychologist_profiles.*, (
                (SELECT COALESCE(SUM(CASE WHEN s.scheduled_at >= ? THEN 1.0 ELSE 0.3 END), 0)
                 FROM slot_participants sp
                 JOIN slots s ON s.id = sp.slot_id
                 WHERE sp.user_id = psychologist_profiles.user_id
                   AND sp.status = 'active')
                +
                (SELECT COALESCE(SUM(CASE WHEN isess.scheduled_at >= ? THEN 1.0 ELSE 0.3 END), 0)
                 FROM intervision_attendance ia
                 JOIN intervision_participants ip ON ip.id = ia.participant_id
                 JOIN intervision_sessions isess ON isess.id = ia.session_id
                 WHERE ip.psychologist_id = psychologist_profiles.user_id
                   AND ia.attended = 1
                   AND isess.status = 'COMPLETED')
            ) as activity_score", [$cutoff, $cutoff])
            ->orderByDesc('activity_score');
        }

        $psychologists = $query->get();
        $problemTypes = ProblemType::where('is_active', true)->orderBy('sort_order')->get();
        $methods = Method::orderBy('name')->get();

        return view('psychologists.index', compact('psychologists', 'problemTypes', 'methods'));
    }

    public function show(int $id): View|RedirectResponse
    {
        $profile = PsychologistProfile::with(['user', 'problemTypes', 'methods'])
            ->where('is_published', true)
            ->where('diploma_verified', true)
            ->where('can_consult', true)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->find($id);

        if (!$profile) {
            return redirect()->route('psychologists.index')
                ->with('error', 'Профиль не найден');
        }

        return view('psychologists.show', compact('profile'));
    }

    public function editProfile(Request $request): View
    {
        $user = $request->user();
        $profile = $user->psychologistProfile ?? PsychologistProfile::create(['user_id' => $user->id]);
        $profile->load('problemTypes', 'methods');

        $problemTypes = ProblemType::where('is_active', true)->orderBy('sort_order')->get();
        $methods = Method::orderBy('name')->get();

        return view('psychologist.profile-edit', compact('profile', 'problemTypes', 'methods'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->psychologistProfile ?? PsychologistProfile::create(['user_id' => $user->id]);

        $validated = $request->validate([
            'bio' => ['nullable', 'string'],
            'methods_description' => ['nullable', 'string'],
            'education' => ['nullable', 'string'],
            'experience_description' => ['nullable', 'string'],
            'hourly_rate_min' => ['nullable', 'numeric', 'min:0'],
            'hourly_rate_max' => ['nullable', 'numeric', 'min:0', 'gte:hourly_rate_min'],
            'is_published' => ['nullable', 'boolean'],
            'work_format'  => ['nullable', 'in:online,offline,both'],
            'city'         => ['nullable', 'string', 'max:100'],
            'languages'    => ['nullable', 'array'],
            'languages.*'  => ['string'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['integer', 'exists:problem_types,id'],
            'methods' => ['nullable', 'array'],
            'methods.*' => ['integer', 'exists:methods,id'],
        ]);

        $isPublished = $request->boolean('is_published');
        if ($isPublished && empty($validated['bio'])) {
            return back()->withErrors(['bio' => 'Для публикации профиля необходимо заполнить поле "О себе"']);
        }

        $profile->update([
            'bio' => $validated['bio'] ?? null,
            'methods_description' => $validated['methods_description'] ?? null,
            'education' => $validated['education'] ?? null,
            'experience_description' => $validated['experience_description'] ?? null,
            'hourly_rate_min' => $validated['hourly_rate_min'] ?? null,
            'hourly_rate_max' => $validated['hourly_rate_max'] ?? null,
            'is_published' => $isPublished,
            'work_format'  => $validated['work_format'] ?? 'online',
            'city'         => $validated['city'] ?? null,
            'languages'    => $validated['languages'] ?? [],
        ]);

        $profile->problemTypes()->sync($validated['specializations'] ?? []);
        $profile->methods()->sync($validated['methods'] ?? []);

        return redirect()->route('psychologist.profile.edit')
            ->with('success', 'Профиль успешно обновлён!');
    }
}
