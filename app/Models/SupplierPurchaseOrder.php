<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'supplier_name',
        'supplier_address',
        'supplier_phone',
        'order_number',
        'status',
        'ordered_at',
        'purchase_order_type',
        'purchase_order_profile',
        'requester_name',
        'apj',
        'sia',
        'sipa',
        'notes',
        'created_by',
        'received_at',
    ];

    protected $casts = [
        'ordered_at' => 'date',
        'received_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(PbfSupplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierPurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
