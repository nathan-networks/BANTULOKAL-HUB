<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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
        'final_rental_amount',
        'final_deposit_amount',
        'payment_status',
        'payment_reference',
        'paid_at',
        'payment_verified_by',
        'invoice_number',
        'acknowledgement_sent_at',
        'payment_instructions_sent_at',
        'payment_confirmation_sent_at',
        'approved_at',
        'rejected_at',
        'handover_at',
        'returned_at',
        'closed_at',
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
            'final_rental_amount' => 'decimal:2',
            'final_deposit_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'acknowledgement_sent_at' => 'datetime',
            'payment_instructions_sent_at' => 'datetime',
            'payment_confirmation_sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'handover_at' => 'datetime',
            'returned_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function paymentVerifier()
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

}
