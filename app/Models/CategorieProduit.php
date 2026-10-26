<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieProduit extends Model
{
    use HasFactory, BelongsToPressing;

    protected $table = 'categories_produits';

    protected $fillable = [
        'pressing_id',
        'nom',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }
}