<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClotureCaisse extends Model
{
    use HasFactory, BelongsToPressing;

    protected $table = 'clotures_caisse';

    protected $fillable = [
        'pressing_id',
        'user_id',
        'date',
        'fond_ouverture',
        'total_encaisse',
        'total_depenses',
        'solde_theorique',
        'solde_reel',
        'ecart',
    ];

    protected $casts = [
        'date' => 'date',
        'fond_ouverture' => 'decimal:0',
        'total_encaisse' => 'decimal:0',
        'total_depenses' => 'decimal:0',
        'solde_theorique' => 'decimal:0',
        'solde_reel' => 'decimal:0',
        'ecart' => 'decimal:0',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}