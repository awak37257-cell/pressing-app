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
    use BelongsToPressing, HasFactory, SoftDeletes;

    protected $fillable = [
        'pressing_id',
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'email',
        'observations',
        'points_fidelite',
        'reste_fidelite_fcfa',
    ];

    protected function casts(): array
    {
        return [
            'points_fidelite' => 'integer',
            'reste_fidelite_fcfa' => 'integer',
        ];
    }

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
