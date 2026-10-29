<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClotureCaisse extends Model
{
    protected $table = 'clotures_caisse';

    protected $fillable = [
        'pressing_id',
        'user_id',
        'date_cloture',
        'total_especes',
        'total_wave',
        'total_orange',
        'total_mtn',
        'total_carte',
        'total_general',
        'montant_physique_declare',
        'ecart',
        'commentaire',
    ];

    protected $casts = [
        'date_cloture' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}