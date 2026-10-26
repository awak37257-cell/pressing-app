<?php

namespace App\Models\Concerns;

use App\Models\Pressing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToPressing
{
    protected static function bootBelongsToPressing(): void
    {
        static::addGlobalScope('pressing', function (Builder $query) {
            $pressingId = auth()->user()?->pressing_id;

            if ($pressingId) {
                $query->where($query->getModel()->getTable() . '.pressing_id', $pressingId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->pressing_id) && auth()->check()) {
                $model->pressing_id = auth()->user()->pressing_id;
            }
        });
    }

    /**
     * Relation avec le pressing.
     */
    public function pressing(): BelongsTo
    {
        return $this->belongsTo(Pressing::class);
    }
}