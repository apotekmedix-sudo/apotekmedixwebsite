<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorConsultation extends Model
{
    public const DURATION_MINUTES = 15;

    protected $fillable = [
        'doctor_profile_id',
        'patient_access_hash',
        'patient_room_token_hash',
        'doctor_room_token_hash',
        'patient_room_token_used_at',
        'doctor_room_token_used_at',
        'patient_name',
        'patient_birth_date',
        'patient_gender',
        'patient_phone',
        'patient_email',
        'guardian_name',
        'guardian_relationship',
        'height_cm',
        'weight_kg',
        'complaint',
        'drug_allergies',
        'requested_medicine',
        'amount',
        'session_duration_minutes',
        'prescription_number',
        'prescribed_items',
        'prescription_approved_at',
        'prescription_used_at',
        'payment_method',
        'payment_channel',
        'payment_proof_path',
        'payment_status',
        'status',
        'privacy_consent_at',
        'guardian_consent_at',
        'paid_at',
        'payment_verified_by_user_id',
        'payment_verified_at',
        'payment_due_at',
        'starts_at',
        'ends_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'patient_name' => 'encrypted',
            'patient_birth_date' => 'encrypted',
            'patient_gender' => 'encrypted',
            'patient_phone' => 'encrypted',
            'patient_email' => 'encrypted',
            'guardian_name' => 'encrypted',
            'guardian_relationship' => 'encrypted',
            'height_cm' => 'encrypted',
            'weight_kg' => 'encrypted',
            'complaint' => 'encrypted',
            'drug_allergies' => 'encrypted',
            'requested_medicine' => 'encrypted',
            'amount' => 'integer',
            'session_duration_minutes' => 'integer',
            'prescribed_items' => 'encrypted:array',
            'prescription_approved_at' => 'datetime',
            'prescription_used_at' => 'datetime',
            'privacy_consent_at' => 'datetime',
            'guardian_consent_at' => 'datetime',
            'paid_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'payment_due_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
            'patient_room_token_used_at' => 'datetime',
            'doctor_room_token_used_at' => 'datetime',
        ];
    }

    public function doctorProfile(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function paymentVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DoctorConsultationMessage::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
