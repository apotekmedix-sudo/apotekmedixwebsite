<?php

namespace Tests\Feature;

use App\Models\GassRoomFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GassRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_requires_the_configured_access_code_before_showing_files(): void
    {
        config(['gass.access_code' => 'gass1234']);

        $this->get(route('gass.room'))
            ->assertRedirect(route('gass.access.form'));

        $this->get(route('gass.access.form'))
            ->assertOk()
            ->assertSee('Masukkan kode akses sebelum masuk ke ruang file.');

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.access.form'))
            ->assertOk()
            ->assertSee('Kode akses');

        $this->get(route('gass.room'))
            ->assertRedirect(route('gass.access.form'));

        $this->post(route('gass.access'), ['access_code' => 'wrong'])
            ->assertRedirect(route('gass.access.form'))
            ->assertSessionHasErrors('access_code');

        $this->post(route('gass.access'), ['access_code' => 'gass1234'])
            ->assertRedirect(route('gass.room'))
            ->assertSessionHas('gass_room_access', true);

        $this->get(route('gass.room'))->assertOk()->assertSee('Unggah File');
    }

    public function test_only_authorized_visitors_can_upload_and_download_files(): void
    {
        Storage::fake('local');
        $file = GassRoomFile::query()->create([
            'original_name' => 'private.bin',
            'path' => 'gass-room/private-file',
            'size_bytes' => 4,
            'mime_type' => 'application/octet-stream',
        ]);
        Storage::disk('local')->put($file->path, 'data');

        $this->get(route('gass.files.download', $file))->assertForbidden();
        $this->post(route('gass.files.upload'), [
            'files' => [UploadedFile::fake()->create('blocked.bin')],
        ])->assertForbidden();

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room'))
            ->assertOk()
            ->assertSee('private.bin');

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.files.download', $file))
            ->assertOk()
            ->assertDownload('private.bin');
    }

    public function test_authorized_visitors_can_preview_safe_files_and_download_unsupported_types(): void
    {
        Storage::fake('local');
        $pdf = GassRoomFile::query()->create([
            'original_name' => 'guide.pdf',
            'path' => 'gass-room/guide-pdf',
            'size_bytes' => 4,
            'mime_type' => 'application/pdf',
        ]);
        $workbook = GassRoomFile::query()->create([
            'original_name' => 'sheet.xlsx',
            'path' => 'gass-room/sheet-xlsx',
            'size_bytes' => 4,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
        Storage::disk('local')->put($pdf->path, '%PDF');
        Storage::disk('local')->put($workbook->path, 'xlsx');

        $this->get(route('gass.files.view', $pdf))->assertForbidden();

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.files.view', $pdf))
            ->assertOk()
            ->assertSee('data-preview-type="pdf"', false);

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.files.content', $pdf))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.files.view', $workbook))
            ->assertOk()
            ->assertSee('data-preview-type="excel"', false)
            ->assertSee('gass-preview-', false);
    }

    public function test_image_video_audio_and_word_files_get_rich_previews(): void
    {
        $cases = [
            ['photo.png', 'image/png', 'image'],
            ['clip.mp4', 'video/mp4', 'video'],
            ['voice.mp3', 'audio/mpeg', 'audio'],
            ['guide.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'word'],
        ];

        foreach ($cases as [$name, $mimeType, $previewType]) {
            $file = GassRoomFile::query()->create([
                'original_name' => $name,
                'path' => 'gass-room/'.str_replace('.', '-', $name),
                'size_bytes' => 10,
                'mime_type' => $mimeType,
            ]);
            Storage::disk('local')->put($file->path, 'preview-data');

            $response = $this->withSession(['gass_room_access' => true])
                ->get(route('gass.files.view', $file));

            $response->assertOk()->assertSee('data-preview-type="'.$previewType.'"', false);
            if ($previewType === 'image') {
                $response->assertSee('<img src=', false);
            } elseif ($previewType === 'video') {
                $response->assertSee('<video src=', false);
            } elseif ($previewType === 'audio') {
                $response->assertSee('<audio src=', false);
            } else {
                $response->assertSee('gass-preview-', false);
            }
        }
    }

    public function test_only_authorized_visitors_can_delete_room_files(): void
    {
        Storage::fake('local');
        $file = GassRoomFile::query()->create([
            'original_name' => 'remove-me.txt',
            'path' => 'gass-room/remove-me',
            'size_bytes' => 4,
            'mime_type' => 'text/plain',
        ]);
        Storage::disk('local')->put($file->path, 'data');

        $this->delete(route('gass.files.destroy', $file))->assertForbidden();
        $this->assertDatabaseHas('gass_room_files', ['id' => $file->id]);

        $this->withSession(['gass_room_access' => true])
            ->delete(route('gass.files.destroy', $file))
            ->assertRedirect(route('gass.room'))
            ->assertSessionHas('success', 'File berhasil dihapus.');

        $this->assertDatabaseMissing('gass_room_files', ['id' => $file->id]);
        Storage::disk('local')->assertMissing($file->path);
    }

    public function test_room_search_filters_files_by_name(): void
    {
        GassRoomFile::query()->create([
            'original_name' => 'Panduan Klinik.pdf',
            'path' => 'gass-room/clinic-guide',
            'size_bytes' => 10,
            'mime_type' => 'application/pdf',
        ]);
        GassRoomFile::query()->create([
            'original_name' => 'Daftar Harga.xlsx',
            'path' => 'gass-room/prices',
            'size_bytes' => 20,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['search' => 'klinik']))
            ->assertOk()
            ->assertSee('Panduan Klinik.pdf')
            ->assertDontSee('Daftar Harga.xlsx');
    }

    public function test_room_can_filter_by_upload_date_and_sort_by_name_or_file_size(): void
    {
        foreach ([
            ['Zebra.pdf', 300, '2026-10-03 08:00:00'],
            ['alpha.pdf', 200, '2026-10-02 08:00:00'],
            ['Middle.pdf', 500, '2026-10-03 09:00:00'],
        ] as [$name, $size, $createdAt]) {
            $file = GassRoomFile::query()->create([
                'original_name' => $name,
                'path' => 'gass-room/'.str_replace('.', '-', $name),
                'size_bytes' => $size,
                'mime_type' => 'application/pdf',
            ]);
            $file->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['uploaded_on' => '2026-10-03']))
            ->assertOk()
            ->assertSee('Zebra.pdf')
            ->assertSee('Middle.pdf')
            ->assertDontSee('alpha.pdf');

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['sort' => 'name_asc']))
            ->assertOk()
            ->assertSeeInOrder(['alpha.pdf', 'Middle.pdf', 'Zebra.pdf']);

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['sort' => 'size_asc']))
            ->assertOk()
            ->assertSeeInOrder(['alpha.pdf', 'Zebra.pdf', 'Middle.pdf']);

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['sort' => 'size_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Middle.pdf', 'Zebra.pdf', 'alpha.pdf']);
    }

    public function test_file_sharing_uses_a_file_only_public_link_without_exposing_the_access_code(): void
    {
        date_default_timezone_set('UTC');
        config(['app.timezone' => 'UTC', 'gass.access_code' => 'share-code-42']);
        $file = GassRoomFile::query()->create([
            'original_name' => 'Panduan GASS.pdf',
            'path' => 'gass-room/share-guide',
            'size_bytes' => 10,
            'mime_type' => 'application/pdf',
        ]);
        $file->forceFill(['created_at' => '2026-10-03 09:45:00', 'updated_at' => '2026-10-03 09:45:00'])->save();

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room'))
            ->assertOk()
            ->assertSee('<td class="gass-file-number">1</td>', false)
            ->assertSee('03/10/2026 16:45')
            ->assertSee('Share')
            ->assertSee('data-share-trigger', false)
            ->assertSeeInOrder(['>Lihat</a>', '>Unduh</a>', '>Share</button>', '>Hapus</button>'], false)
            ->assertDontSee('share-code-42');

        $this->withSession(['gass_room_access' => true])
            ->post(route('gass.files.share', $file))
            ->assertRedirect();

        $file->refresh();
        $this->assertTrue($file->is_public);
        $this->assertNotEmpty($file->share_token);

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room'))
            ->assertOk()
            ->assertSee(route('gass.files.shared.view', $file->share_token), false)
            ->assertDontSee('share-code-42')
            ->assertDontSee(rawurlencode(route('gass.room')), false);
    }

    public function test_share_button_can_create_and_revoke_a_public_link_without_reloading(): void
    {
        $file = GassRoomFile::query()->create([
            'original_name' => 'share-async.txt',
            'path' => 'gass-room/share-async',
            'size_bytes' => 4,
        ]);

        $response = $this->withSession(['gass_room_access' => true])
            ->withHeader('Accept', 'application/json')
            ->post(route('gass.files.share', $file));

        $response->assertOk()->assertJsonPath('is_public', true);
        $file->refresh();
        $response->assertJsonPath('url', route('gass.files.shared.view', $file->share_token));

        $this->post(route('gass.files.share', $file), [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('is_public', false);

        $file->refresh();
        $this->assertFalse($file->is_public);
        $this->assertNull($file->share_token);
    }

    public function test_public_share_links_only_expose_the_shared_file_and_can_be_revoked(): void
    {
        Storage::fake('local');
        $sharedFile = GassRoomFile::query()->create([
            'original_name' => 'shared.pdf',
            'path' => 'gass-room/shared-pdf',
            'size_bytes' => 4,
            'mime_type' => 'application/pdf',
            'is_public' => true,
            'share_token' => str_repeat('a', 64),
        ]);
        $privateFile = GassRoomFile::query()->create([
            'original_name' => 'private.pdf',
            'path' => 'gass-room/private-pdf',
            'size_bytes' => 4,
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put($sharedFile->path, '%PDF');

        $this->get(route('gass.files.shared.view', $sharedFile->share_token))
            ->assertOk()
            ->assertSee('shared.pdf')
            ->assertDontSee('private.pdf')
            ->assertDontSee(route('gass.room'))
            ->assertSee(route('gass.files.shared.content', $sharedFile->share_token));
        $this->get(route('gass.files.shared.content', $sharedFile->share_token))->assertOk();
        $this->get(route('gass.files.shared.download', $sharedFile->share_token))
            ->assertOk()
            ->assertDownload('shared.pdf');
        $this->get(route('gass.files.shared.view', $privateFile->id))->assertNotFound();

        $this->withSession(['gass_room_access' => true])
            ->post(route('gass.files.share', $sharedFile))
            ->assertRedirect();

        $this->get(route('gass.files.shared.view', str_repeat('a', 64)))->assertNotFound();
    }

    public function test_room_rejects_invalid_date_and_sort_filters(): void
    {
        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['uploaded_on' => 'not-a-date']))
            ->assertSessionHasErrors('uploaded_on');

        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room', ['sort' => 'random']))
            ->assertSessionHasErrors('sort');
    }

    public function test_room_upload_form_offers_a_dropzone_and_file_selection_summary(): void
    {
        $this->withSession(['gass_room_access' => true])
            ->get(route('gass.room'))
            ->assertOk()
            ->assertSee('id="gass-dropzone"', false)
            ->assertSee('Tarik file ke sini atau klik untuk memilih')
            ->assertSee('Pilih maksimal 5 file sekaligus')
            ->assertSee('const maxFiles = 5;', false)
            ->assertSee('id="gass-file-summary"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('id="gass-selected-files"', false)
            ->assertSee("dropzone.addEventListener('drop'", false);
    }

    public function test_upload_accepts_five_files_and_rejects_six(): void
    {
        Storage::fake('local');

        $tooManyFiles = array_map(
            fn ($index) => UploadedFile::fake()->create("too-many-{$index}.txt"),
            range(1, 6),
        );

        $this->withSession(['gass_room_access' => true])
            ->post(route('gass.files.upload'), ['files' => $tooManyFiles])
            ->assertSessionHasErrors('files');

        $this->assertSame(0, GassRoomFile::query()->count());

        $fiveFiles = array_map(
            fn ($index) => UploadedFile::fake()->create("allowed-{$index}.txt"),
            range(1, 5),
        );

        $this->withSession(['gass_room_access' => true])
            ->post(route('gass.files.upload'), ['files' => $fiveFiles])
            ->assertRedirect(route('gass.room'))
            ->assertSessionHas('success');

        $this->assertSame(5, GassRoomFile::query()->count());
    }

    public function test_upload_accepts_arbitrary_file_types_without_an_application_size_limit(): void
    {
        Storage::fake('local');
        $upload = UploadedFile::fake()->create('archive.unknown', 2048, 'application/octet-stream');

        $this->withSession(['gass_room_access' => true])
            ->post(route('gass.files.upload'), ['files' => [$upload]])
            ->assertRedirect(route('gass.room'))
            ->assertSessionHas('success');

        $storedFile = GassRoomFile::query()->firstOrFail();
        $this->assertSame('archive.unknown', $storedFile->original_name);
        $this->assertSame(2 * 1024 * 1024, $storedFile->size_bytes);
        Storage::disk('local')->assertExists($storedFile->path);
    }
}
