<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    use HasFactory;

    protected $table = 'mouvements_stock';
    public $timestamps = false;

    protected $fillable = [
        'pressing_id',
        'produit_id',
        'type',
        'quantite',
        'stock_apres',
        'motif',
        'achat_id',
        'user_id',
        'date_mouvement',
    ];

    protected $casts = [
        'date_mouvement' => 'datetime',
        'quantite' => 'decimal:2',
        'stock_apres' => 'decimal:2',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function achat(): BelongsTo
    {
        return $this->belongsTo(Achat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}