<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Tarif;
use App\Models\TypeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ServiceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,gerant'),
        ];
    }

    public function index(): View
    {
        $pressingId  = auth()->user()->pressing_id;
        $services    = Service::where('pressing_id', $pressingId)
            ->with(['tarifs.typeArticle'])
            ->orderBy('nom')
            ->get();
        $typesArticles = TypeArticle::where('pressing_id', $pressingId)->orderBy('nom')->get();

        return view('services.index', compact('services', 'typesArticles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $pressingId = auth()->user()->pressing_id;

        $data = $request->validate([
            'nom'         => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $data['pressing_id'] = $pressingId;

        Service::create($data);

        return redirect()->route('services.index')->with('success', 'Service ajouté avec succès.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $service->update($data);

        return redirect()->route('services.index')->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service supprimé.');
    }
}
