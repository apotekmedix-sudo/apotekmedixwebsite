<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorConsultationMessage extends Model
{
    protected $fillable = [
        'doctor_consultation_id',
        'sender_type',
        'sender_user_id',
        'body',
    ];

    protected function casts(): array
    {
        return ['body' => 'encrypted'];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(DoctorConsultation::class, 'doctor_consultation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
