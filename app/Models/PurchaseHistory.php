<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    protected $fillable = [
        'buyer_type',
        'user_id',
        'customer_id',
        'buyer_name',
        'requester_name',
        'outlet_name',
        'pbf_name',
        'pbf_address',
        'pbf_phone',
        'purchase_order_type',
        'purchase_order_profile',
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
        'discount_code',
        'discount_code_amount',
        'consultation_prescription_number',
        'approval_status',
        'payment_method',
        'payment_qris_image_path',
        'payment_bank_name',
        'payment_account_number',
        'payment_account_holder',
        'payment_instructions',
        'payment_term_days',
        'transaction_channel',
        'price_channel',
        'is_pbf_purchase_order',
        'stock_committed',
        'payment_status',
        'receipt_status',
        'cashier_id',
        'paid_at',
        'verified_at',
        'printed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'integer',
        'original_total' => 'integer',
        'discounted_total' => 'integer',
        'discount_code_amount' => 'integer',
        'payment_term_days' => 'integer',
        'is_pbf_purchase_order' => 'boolean',
        'stock_committed' => 'boolean',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'printed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'effective_total',
        'approval_label',
        'due_date_formatted',
        'tempo_due_status',
        'tempo_due_label',
        'created_at_wib',
        'verified_at_wib',
        'paid_at_wib',
        'printed_at_wib',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customerProfile()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function getEffectiveTotalAttribute(): int
    {
        $discounted = (int) ($this->attributes['discounted_total'] ?? 0);
        $original = (int) ($this->attributes['original_total'] ?? 0);
        $storedTotal = (int) ($this->attributes['total'] ?? 0);

        if (
            ($this->approval_status ?? 'pending') === 'approved'
            || in_array($this->receipt_status ?? 'pending', ['verified', 'printed'], true)
            || !empty($this->attributes['discount_code'])
        ) {
            return $discounted > 0 ? $discounted : ($storedTotal > 0 ? $storedTotal : $original);
        }

        return $discounted > 0 ? $discounted : ($original > 0 ? $original : $storedTotal);
    }

    public function getApprovalLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'Setuju',
            'rejected' => 'Tidak',
            default => 'Belum diproses',
        };
    }

    public function isTempoPayment(): bool
    {
        return strcasecmp(trim((string) ($this->attributes['payment_method'] ?? '')), 'Tempo') === 0;
    }

    public function getDueAtAttribute(): ?\Illuminate\Support\Carbon
    {
        if (!$this->isTempoPayment() || !$this->created_at) {
            return null;
        }
        $days = max(1, (int) ($this->attributes['payment_term_days'] ?? 0) ?: 14);
        return $this->created_at->copy()->timezone('Asia/Jakarta')->addDays($days)->endOfDay();
    }

    public function getDueDateFormattedAttribute(): ?string
    {
        return $this->due_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y');
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_at) {
            return null;
        }
        $today = now()->timezone('Asia/Jakarta')->startOfDay();
        $dueDay = $this->due_at->copy()->timezone('Asia/Jakarta')->startOfDay();
        return (int) $today->diffInDays($dueDay, false);
    }

    public function getTempoDueStatusAttribute(): ?string
    {
        if (!$this->isTempoPayment()) {
            return null;
        }
        if (($this->attributes['payment_status'] ?? '') === 'paid') {
            return 'paid';
        }
        if (($this->attributes['receipt_status'] ?? '') === 'cancelled' || ($this->attributes['approval_status'] ?? '') === 'rejected') {
            return 'cancelled';
        }
        $days = $this->days_until_due;
        if ($days === null) {
            return null;
        }
        if ($days < 0) {
            return 'overdue';
        }
        if ($days === 0) {
            return 'due_today';
        }
        if ($days <= 3) {
            return 'due_soon';
        }
        return 'upcoming';
    }

    public function getTempoDueLabelAttribute(): ?string
    {
        $status = $this->tempo_due_status;
        $days = $this->days_until_due;
        return match ($status) {
            'paid' => 'Sudah Lunas',
            'cancelled' => 'Dibatalkan',
            'overdue' => 'Lewat ' . abs((int) $days) . ' hari',
            'due_today' => 'Jatuh tempo hari ini!',
            'due_soon' => 'Sisa ' . (int) $days . ' hari lagi',
            'upcoming' => 'Sisa ' . (int) $days . ' hari',
            default => null,
        };
    }

    public function getCreatedAtWibAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : null;
    }

    public function getVerifiedAtWibAttribute(): ?string
    {
        return $this->verified_at ? $this->verified_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : null;
    }

    public function getPaidAtWibAttribute(): ?string
    {
        return $this->paid_at ? $this->paid_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : null;
    }

    public function getPrintedAtWibAttribute(): ?string
    {
        return $this->printed_at ? $this->printed_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s') . ' WIB' : null;
    }
}
