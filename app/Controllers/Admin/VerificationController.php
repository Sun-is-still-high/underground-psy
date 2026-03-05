<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use Core\Request;
use Core\Database;
use App\Models\User;
use App\Models\PsychologistProfile;

class VerificationController extends Controller
{
    private User $userModel;
    private PsychologistProfile $profileModel;

    public function __construct()
    {
        $this->userModel   = new User();
        $this->profileModel = new PsychologistProfile();
    }

    /**
     * GET /admin/verification — список психологов с загруженными дипломами
     */
    public function index(): void
    {
        $this->requireAdmin();

        $rows = Database::query(
            "SELECT pp.id as profile_id, pp.diploma_scan_url, pp.diploma_verified,
                    u.id as user_id, u.name, u.email
             FROM psychologist_profiles pp
             JOIN users u ON pp.user_id = u.id
             WHERE pp.diploma_scan_url IS NOT NULL
             ORDER BY pp.diploma_verified ASC, pp.updated_at DESC"
        );

        $this->view('admin/verification/index', ['profiles' => $rows]);
    }

    /**
     * POST /admin/verification/{profileId}/approve
     */
    public function approve(int $profileId): void
    {
        $this->requireAdmin();

        Database::execute(
            "UPDATE psychologist_profiles SET diploma_verified = 1 WHERE id = :id",
            ['id' => $profileId]
        );

        Session::flash('success', 'Диплом подтверждён');
        $this->redirect('/admin/verification');
    }

    /**
     * POST /admin/verification/{profileId}/reject
     */
    public function reject(int $profileId): void
    {
        $this->requireAdmin();

        Database::execute(
            "UPDATE psychologist_profiles SET diploma_verified = 0, diploma_scan_url = NULL WHERE id = :id",
            ['id' => $profileId]
        );

        Session::flash('success', 'Диплом отклонён, скан удалён. Психолог сможет загрузить повторно.');
        $this->redirect('/admin/verification');
    }

}
