<?php

namespace App\Http\Controllers;

use App\Helpers\XlsxWriter;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminBusinessDataExportController extends Controller
{
    public function export()
    {
        $sheets = [$this->productsSheet()];
        $businessTables = [
            ['customers', 'Pelanggan'],
            ['purchase_histories', 'Transaksi'],
            ['banners', 'Banner'],
            ['promo_products', 'Produk Promo'],
            ['news', 'Berita'],
            ['activities', 'Aktivitas'],
            ['medicines_grosir', 'Produk Grosir'],
        ];

        foreach ($businessTables as [$table, $sheetName]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $query = DB::table($table);
            if (in_array('id', $columns, true)) {
                $query->orderBy('id');
            }

            $rows = $query->get()->map(fn ($row) => array_map(
                fn ($column) => $row->{$column},
                $columns
            ))->all();

            $sheets[] = [
                'name' => $sheetName,
                'headers' => array_map('strtoupper', $columns),
                'rows' => $rows,
            ];
        }

        return XlsxWriter::downloadWorkbook(
            'backup-data-bisnis-'.now()->format('Ymd-His').'.xlsx',
            $sheets
        );
    }

    private function productsSheet(): array
    {
        $columns = Schema::getColumnListing('medicines');
        $headers = array_map('strtoupper', $columns);
        $headers[] = 'IMAGE_URL';
        $headers[] = 'IMAGE_GALLERY_URLS';

        $rows = Medicine::query()->orderBy('id')->get()->map(function (Medicine $product) use ($columns) {
            $values = array_map(
                fn ($column) => $product->getAttribute($column),
                $columns
            );
            $imageUrls = array_map(
                fn ($path) => url('storage/'.$path),
                $product->image_gallery
            );
            $values[] = $product->image_url ?: '';
            $values[] = $imageUrls;

            return $values;
        })->all();

        return [
            'name' => 'Produk',
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}
