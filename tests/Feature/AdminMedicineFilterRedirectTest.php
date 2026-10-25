<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminMedicineController;
use App\Models\Medicine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminMedicineFilterRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_redirects_back_to_filtered_index_with_query_parameters(): void
    {
        $medicine = Medicine::create([
            'nama_obat' => 'Paracetamol',
            'kategori' => 'ABC',
            'harga' => 10000,
            'stok' => 10,
            'deskripsi' => 'Obat demam',
            'is_resep' => false,
            'is_grosir' => false,
        ]);

        $request = Request::create('/admin/medicines/' . $medicine->id, 'PUT', [
            'nama_obat' => 'Paracetamol',
            'kategori' => 'ABC',
            'harga' => 10000,
            'stok' => 10,
            'komposisi' => 'Paracetamol',
            'indikasi' => 'Demam',
            'golongan' => 'BEBAS',
            'search' => 'para',
            'tipe' => 'biasa',
            'page' => 3,
        ]);

        $response = (new AdminMedicineController())->update($request, $medicine);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            route('admin.medicines.index', ['search' => 'para', 'kategori' => 'ABC', 'tipe' => 'biasa', 'page' => 3]),
            $response->getTargetUrl()
        );
    }

    public function test_user_and_admin_can_search_and_filter_by_indikasi_komposisi_sediaan_dosis_and_golongan(): void
    {
        Medicine::create([
            'nama_obat' => 'Sanmol Sirup Anak',
            'kategori' => 'SANBE',
            'kategori_produk' => 'OBAT',
            'harga' => 18000,
            'harga_grosir' => 15000,
            'harga_retail' => 20000,
            'stok' => 20,
            'indikasi' => 'Meredakan demam dan nyeri sakit kepala',
            'komposisi' => 'Paracetamol 120 mg per 5 ml',
            'sediaan' => 'Sirup',
            'dosis' => '3x sehari 5ml',
            'golongan' => 'Obat Bebas',
            'deskripsi' => 'Sirup penurun panas anak',
        ]);

        Medicine::create([
            'nama_obat' => 'Amoxicillin Kaplet',
            'kategori' => 'KIMIA FARMA',
            'kategori_produk' => 'OBAT',
            'harga' => 12000,
            'harga_grosir' => 10000,
            'harga_retail' => 14000,
            'stok' => 15,
            'indikasi' => 'Infeksi saluran pernapasan atas',
            'komposisi' => 'Amoxicillin Trihydrate 500mg',
            'sediaan' => 'Tablet',
            'dosis' => '500mg 3x1 sehari',
            'golongan' => 'Obat Keras',
            'deskripsi' => 'Antibiotik',
        ]);

        // 1. User B2C search by indikasi ("demam")
        $this->get(route('products.apotek', ['search' => 'demam']))
            ->assertOk()
            ->assertSee('Sanmol Sirup Anak')
            ->assertDontSee('Amoxicillin Kaplet');

        // 2. User B2C search by sediaan + komposisi ("sirup paracetamol")
        $this->get(route('products.apotek', ['search' => 'sirup paracetamol']))
            ->assertOk()
            ->assertSee('Sanmol Sirup Anak')
            ->assertDontSee('Amoxicillin Kaplet');

        // 3. User B2C search by dosis variant ("500 mg" matches "500mg")
        $this->get(route('products.apotek', ['search' => '500 mg']))
            ->assertOk()
            ->assertSee('Amoxicillin Kaplet')
            ->assertDontSee('Sanmol Sirup Anak');

        // 4. User B2C dedicated attribute filters (sediaan=Tablet & golongan=Obat Keras)
        $this->get(route('products.apotek', ['sediaan' => 'Tablet', 'golongan' => 'Obat Keras']))
            ->assertOk()
            ->assertSee('Amoxicillin Kaplet')
            ->assertDontSee('Sanmol Sirup Anak');

        // 5. Admin Produk Kami search & filter by indikasi/dosis/golongan
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.produk.index', ['indikasi' => 'demam', 'sediaan' => 'Sirup']))
            ->assertOk()
            ->assertSee('Sanmol Sirup Anak')
            ->assertDontSee('Amoxicillin Kaplet');
    }

    public function test_admin_can_create_banners_with_optional_title_and_url_and_homepage_renders_slideshow(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        // 1. Create banner without judul and without url_tujuan
        $file1 = \Illuminate\Http\UploadedFile::fake()->image('banner1.jpg', 1600, 600);
        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'judul' => '',
                'url_tujuan' => '',
                'gambar' => $file1,
                'aktif' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        // 2. Create second banner with judul and url_tujuan
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('banner2.jpg', 1600, 600);
        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'judul' => 'Promo Vitamin Akhir Pekan',
                'url_tujuan' => '/products-apotek',
                'gambar' => $file2,
                'aktif' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseCount('banners', 2);
        $this->assertDatabaseHas('banners', [
            'judul' => null,
            'url_tujuan' => null,
            'aktif' => 1,
        ]);
        $this->assertDatabaseHas('banners', [
            'judul' => 'Promo Vitamin Akhir Pekan',
            'url_tujuan' => '/products-apotek',
            'aktif' => 1,
        ]);

        // 3. Homepage renders both banners & slideshow navigation dots
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="homeBannerSlider"', false)
            ->assertSee('Promo Vitamin Akhir Pekan')
            ->assertSee('id="homeBannerDots"', false);

        foreach (\App\Models\Banner::all() as $banner) {
            \App\Helpers\ImageHelper::deleteBannerImage($banner->gambar);
        }
    }
}
