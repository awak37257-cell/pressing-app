<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pressing_id',
        'numero_ticket',
        'client_id',
        'user_id',
        'date_depot',
        'date_retrait_prevue',
        'date_retrait_reelle',
        'statut',
        'express',
        'montant_total',
        'remise',
        'montant_paye',
        'reste_a_payer',
        'observations',
    ];

    protected $casts = [
        'date_depot' => 'datetime',
        'date_retrait_prevue' => 'date',
        'date_retrait_reelle' => 'datetime',
        'express' => 'boolean',
        'montant_total' => 'decimal:0',
        'remise' => 'decimal:0',
        'montant_paye' => 'decimal:0',
        'reste_a_payer' => 'decimal:0',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(ArticleDepot::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}