<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    protected $fillable = [
        'buyer_type',
        'user_id',
        'buyer_name',
        'requester_name',
        'outlet_name',
        'pbf_name',
        'pbf_address',
        'phone',
        'address',
        'kecamatan',
        'kota',
        'sia',
        'sipa',
        'no_izin_pbf',
        'apj',
        'sika',
        'items',
        'total',
        'original_total',
        'discounted_total',
        'approval_status',
        'payment_method',
        'payment_term_days',
        'transaction_channel',
        'payment_status',
        'cashier_id',
        'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'integer',
        'original_total' => 'integer',
        'discounted_total' => 'integer',
        'payment_term_days' => 'integer',
        'paid_at' => 'datetime',
    ];

    protected $appends = ['effective_total', 'approval_label'];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEffectiveTotalAttribute(): int
    {
        $discounted = (int) ($this->attributes['discounted_total'] ?? 0);
        $original = (int) ($this->attributes['original_total'] ?? 0);
        $storedTotal = (int) ($this->attributes['total'] ?? 0);

        if (($this->approval_status ?? 'pending') === 'approved') {
            return $discounted > 0 ? $discounted : ($storedTotal > 0 ? $storedTotal : $original);
        }

        return $original > 0 ? $original : $storedTotal;
    }

    public function getApprovalLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'Setuju',
            'rejected' => 'Tidak',
            default => 'Belum diproses',
        };
    }
}
