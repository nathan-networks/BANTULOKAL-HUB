<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'equipment_id',
        'customer_name',
        'customer_email',
        'customer_whatsapp',
        'rental_start_date',
        'rental_end_date',
        'price_per_day',
        'total_amount',
        'status',
        'customer_notes',
        'admin_notes',
        'contacted_at',
        'handled_by',
        'terms_version',
        'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'rental_start_date' => 'date',
            'rental_end_date' => 'date',
            'price_per_day' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'contacted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
