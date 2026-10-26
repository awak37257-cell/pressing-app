<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotHistorique extends Model
{
    protected $table = 'depot_historique';

    public $timestamps = false;

    protected $fillable = ['depot_id', 'user_id', 'statut', 'note', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
