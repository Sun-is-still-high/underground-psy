<?php

namespace App\Http\Controllers;

use App\Models\ClientCase;
use App\Models\CaseResponse;
use App\Models\ProblemType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaseSearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClientCase::with(['problemType', 'client'])
            ->where('status', 'OPEN');

        if ($request->filled('problem_type')) {
            $query->where('problem_type_id', $request->integer('problem_type'));
        }

        if ($request->filled('budget_type')) {
            $query->where('budget_type', $request->input('budget_type'));
        }

        $cases = $query->latest()->take(50)->get();
        $problemTypes = ProblemType::where('is_active', true)->orderBy('sort_order')->get();

        $stats = ProblemType::withCount(['cases' => fn($q) => $q->where('status', 'OPEN')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('psychologist.cases.search', compact('cases', 'problemTypes', 'stats'));
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        $case = ClientCase::with(['problemType', 'client'])->findOrFail($id);
        $hasResponded = CaseResponse::where('case_id', $id)
            ->where('psychologist_id', $request->user()->id)
            ->exists();

        return view('psychologist.cases.show', compact('case', 'hasResponded'));
    }

    public function respond(Request $request, int $id): RedirectResponse
    {
        $case = ClientCase::findOrFail($id);

        if ($case->status !== 'OPEN') {
            return back()->with('error', 'Кейс уже закрыт или в работе');
        }

        $alreadyResponded = CaseResponse::where('case_id', $id)
            ->where('psychologist_id', $request->user()->id)
            ->exists();

        if ($alreadyResponded) {
            return back()->with('error', 'Вы уже откликнулись на этот кейс');
        }

        $validated = $request->validate([
            'message' => ['required', 'string'],
            'proposed_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        CaseResponse::create([
            'case_id' => $id,
            'psychologist_id' => $request->user()->id,
            'message' => $validated['message'],
            'proposed_price' => $validated['proposed_price'] ?? null,
        ]);

        return redirect()->route('psychologist.cases.show', $id)
            ->with('success', 'Ваш отклик отправлен клиенту!');
    }
}
