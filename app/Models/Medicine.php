<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use App\Models\MedicineStockMovement;
use App\Services\MedicineStockLedger;

class Medicine extends Model
{
    use HasFactory;

    private static ?\WeakMap $stockMovementBefore = null;

    protected $table = 'medicines';

    protected static function booted(): void
    {
        static::created(function (Medicine $medicine): void {
            MedicineStockLedger::recordOpening($medicine);
        });

        static::updating(function (Medicine $medicine): void {
            self::$stockMovementBefore ??= new \WeakMap();
            self::$stockMovementBefore[$medicine] = MedicineStockLedger::physicalUnitsFrom(
                $medicine->getRawOriginal()
            );
        });

        static::creating(function (Medicine $medicine): void {
            if ($medicine->kategori === null) {
                $medicine->kategori = '';
            }
            if ($medicine->deskripsi === null) {
                $medicine->deskripsi = (string) ($medicine->nama_obat ?? '');
            }
            if (Schema::hasColumn('medicines', 'stock_base_units') && $medicine->stock_base_units === null) {
                $medicine->stock_base_units = (int) $medicine->stok * $medicine->units_per_box;
            }
        });

        static::updated(function (Medicine $medicine): void {
            if (isset(self::$stockMovementBefore[$medicine])) {
                MedicineStockLedger::recordModelUpdate(
                    $medicine,
                    self::$stockMovementBefore[$medicine],
                    $medicine->getPhysicalStockUnits()
                );
                unset(self::$stockMovementBefore[$medicine]);
            }
        });

        static::updating(function (Medicine $medicine): void {
            $packagingChanged = $medicine->isDirty([
                'unit_packaging_enabled',
                'tablets_per_strip',
                'strips_per_box',
                'satuan',
                'sediaan',
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
        'harga_modal',
        'harga_internal',
        'harga_grosir',
        'harga_retail',
        'promo_b2b_percent',
        'promo_b2c_percent',
        'is_resep',
        'is_grosir',
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
        'dosis',
        'gambar',
        'gambar_tambahan',
    ];

    protected $casts = [
        'harga'   => 'decimal:2',
        'harga_modal'  => 'decimal:2',
        'harga_internal' => 'decimal:2',
        'harga_grosir' => 'decimal:2',
        'harga_retail' => 'decimal:2',
        'promo_b2b_percent' => 'integer',
        'promo_b2c_percent' => 'integer',
        'is_resep' => 'boolean',
        'is_grosir' => 'boolean',
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

    /**
     * Get existing columns for the medicines table safely.
     */
    public static function availableColumns(): array
    {
        return Schema::getColumnListing('medicines');
    }

    public function isRedLogo(): bool
    {
        $logo = mb_strtolower(trim((string) $this->logo));
        if (in_array($logo, ['merah', 'red', 'logo merah', 'k'], true)) {
            return true;
        }

        $golongan = mb_strtolower(trim((string) $this->golongan));
        if ($logo === '' && str_contains($golongan, 'keras')) {
            return true;
        }

        return false;
    }

    /**
     * Expand dosage-like terms (e.g., "500mg" <-> "500 mg") so both variations match.
     */
    public static function expandSearchVariants(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $variants = [$term];
        if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(mg|ml|gr|g|mcg|iu|%)/i', $term, $m)) {
            $variants[] = $m[1] . $m[2];
            $variants[] = $m[1] . ' ' . $m[2];
        }

        return array_values(array_unique($variants));
    }

    /**
     * Universal search across Nama Obat, Indikasi, Komposisi, Sediaan (Sirup/Tablet/dll),
     * Dosis, Golongan, Kemasan, Satuan, Pabrik/Principle, Brand, SKU, dan Deskripsi.
     */
    public function scopeSearch($query, $search)
    {
        $search = trim((string) $search);
        if ($search === '') {
            return $query;
        }

        $existing = array_flip(self::availableColumns());
        $candidateColumns = [
            'nama_obat',
            'indikasi',
            'komposisi',
            'sediaan',
            'dosis',
            'golongan',
            'satuan',
            'kemasan',
            'kategori',
            'brand',
            'sku',
            'logo',
            'deskripsi',
        ];
        $columns = array_values(array_filter($candidateColumns, fn ($col) => isset($existing[$col])));

        if (empty($columns)) {
            return $query;
        }

        $tokens = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [$search];

        return $query->where(function ($outerQuery) use ($search, $tokens, $columns) {
            // Exact phrase match across any column
            $outerQuery->where(function ($phraseQuery) use ($search, $columns) {
                foreach (self::expandSearchVariants($search) as $variant) {
                    foreach ($columns as $col) {
                        $phraseQuery->orWhere($col, 'like', '%' . $variant . '%');
                    }
                }
            });

            // Multi-word AND match across columns (e.g. "paracetamol sirup" or "demam 500mg")
            if (count($tokens) > 1) {
                $outerQuery->orWhere(function ($andQuery) use ($tokens, $columns) {
                    foreach ($tokens as $token) {
                        $variants = self::expandSearchVariants($token);
                        $andQuery->where(function ($tokenQuery) use ($variants, $columns) {
                            foreach ($variants as $variant) {
                                foreach ($columns as $col) {
                                    $tokenQuery->orWhere($col, 'like', '%' . $variant . '%');
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    /**
     * Dedicated filter scope for Indikasi, Komposisi, Sediaan, Dosis, and Golongan.
     */
    public function scopeFilterByAttributes($query, array $filters)
    {
        $existing = array_flip(self::availableColumns());
        $pickCols = fn (array $cols) => array_values(array_filter($cols, fn ($c) => isset($existing[$c])));

        $indikasi = trim((string) ($filters['indikasi'] ?? ''));
        if ($indikasi !== '') {
            $cols = $pickCols(['indikasi', 'deskripsi']);
            if (!empty($cols)) {
                $query->where(function ($q) use ($indikasi, $cols) {
                    foreach ($cols as $col) {
                        $q->orWhere($col, 'like', '%' . $indikasi . '%');
                    }
                });
            }
        }

        $komposisi = trim((string) ($filters['komposisi'] ?? ''));
        if ($komposisi !== '') {
            $cols = $pickCols(['komposisi', 'nama_obat', 'deskripsi']);
            if (!empty($cols)) {
                $query->where(function ($q) use ($komposisi, $cols) {
                    foreach (self::expandSearchVariants($komposisi) as $variant) {
                        foreach ($cols as $col) {
                            $q->orWhere($col, 'like', '%' . $variant . '%');
                        }
                    }
                });
            }
        }

        $sediaan = trim((string) ($filters['sediaan'] ?? ''));
        if ($sediaan !== '') {
            $cols = $pickCols(['sediaan', 'satuan', 'kemasan', 'nama_obat', 'deskripsi']);
            if (!empty($cols)) {
                $query->where(function ($q) use ($sediaan, $cols) {
                    foreach ($cols as $col) {
                        $q->orWhere($col, 'like', '%' . $sediaan . '%');
                    }
                });
            }
        }

        $dosis = trim((string) ($filters['dosis'] ?? ''));
        if ($dosis !== '') {
            $cols = $pickCols(['dosis', 'komposisi', 'nama_obat', 'kemasan', 'deskripsi']);
            if (!empty($cols)) {
                $query->where(function ($q) use ($dosis, $cols) {
                    foreach (self::expandSearchVariants($dosis) as $variant) {
                        foreach ($cols as $col) {
                            $q->orWhere($col, 'like', '%' . $variant . '%');
                        }
                    }
                });
            }
        }

        $golongan = trim((string) ($filters['golongan'] ?? ''));
        if ($golongan !== '') {
            $cols = $pickCols(['golongan', 'logo', 'deskripsi']);
            if (!empty($cols)) {
                $query->where(function ($q) use ($golongan, $cols) {
                    foreach ($cols as $col) {
                        $q->orWhere($col, 'like', '%' . $golongan . '%');
                    }
                });
            }
        }

        return $query;
    }

    /**
     * Common + database-derived options for Sediaan and Golongan filter dropdowns.
     */
    public static function getAttributeFilterOptions(): array
    {
        $existing = array_flip(self::availableColumns());

        $defaultSediaan = [
            'Tablet',
            'Sirup',
            'Kapsul',
            'Kaplet',
            'Krim',
            'Salep',
            'Gel',
            'Drops',
            'Suspensi',
            'Sachet',
            'Botol',
            'Box',
            'Strip',
            'Tube',
            'Fls',
            'Ampul',
            'Vial',
            'Inhaler',
        ];

        $dbSediaan = [];
        if (isset($existing['sediaan'])) {
            $dbSediaan = self::query()
                ->whereNotNull('sediaan')
                ->where('sediaan', '!=', '')
                ->distinct()
                ->pluck('sediaan')
                ->map(fn ($v) => ucfirst(strtolower(trim((string) $v))))
                ->all();
        }

        $sediaanList = collect(array_merge($defaultSediaan, $dbSediaan))
            ->filter()
            ->unique(fn ($v) => strtolower($v))
            ->values()
            ->all();

        $defaultGolongan = [
            'Obat Bebas',
            'Bebas Terbatas',
            'Obat Keras',
            'Vitamin & Suplemen',
            'Obat Herbal / Jamu',
        ];

        $dbGolongan = [];
        if (isset($existing['golongan'])) {
            $dbGolongan = self::query()
                ->whereNotNull('golongan')
                ->where('golongan', '!=', '')
                ->distinct()
                ->pluck('golongan')
                ->map(fn ($v) => trim((string) $v))
                ->all();
        }

        $golonganList = collect(array_merge($defaultGolongan, $dbGolongan))
            ->filter()
            ->unique(fn ($v) => strtolower($v))
            ->values()
            ->all();

        return [
            'sediaanList' => $sediaanList,
            'golonganList' => $golonganList,
        ];
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

    public function scopeAvailableForB2c($query)
    {
        return $query->where(function ($catalogQuery) {
            $catalogQuery->where(function ($packagedQuery) {
                $packagedQuery->where('unit_packaging_enabled', true)
                    ->where(function ($priceQuery) {
                        $priceQuery->where('price_box', '>', 0)
                            ->orWhere('harga_retail', '>', 0);
                    });
            })->orWhere(function ($unpackagedQuery) {
                $unpackagedQuery->where(function ($packagingQuery) {
                    $packagingQuery->where('unit_packaging_enabled', false)
                        ->orWhereNull('unit_packaging_enabled');
                })->where(function ($priceQuery) {
                    $priceQuery->where('harga_retail', '>', 0)
                        ->orWhere('harga', '>', 0);
                });
            });
        });
    }

    public function scopeWithoutRedLogo($query)
    {
        return $query->where(function ($catalogQuery) {
            $catalogQuery->whereNull('logo')
                ->orWhereRaw('LOWER(TRIM(logo)) NOT IN (?, ?)', ['merah', 'red']);
        });
    }

    public function hasRedLogo(): bool
    {
        return $this->isRedLogo();
    }

    public function isAvailable(): bool
    {
        return $this->available_stock_units > 0;
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(MedicineStockMovement::class);
    }

    public function getAvailableStockUnitsAttribute(): int
    {
        if (array_key_exists('available_stock_units', $this->attributes)) {
            return max(0, (int) $this->attributes['available_stock_units']);
        }

        return $this->getPhysicalStockUnits();
    }

    public function getPhysicalStockUnits(): int
    {
        $stock = $this->usesTabletPackaging()
            ? ($this->stock_base_units ?? $this->stok)
            : $this->stok;

        return max(0, (int) ($stock ?? 0));
    }

    public static function supportsTabletPackaging(?string $unit, ?string $form): bool
    {
        $unit = mb_strtolower(trim((string) $unit));
        $form = mb_strtolower(trim((string) $form));
        $packageUnit = $unit !== '' ? $unit : ($form === 'box' ? $form : '');

        if ($packageUnit !== 'box') {
            return false;
        }

        $nonTabletPackage = '/botol|bottle|tube|tabung|sachet|flacon|\bfls\b|vial|ampul|ampoule|\bpot\b|sirup|syrup|suspensi|drops|tetes|krim|cream|salep|ointment|\bgel\b|inhaler|nebule|suppositoria|ovula|enema|\bpen\b/u';

        if (preg_match($nonTabletPackage, $form)) {
            return false;
        }

        if ($form === '' || $form === 'box') {
            return true;
        }

        return preg_match('/tablet|kaplet|caplet|kapsul|capsule|blister|\bstrip\b|\bpil\b|\bbox\b/u', $form) === 1;
    }

    public function usesTabletPackaging(): bool
    {
        return (bool) $this->unit_packaging_enabled
            && self::supportsTabletPackaging((string) $this->satuan, (string) $this->sediaan);
    }

    public function getUnitsPerBoxAttribute(): int
    {
        if (!$this->usesTabletPackaging()) {
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
        $stockUnits = $this->available_stock_units;

        return $stockUnits / $this->units_per_box;
    }

    public function getStockBoxesAttribute(): float
    {
        return $this->stock_packages;
    }

    public function getStockStripsAttribute(): int
    {
        return $this->getConvertedUnitStocksForUnits()['strip'];
    }

    public function getStockTabletsAttribute(): int
    {
        return $this->getConvertedUnitStocksForUnits()['tablet'];
    }

    /**
     * Hitung ketiga stok terkonversi (Box/Satuan, Strip, Tablet) dari jumlah unit dasar (tablet).
     * Contoh: 1 Box = 10 Strip, 1 Strip = 10 Tablet (100 Tablet/Box).
     * - Jika tersedia 1000 unit -> Box: 10, Strip: 100, Tablet: 1000
     * - Jika dibeli 1 Box (100 unit) -> Box: 9, Strip: 90, Tablet: 900
     * - Jika dibeli 1 Strip (10 unit) -> Box: 9 (9.9), Strip: 99, Tablet: 990
     */
    public function getConvertedUnitStocksForUnits(?int $stockUnits = null): array
    {
        $units = max(0, $stockUnits ?? $this->available_stock_units);
        $unitsPerBox = max(1, $this->units_per_box);
        $tabletsPerStrip = max(1, (int) $this->tablets_per_strip);

        if (!$this->usesTabletPackaging()) {
            return [
                'box' => $units,
                'box_decimal' => (float) $units,
                'strip' => 0,
                'tablet' => 0,
            ];
        }

        return [
            'box' => intdiv($units, $unitsPerBox),
            'box_decimal' => round($units / $unitsPerBox, 6),
            'strip' => intdiv($units, $tabletsPerStrip),
            'tablet' => $units,
        ];
    }

    public function getConvertedStockSummaryAttribute(): string
    {
        return $this->getConvertedStockSummaryForUnits($this->available_stock_units);
    }

    public function getConvertedStockSummaryForUnits(?int $stockUnits = null): string
    {
        $units = max(0, $stockUnits ?? $this->available_stock_units);
        if ($units <= 0) {
            return '0 ' . $this->package_unit_label;
        }

        if (!$this->usesTabletPackaging()) {
            return number_format($units, 0, ',', '.') . ' ' . $this->package_unit_label;
        }

        $converted = $this->getConvertedUnitStocksForUnits($units);

        return sprintf(
            '%s %s · %s Strip · %s Tablet',
            number_format($converted['box'], 0, ',', '.'),
            $this->package_unit_label,
            number_format($converted['strip'], 0, ',', '.'),
            number_format($converted['tablet'], 0, ',', '.')
        );
    }

    public function getStockBreakdownAttribute(): string
    {
        $stockUnits = $this->available_stock_units;

        return $this->getStockBreakdownForUnits($stockUnits);
    }

    public function getStockBreakdownForUnits(int $stockUnits): string
    {
        if ($stockUnits <= 0) {
            return '0 ' . $this->package_unit_label;
        }

        if (!$this->usesTabletPackaging()) {
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
        if (!$this->usesTabletPackaging()) {
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
        if (!$this->usesTabletPackaging()) {
            if ($unit !== 'box') {
                return null;
            }

            $retailPrice = (float) ($this->harga_retail ?: $this->price_box);

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
            'sirup' => 'Sirup',
            'tablet' => 'Tablet',
            'kapsul' => 'Kapsul',
            'kaplet' => 'Kaplet',
            'krim' => 'Krim',
            'salep' => 'Salep',
            'gel' => 'Gel',
            'drops' => 'Drops / Tetes',
            'suspensi' => 'Suspensi',
            'botol' => 'Botol',
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
            'b2b' => 'promo_b2b_percent',
            'b2c' => 'promo_b2c_percent',
            default => null,
        };

        return $column ? max(0, min(100, (int) $this->{$column})) : 0;
    }

    public function getChannelBasePrice(string $channel, string $unit = 'box'): float
    {
        if ($channel === 'internal') {
            $internalPrice = (float) $this->harga_internal;
            if ($internalPrice <= 0 || !$this->usesTabletPackaging() || $unit === 'box') {
                return $internalPrice;
            }

            $factor = $this->getSaleUnitFactor($unit);

            return $factor > 0 ? $internalPrice * $factor / $this->units_per_box : 0;
        }

        if ($channel === 'b2b') {
            $boxPrice = (float) $this->harga_grosir;

            if (!$this->usesTabletPackaging() || $unit === 'box') {
                return $boxPrice;
            }

            $factor = $this->getSaleUnitFactor($unit);

            return $factor > 0 ? $boxPrice * $factor / $this->units_per_box : 0;
        }

        if ($this->usesTabletPackaging() && $channel === 'b2c') {
            return (float) ($this->getSaleUnitPrice($unit) ?? 0);
        }

        $legacyRetailPrice = (float) ($this->harga_retail ?: $this->price_box);
        if ($legacyRetailPrice <= 0) {
            $legacyRetailPrice = (float) $this->harga;
        }
        if ($this->usesTabletPackaging() && $unit !== 'box') {
            $factor = $this->getSaleUnitFactor($unit);

            return $factor > 0 ? $legacyRetailPrice * $factor / $this->units_per_box : 0;
        }

        return $legacyRetailPrice;
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
