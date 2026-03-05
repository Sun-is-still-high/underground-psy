<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Request;
use App\Models\PublicQuestion;
use App\Models\User;

class QuestionController extends Controller
{
    private PublicQuestion $questionModel;

    public function __construct()
    {
        $this->questionModel = new PublicQuestion();
    }

    /**
     * GET /ask — публичная форма вопроса (без авторизации)
     */
    public function askForm(): void
    {
        $this->view('questions/ask');
    }

    /**
     * POST /ask — сохранить вопрос
     */
    public function askSubmit(): void
    {
        $request = new Request();

        $name     = trim($request->input('author_name', ''));
        $email    = trim($request->input('author_email', ''));
        $question = trim($request->input('question', ''));

        $errors = [];
        if (empty($name))     $errors[] = 'Укажите ваше имя';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Укажите корректный email (ответ придёт на него)';
        }
        if (empty($question) || mb_strlen($question) < 20) {
            $errors[] = 'Вопрос должен содержать не менее 20 символов';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->post());
            $this->redirect('/ask');
            return;
        }

        $this->questionModel->create([
            'author_name'  => $name,
            'author_email' => $email,
            'question'     => $question,
        ]);

        Session::flash('success', 'Ваш вопрос отправлен! После ответа психолога он появится в разделе «Спросить психолога».');
        $this->redirect('/ask');
    }

    /**
     * GET /questions — публичная лента Q&A
     */
    public function publicIndex(): void
    {
        $questions = $this->questionModel->getAnswered(30);
        $this->view('questions/index', ['questions' => $questions]);
    }

    /**
     * GET /psychologist/questions — список ожидающих ответа (только PSYCHOLOGIST)
     */
    public function psychologistIndex(): void
    {
        $this->requireAuth();
        $userModel = new User();
        $user = $userModel->getUserById(Session::userId());

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $pending = $this->questionModel->getPending();
        $this->view('psychologist/questions', ['questions' => $pending, 'user' => $user]);
    }

    /**
     * POST /psychologist/questions/{id}/answer — ответить на вопрос
     */
    public function answer(int $id): void
    {
        $this->requireAuth();
        $userModel = new User();
        $user = $userModel->getUserById(Session::userId());

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $question = $this->questionModel->find($id);
        if (!$question) {
            Session::flash('errors', ['Вопрос не найден']);
            $this->redirect('/psychologist/questions');
            return;
        }

        if ($this->questionModel->hasAnswered($id, $user['id'])) {
            Session::flash('errors', ['Вы уже ответили на этот вопрос']);
            $this->redirect('/psychologist/questions');
            return;
        }

        $request = new Request();
        $answer  = trim($request->input('answer', ''));

        if (mb_strlen($answer) < 20) {
            Session::flash('errors', ['Ответ должен содержать не менее 20 символов']);
            $this->redirect('/psychologist/questions');
            return;
        }

        $this->questionModel->addAnswer($id, $user['id'], $answer);

        Session::flash('success', 'Ответ опубликован!');
        $this->redirect('/psychologist/questions');
    }
}
