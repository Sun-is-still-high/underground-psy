<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PsychologistProfile;
use App\Notifications\DiplomaApproved;
use App\Notifications\DiplomaRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    /**
     * GET /admin/verification — список психологов с загруженными дипломами
     */
    public function index()
    {
        $profiles = PsychologistProfile::with('user')
            ->whereNotNull('diploma_scan_url')
            ->orderByRaw('diploma_verified IS NULL DESC')
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
        $profile->update([
            'diploma_verified'           => true,
            'diploma_rejection_comment'  => null,
        ]);

        // Активировать только если email уже подтверждён
        if ($profile->user->hasVerifiedEmail()) {
            $profile->user->update(['status' => 'active']);
            $message = 'Диплом подтверждён, аккаунт активирован.';
        } else {
            $message = 'Диплом подтверждён. Аккаунт будет активирован после подтверждения email психологом.';
        }

        $profile->user->notify(new DiplomaApproved());

        return redirect()->route('admin.verification.index')
            ->with('success', $message);
    }

    /**
     * POST /admin/verification/{profile}/reject
     */
    public function reject(PsychologistProfile $profile, Request $request)
    {
        $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $profile->update([
            'diploma_verified'          => false,
            'diploma_rejection_comment' => $request->comment,
        ]);

        // Вернуть статус pending, если вдруг был active
        $profile->user->update(['status' => 'pending_verification']);

        $profile->user->notify(new DiplomaRejected($request->comment));

        return redirect()->route('admin.verification.index')
            ->with('success', 'Диплом отклонён. Психолог увидит причину и сможет загрузить повторно.');
    }

    /**
     * GET /admin/verification/{profile}/diploma — безопасная отдача файла диплома
     */
    public function showDiploma(PsychologistProfile $profile)
    {
        abort_unless($profile->diploma_scan_url, 404);

        return Storage::disk('local')->response($profile->diploma_scan_url);
    }
}
