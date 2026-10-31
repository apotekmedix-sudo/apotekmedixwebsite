<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ConsultationAccessCode extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_USED = 'used';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'code_hash',
        'code_value',
        'status',
        'doctor_consultation_id',
        'generated_by_user_id',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(DoctorConsultation::class, 'doctor_consultation_id');
    }

    public function readCode(): ?string
    {
        try {
            return Crypt::decryptString($this->code_value);
        } catch (DecryptException $exception) {
            Log::warning('Consultation access code could not be decrypted.', [
                'consultation_access_code_id' => $this->getKey(),
                'exception' => $exception::class,
            ]);

            return null;
        }
    }

    public static function createAvailable(int $userId): self
    {
        do {
            $code = '0' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $codeHash = DoctorConsultation::hashAccessCode($code);
        } while (self::query()->where('code_hash', $codeHash)->exists());

        return self::query()->create([
            'code_hash' => $codeHash,
            'code_value' => Crypt::encryptString($code),
            'status' => self::STATUS_AVAILABLE,
            'generated_by_user_id' => $userId,
        ]);
    }
}
