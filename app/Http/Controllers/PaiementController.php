<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class PaiementController extends Controller implements HasMiddleware
{
    private const MODES = 'especes,orange_money,mtn_money,wave';

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,gerant'),
        ];
    }

    public function edit(Paiement $paiement): View
    {
        $paiement->load('depot.client');

        return view('paiements.edit', compact('paiement'));
    }

    public function update(Request $request, Paiement $paiement): RedirectResponse
    {
        $data = $request->validate([
            'montant'   => 'required|integer|min:1',
            'mode'      => 'required|in:' . self::MODES,
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $paiement->update($data);

        return redirect()->route('depots.show', $paiement->depot_id)
            ->with('success', 'Paiement modifié avec succès.');
    }
}
