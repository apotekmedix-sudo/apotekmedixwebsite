<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ShoppingAccessCode extends Model
{
    public const CHANNEL_INTERNAL = 'internal';
    public const CHANNEL_B2B = 'b2b';

    protected $fillable = [
        'channel',
        'code_hash',
        'code_value',
        'generated_by',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function verify(string $code): bool
    {
        if ($this->expires_at?->isFuture() !== true) {
            return false;
        }

        $normalizedCode = strtoupper(trim($code));
        if ($this->code_value) {
            return hash_equals($this->readCode(), $normalizedCode);
        }

        return Hash::check($normalizedCode, $this->code_hash);
    }

    public function readCode(): ?string
    {
        if (!$this->code_value) {
            return null;
        }

        return Crypt::decryptString($this->code_value);
    }

    public function replaceCode(string $code): void
    {
        $normalizedCode = strtoupper(trim($code));
        $this->forceFill([
            'code_hash' => Hash::make($normalizedCode),
            'code_value' => Crypt::encryptString($normalizedCode),
            'expires_at' => now()->addDays((int) config('internal.code_validity_days', 30)),
            'generated_by' => auth()->id(),
        ])->save();
    }

    public static function matchesSession(string $channel, ?string $sessionHash): bool
    {
        if (!$sessionHash) {
            return false;
        }

        $accessCode = self::where('channel', $channel)->first();

        return $accessCode !== null
            && $accessCode->expires_at?->isFuture() === true
            && hash_equals((string) $accessCode->code_hash, $sessionHash);
    }

    public static function generate(string $channel): string
    {
        do {
            $code = (string) random_int(10000, 99999);
            $isDuplicate = self::query()->get()->contains(
                fn (self $existing) => $existing->readCode() === $code
            );
        } while ($isDuplicate);

        $accessCode = self::firstOrNew(['channel' => $channel]);
        $accessCode->replaceCode($code);

        return $code;
    }
}
