<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'apartment',
        'city',
        'province',
        'postal_code',
        'is_default_address',
        'is_cashier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_default_address' => 'boolean',
            'is_cashier' => 'boolean',
        ];
    }

    // Methods untuk cek role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUser()
    {
        return $this->role === 'user' && !$this->is_cashier && !$this->isDoctor();
    }

    public function isCashier(): bool
    {
        return $this->role === 'user' && $this->is_cashier;
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function isDoctor(): bool
    {
        return $this->doctorProfile()
            ->where('is_active', true)
            ->where('str_verified', true)
            ->exists();
    }

    public function purchaseHistories(): HasMany
    {
        return $this->hasMany(PurchaseHistory::class);
    }
}
