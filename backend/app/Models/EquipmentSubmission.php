<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentSubmission extends Model
{
    protected $fillable = [
        'owner_name',
        'whatsapp',
        'email',
        'equipment_name',
        'description',
        'category_id',
        'proposed_price_per_day',
        'proposed_deposit_amount',
        'location',
        'status',
        'admin_notes',
        'contacted_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'proposed_price_per_day' => 'decimal:2',
            'proposed_deposit_amount' => 'decimal:2',
            'contacted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
