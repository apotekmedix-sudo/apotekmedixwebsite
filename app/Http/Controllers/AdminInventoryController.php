<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineStockMovement;
use App\Support\StockAvailability;
use Illuminate\Http\Request;

class AdminInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort') === 'lowest_stock' ? 'lowest_stock' : 'name';
        $query = Medicine::query()->when($search !== '', fn ($builder) => $builder->search($search));
        $query = $sort === 'lowest_stock'
            ? StockAvailability::prioritizeLowestAvailableStock($query)
            : $query->orderBy('nama_obat');
        $products = $query->paginate(30, ['*'], 'products_page')->withQueryString();
        StockAvailability::annotate($products->getCollection());

        $movements = MedicineStockMovement::with(['medicine:id,nama_obat,satuan,sediaan,unit_packaging_enabled,strips_per_box,tablets_per_strip', 'user:id,name'])
            ->when($search !== '', fn ($builder) => $builder->whereHas('medicine', fn ($medicineQuery) => $medicineQuery->search($search)))
            ->latest()
            ->paginate(50, ['*'], 'movements_page')
            ->withQueryString();

        return view('admin.inventory.index', compact('products', 'movements', 'search', 'sort'));
    }
}
