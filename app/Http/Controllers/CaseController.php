<?php

namespace App\Http\Controllers;

use App\Models\ClientCase;
use App\Models\CaseResponse;
use App\Models\ProblemType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CaseController extends Controller
{
    public function index(Request $request): View
    {
        $cases = ClientCase::where('client_id', $request->user()->id)
            ->with('problemType')
            ->latest()
            ->get();

        return view('client.cases.index', compact('cases'));
    }

    public function create(): View
    {
        $problemTypes = ProblemType::where('is_active', true)->orderBy('sort_order')->get();
        return view('client.cases.create', compact('problemTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'problem_type_id' => ['required', 'exists:problem_types,id'],
            'is_anonymous' => ['nullable', 'boolean'],
            'budget_type' => ['required', 'in:PAID,REVIEW,NEGOTIABLE'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['client_id'] = $request->user()->id;
        $validated['is_anonymous'] = $request->boolean('is_anonymous');

        ClientCase::create($validated);

        return redirect()->route('client.cases.index')
            ->with('success', 'Ваш запрос успешно опубликован! Психологи смогут откликнуться на него.');
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        $case = ClientCase::with(['problemType', 'responses.psychologist.psychologistProfile'])
            ->where('client_id', $request->user()->id)
            ->findOrFail($id);

        return view('client.cases.show', compact('case'));
    }

    public function acceptResponse(Request $request, int $caseId, int $responseId): RedirectResponse
    {
        $case = ClientCase::where('client_id', $request->user()->id)->findOrFail($caseId);

        if ($case->status !== 'OPEN') {
            return back()->with('error', 'Кейс уже закрыт или в работе');
        }

        ClientCase::where('id', $caseId)->update(['status' => 'IN_PROGRESS']);
        CaseResponse::where('id', $responseId)->where('case_id', $caseId)->update([
            'status' => 'ACCEPTED',
            'responded_at' => now(),
        ]);
        CaseResponse::where('case_id', $caseId)->where('id', '!=', $responseId)->update(['status' => 'REJECTED']);

        return redirect()->route('client.cases.show', $caseId)
            ->with('success', 'Вы приняли отклик! Психолог получит уведомление.');
    }

    public function close(Request $request, int $id): RedirectResponse
    {
        $case = ClientCase::where('client_id', $request->user()->id)->findOrFail($id);

        $case->update(['status' => 'CLOSED', 'closed_at' => now()]);

        return redirect()->route('client.cases.index')->with('success', 'Кейс закрыт');
    }
}
