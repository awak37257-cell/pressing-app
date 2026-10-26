<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToPressing;

    protected $fillable = [
        'pressing_id',
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'email',
        'observations',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function depots(): HasMany
    {
        return $this->hasMany(Depot::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}