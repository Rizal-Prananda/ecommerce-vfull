<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'mst_size_id', 'sku', 'size', 'color', 'stock', 'price'])]
class ProductVariant extends Model
{
    public function setSizeAttribute($value): void
    {
        $this->attributes['size'] = strtoupper(trim((string) $value));
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'mst_size_id' => 'integer',
            'stock' => 'integer',
            'price' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function mstSize(): BelongsTo
    {
        return $this->belongsTo(MstSize::class, 'mst_size_id');
    }
}
