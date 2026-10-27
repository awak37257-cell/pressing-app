<?php

namespace App\Http\Controllers;

use App\Models\Pressing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class PressingsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
        ];
    }

    public function index(): View
    {
        $pressings = Pressing::withCount('users')->orderBy('nom')->get();

        return view('pressings.index', compact('pressings'));
    }

    public function create(): View
    {
        return view('pressings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'adresse'   => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:30',
        ]);

        Pressing::create($data);

        return redirect()->route('pressings.index')->with('success', 'Pressing créé avec succès.');
    }

    public function edit(Pressing $pressing): View
    {
        return view('pressings.edit', compact('pressing'));
    }

    public function update(Request $request, Pressing $pressing): RedirectResponse
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'adresse'   => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:30',
        ]);

        $pressing->update($data);

        return redirect()->route('pressings.index')->with('success', 'Pressing mis à jour.');
    }

    public function destroy(Pressing $pressing): RedirectResponse
    {
        if ($pressing->users()->exists()) {
            return redirect()->route('pressings.index')
                ->with('error', 'Impossible de supprimer ce pressing : des utilisateurs y sont rattachés.');
        }

        $pressing->delete();

        return redirect()->route('pressings.index')->with('success', 'Pressing supprimé.');
    }
}
