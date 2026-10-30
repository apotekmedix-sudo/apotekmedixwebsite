<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'whatsapp_phone',
        'specialty',
        'photo_path',
        'bio',
        'education',
        'practice_place',
        'practice_city',
        'str_number',
        'str_verified',
        'experience_years',
        'consultation_price',
        'session_duration_minutes',
        'is_online',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'str_verified' => 'boolean',
            'is_online' => 'boolean',
            'is_active' => 'boolean',
            'experience_years' => 'integer',
            'consultation_price' => 'integer',
            'session_duration_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(DoctorConsultation::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
