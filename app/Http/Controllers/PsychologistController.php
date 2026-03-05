<?php

namespace App\Http\Controllers;

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
        $query = PsychologistProfile::with(['user', 'problemTypes'])
            ->where('is_published', true);

        if ($request->filled('specialization')) {
            $query->whereHas('problemTypes', fn($q) => $q->where('problem_types.id', $request->integer('specialization')));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $psychologists = $query->get();
        $problemTypes = ProblemType::where('is_active', true)->orderBy('sort_order')->get();

        return view('psychologists.index', compact('psychologists', 'problemTypes'));
    }

    public function show(int $id): View|RedirectResponse
    {
        $profile = PsychologistProfile::with(['user', 'problemTypes'])
            ->where('is_published', true)
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
        $profile->load('problemTypes');

        $problemTypes = ProblemType::where('is_active', true)->orderBy('sort_order')->get();

        return view('psychologist.profile-edit', compact('profile', 'problemTypes'));
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
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['integer', 'exists:problem_types,id'],
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
        ]);

        $profile->problemTypes()->sync($validated['specializations'] ?? []);

        return redirect()->route('psychologist.profile.edit')
            ->with('success', 'Профиль успешно обновлён!');
    }
}
