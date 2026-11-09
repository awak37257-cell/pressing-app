<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use BelongsToPressing, HasFactory, SoftDeletes;

    protected $fillable = [
        'pressing_id',
        'numero_ticket',
        'client_id',
        'user_id',
        'date_depot',
        'date_retrait_prevue',
        'date_retrait_reelle',
        'consommation_validee_at',
        'statut',
        'express',
        'montant_total',
        'remise',
        'points_fidelite_utilises',
        'montant_paye',
        'reste_a_payer',
        'observations',
    ];

    protected $casts = [
        'date_depot' => 'datetime',
        'date_retrait_prevue' => 'date',
        'date_retrait_reelle' => 'datetime',
        'consommation_validee_at' => 'datetime',
        'express' => 'boolean',
        'montant_total' => 'decimal:0',
        'remise' => 'decimal:0',
        'points_fidelite_utilises' => 'integer',
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

    public function historique(): HasMany
    {
        return $this->hasMany(DepotHistorique::class)->orderBy('created_at')->orderBy('id');
    }

    public function consommations(): HasMany
    {
        return $this->hasMany(DepotConsommation::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function estEnRetard(): bool
    {
        return in_array($this->statut, ['depose', 'en_traitement'], true)
            && $this->date_retrait_prevue !== null
            && $this->date_retrait_prevue->lt(today());
    }
}
