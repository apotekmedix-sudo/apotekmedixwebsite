<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProductOffer extends Model
{
    protected $fillable = [
        'supplier_id',
        'medicine_id',
        'supplier_code',
        'purchase_price',
        'minimum_quantity',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'minimum_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(PbfSupplier::class, 'supplier_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
