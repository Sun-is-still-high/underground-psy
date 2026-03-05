<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TriadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /** Очередь модерации */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $tasks = Task::with('author')
            ->where('status', $status)
            ->orderBy('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.tasks.index', compact('tasks', 'status'));
    }

    /** Просмотр задания */
    public function show(Task $task)
    {
        $task->load('author');
        return view('admin.tasks.show', compact('task'));
    }

    /** Одобрить (с возможной правкой) */
    public function approve(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'                 => 'sometimes|string|max:255',
            'description'           => 'sometimes|string',
            'instruction_client'    => 'sometimes|string',
            'instruction_therapist' => 'sometimes|string',
            'instruction_observer'  => 'sometimes|string',
            'duration_minutes'      => 'sometimes|integer|min:10|max:180',
        ]);

        $task->update([
            ...$validated,
            'status'       => TaskStatus::Approved,
            'moderated_by' => Auth::id(),
            'moderation_comment' => null,
        ]);

        $this->notify($task->author_id, 'task_approved', [
            'task_id'    => $task->id,
            'task_title' => $task->title,
        ]);

        return redirect()->route('admin.tasks.index')
            ->with('success', "Задание «{$task->title}» одобрено");
    }

    /** Отклонить с комментарием */
    public function reject(Request $request, Task $task)
    {
        $request->validate([
            'moderation_comment' => 'required|string',
        ], [
            'moderation_comment.required' => 'Укажите причину отклонения',
        ]);

        $task->update([
            'status'             => TaskStatus::Rejected,
            'moderated_by'       => Auth::id(),
            'moderation_comment' => $request->moderation_comment,
        ]);

        $this->notify($task->author_id, 'task_rejected', [
            'task_id'    => $task->id,
            'task_title' => $task->title,
            'comment'    => $request->moderation_comment,
        ]);

        return redirect()->route('admin.tasks.index')
            ->with('success', "Задание «{$task->title}» отклонено");
    }

    /** Обновить поля задания (без смены статуса) */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'instruction_client'    => 'required|string',
            'instruction_therapist' => 'required|string',
            'instruction_observer'  => 'required|string',
            'duration_minutes'      => 'required|integer|min:10|max:180',
        ]);

        $task->update($validated);

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Задание обновлено');
    }

    private function notify(int $userId, string $type, array $data): void
    {
        TriadNotification::create([
            'user_id'    => $userId,
            'type'       => $type,
            'data'       => $data,
            'created_at' => now(),
        ]);
    }
}
