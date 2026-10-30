<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePaymentSetting extends Model
{
    protected $fillable = [
        'qris_image_path',
        'bank_name',
        'account_number',
        'account_holder',
        'payment_instructions',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function getQrisImageUrlAttribute(): ?string
    {
        return $this->qris_image_path ? asset('storage/' . $this->qris_image_path) : null;
    }

    public function hasQris(): bool
    {
        return filled($this->qris_image_path);
    }

    public function hasBankTransfer(): bool
    {
        return filled($this->bank_name)
            && filled($this->account_number)
            && filled($this->account_holder);
    }
}
