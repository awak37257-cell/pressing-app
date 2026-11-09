<?php

namespace App\Http\Controllers;

use App\Models\ArticleDepot;
use App\Models\TypeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class TypeArticleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,gerant'),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $pressingId = $this->pressingId();
        $data = $request->validate([
            'nom_type_article' => [
                'required',
                'string',
                'max:50',
                Rule::unique('types_articles', 'nom')->where('pressing_id', $pressingId),
            ],
        ]);

        TypeArticle::create([
            'pressing_id' => $pressingId,
            'nom' => $data['nom_type_article'],
        ]);

        return redirect()->route('services.index')->with('success', 'Type d’article ajouté avec succès.');
    }

    public function update(Request $request, TypeArticle $typeArticle): RedirectResponse
    {
        $data = $request->validate([
            'nom_type_article' => [
                'required',
                'string',
                'max:50',
                Rule::unique('types_articles', 'nom')
                    ->where('pressing_id', $this->pressingId())
                    ->ignore($typeArticle->id),
            ],
        ]);

        $typeArticle->update(['nom' => $data['nom_type_article']]);

        return redirect()->route('services.index')->with('success', 'Type d’article mis à jour.');
    }

    public function destroy(TypeArticle $typeArticle): RedirectResponse
    {
        if (ArticleDepot::where('type_article_id', $typeArticle->id)->exists()) {
            return redirect()->route('services.index')
                ->with('error', 'Ce type d’article est utilisé dans un dépôt et ne peut pas être supprimé.');
        }

        $typeArticle->delete();

        return redirect()->route('services.index')->with('success', 'Type d’article supprimé.');
    }
}
