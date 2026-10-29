<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeController extends Controller
{
    // 1. Liste des employés
    public function index()
    {
        $employes = User::where('pressing_id', auth()->user()->pressing_id)
            ->latest()
            ->paginate(10);

        return view('employes.index', compact('employes'));
    }

    // 2. Formulaire de création
    public function create()
    {
        return view('employes.create');
    }

    // 3. Enregistrement d'un nouvel employé
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['gerant', 'caissier', 'atelier'])],
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'pressing_id' => auth()->user()->pressing_id,
            'nom' => $request->nom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'doit_changer_mot_de_passe' => true,
        ]);

        return redirect()->route('employes.index')
            ->with('success', 'Le compte employé a été créé avec succès.');
    }

    // 4. Formulaire de modification
    public function edit(User $employe)
    {
        return view('employes.edit', compact('employe'));
    }

    // 5. Mise à jour de l'employé
    public function update(Request $request, User $employe)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($employe->id)],
            'telephone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['gerant', 'caissier', 'atelier'])],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $employe->nom = $request->nom;
        $employe->email = $request->email;
        $employe->telephone = $request->telephone;
        $employe->role = $request->role;

        // Mise à jour du mot de passe uniquement s'il est renseigné
        if ($request->filled('password')) {
            $employe->password = Hash::make($request->password);
        }

        $employe->save();

        return redirect()->route('employes.index')
            ->with('success', 'Le compte de ' . $employe->nom . ' a été mis à jour.');
    }

    // 6. Suppression de l'employé
    public function destroy(User $employe)
    {
        // Empêcher la suppression de son propre compte
        if ($employe->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte admin.');
        }

        $employe->delete();

        return redirect()->route('employes.index')
            ->with('success', 'Le compte employé a été supprimé.');
    }
}