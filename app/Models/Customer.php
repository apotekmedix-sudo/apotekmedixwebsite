<?php

namespace App\Models;

use App\Support\CustomerIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'identity_key',
        'user_id',
        'buyer_type',
        'name',
        'requester_name',
        'email',
        'phone',
        'outlet_name',
        'pbf_name',
        'address',
        'kecamatan',
        'kota',
        'sia',
        'sipa',
        'no_izin_pbf',
        'apj',
        'sika',
        'last_order_at',
    ];

    protected $casts = [
        'last_order_at' => 'datetime',
    ];

    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(PurchaseHistory::class);
    }

    public static function identityKeyFor(array $data, ?int $orderId = null): string
    {
        return CustomerIdentity::keyForOrder($data, $orderId);
    }

    public static function syncFromOrder(PurchaseHistory $order): self
    {
        $data = $order->getAttributes();
        $data['email'] = $order->customer?->email;
        $identityKey = self::identityKeyFor($data, $order->id);
        $attributes = [
            'user_id' => $order->user_id,
            'buyer_type' => $order->buyer_type ?: 'umum',
            'name' => $order->buyer_name ?: $order->requester_name ?: 'Customer',
            'requester_name' => $order->requester_name,
            'email' => $data['email'],
            'phone' => $order->phone,
            'outlet_name' => $order->outlet_name,
            'pbf_name' => $order->pbf_name,
            'address' => $order->address,
            'kecamatan' => $order->kecamatan,
            'kota' => $order->kota,
            'sia' => $order->sia,
            'sipa' => $order->sipa,
            'no_izin_pbf' => $order->no_izin_pbf,
            'apj' => $order->apj,
            'sika' => $order->sika,
            'last_order_at' => $order->created_at,
        ];
        $customer = self::firstOrCreate(
            ['identity_key' => $identityKey],
            $attributes
        );
        $customer->fill($attributes);
        $customer->save();

        return $customer;
    }
}
