<?php

namespace App\Support;

use Illuminate\Support\Str;

class CustomerIdentity
{
    public static function keyForOrder(array $data, ?int $orderId = null): string
    {
        $buyerType = Str::lower(trim((string) ($data['buyer_type'] ?? 'umum')));
        $phone = preg_replace('/\D+/', '', (string) ($data['phone'] ?? ''));
        $email = Str::lower(trim((string) ($data['email'] ?? '')));
        $organization = Str::lower(trim((string) (($data['outlet_name'] ?? null) ?: ($data['pbf_name'] ?? ''))));
        $name = Str::lower(trim((string) ($data['buyer_name'] ?? $data['requester_name'] ?? '')));

        if (!empty($data['user_id'])) {
            $identity = 'user:' . (int) $data['user_id'];
        } elseif ($phone !== '') {
            $identity = implode('|', [$buyerType, 'phone:' . $phone, $organization ?: $name]);
        } elseif ($email !== '') {
            $identity = implode('|', [$buyerType, 'email:' . $email]);
        } elseif ($organization !== '') {
            $identity = implode('|', [$buyerType, 'organization:' . $organization]);
        } elseif ($orderId !== null) {
            $identity = 'order:' . $orderId;
        } else {
            $identity = 'manual:' . Str::uuid();
        }

        return hash('sha256', $identity);
    }
}
