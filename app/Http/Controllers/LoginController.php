<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials + ['actif' => true])) {
            return back()->withErrors(['email' => 'Identifiants incorrects ou compte désactivé.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->doit_changer_mot_de_passe) {
            return redirect()->route('password.force.change');
        }

        if ($user->role === 'operateur') {
            return redirect()->route('traitement.index');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
