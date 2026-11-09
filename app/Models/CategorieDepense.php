<?php

namespace App\Models;

use App\Models\Concerns\BelongsToPressing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieDepense extends Model
{
    use BelongsToPressing, HasFactory;

    public const NOMS_PAR_DEFAUT = [
        'Électricité',
        'Eau',
        'Salaires',
        'Loyer',
        'Achats de produits',
        'Remboursement client',
    ];

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

    public static function initialiserPourPressing(Pressing $pressing): void
    {
        foreach (self::NOMS_PAR_DEFAUT as $nom) {
            self::withoutGlobalScope('pressing')->firstOrCreate([
                'pressing_id' => $pressing->id,
                'nom' => $nom,
            ]);
        }
    }
}
