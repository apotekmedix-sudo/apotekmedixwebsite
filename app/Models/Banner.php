<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'judul',
        'subjudul',
        'gambar',
        'url_tujuan',
        'label_tombol',
        'aktif',
        'urutan',
    ];

    protected $casts = [
        'aktif'  => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope: hanya banner aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('id');
    }

    /**
     * URL gambar lengkap
     */
    public function getImageUrlAttribute(): string
    {
        return url('storage/' . $this->gambar);
    }
}
