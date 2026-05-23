<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'slug', 'category', 'mst_label_id', 'unit', 'price', 'rating', 'image', 'stock', 'is_active', 'is_new', 'is_sale', 'is_best_seller'])]
class Product extends Model
{
    use HasFactory;

    public function mstLabel(): BelongsTo
    {
        return $this->belongsTo(MstLabel::class, 'mst_label_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(ProductStockMovement::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'rating' => 'decimal:1',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'is_sale' => 'boolean',
            'is_best_seller' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        $value = trim((string) ($this->image ?? ''));
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/products/')) {
            return url('/product-media/' . ltrim(Str::after($value, '/storage/'), '/'));
        }

        if (str_starts_with($value, 'storage/products/')) {
            return url('/product-media/' . ltrim(Str::after($value, 'storage/'), '/'));
        }

        return url('/product-media/' . ltrim($value, '/'));
    }

    public function getPriceFormattedAttribute(): string
    {
        $n = (int) ($this->price ?? 0);
        return 'Rp ' . number_format(max(0, $n), 0, ',', '.');
    }
}
