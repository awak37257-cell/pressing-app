<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    use HasFactory, BelongsToPressing;

    protected $fillable = [
        'pressing_id',
        'nom',
        'telephone',
        'adresse',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function achats(): HasMany
    {
        return $this->hasMany(Achat::class);
    }
}