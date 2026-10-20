<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleDepot extends Model
{
    use HasFactory;

    protected $table = 'articles_depot';

    protected $fillable = [
        'depot_id',
        'type_article_id',
        'service_id',
        'quantite',
        'prix_unitaire',
        'couleur',
        'marque',
        'etat_constate',
        'statut',
        'observation',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:0',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function typeArticle(): BelongsTo
    {
        return $this->belongsTo(TypeArticle::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}