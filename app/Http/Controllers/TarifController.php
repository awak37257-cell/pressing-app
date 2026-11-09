<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Tarif;
use App\Models\TypeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'service_id' => ['required', Rule::exists('services', 'id')->where('pressing_id', $pressingId)],
            'type_article_id' => ['required', Rule::exists('types_articles', 'id')->where('pressing_id', $pressingId)],
            'prix' => 'required|integer|min:0',
            'prix_express' => 'nullable|integer|min:0',
        ]);

        // Upsert : update if exists, create otherwise
        Tarif::updateOrCreate(
            ['service_id' => $data['service_id'], 'type_article_id' => $data['type_article_id']],
            ['prix' => $data['prix'], 'prix_express' => $data['prix_express'] ?? 0]
        );

        return redirect()->route('services.index')->with('success', 'Tarif enregistré.');
    }

    public function preRemplirPourTests(): RedirectResponse
    {
        $pressingId = $this->pressingId();
        $typesArticles = TypeArticle::where('pressing_id', $pressingId)->orderBy('id')->get();
        $services = Service::where('pressing_id', $pressingId)->orderBy('id')->get();

        if ($typesArticles->isEmpty() || $services->isEmpty()) {
            return redirect()->route('services.index')
                ->with('error', 'Ajoutez au moins un type d’article et un service avant de préremplir les tarifs.');
        }

        DB::transaction(function () use ($typesArticles, $services): void {
            foreach ($services as $service) {
                foreach ($typesArticles as $typeArticle) {
                    $prix = $this->prixTest($typeArticle->nom, $service->nom);

                    Tarif::updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'type_article_id' => $typeArticle->id,
                        ],
                        [
                            'prix' => $prix,
                            'prix_express' => (int) (ceil($prix * 0.25 / 100) * 100),
                        ],
                    );
                }
            }
        });

        return redirect()->route('services.index')->with(
            'success',
            'Les tarifs de test ont été appliqués à tous les articles et services. Vous pourrez les modifier individuellement.',
        );
    }

    public function update(Request $request, Tarif $tarif): RedirectResponse
    {
        $this->authorizeTarifPressing($tarif);

        $data = $request->validate([
            'prix' => 'required|integer|min:0',
            'prix_express' => 'nullable|integer|min:0',
        ]);

        $tarif->update([
            'prix' => $data['prix'],
            'prix_express' => $data['prix_express'] ?? 0,
        ]);

        return redirect()->route('services.index')->with('success', 'Tarif mis à jour.');
    }

    public function destroy(Tarif $tarif): RedirectResponse
    {
        $this->authorizeTarifPressing($tarif);
        $tarif->delete();

        return redirect()->route('services.index')->with('success', 'Tarif supprimé.');
    }

    private function authorizeTarifPressing(Tarif $tarif): void
    {
        abort_unless(
            (int) $tarif->service()->value('pressing_id') === $this->pressingId()
            && (int) $tarif->typeArticle()->value('pressing_id') === $this->pressingId(),
            404,
        );
    }

    private function prixTest(string $nomArticle, string $nomService): int
    {
        $article = Str::lower($nomArticle);
        $service = Str::lower($nomService);

        $prixBase = match (true) {
            Str::contains($article, ['t-shirt', 'tee-shirt', 'polo']) => 800,
            Str::contains($article, ['chemise', 'pantalon', 'jupe']) => 1500,
            Str::contains($article, ['robe', 'costume']) => 2000,
            Str::contains($article, ['veste', 'manteau', 'couverture']) => 2500,
            default => 1200,
        };

        $multiplicateur = match (true) {
            Str::contains($service, 'repass') => 0.6,
            Str::contains($service, ['nettoy', 'sec']) => 1.5,
            Str::contains($service, 'teint') => 2,
            default => 1,
        };

        return (int) (round(($prixBase * $multiplicateur) / 100) * 100);
    }
}
