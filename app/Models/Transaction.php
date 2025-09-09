<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    public function scopeGetChartData(Builder $query, int $month, int $status): void
    {
        $startDate = verta()->startMonth()->subMonths($month - 1)->toCarbon();
        $query->where('created_at', '>=', $startDate)->where('status', $status);
    }
}
