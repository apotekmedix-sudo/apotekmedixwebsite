<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicines';

    protected static function booted(): void
    {
        static::creating(function (Medicine $medicine): void {
            if (Schema::hasColumn('medicines', 'stock_base_units') && $medicine->stock_base_units === null) {
                $medicine->stock_base_units = (int) $medicine->stok * $medicine->units_per_box;
            }
        });

        static::updating(function (Medicine $medicine): void {
            $packagingChanged = $medicine->isDirty([
                'unit_packaging_enabled',
                'tablets_per_strip',
                'strips_per_box',
            ]);

            if (Schema::hasColumn('medicines', 'stock_base_units')
                && ($medicine->isDirty('stok') || $packagingChanged)
                && !$medicine->isDirty('stock_base_units')) {
                $medicine->stock_base_units = (int) $medicine->stok * $medicine->units_per_box;
            }
        });
    }

    protected $fillable = [
        'sku',
        'distributor',
        'nama_obat',
        'logo',
        'golongan',
        'satuan',
        'kemasan',
        'sediaan',
        'kelompok',
        'kategori',
        'brand',
        'kategori_produk',
        'harga',
        'harga_internal',
        'harga_modal',
        'harga_grosir',
        'harga_retail',
        'promo_internal_percent',
        'promo_b2b_percent',
        'promo_b2c_percent',
        'stok',
        'unit_packaging_enabled',
        'tablets_per_strip',
        'strips_per_box',
        'price_tablet',
        'price_strip',
        'price_box',
        'stock_base_units',
        'terjual',
        'grade',
        'deskripsi',
        'komposisi',
        'indikasi',
        'gambar',
        'gambar_tambahan',
    ];

    protected $casts = [
        'harga'   => 'decimal:2',
        'harga_internal' => 'decimal:2',
        'harga_modal'  => 'decimal:2',
        'harga_grosir' => 'decimal:2',
        'harga_retail' => 'decimal:2',
        'promo_internal_percent' => 'integer',
        'promo_b2b_percent' => 'integer',
        'promo_b2c_percent' => 'integer',
        'stok'    => 'integer',
        'unit_packaging_enabled' => 'boolean',
        'tablets_per_strip' => 'integer',
        'strips_per_box' => 'integer',
        'price_tablet' => 'decimal:2',
        'price_strip' => 'decimal:2',
        'price_box' => 'decimal:2',
        'stock_base_units' => 'integer',
        'terjual' => 'integer',
        'gambar_tambahan' => 'array',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->where('nama_obat', 'like', "%{$search}%")
                     ->orWhere('kategori', 'like', "%{$search}%")
                     ->orWhere('deskripsi', 'like', "%{$search}%");
    }

    public function scopeByCategory($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope: exclude produk PBF — dipakai di semua halaman publik
     * kecuali halaman /products-pbf
     */
    public function scopeNonPbf($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('kelompok')
              ->orWhere('kelompok', '!=', 'PBF');
        });
    }

    public function isAvailable(): bool
    {
        return (int) ($this->stock_base_units ?? $this->stok) > 0;
    }

    public function getUnitsPerBoxAttribute(): int
    {
        if (!$this->unit_packaging_enabled) {
            return 1;
        }

        return max(1, (int) $this->tablets_per_strip) * max(1, (int) $this->strips_per_box);
    }

    public function getPackageUnitLabelAttribute(): string
    {
        $label = trim((string) $this->satuan);
        if ($label === '') {
            $label = trim((string) ($this->sediaan_label ?: 'Pcs'));
        }

        return mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');
    }

    public function getStockPackagesAttribute(): float
    {
        $stockUnits = (int) ($this->stock_base_units ?? $this->stok);

        return $stockUnits / $this->units_per_box;
    }

    public function getStockBoxesAttribute(): float
    {
        return $this->stock_packages;
    }

    public function getStockBreakdownAttribute(): string
    {
        $stockUnits = (int) ($this->stock_base_units ?? $this->stok);
        if ($stockUnits <= 0) {
            return '0 ' . $this->package_unit_label;
        }

        if (!$this->unit_packaging_enabled) {
            return number_format($stockUnits, 0, ',', '.') . ' ' . $this->package_unit_label;
        }

        $unitsPerBox = $this->units_per_box;
        $packages = intdiv($stockUnits, $unitsPerBox);
        $remainder = $stockUnits % $unitsPerBox;
        $strips = intdiv($remainder, max(1, (int) $this->tablets_per_strip));
        $tablets = $remainder % max(1, (int) $this->tablets_per_strip);
        $parts = [];

        if ($packages > 0) {
            $parts[] = number_format($packages, 0, ',', '.') . ' ' . $this->package_unit_label;
        }
        if ($strips > 0) {
            $parts[] = number_format($strips, 0, ',', '.') . ' strip';
        }
        if ($tablets > 0 || $parts === []) {
            $parts[] = number_format($tablets, 0, ',', '.') . ' tablet';
        }

        return implode(', ', $parts);
    }

    public function getSaleUnitFactor(string $unit): int
    {
        if (!$this->unit_packaging_enabled) {
            return $unit === 'box' ? 1 : 0;
        }

        return match ($unit) {
            'tablet' => 1,
            'strip' => max(1, (int) $this->tablets_per_strip),
            'box' => $this->units_per_box,
            default => 0,
        };
    }

    public function getSaleUnitPrice(string $unit): ?float
    {
        if (!$this->unit_packaging_enabled) {
            if ($unit !== 'box') {
                return null;
            }

            $retailPrice = (float) $this->harga_retail;

            return $retailPrice > 0 ? $retailPrice : (float) $this->harga;
        }

        $price = match ($unit) {
            'tablet' => $this->price_tablet,
            'strip' => $this->price_strip,
            'box' => $this->price_box,
            default => null,
        };

        if ($unit === 'box' && (float) $price <= 0) {
            $price = $this->harga_retail;
        }

        return $price === null ? null : (float) $price;
    }

    public function setStockInPackages(float $packages): void
    {
        $this->stock_base_units = (int) round($packages * $this->units_per_box);
        $this->stok = intdiv((int) $this->stock_base_units, $this->units_per_box);
    }

    public function setStockInBoxes(float $boxes): void
    {
        $this->setStockInPackages($boxes);
    }

    public function getSediaanLabelAttribute(): string
    {
        if (empty($this->sediaan)) {
            return '';
        }

        $labels = [
            'box' => 'Box',
            'fls' => 'Fls / Flacon',
            'pot' => 'Pot',
            'tube' => 'Tube',
            'blister' => 'Blister / Strip',
            'sachet' => 'Sachet',
            'ampul' => 'Ampul',
            'vial' => 'Vial',
            'catch_cover' => 'Catch Cover',
            'suppositoria' => 'Suppositoria',
            'ovula' => 'Ovula',
            'enema' => 'Enema',
            'inhaler' => 'Inhaler / Nebules',
            'pen' => 'Pen',
            'pcs' => 'Pcs',
        ];

        $key = strtolower(trim((string) $this->sediaan));

        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function getFormattedCatalogPrice(string $column = 'harga_retail'): string
    {
        $value = (float) ($this->{$column} ?: $this->harga);
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function getPromoPercent(string $channel): int
    {
        $column = match ($channel) {
            'internal' => 'promo_internal_percent',
            'b2b' => 'promo_b2b_percent',
            'b2c' => 'promo_b2c_percent',
            default => null,
        };

        return $column ? max(0, min(100, (int) $this->{$column})) : 0;
    }

    public function getChannelBasePrice(string $channel, string $unit = 'box'): float
    {
        if ($channel === 'b2b') {
            $boxPrice = (float) $this->harga_grosir;

            if (!$this->unit_packaging_enabled || $unit === 'box') {
                return $boxPrice;
            }

            $factor = $this->getSaleUnitFactor($unit);

            return $factor > 0 ? $boxPrice * $factor / $this->units_per_box : 0;
        }

        if ($this->unit_packaging_enabled && $channel === 'b2c') {
            return (float) ($this->getSaleUnitPrice($unit) ?? 0);
        }

        $legacyRetailPrice = (float) $this->harga_retail;
        if ($legacyRetailPrice <= 0) {
            $legacyRetailPrice = (float) $this->harga;
        }
        $boxPrice = $channel === 'internal' && $this->harga_internal !== null
            ? (float) $this->harga_internal
            : $legacyRetailPrice;

        if ($this->unit_packaging_enabled && $unit !== 'box') {
            $factor = $this->getSaleUnitFactor($unit);

            return $factor > 0 ? $boxPrice * $factor / $this->units_per_box : 0;
        }

        return $boxPrice;
    }

    public function getChannelPromoPrice(string $channel, string $unit = 'box'): float
    {
        $basePrice = $this->getChannelBasePrice($channel, $unit);

        return round($basePrice * (100 - $this->getPromoPercent($channel)) / 100, 2);
    }

    /**
     * Get full URL gambar produk
     * Gunakan: $medicine->image_url di blade
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->gambar) {
            return null;
        }
        
        // Coba beberapa kemungkinan path
        $paths = [
            'storage/' . $this->gambar,           // public/storage/medicines/xxx.jpg
            $this->gambar,                         // langsung medicines/xxx.jpg
            'public/storage/' . $this->gambar,    // public/public/storage/medicines/xxx.jpg (hosting aneh)
        ];
        
        foreach ($paths as $path) {
            if (file_exists(public_path($path))) {
                return url($path);
            }
        }
        
        // Fallback: return URL tanpa cek file
        return url('storage/' . $this->gambar);
    }

    public function getImageGalleryAttribute(): array
    {
        return collect([$this->gambar, ...($this->gambar_tambahan ?? [])])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->unique()
            ->values()
            ->all();
    }
}
