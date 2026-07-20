<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    protected array $sortableColumns = [];

    public function scopeSort(Builder $query, ?string $sortBy, string $defaultSort = 'created_at', string $defaultDir = 'desc'): Builder
    {
        $direction = request('direction', $defaultDir);
        $column = in_array($sortBy, $this->sortableColumns) ? $sortBy : $defaultSort;
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : $defaultDir;

        return $query->orderBy($column, $direction);
    }
}
