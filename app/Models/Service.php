<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory, BelongsToPressing;

    protected $fillable = [
        'pressing_id',
        'nom',
        'description',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function quantitesStandard(): HasMany
    {
        return $this->hasMany(ServiceProduit::class);
    }

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }
}