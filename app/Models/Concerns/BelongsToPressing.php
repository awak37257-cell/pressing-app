<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

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
}
