<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) ($this->id ?? 0),
            'title' => (string) ($this->title ?? ''),
            'slug' => (string) ($this->slug ?? ''),
            'category' => (string) ($this->category ?? ''),
            'unit' => (string) ($this->unit ?? 'Pieces'),
            'price_formatted' => (string) ($this->price_formatted ?? ''),
            'rating' => (float) ($this->rating ?? 0),
            'image_url' => $this->image_url,
            'stock' => (int) ($this->stock ?? 0),
            'is_new' => (bool) ($this->is_new ?? false),
            'is_sale' => (bool) ($this->is_sale ?? false),
            'is_sold' => ((int) ($this->stock ?? 0)) <= 0,
        ];
    }
}
