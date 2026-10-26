<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotConsommation extends Model
{
    protected $table = 'depot_consommations';

    protected $fillable = ['depot_id', 'produit_id', 'mouvement_stock_id', 'user_id', 'quantite', 'cout_unitaire'];

    protected $casts = ['quantite' => 'float', 'cout_unitaire' => 'float'];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCoutAttribute(): float
    {
        return round($this->quantite * $this->cout_unitaire, 2);
    }
}
