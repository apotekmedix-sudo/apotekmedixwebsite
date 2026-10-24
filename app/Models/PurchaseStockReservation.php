<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseStockReservation extends Model
{
    protected $fillable = [
        'purchase_history_id',
        'medicine_id',
        'quantity_units',
        'sale_quantity',
    ];
}
