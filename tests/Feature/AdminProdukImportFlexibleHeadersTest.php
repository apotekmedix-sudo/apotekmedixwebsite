<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminProdukImportController;
use App\Http\Controllers\AdminProdukController;
use App\Http\Controllers\HomeController;
use App\Helpers\XlsxWriter;
use App\Models\Medicine;
use App\Models\User;
use App\Services\ProductImageDownloader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminProdukImportFlexibleHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('customer');
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('distributor')->nullable();
            $table->string('nama_obat');
            $table->string('logo')->nullable();
            $table->string('golongan')->nullable();
            $table->string('satuan')->nullable();
            $table->string('kemasan')->nullable();
            $table->string('sediaan')->nullable();
            $table->string('kelompok')->nullable();
            $table->string('kategori')->nullable();
            $table->string('brand')->nullable();
            $table->string('kategori_produk')->nullable();
            $table->decimal('harga', 12, 2)->default(0);
            $table->decimal('harga_modal', 12, 2)->default(0);
            $table->decimal('harga_internal', 12, 2)->nullable();
            $table->decimal('harga_grosir', 12, 2)->default(0);
            $table->decimal('harga_retail', 12, 2)->default(0);
            $table->unsignedInteger('promo_b2c_percent')->default(0);
            $table->boolean('unit_packaging_enabled')->default(false);
            $table->unsignedInteger('tablets_per_strip')->nullable();
            $table->unsignedInteger('strips_per_box')->nullable();
            $table->decimal('price_tablet', 12, 2)->nullable();
            $table->decimal('price_strip', 12, 2)->nullable();
            $table->decimal('price_box', 12, 2)->nullable();
            $table->unsignedInteger('stock_base_units')->nullable();
            $table->integer('stok')->default(0);
            $table->integer('terjual')->default(0);
            $table->string('grade')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('komposisi')->nullable();
            $table->text('indikasi')->nullable();
            $table->string('gambar')->nullable();
            $table->json('gambar_tambahan')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedInteger('quantity_units');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('purchase_stock_reservations');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_import_accepts_loose_headers_when_required_columns_are_present(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($path, "SKU,Nama Produk,Brand,Harga,Stok,Terjual,Grade,Komposisi,Indikasi,Kategori\nSKU-999,Obat Uji,Brand Uji,12000,10,12,A,Paracetamol,Demam,PRODUK LENGKAP\n");

        $file = new UploadedFile($path, 'test.csv', 'text/csv', null, true);
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        $response = (new AdminProdukImportController())->import($request);

        $this->assertTrue($response->isRedirect());
        $errors = $response->getSession()->get('errors');
        $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
        $this->assertDatabaseHas('medicines', [
            'sku' => 'SKU-999',
            'nama_obat' => 'Obat Uji',
            'brand' => 'Brand Uji',
            'terjual' => 12,
            'grade' => 'A',
        ]);

        @unlink($path);
    }

    public function test_import_downloads_external_product_photo_links_to_local_storage(): void
    {
        $imageUrl = 'https://images.example.net/products/medicine.jpg';
        $downloader = \Mockery::mock(ProductImageDownloader::class);
        $downloader->shouldReceive('download')->once()->with($imageUrl)->andReturn('medicines/downloaded-product.jpg');
        $this->app->instance(ProductImageDownloader::class, $downloader);

        $path = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($path, "NAMA PRODUK,LINK FOTO\nObat Foto Link,{$imageUrl}\n");
        $file = new UploadedFile($path, 'products.csv', 'text/csv', null, true);
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        try {
            $response = (new AdminProdukImportController())->import($request);
            $errors = $response->getSession()->get('errors');

            $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
            $this->assertDatabaseHas('medicines', [
                'nama_obat' => 'Obat Foto Link',
                'gambar' => 'medicines/downloaded-product.jpg',
            ]);
        } finally {
            @unlink($path);
        }
    }

    public function test_import_downloads_second_product_photo_into_gallery(): void
    {
        $imageUrl = 'https://images.example.net/products/medicine-secondary.jpg';
        $downloader = \Mockery::mock(ProductImageDownloader::class);
        $downloader->shouldReceive('download')->once()->with($imageUrl)->andReturn('medicines/downloaded-secondary.jpg');
        $this->app->instance(ProductImageDownloader::class, $downloader);

        $path = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($path, "NAMA PRODUK,LINK FOTO 2\nObat Foto Kedua,{$imageUrl}\n");
        $file = new UploadedFile($path, 'products.csv', 'text/csv', null, true);
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        try {
            $response = (new AdminProdukImportController())->import($request);
            $errors = $response->getSession()->get('errors');

            $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
            $product = Medicine::where('nama_obat', 'Obat Foto Kedua')->firstOrFail();
            $this->assertSame(['medicines/downloaded-secondary.jpg'], $product->gambar_tambahan);
        } finally {
            @unlink($path);
        }
    }

    public function test_product_image_downloader_rejects_non_https_links(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('HTTPS');

        (new ProductImageDownloader())->download('http://images.example.net/product.jpg');
    }

    public function test_product_image_downloader_rejects_private_network_hosts(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('host internet publik');

        (new ProductImageDownloader())->download('https://127.0.0.1/product.jpg');
    }

    public function test_homepage_featured_products_only_include_products_with_at_least_ten_sales(): void
    {
        Medicine::create([
            'nama_obat' => 'Obat Grade A',
            'harga' => 12000,
            'stok' => 10,
            'grade' => 'A',
            'terjual' => 10,
        ]);

        Medicine::create([
            'nama_obat' => 'Obat Grade A Terjual 9',
            'harga' => 12000,
            'stok' => 10,
            'grade' => 'A',
            'terjual' => 9,
        ]);

        Medicine::create([
            'nama_obat' => 'Obat Grade A Belum Terjual',
            'harga' => 12000,
            'stok' => 10,
            'grade' => 'A',
            'terjual' => 0,
        ]);

        Medicine::create([
            'nama_obat' => 'Obat Grade B',
            'harga' => 15000,
            'stok' => 10,
            'grade' => 'B',
        ]);

        Medicine::create([
            'nama_obat' => 'Obat PBF Grade A',
            'harga' => 15000,
            'stok' => 10,
            'grade' => 'A',
            'kelompok' => 'PBF',
        ]);

        Medicine::create([
            'nama_obat' => 'Obat Tanpa Harga B2C',
            'harga' => 0,
            'harga_grosir' => 15000,
            'stok' => 10,
            'grade' => 'A',
        ]);

        Medicine::create([
            'nama_obat' => 'Obat Promo B2C',
            'harga_retail' => 12000,
            'promo_b2c_percent' => 10,
            'stok' => 10,
        ]);

        $view = (new HomeController())->index(new Request());
        $featuredProducts = $view->getData()['featuredProducts'];
        $discountedProducts = $view->getData()['discountedProducts'];

        $this->assertCount(1, $featuredProducts);
        $this->assertSame('Obat Grade A', $featuredProducts->first()->nama_obat);
        $this->assertSame(10, $featuredProducts->first()->terjual);
        $this->assertCount(1, $discountedProducts);
        $this->assertSame('Obat Promo B2C', $discountedProducts->first()->nama_obat);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Terlaris B2C')
            ->assertSee('Promo B2C')
            ->assertDontSee('href="' . route('products.apotek') . '"', false)
            ->assertSee('class="discounted-product-card"', false)
            ->assertSee('product-unit-card--single', false)
            ->assertDontSee('<div class="discounted-product-prices">', false)
            ->assertSee('data-price-target="home-price-current-a-', false)
            ->assertSee('data-price-target="home-price-b-', false);
    }

    public function test_homepage_hides_promo_and_bestseller_sections_when_they_have_no_products(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Produk Promo')
            ->assertDontSee('Produk Terlaris')
            ->assertDontSee('Promo B2C')
            ->assertDontSee('Belum ada produk promo B2C saat ini.')
            ->assertDontSee('Belum ada produk terlaris tersedia saat ini.');
    }

    public function test_homepage_expands_additional_products_below_the_first_twenty(): void
    {
        foreach (range(1, 22) as $number) {
            Medicine::create([
                'nama_obat' => 'Produk Beranda ' . $number,
                'harga_retail' => 12000,
                'stok' => 10,
            ]);
        }

        $response = $this->get(route('home'))->assertOk();

        $this->assertCount(22, $response->viewData('allB2cProducts'));
        $response->assertSee('id="homeAllB2cProductsGrid"', false)
            ->assertSee('id="homeShowMoreProducts"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('data-additional-home-product', false)
            ->assertSee('fa-chevron-down', false)
            ->assertSeeInOrder(['id="homeCategoryGrid"', 'class="home-search-section"'], false)
            ->assertDontSee('href="' . route('products.apotek') . '"', false);
    }

    public function test_homepage_search_filters_b2c_products_across_product_details(): void
    {
        Medicine::create([
            'nama_obat' => 'Paracetamol Medix',
            'harga_retail' => 12000,
            'stok' => 10,
            'komposisi' => 'Paracetamol 500 mg',
            'indikasi' => 'Meredakan demam',
        ]);
        Medicine::create([
            'nama_obat' => 'Vitamin C Medix',
            'harga_retail' => 15000,
            'stok' => 10,
            'komposisi' => 'Asam askorbat',
            'deskripsi' => 'Kata khusus pencarian deskripsi',
        ]);

        $this->get(route('home', ['search' => 'demam']))
            ->assertOk()
            ->assertSee('name="search" value="demam"', false)
            ->assertSee('name="kategori_pilihan"', false)
            ->assertSee('name="perusahaan"', false)
            ->assertSee('name="sort"', false)
            ->assertSee('Hasil Pencarian')
            ->assertSee('Paracetamol Medix')
            ->assertDontSee('Vitamin C Medix')
            ->assertDontSee('Produk Terlaris')
            ->assertSee('id="homeProductSearchResults"', false);

        $this->get(route('home', ['search' => 'khusus pencarian deskripsi']))
            ->assertOk()
            ->assertSee('Vitamin C Medix')
            ->assertDontSee('Paracetamol Medix');
    }

    public function test_import_maps_template_fields_without_tablet_or_strip_prices_and_conversions(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($path, implode("\n", [
            'NO,PRINCIPLE,NAMA PRODUK,LOGO,GOLONGAN,KATEGORI,KOMPOSISI,INDIKASI,DESKRIPSI,SATUAN,KEMASAN,SEDIAAN,STOK SATUAN,MODAL,HARGA INTERNAL,HARGA JUAL B2B,HARGA JUAL B2C,LINK FOTO 1,LINK FOTO 2',
            '1,PT KALBE FARMA,Promag Tablet,HIJAU,Obat Bebas,vitamin-suplemen,"Antasida, Simetikon","Meredakan sakit maag, dan perih","Deskripsi yang diisi terpisah",BOX,10 blister @ 6 tablet,TABLET,2,100000,110000,130000,140000,,',
        ]) . "\n");

        $file = new UploadedFile($path, 'products.csv', 'text/csv', null, true);
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        $response = (new AdminProdukImportController())->import($request);

        $this->assertTrue($response->isRedirect());
        $errors = $response->getSession()->get('errors');
        $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');

        $product = Medicine::where('nama_obat', 'Promag Tablet')->firstOrFail();
        $this->assertSame('HIJAU', $product->logo);
        $this->assertSame('Obat Bebas', $product->golongan);
        $this->assertSame('vitamin-suplemen', $product->kategori_produk);
        $this->assertSame('Antasida, Simetikon', $product->komposisi);
        $this->assertSame('Deskripsi yang diisi terpisah', $product->deskripsi);
        $this->assertSame(110000.0, (float) $product->harga_internal);
        $this->assertSame(130000.0, $product->getChannelBasePrice('b2b'));
        $this->assertSame(140000.0, $product->getChannelBasePrice('b2c'));
        $this->assertSame(0.0, $product->getChannelBasePrice('b2c', 'strip'));
        $this->assertSame(2, (int) $product->stock_base_units);

        @unlink($path);
    }

    public function test_import_maps_display_category_names_and_does_not_fill_composition_from_product_name(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'category-composition-import');
        file_put_contents($path, XlsxWriter::build(
            ['NAMA PRODUK', 'KATEGORI', 'KOMPOSISI', 'DESKRIPSI'],
            [
                ['Produk OTC', 'OTC', '', 'Deskripsi OTC'],
                ['Produk Flu', 'Flu & Batuk', '', ''],
                ['Produk Resep', 'Obat Resep', '', ''],
            ]
        ));
        $file = new UploadedFile(
            $path,
            'products.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        try {
            $response = (new AdminProdukImportController())->import($request);
            $errors = $response->getSession()->get('errors');
            $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
        } finally {
            @unlink($path);
        }

        $this->assertDatabaseHas('medicines', [
            'nama_obat' => 'Produk OTC',
            'kategori_produk' => 'otc',
            'komposisi' => null,
            'deskripsi' => 'Deskripsi OTC',
        ]);
        $this->assertDatabaseHas('medicines', [
            'nama_obat' => 'Produk Flu',
            'kategori_produk' => 'flu-batuk',
            'komposisi' => null,
            'deskripsi' => '',
        ]);
        $this->assertDatabaseHas('medicines', [
            'nama_obat' => 'Produk Resep',
            'kategori_produk' => 'obat-resep',
            'komposisi' => null,
            'deskripsi' => '',
        ]);
    }

    public function test_import_accepts_every_category_label_shown_in_the_product_category_list(): void
    {
        $categories = \App\Support\HomepageProductCategories::options();
        $rows = [];
        foreach ($categories as $label) {
            $rows[] = ['Produk ' . count($rows), $label];
        }

        $path = tempnam(sys_get_temp_dir(), 'all-category-import');
        file_put_contents($path, XlsxWriter::build(['NAMA PRODUK', 'KATEGORI'], $rows));
        $file = new UploadedFile(
            $path,
            'all-categories.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        try {
            $response = (new AdminProdukImportController())->import($request);
            $errors = $response->getSession()->get('errors');
            $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
        } finally {
            @unlink($path);
        }

        foreach (array_keys($categories) as $index => $category) {
            $this->assertDatabaseHas('medicines', [
                'nama_obat' => 'Produk ' . $index,
                'kategori_produk' => $category,
            ]);
        }
    }

    public function test_import_accepts_blank_optional_product_columns_and_incomplete_packaging_columns(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'blank-product-import');
        file_put_contents($path, implode("\n", [
            'NAMA PRODUK,KATEGORI,STOK SATUAN,UNIT PACKAGING ENABLED,STRIP PER KEMASAN,TABLET PER STRIP,MODAL,HARGA JUAL B2B,HARGA JUAL B2C,LINK FOTO 1,LINK FOTO 2',
            'Produk Kolom Kosong,,5,YA,,,,,,,',
        ]) . "\n");
        $file = new UploadedFile($path, 'products.csv', 'text/csv', null, true);
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        $response = (new AdminProdukImportController())->import($request);
        $errors = $response->getSession()->get('errors');
        $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');

        $product = Medicine::where('nama_obat', 'Produk Kolom Kosong')->firstOrFail();
        $this->assertSame('OBAT', $product->kategori_produk);
        $this->assertSame(5, (int) $product->stock_base_units);
        $this->assertFalse($product->usesTabletPackaging());

        @unlink($path);
    }

    public function test_import_accepts_fractional_package_stock_and_converts_to_base_units(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fractional-stock');
        file_put_contents($path, implode("\n", [
            'NAMA PRODUK,SATUAN,SEDIAAN,STOK KEMASAN,STRIP PER KEMASAN,TABLET PER STRIP',
            'Obat Stok Parsial,BOX,TABLET,2.53,10,10',
        ]) . "\n");

        $file = new UploadedFile($path, 'stock.csv', 'text/csv', null, true);
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        $response = (new AdminProdukImportController())->import($request);
        $this->assertTrue($response->isRedirect());
        $errors = $response->getSession()->get('errors');
        $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');

        $product = Medicine::where('nama_obat', 'Obat Stok Parsial')->firstOrFail();
        $this->assertSame(253, (int) $product->stock_base_units);
        $this->assertSame(2, (int) $product->stok);
        $this->assertSame(2.53, $product->stock_packages);

        @unlink($path);
    }

    public function test_downloaded_excel_template_can_be_imported_as_is(): void
    {
        $controller = new AdminProdukImportController();
        $template = $controller->downloadTemplate();
        $path = tempnam(sys_get_temp_dir(), 'template');
        file_put_contents($path, $template->getContent());

        $file = new UploadedFile(
            $path,
            'template_produk.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $request = new Request();
        $request->setMethod('POST');
        $request->merge(['mode' => 'complete']);
        $request->files->set('file', $file);

        $response = $controller->import($request);

        $this->assertTrue($response->isRedirect());
        $errors = $response->getSession()->get('errors');
        $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
        $this->assertDatabaseHas('medicines', [
            'nama_obat' => 'Promag Tablet',
            'harga_internal' => 120000,
            'harga_grosir' => 135437,
            'harga_retail' => 153102,
            'golongan' => 'Obat Bebas',
            'kategori_produk' => 'obat-resep',
            'deskripsi' => 'Membantu meredakan rasa tidak nyaman akibat asam lambung.',
        ]);
        $this->assertDatabaseHas('medicines', [
            'nama_obat' => 'Anakod Sirup',
            'satuan' => 'BOTOL',
            'unit_packaging_enabled' => false,
            'kategori_produk' => 'flu-batuk',
            'deskripsi' => 'Sirup untuk membantu meredakan gejala flu dan batuk.',
        ]);

        @unlink($path);
    }

    public function test_reimporting_xlsx_updates_existing_unassigned_product_and_keeps_each_price_column_aligned(): void
    {
        $product = Medicine::create([
            'nama_obat' => 'Promag Tablet',
            'kategori' => 'PT KALBE FARMA',
            'harga' => 123660,
            'harga_retail' => 15310230000000000,
            'harga_grosir' => 135437,
            'kelompok' => null,
            'stok' => 1,
        ]);
        $template = (new AdminProdukImportController())->downloadTemplate();
        $path = tempnam(sys_get_temp_dir(), 'template');
        file_put_contents($path, $template->getContent());

        $file = new UploadedFile(
            $path,
            'template_produk.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $request = new Request();
        $request->setMethod('POST');
        $request->files->set('file', $file);

        $response = (new AdminProdukImportController())->import($request);

        $errors = $response->getSession()->get('errors');
        $this->assertTrue($errors === null || !$errors->any(), $errors ? $errors->first('file') : 'No errors');
        $this->assertSame(1, Medicine::where('nama_obat', 'Promag Tablet')->count());
        $product->refresh();
        $this->assertSame(153102.0, (float) $product->harga_retail);
        $this->assertSame(153102.0, $product->getChannelBasePrice('b2c', 'box'));
        $this->assertSame(0.0, $product->getChannelBasePrice('b2c', 'strip'));
        $this->assertSame(0.0, $product->getChannelBasePrice('b2c', 'tablet'));

        @unlink($path);
    }

    public function test_product_crud_saves_all_template_fields_and_channel_prices(): void
    {
        $payload = [
            'nama_obat' => 'Produk CRUD',
            'kategori' => 'PT Kalbe Farma',
            'logo' => 'HIJAU',
            'golongan' => 'Obat Bebas',
            'kategori_produk' => 'SKINCARE & KOSMETIK',
            'satuan' => 'BOX',
            'kemasan' => '10 strip @ 10 tablet',
            'sediaan' => 'TABLET',
            'komposisi' => 'Komposisi contoh',
            'indikasi' => 'Indikasi contoh',
            'harga_modal' => 117771,
            'harga_internal' => 125000,
            'harga_grosir' => 135437,
            'harga_retail' => 153102,
            'stok' => 10,
            'deskripsi' => 'Deskripsi CRUD terpisah',
        ];
        $controller = new AdminProdukController();
        $payload['gambar'] = [
            UploadedFile::fake()->create('produk-utama.jpg', 100, 'image/jpeg'),
            UploadedFile::fake()->create('produk-kedua.png', 100, 'image/png'),
        ];
        $response = $controller->store(Request::create('/', 'POST', $payload, [], [
            'gambar' => $payload['gambar'],
        ]));

        $this->assertTrue($response->isRedirect());
        $product = Medicine::where('nama_obat', 'Produk CRUD')->firstOrFail();
        $this->assertCount(2, $product->image_gallery);
        $this->assertSame('SKINCARE & KOSMETIK', $product->kategori_produk);
        $this->assertSame('HIJAU', $product->logo);
        $this->assertSame('Obat Bebas', $product->golongan);
        $this->assertSame('Deskripsi CRUD terpisah', $product->deskripsi);
        $this->assertSame(125000.0, (float) $product->harga_internal);
        $this->assertSame('10 strip @ 10 tablet', $product->kemasan);
        $this->assertFalse($product->unit_packaging_enabled);
        $this->assertNull($product->strips_per_box);
        $this->assertNull($product->tablets_per_strip);
        $this->assertSame(10, (int) $product->stock_base_units);
        $this->assertSame(135437.0, $product->getChannelBasePrice('b2b'));
        $this->assertSame(153102.0, $product->getChannelBasePrice('b2c'));
        $this->assertNull($product->price_strip);
        $this->assertNull($product->price_tablet);

        $product->forceFill([
            'kelompok' => 'PBF',
            'sku' => 'CRUD-001',
            'grade' => 'A',
            'terjual' => 4,
            'distributor' => 'Distributor Lama',
        ])->save();
        $payload['nama_obat'] = 'Produk CRUD Diperbarui';
        $payload['logo'] = 'MERAH';
        unset($payload['gambar']);
        $controller->update(Request::create('/', 'PUT', $payload), $product);
        $product->refresh();
        $this->assertSame('Produk CRUD Diperbarui', $product->nama_obat);
        $this->assertSame('Deskripsi CRUD terpisah', $product->deskripsi);
        $this->assertSame(10, (int) $product->stock_base_units);
        $this->assertSame('MERAH', $product->logo);
        $this->assertSame('PBF', $product->kelompok);
        $this->assertSame('CRUD-001', $product->sku);
        $this->assertSame('A', $product->grade);
        $this->assertSame(4, (int) $product->terjual);
        $this->assertSame('Distributor Lama', $product->distributor);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.produk.create'))
            ->assertOk()
            ->assertSee('name="deskripsi"', false);
        $this->get(route('admin.produk.edit', $product))
            ->assertOk()
            ->assertSee('name="deskripsi"', false)
            ->assertSee('Deskripsi CRUD terpisah');

        $controller->updateInline(new Request([
            'harga_modal' => 120000,
            'harga_grosir' => 136000,
            'harga_retail' => 154000,
            'price_strip' => 15400,
            'price_tablet' => 1540,
            'stok' => 9,
        ]), $product);
        $product->refresh();
        $this->assertSame(154000.0, $product->getChannelBasePrice('b2c'));
        $this->assertSame(136000.0, $product->getChannelBasePrice('b2b'));
        $this->assertSame(0.0, (float) $product->price_strip);
        $this->assertSame(0.0, (float) $product->price_tablet);

        foreach ($product->image_gallery as $image) {
            \App\Helpers\ImageHelper::deleteProductImage($image);
        }
        $this->assertSame(154000.0, $product->getChannelBasePrice('b2c'));
    }

    public function test_product_crud_stores_stock_as_base_units_without_packaging_conversion(): void
    {
        $controller = new AdminProdukController();
        $payload = [
            'nama_obat' => 'Produk Stok Satuan',
            'kategori' => 'Pabrik Contoh',
            'kategori_produk' => 'otc',
            'satuan' => 'BOX',
            'sediaan' => 'TABLET',
            'harga_retail' => 10000,
            'stok' => 15,
        ];

        $controller->store(Request::create('/', 'POST', $payload));
        $product = Medicine::where('nama_obat', 'Produk Stok Satuan')->firstOrFail();

        $this->assertSame(15, (int) $product->stock_base_units);
        $this->assertFalse($product->usesTabletPackaging());

        $payload['stok'] = 7;
        $controller->update(Request::create('/', 'PUT', $payload), $product);
        $product->refresh();

        $this->assertSame(7, (int) $product->stock_base_units);
        $this->assertSame(7, $product->getPhysicalStockUnits());
        $this->assertFalse($product->usesTabletPackaging());
    }

    public function test_product_create_and_edit_forms_include_a_separate_description_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Medicine::create([
            'nama_obat' => 'Produk Form Deskripsi',
            'kategori' => 'Pabrik Contoh',
            'kategori_produk' => 'otc',
            'deskripsi' => 'Deskripsi form',
            'harga' => 10000,
            'stok' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.produk.create'))
            ->assertOk()
            ->assertSee('name="deskripsi"', false);
        $this->get(route('admin.produk.edit', $product))
            ->assertOk()
            ->assertSee('name="deskripsi"', false)
            ->assertSee('Deskripsi form');
    }

    public function test_admin_product_index_loads_without_missing_company_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Medicine::create([
            'nama_obat' => 'Produk Satuan',
            'satuan' => 'Box',
            'sediaan' => 'Tablet',
            'kategori' => 'Pabrik Contoh',
            'harga' => 10000,
            'stok' => 2,
            'stock_base_units' => 200,
            'unit_packaging_enabled' => true,
            'strips_per_box' => 10,
            'tablets_per_strip' => 10,
            'price_strip' => 1000,
            'price_tablet' => 100,
            'deskripsi' => 'Produk untuk tes harga strip dan tablet',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.produk.index'))
            ->assertOk()
            ->assertSee('Produk Kami')
            ->assertDontSee('class="kat-tabs"', false)
            ->assertDontSee('class="kat-tab', false)
            ->assertDontSee('Harga Strip / Tablet')
            ->assertDontSee('name="price_strip"', false)
            ->assertDontSee('name="price_tablet"', false);
    }

}
