<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pressing_id',
        'categorie_produit_id',
        'nom',
        'unite',
        'stock_actuel',
        'seuil_alerte',
        'prix_achat_moyen',
    ];

    protected $casts = [
        'stock_actuel' => 'decimal:2',
        'seuil_alerte' => 'decimal:2',
        'prix_achat_moyen' => 'decimal:0',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieProduit::class, 'categorie_produit_id');
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }
}