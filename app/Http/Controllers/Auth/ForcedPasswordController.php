<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcedPasswordController extends Controller
{
    public function edit()
    {
        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->doit_changer_mot_de_passe = false; // Désactivation du blocage
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }
}