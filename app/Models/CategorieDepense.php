<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieDepense extends Model
{
    use HasFactory;

    protected $table = 'categories_depenses';

    protected $fillable = [
        'pressing_id',
        'nom',
    ];

    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
}