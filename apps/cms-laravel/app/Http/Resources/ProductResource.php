<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSale = (bool) ($this->is_sale ?? false);
        $isNew = (bool) ($this->is_new ?? false);
        $isBestSeller = (bool) ($this->is_best_seller ?? false);

        $label = 'none';
        if ($isSale) {
            $label = 'promo';
        } elseif ($isBestSeller) {
            $label = 'best_seller';
        } elseif ($isNew) {
            $label = 'new';
        }

        return [
            'id' => (int) ($this->id ?? 0),
            'title' => (string) ($this->title ?? ''),
            'slug' => (string) ($this->slug ?? ''),
            'category' => (string) ($this->category ?? ''),
            'unit' => (string) ($this->unit ?? 'Pieces'),
            'price' => (int) ($this->price ?? 0),
            'price_formatted' => (string) ($this->price_formatted ?? ''),
            'rating' => (float) ($this->rating ?? 0),
            'image_url' => $this->image_url,
            'stock' => (int) ($this->stock ?? 0),
            'variants' => $this->variants
                ? $this->variants->map(fn($v) => [
                    'id' => (int) ($v->id ?? 0),
                    'sku' => $v->sku !== null ? (string) $v->sku : null,
                    'mst_size_id' => (int) ($v->mst_size_id ?? 0),
                    'size' => (string) ($v->mstSize?->code ?? $v->size ?? ''),
                    'color' => $v->color !== null ? (string) $v->color : null,
                    'stock' => (int) ($v->stock ?? 0),
                    'price' => $v->price !== null ? (int) $v->price : null,
                ])->values()
                : [],
            'is_new' => $isNew,
            'is_sale' => $isSale,
            'is_best_seller' => $isBestSeller,
            'label' => $label,
            'mst_label' => $this->mstLabel ? [
                'id' => (int) ($this->mstLabel->id ?? 0),
                'code' => (string) ($this->mstLabel->code ?? ''),
                'name' => (string) ($this->mstLabel->name ?? ''),
            ] : null,
            'is_sold' => ((int) ($this->stock ?? 0)) <= 0,
        ];
    }
}
