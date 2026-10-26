<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use HasFactory, BelongsToPressing;

    protected $fillable = [
        'pressing_id',
        'categorie_depense_id',
        'user_id',
        'achat_id',
        'libelle',
        'montant',
        'date_depense',
        'mode_paiement',
    ];

    protected $casts = [
        'date_depense' => 'datetime',
        'montant' => 'decimal:0',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieDepense::class, 'categorie_depense_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achat(): BelongsTo
    {
        return $this->belongsTo(Achat::class);
    }
}