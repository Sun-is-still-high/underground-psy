<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Request;
use App\Models\User;
use App\Models\PsychologistProfile;
use App\Models\ProblemType;

class PsychologistController extends Controller
{
    private PsychologistProfile $profileModel;
    private ProblemType $problemTypeModel;
    private User $userModel;

    public function __construct()
    {
        $this->profileModel = new PsychologistProfile();
        $this->problemTypeModel = new ProblemType();
        $this->userModel = new User();
    }

    /**
     * Публичный каталог психологов
     */
    public function index(): void
    {
        $request = new Request();

        $filters = [
            'problem_type_id' => $request->get('specialization') ? (int) $request->get('specialization') : null,
            'search' => $request->get('search'),
        ];

        $psychologists = $this->profileModel->getPublishedProfiles($filters);

        foreach ($psychologists as &$psy) {
            $psy['specializations'] = $this->profileModel->getSpecializations($psy['id']);
        }
        unset($psy);

        $problemTypes = $this->problemTypeModel->getActive();

        $this->view('psychologists/index', [
            'psychologists' => $psychologists,
            'problemTypes' => $problemTypes,
            'filters' => $filters,
        ]);
    }

    /**
     * Публичная страница профиля
     */
    public function show(int $id): void
    {
        $profile = $this->profileModel->getProfileWithDetails($id);

        if (!$profile) {
            Session::flash('errors', ['Профиль не найден']);
            $this->redirect('/psychologists');
            return;
        }

        $specializations = $this->profileModel->getSpecializations($id);
        $stats = $this->profileModel->getActivityStats($profile['user_id']);

        $this->view('psychologists/show', [
            'profile' => $profile,
            'specializations' => $specializations,
            'stats' => $stats,
        ]);
    }

    /**
     * Форма редактирования профиля (только для психологов)
     */
    public function editProfile(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $profile = $this->profileModel->findByUserId($user['id']);

        if (!$profile) {
            $profileId = $this->profileModel->create(['user_id' => $user['id']]);
            $profile = $this->profileModel->find($profileId);
        }

        $specializations = $this->profileModel->getSpecializations($profile['id']);
        $problemTypes = $this->problemTypeModel->getActive();

        $this->view('psychologist/profile-edit', [
            'user' => $user,
            'profile' => $profile,
            'specializations' => $specializations,
            'problemTypes' => $problemTypes,
        ]);
    }

    /**
     * Сохранение профиля
     */
    public function updateProfile(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $profile = $this->profileModel->findByUserId($user['id']);
        if (!$profile) {
            $this->redirect('/dashboard');
            return;
        }

        $request = new Request();

        $bio = trim($request->input('bio', ''));
        $methodsDescription = trim($request->input('methods_description', ''));
        $education = trim($request->input('education', ''));
        $experienceDescription = trim($request->input('experience_description', ''));
        $hourlyRateMin = $request->input('hourly_rate_min') !== '' ? (float) $request->input('hourly_rate_min') : null;
        $hourlyRateMax = $request->input('hourly_rate_max') !== '' ? (float) $request->input('hourly_rate_max') : null;
        $isPublished = $request->input('is_published') === '1' ? 1 : 0;

        $errors = [];

        if ($isPublished && empty($bio)) {
            $errors[] = 'Для публикации профиля необходимо заполнить поле "О себе"';
        }
        if ($hourlyRateMin !== null && $hourlyRateMax !== null && $hourlyRateMin > $hourlyRateMax) {
            $errors[] = 'Минимальная ставка не может быть больше максимальной';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->post());
            $this->redirect('/psychologist/profile/edit');
            return;
        }

        $this->profileModel->update($profile['id'], [
            'bio' => $bio,
            'methods_description' => $methodsDescription,
            'education' => $education,
            'experience_description' => $experienceDescription,
            'hourly_rate_min' => $hourlyRateMin,
            'hourly_rate_max' => $hourlyRateMax,
            'is_published' => $isPublished,
        ]);

        $specializations = $request->input('specializations');
        if (is_array($specializations)) {
            $this->profileModel->saveSpecializations($profile['id'], $specializations);
        } else {
            $this->profileModel->saveSpecializations($profile['id'], []);
        }

        Session::flash('success', 'Профиль успешно обновлён!');
        $this->redirect('/psychologist/profile/edit');
    }

    private function getUser(): array
    {
        $user = $this->userModel->getUserById(Session::userId());
        if (!$user) {
            Session::logout();
            $this->redirect('/login');
            exit;
        }
        return $user;
    }
}
