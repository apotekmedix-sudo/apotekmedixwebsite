<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'is_unlimited',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'is_unlimited' => 'boolean',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_unlimited) {
            return true;
        }

        return $this->usage_limit !== null && $this->used_count < $this->usage_limit;
    }

    public function remainingQuota(): ?int
    {
        if ($this->is_unlimited) {
            return null;
        }

        return max(0, (int) ($this->usage_limit ?? 0) - (int) $this->used_count);
    }

    public function calculateDiscount(int $subtotal): int
    {
        $subtotal = max(0, $subtotal);
        if ($subtotal <= 0) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            $pct = min(100, max(0, (int) $this->discount_value));
            return min($subtotal, (int) round(($subtotal * $pct) / 100));
        }

        return min($subtotal, max(0, (int) $this->discount_value));
    }

    public function getFormattedValueAttribute(): string
    {
        if ($this->discount_type === 'percent') {
            return $this->discount_value . '%';
        }

        return 'Rp ' . number_format((int) $this->discount_value, 0, ',', '.');
    }
}
