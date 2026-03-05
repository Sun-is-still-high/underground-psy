<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PsychologistProfile;

class VerificationController extends Controller
{
    /**
     * GET /admin/verification — список психологов с загруженными дипломами
     */
    public function index()
    {
        $profiles = PsychologistProfile::with('user')
            ->whereNotNull('diploma_scan_url')
            ->orderBy('diploma_verified')
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.verification.index', compact('profiles'));
    }

    /**
     * POST /admin/verification/{profile}/approve
     */
    public function approve(PsychologistProfile $profile)
    {
        $profile->update(['diploma_verified' => true]);

        return redirect()->route('admin.verification.index')
            ->with('success', 'Диплом подтверждён');
    }

    /**
     * POST /admin/verification/{profile}/reject
     */
    public function reject(PsychologistProfile $profile)
    {
        $profile->update([
            'diploma_verified' => false,
            'diploma_scan_url' => null,
        ]);

        return redirect()->route('admin.verification.index')
            ->with('success', 'Диплом отклонён, скан удалён. Психолог сможет загрузить повторно.');
    }
}
