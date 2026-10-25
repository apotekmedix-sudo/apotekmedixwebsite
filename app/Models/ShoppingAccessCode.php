<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShoppingAccessCode extends Model
{
    public const CHANNEL_B2B = 'b2b';
    public const CHANNEL_B2C = 'b2c';

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

        return $this->matchesCode($code);
    }

    public function matchesCode(string $code): bool
    {
        $normalizedCode = strtoupper(trim($code));
        if ($this->code_hash) {
            return Hash::check($normalizedCode, $this->code_hash);
        }

        $storedCode = $this->readCode();

        return $storedCode !== null && hash_equals($storedCode, $normalizedCode);
    }

    public function readCode(): ?string
    {
        if (!$this->code_value) {
            return null;
        }

        try {
            return Crypt::decryptString($this->code_value);
        } catch (DecryptException $exception) {
            Log::warning('Shopping access code could not be decrypted; regenerate the code.', [
                'shopping_access_code_id' => $this->getKey(),
                'channel' => $this->channel,
                'exception' => $exception::class,
            ]);

            return null;
        }
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

    public static function hasActiveB2cSession(): bool
    {
        $expiresAt = (int) session('b2c_access_expires_at', 0);
        $codeHash = session('b2c_access_code_hash');

        if ($expiresAt <= now()->timestamp || !$codeHash) {
            return false;
        }

        return self::matchesSession(self::CHANNEL_B2C, $codeHash);
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
                fn (self $existing) => $existing->matchesCode($code)
            );
        } while ($isDuplicate);

        $accessCode = self::firstOrNew(['channel' => $channel]);
        $accessCode->replaceCode($code);

        return $code;
    }
}
