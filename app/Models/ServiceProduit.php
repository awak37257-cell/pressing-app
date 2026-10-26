<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProduit extends Model
{
    protected $table = 'service_produit';

    protected $fillable = ['service_id', 'type_article_id', 'produit_id', 'quantite_par_article'];

    protected $casts = ['quantite_par_article' => 'float'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function typeArticle(): BelongsTo
    {
        return $this->belongsTo(TypeArticle::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class)->withTrashed();
    }
}
