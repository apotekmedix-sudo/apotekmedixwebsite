<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineStockMovement extends Model
{
    protected $fillable = [
        'movement_type',
        'physical_delta',
        'reserved_delta',
        'physical_before',
        'physical_after',
        'reference_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'physical_delta' => 'integer',
        'reserved_delta' => 'integer',
        'physical_before' => 'integer',
        'physical_after' => 'integer',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
