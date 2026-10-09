<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCategoryPricing extends Model
{
    protected $fillable = [
        'category_id',
        'pricing_method',
        'base_price',
        'included_quantity',
        'included_unit',
        'extra_unit_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'included_quantity' => 'decimal:2',
            'extra_unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ServiceCategory::class,
            'category_id'
        );
    }
}