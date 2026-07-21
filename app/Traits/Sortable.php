<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    public function scopeSort(Builder $query, ?string $sortBy, string $defaultSort = 'created_at', string $defaultDir = 'desc'): Builder
    {
        $columns = property_exists($this, 'sortableColumns') ? $this->sortableColumns : [];
        $direction = request('direction', $defaultDir);
        $column = in_array($sortBy, $columns) ? $sortBy : $defaultSort;
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : $defaultDir;

        return $query->orderBy($column, $direction);
    }
}
