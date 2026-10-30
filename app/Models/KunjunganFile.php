<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganFile extends Model
{
    protected $fillable = [
        'original_name',
        'path',
        'size_bytes',
        'mime_type',
    ];
}
