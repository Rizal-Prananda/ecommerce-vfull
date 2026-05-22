<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'delta',
        'stock_before',
        'stock_after',
        'source',
        'actor_user_id',
        'actor_pelanggan_id',
        'reference',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'delta' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'actor_user_id' => 'integer',
            'actor_pelanggan_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

