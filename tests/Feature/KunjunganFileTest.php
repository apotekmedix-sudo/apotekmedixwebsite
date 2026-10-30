<?php

namespace Tests\Feature;

use App\Models\KunjunganFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KunjunganFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_requires_the_configured_access_code(): void
    {
        config(['kunjungan.access_code' => 'Rh.101185']);

        $this->get(route('kunjungan.room'))
            ->assertRedirect(route('kunjungan.access.form'));

        $this->post(route('kunjungan.access'), ['access_code' => 'wrong'])
            ->assertSessionHasErrors('access_code');

        $this->post(route('kunjungan.access'), ['access_code' => 'Rh.101185'])
            ->assertRedirect(route('kunjungan.room'))
            ->assertSessionHas('kunjungan_room_access', true);

        $this->get(route('kunjungan.room'))
            ->assertOk()
            ->assertSee('Arsip File KUNJUNGAN');
    }

    public function test_file_upload_and_download_require_room_access(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('laporan.pdf', '%PDF-1.4 test');

        $this->post(route('kunjungan.files.upload'), ['files' => [$file]])
            ->assertForbidden();

        $this->withSession(['kunjungan_room_access' => true])
            ->post(route('kunjungan.files.upload'), ['files' => [$file]])
            ->assertRedirect(route('kunjungan.room'))
            ->assertSessionHas('success', 'File berhasil diunggah ke KUNJUNGAN.');

        $storedFile = KunjunganFile::query()->firstOrFail();
        Storage::disk('local')->assertExists($storedFile->path);

        $this->get(route('kunjungan.files.download', $storedFile))
            ->assertForbidden();

        $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.files.download', $storedFile))
            ->assertOk()
            ->assertDownload('laporan.pdf');
    }

    public function test_pdf_can_be_previewed_inline_only_with_room_access(): void
    {
        Storage::fake('local');
        $file = KunjunganFile::query()->create([
            'original_name' => 'laporan.pdf',
            'path' => 'kunjungan-room/laporan.pdf',
            'size_bytes' => 13,
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put($file->path, '%PDF-1.4 test');

        $this->get(route('kunjungan.files.preview', $file))
            ->assertForbidden();

        $response = $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.files.preview', $file));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));

        $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.room'))
            ->assertOk()
            ->assertSee('Lihat PDF')
            ->assertSee(route('kunjungan.files.preview', $file), false);
    }

    public function test_search_and_sort_support_recent_old_and_alphabetical_orders(): void
    {
        foreach ([
            ['zeta.pdf', '2026-10-28 10:00:00'],
            ['Alpha.pdf', '2026-10-29 10:00:00'],
            ['Beta.pdf', '2026-10-30 10:00:00'],
        ] as [$name, $createdAt]) {
            $file = KunjunganFile::query()->create([
                'original_name' => $name,
                'path' => 'kunjungan-room/' . str_replace('.', '-', $name),
                'size_bytes' => 100,
                'mime_type' => 'application/pdf',
            ]);
            $file->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.room', ['sort' => 'newest']))
            ->assertOk()
            ->assertSeeInOrder(['Beta.pdf', 'Alpha.pdf', 'zeta.pdf']);

        $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.room', ['sort' => 'oldest']))
            ->assertOk()
            ->assertSeeInOrder(['zeta.pdf', 'Alpha.pdf', 'Beta.pdf']);

        $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.room', ['sort' => 'name_asc', 'search' => 'pdf']))
            ->assertOk()
            ->assertSeeInOrder(['Alpha.pdf', 'Beta.pdf', 'zeta.pdf']);

        $this->withSession(['kunjungan_room_access' => true])
            ->get(route('kunjungan.room', ['sort' => 'name_desc']))
            ->assertOk()
            ->assertSeeInOrder(['zeta.pdf', 'Beta.pdf', 'Alpha.pdf']);
    }

    public function test_footer_links_to_the_kunjungan_access_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('kunjungan.access.form'))
            ->assertSee('Akses File KUNJUNGAN');
    }
}
