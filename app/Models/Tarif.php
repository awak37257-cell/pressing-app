<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarif extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_article_id',
        'service_id',
        'prix',
        'prix_express',
    ];

    protected $casts = [
        'prix' => 'decimal:0',
        'prix_express' => 'decimal:0',
    ];

    public function typeArticle(): BelongsTo
    {
        return $this->belongsTo(TypeArticle::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}