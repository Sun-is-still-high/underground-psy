<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiplomaController extends Controller
{
    /**
     * POST /psychologist/diploma/reupload
     * Повторная загрузка диплома после отклонения.
     */
    public function reupload(Request $request)
    {
        $request->validate([
            'diploma_scan'        => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'diploma_number'      => ['required', 'string', 'max:100'],
            'diploma_year'        => ['required', 'integer', 'min:1950', 'max:' . date('Y')],
            'diploma_institution' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $profile = $user->psychologistProfile;

        if (!$profile || !$profile->diploma_rejection_comment) {
            return redirect()->route('dashboard');
        }

        $path = $request->file('diploma_scan')->store("private/diplomas/{$user->id}", 'local');

        $profile->update([
            'diploma_scan_url'          => $path,
            'diploma_number'            => $request->diploma_number,
            'diploma_year'              => $request->diploma_year,
            'diploma_institution'       => $request->diploma_institution,
            'diploma_verified'          => false,
            'diploma_rejection_comment' => null,
        ]);

        $user->update(['status' => 'pending_verification']);

        return redirect()->route('dashboard')
            ->with('success', 'Документы отправлены на повторную проверку.');
    }
}
