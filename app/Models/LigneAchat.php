<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneAchat extends Model
{
    use HasFactory;

    protected $table = 'lignes_achat';
    public $timestamps = false;

    protected $fillable = [
        'achat_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
    ];

    protected $casts = [
        'quantite' => 'float',
        'prix_unitaire' => 'float',
    ];

    public function achat(): BelongsTo
    {
        return $this->belongsTo(Achat::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}