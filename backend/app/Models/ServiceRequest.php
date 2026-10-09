<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_whatsapp',
        'location',
        'category_id',
        'description',
        'worker_count',
        'preferred_date',
        'estimated_total',
        'final_total',
        'worker_pool_amount',
        'platform_amount',
        'status',
        'admin_notes',
        'reviewed_by',
        'terms_version',
        'terms_accepted_at',
        'payment_status',
        'payment_reference',
        'paid_at',
        'invoice_number',
        'acknowledgement_sent_at',
        'approved_at',
        'completed_at',
        'service_fee',
        'approved_expenses',
        'expense_notes',
        'payment_verified_by',
    ];

    protected function casts(): array
    {
        return [
            'worker_count' => 'integer',
            'preferred_date' => 'date',
            'estimated_total' => 'decimal:2',
            'final_total' => 'decimal:2',
            'worker_pool_amount' => 'decimal:2',
            'platform_amount' => 'decimal:2',
            'terms_accepted_at' => 'datetime',
            'paid_at' => 'datetime',
            'acknowledgement_sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'service_fee' => 'decimal:2',
            'approved_expenses' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ServiceCategory::class,
            'category_id'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'payment_verified_by'
        );
    }

}
