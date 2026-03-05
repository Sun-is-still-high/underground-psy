<?php

namespace App\Http\Controllers\Triads;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /** Публичный банк заданий (одобренные) */
    public function index(Request $request)
    {
        $tasks = Task::approved()
            ->when($request->search, fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('triads.tasks.index', compact('tasks'));
    }

    /** Форма предложения нового задания */
    public function create()
    {
        return view('triads.tasks.create');
    }

    /** Сохранить предложенное задание */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'required|string',
            'instruction_client'     => 'required|string',
            'instruction_therapist'  => 'required|string',
            'instruction_observer'   => 'required|string',
            'duration_minutes'       => 'required|integer|min:10|max:180',
        ], [
            'title.required'                 => 'Укажите название задания',
            'description.required'           => 'Опишите кейс',
            'instruction_client.required'    => 'Добавьте инструкцию для клиента',
            'instruction_therapist.required' => 'Добавьте инструкцию для терапевта',
            'instruction_observer.required'  => 'Добавьте инструкцию для наблюдателя',
            'duration_minutes.required'      => 'Укажите длительность',
            'duration_minutes.min'           => 'Минимальная длительность — 10 минут',
            'duration_minutes.max'           => 'Максимальная длительность — 180 минут',
        ]);

        Task::create([
            ...$validated,
            'author_id' => Auth::id(),
            'status'    => TaskStatus::Pending,
        ]);

        return redirect()->route('triads.tasks.my')
            ->with('success', 'Задание отправлено на модерацию');
    }

    /** Мои предложенные задания */
    public function my()
    {
        $tasks = Auth::user()
            ->proposedTasks()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('triads.tasks.my', compact('tasks'));
    }

    /** Форма редактирования отклонённого задания */
    public function edit(Task $task)
    {
        abort_unless($task->author_id === Auth::id(), 403);
        abort_unless($task->status === TaskStatus::Rejected, 403);

        return view('triads.tasks.edit', compact('task'));
    }

    /** Сохранить правки и переотправить на модерацию */
    public function update(Request $request, Task $task)
    {
        abort_unless($task->author_id === Auth::id(), 403);
        abort_unless($task->status === TaskStatus::Rejected, 403);

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'required|string',
            'instruction_client'     => 'required|string',
            'instruction_therapist'  => 'required|string',
            'instruction_observer'   => 'required|string',
            'duration_minutes'       => 'required|integer|min:10|max:180',
        ]);

        $task->update([
            ...$validated,
            'status'             => TaskStatus::Pending,
            'moderation_comment' => null,
        ]);

        return redirect()->route('triads.tasks.my')
            ->with('success', 'Задание отправлено на повторную модерацию');
    }
}
