<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PsychologistProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:CLIENT,PSYCHOLOGIST'],
            'gender'   => ['required', 'in:male,female,not_specified'],
        ];

        if ($request->role === 'PSYCHOLOGIST') {
            $rules['diploma_scan']        = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];
            $rules['diploma_number']      = ['required', 'string', 'max:100'];
            $rules['diploma_year']        = ['required', 'integer', 'min:1950', 'max:' . date('Y')];
            $rules['diploma_institution'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        $status = $request->role === 'PSYCHOLOGIST' ? 'pending_verification' : 'active';

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'gender'   => $request->gender,
            'status'   => $status,
        ]);

        // Загрузка диплома для психолога
        if ($request->role === 'PSYCHOLOGIST') {
            $path = $request->file('diploma_scan')->store("private/diplomas/{$user->id}", 'local');

            PsychologistProfile::create([
                'user_id'             => $user->id,
                'diploma_scan_url'    => $path,
                'diploma_number'      => $request->diploma_number,
                'diploma_year'        => $request->diploma_year,
                'diploma_institution' => $request->diploma_institution,
                'diploma_verified'    => false,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
