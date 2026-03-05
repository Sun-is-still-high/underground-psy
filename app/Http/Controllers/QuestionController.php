<?php

namespace App\Http\Controllers;

use App\Models\PublicAnswer;
use App\Models\PublicQuestion;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * GET /ask — публичная форма вопроса
     */
    public function askForm()
    {
        return view('questions.ask');
    }

    /**
     * POST /ask — сохранить вопрос
     */
    public function askSubmit(Request $request)
    {
        $request->validate([
            'author_name'  => 'required|string|max:100',
            'author_email' => 'required|email|max:150',
            'question'     => 'required|string|min:20',
        ], [
            'author_name.required'  => 'Укажите ваше имя',
            'author_email.required' => 'Укажите корректный email (ответ придёт на него)',
            'author_email.email'    => 'Укажите корректный email (ответ придёт на него)',
            'question.required'     => 'Вопрос должен содержать не менее 20 символов',
            'question.min'          => 'Вопрос должен содержать не менее 20 символов',
        ]);

        PublicQuestion::create([
            'author_name'  => $request->author_name,
            'author_email' => $request->author_email,
            'question'     => $request->question,
            'status'       => 'PENDING',
        ]);

        return redirect()->route('questions.ask')
            ->with('success', 'Ваш вопрос отправлен! После ответа психолога он появится в разделе «Спросить психолога».');
    }

    /**
     * GET /questions — публичная лента Q&A
     */
    public function publicIndex()
    {
        $questions = PublicQuestion::answered()
            ->with(['answers.psychologist.psychologistProfile'])
            ->latest()
            ->take(30)
            ->get();

        return view('questions.index', compact('questions'));
    }

    /**
     * GET /psychologist/questions — список ожидающих ответа
     */
    public function psychologistIndex()
    {
        $questions = PublicQuestion::pending()->oldest()->get();

        return view('psychologist.questions', compact('questions'));
    }

    /**
     * POST /psychologist/questions/{question}/answer
     */
    public function answer(Request $request, PublicQuestion $question)
    {
        if ($question->status !== 'PENDING') {
            return redirect()->route('psychologist.questions')
                ->with('errors', ['Этот вопрос уже получил ответ']);
        }

        if (PublicAnswer::where('question_id', $question->id)->where('psychologist_id', auth()->id())->exists()) {
            return redirect()->route('psychologist.questions')
                ->with('errors', ['Вы уже ответили на этот вопрос']);
        }

        $request->validate([
            'answer' => 'required|string|min:20',
        ], [
            'answer.required' => 'Ответ должен содержать не менее 20 символов',
            'answer.min'      => 'Ответ должен содержать не менее 20 символов',
        ]);

        PublicAnswer::create([
            'question_id'     => $question->id,
            'psychologist_id' => auth()->id(),
            'answer'          => $request->answer,
        ]);

        $question->update(['status' => 'ANSWERED']);

        return redirect()->route('psychologist.questions')
            ->with('success', 'Ответ опубликован!');
    }
}
