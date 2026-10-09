<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentOwner extends Model
{
    protected $fillable = [
        'name',
        'whatsapp',
        'email',
        'notes',
        'status',
    ];

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
