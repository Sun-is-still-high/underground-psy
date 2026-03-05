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
            'search'          => $request->get('search'),
            'gender'          => $request->get('gender'),
            'work_format'     => $request->get('work_format'),
            'language'        => $request->get('language'),
            'price_max'       => $request->get('price_max') ? (float) $request->get('price_max') : null,
        ];

        $psychologists = $this->profileModel->getPublishedProfiles($filters);

        foreach ($psychologists as &$psy) {
            $psy['specializations'] = $this->profileModel->getSpecializations($psy['id']);
        }
        unset($psy);

        $problemTypes = $this->problemTypeModel->getActive();

        $this->view('psychologists/index', [
            'psychologists' => $psychologists,
            'problemTypes'  => $problemTypes,
            'filters'       => $filters,
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
        $stats           = $this->profileModel->getActivityStats($profile['user_id']);

        $this->view('psychologists/show', [
            'profile'         => $profile,
            'specializations' => $specializations,
            'stats'           => $stats,
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
            $profile   = $this->profileModel->find($profileId);
        }

        $specializations = $this->profileModel->getSpecializations($profile['id']);
        $problemTypes    = $this->problemTypeModel->getActive();
        $completeness    = $this->profileModel->getProfileCompleteness($profile, $specializations);
        $needsConfirm    = $this->profileModel->needsPriceConfirmation($profile['id']);

        $this->view('psychologist/profile-edit', [
            'user'            => $user,
            'profile'         => $profile,
            'specializations' => $specializations,
            'problemTypes'    => $problemTypes,
            'completeness'    => $completeness,
            'needsConfirm'    => $needsConfirm,
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

        $bio                = trim($request->input('bio', ''));
        $methodsDescription = trim($request->input('methods_description', ''));
        $education          = trim($request->input('education', ''));
        $experienceDesc     = trim($request->input('experience_description', ''));
        $hourlyRateMin      = $request->input('hourly_rate_min') !== '' ? (float) $request->input('hourly_rate_min') : null;
        $hourlyRateMax      = $request->input('hourly_rate_max') !== '' ? (float) $request->input('hourly_rate_max') : null;
        $isPublished        = $request->input('is_published') === '1' ? 1 : 0;
        $workFormat         = $request->input('work_format', 'ONLINE');
        $city               = trim($request->input('city', ''));
        $languages          = trim($request->input('languages', ''));
        $profileMode        = $request->input('profile_mode', 'FULL');

        // Целевая аудитория — массив чекбоксов
        $targetAudienceArr = $request->input('target_audience');
        $targetAudience    = is_array($targetAudienceArr) ? implode(',', $targetAudienceArr) : '';

        // Пол — обновляем в users
        $gender = $request->input('gender');
        if (in_array($gender, ['MALE', 'FEMALE'])) {
            $this->userModel->updateGender($user['id'], $gender);
        } elseif ($gender === '') {
            $this->userModel->updateGender($user['id'], null);
        }

        $errors = [];

        if ($isPublished && empty($bio)) {
            $errors[] = 'Для публикации профиля необходимо заполнить поле «О себе»';
        }
        if ($hourlyRateMin !== null && $hourlyRateMax !== null && $hourlyRateMin > $hourlyRateMax) {
            $errors[] = 'Минимальная ставка не может быть больше максимальной';
        }
        if (!in_array($workFormat, ['ONLINE', 'OFFLINE', 'BOTH'])) {
            $workFormat = 'ONLINE';
        }
        if ($workFormat !== 'ONLINE' && empty($city)) {
            $errors[] = 'Для офлайн-приёмов укажите город';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->post());
            $this->redirect('/psychologist/profile/edit');
            return;
        }

        // Обработка загрузки фото
        $photoUrl = $profile['photo_url'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            $uploaded = $this->uploadFile($_FILES['photo'], 'photos', ['image/jpeg', 'image/png', 'image/webp']);
            if ($uploaded) {
                $photoUrl = $uploaded;
            } else {
                Session::flash('errors', ['Не удалось загрузить фото. Допустимые форматы: JPG, PNG, WEBP. Максимум 5 МБ']);
                $this->redirect('/psychologist/profile/edit');
                return;
            }
        }

        $this->profileModel->update($profile['id'], [
            'bio'                    => $bio,
            'methods_description'    => $methodsDescription,
            'education'              => $education,
            'experience_description' => $experienceDesc,
            'hourly_rate_min'        => $hourlyRateMin,
            'hourly_rate_max'        => $hourlyRateMax,
            'is_published'           => $isPublished,
            'work_format'            => $workFormat,
            'city'                   => $city ?: null,
            'languages'              => $languages ?: null,
            'target_audience'        => $targetAudience ?: null,
            'photo_url'              => $photoUrl,
            'profile_mode'           => in_array($profileMode, ['FULL', 'REGISTRY']) ? $profileMode : 'FULL',
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

    /**
     * Загрузка скана диплома
     */
    public function uploadDiploma(): void
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

        if (empty($_FILES['diploma']['name'])) {
            Session::flash('errors', ['Файл диплома не выбран']);
            $this->redirect('/psychologist/profile/edit');
            return;
        }

        $uploaded = $this->uploadFile(
            $_FILES['diploma'],
            'diplomas/' . $user['id'],
            ['image/jpeg', 'image/png', 'application/pdf']
        );

        if (!$uploaded) {
            Session::flash('errors', ['Не удалось загрузить файл. Допустимые форматы: JPG, PNG, PDF. Максимум 10 МБ']);
            $this->redirect('/psychologist/profile/edit');
            return;
        }

        $this->profileModel->update($profile['id'], [
            'diploma_scan_url' => $uploaded,
            'diploma_verified' => 0,
        ]);

        Session::flash('success', 'Диплом загружен и отправлен на проверку администратору');
        $this->redirect('/psychologist/profile/edit');
    }

    /**
     * Подтверждение актуальности цен
     */
    public function confirmPrice(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $profile = $this->profileModel->findByUserId($user['id']);
        if ($profile) {
            $this->profileModel->confirmPrice($profile['id']);
            Session::flash('success', 'Актуальность цен подтверждена!');
        }

        $this->redirect('/psychologist/profile/edit');
    }

    /**
     * Загрузка файла в storage/
     * Возвращает относительный URL или null при ошибке
     */
    private function uploadFile(array $file, string $subdir, array $allowedMimes): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedMimes)) {
            return null;
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('', true) . '.' . $ext;
        $dir      = __DIR__ . '/../../storage/' . $subdir;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return null;
        }

        return '/storage/' . $subdir . '/' . $filename;
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
