<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Tarif;
use App\Models\TypeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class TarifController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,gerant'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $pressingId = auth()->user()->pressing_id;

        $data = $request->validate([
            'service_id'      => ['required', Rule::exists('services', 'id')->where('pressing_id', $pressingId)],
            'type_article_id' => ['required', Rule::exists('types_articles', 'id')->where('pressing_id', $pressingId)],
            'prix'            => 'required|integer|min:0',
            'prix_express'    => 'nullable|integer|min:0',
        ]);

        // Upsert : update if exists, create otherwise
        Tarif::updateOrCreate(
            ['service_id' => $data['service_id'], 'type_article_id' => $data['type_article_id']],
            ['prix' => $data['prix'], 'prix_express' => $data['prix_express'] ?? 0]
        );

        return redirect()->route('services.index')->with('success', 'Tarif enregistré.');
    }

    public function update(Request $request, Tarif $tarif): RedirectResponse
    {
        $data = $request->validate([
            'prix'         => 'required|integer|min:0',
            'prix_express' => 'nullable|integer|min:0',
        ]);

        $tarif->update([
            'prix'         => $data['prix'],
            'prix_express' => $data['prix_express'] ?? 0,
        ]);

        return redirect()->route('services.index')->with('success', 'Tarif mis à jour.');
    }

    public function destroy(Tarif $tarif): RedirectResponse
    {
        $tarif->delete();

        return redirect()->route('services.index')->with('success', 'Tarif supprimé.');
    }
}
