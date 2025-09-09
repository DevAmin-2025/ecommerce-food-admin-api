<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = [];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getIsSaleAttribute(): bool
    {
        return $this->sale_price > 0
            & $this->date_on_sale_from < Carbon::now()
            & $this->date_on_sale_to > Carbon::now();
    }
}
