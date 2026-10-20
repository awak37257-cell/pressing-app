<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeArticle extends Model
{
    use HasFactory;

    protected $table = 'types_articles';

    protected $fillable = [
        'pressing_id',
        'nom',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }
}