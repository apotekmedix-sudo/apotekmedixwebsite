<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPurchaseOrderItem extends Model
{
    protected $fillable = [
        'supplier_purchase_order_id',
        'medicine_id',
        'supplier_product_offer_id',
        'product_name',
        'supplier_code',
        'active_ingredient',
        'dosage_form',
        'strength',
        'unit_label',
        'quantity',
        'received_quantity',
        'unit_cost',
        'line_total',
        'requested_discount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
        'requested_discount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SupplierPurchaseOrder::class, 'supplier_purchase_order_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
