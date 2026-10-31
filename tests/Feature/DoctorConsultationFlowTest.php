<?php

namespace Tests\Feature;

use App\Models\DoctorConsultation;
use App\Models\ConsultationAccessCode;
use App\Models\DoctorConsultationPaymentSetting;
use App\Models\Medicine;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DoctorConsultationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_enters_details_before_selecting_doctor_and_goes_directly_to_payment(): void
    {
        $doctor = $this->createDoctor();

        $this->get(route('consultations.intake'))
            ->assertOk()
            ->assertSee('Data pasien')
            ->assertSee('Informasi konsultasi')
            ->assertSee('name="patient_address"', false)
            ->assertDontSee('Dr. Contoh');

        $this->post(route('consultations.patient.store'), [
            'patient_name' => 'Pasien Contoh',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'patient_address' => 'Jl. Contoh No. 10, Bogor',
            'height_cm' => '170',
            'weight_kg' => '65.5',
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'privacy_consent' => '1',
        ])->assertRedirect(route('consultations.doctors'));

        $this->get(route('consultations.doctors'))
            ->assertOk()
            ->assertSee('Dr. Contoh')
            ->assertSee(route('consultations.payment', $doctor), false)
            ->assertDontSee('Semua kategori');
        $this->get(route('consultations.doctor', $doctor))
            ->assertOk()
            ->assertSee('Chat Dokter');

        $this->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertSee('Pasien Contoh')
            ->assertSee('Dr. Contoh')
            ->assertSee('STR-TEST-123');
    }

    public function test_offline_status_and_existing_consultations_do_not_limit_new_bookings(): void
    {
        $doctor = $this->createDoctor();
        $doctor->update(['is_online' => false]);
        DoctorConsultationPaymentSetting::create(['qris_image_path' => 'consultation-payments/qris.png']);
        DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Sebelumnya',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'sebelumnya@example.test',
            'complaint' => 'Konsultasi yang sedang berlangsung.',
            'drug_allergies' => 'Tidak ada',
            'amount' => 49000,
            'payment_method' => 'qris',
            'payment_channel' => 'online',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->post(route('consultations.patient.store'), [
            'patient_name' => 'Pasien Baru',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'baru@example.test',
            'patient_address' => 'Jl. Contoh No. 11, Bogor',
            'height_cm' => '170',
            'weight_kg' => '65.5',
            'complaint' => 'Keluhan konsultasi baru.',
            'drug_allergies' => 'Tidak ada',
            'privacy_consent' => '1',
        ])->assertRedirect(route('consultations.doctors'));

        $this->get(route('consultations.doctors'))
            ->assertOk()
            ->assertSee('Status: Offline')
            ->assertSee('Chat Dokter');
        $this->get(route('consultations.doctor', $doctor))
            ->assertOk()
            ->assertSee('Chat Dokter')
            ->assertSee('Status layanan: Offline');
        $this->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertSee('Pasien Baru');

        $this->post(route('consultations.store', $doctor), [
            'payment_method' => 'qris',
            'payment_channel' => 'online',
            'privacy_consent' => '1',
        ])->assertRedirect();

        $this->assertSame(2, DoctorConsultation::query()->where('doctor_profile_id', $doctor->id)->count());
        $this->assertSame(1, DoctorConsultation::query()->where('status', 'active')->count());
        $this->assertSame(1, DoctorConsultation::query()->where('status', 'awaiting_payment')->count());
        $this->assertFalse($doctor->fresh()->is_online);
    }

    public function test_whatsapp_access_code_confirms_payment_and_can_only_be_used_once(): void
    {
        $doctor = $this->createDoctor();
        $doctor->update(['session_duration_minutes' => 30]);
        Storage::fake('local');
        DoctorConsultationPaymentSetting::create(['qris_image_path' => 'consultation-payments/qris.png']);
        $response = $this->withSession([
            'doctor_consultation.patient' => [
                'name' => 'Pasien Contoh',
                'birth_date' => now()->subYears(30)->toDateString(),
                'gender' => 'Perempuan',
                'phone' => '081234567890',
                'email' => 'pasien@example.test',
                'address' => 'Jl. Contoh No. 12, Bogor',
                'guardian_name' => null,
                'guardian_relationship' => null,
                'guardian_consent_at' => null,
                'privacy_consent_at' => now()->toDateTimeString(),
            ],
            'doctor_consultation.intake' => [
                'height_cm' => '170',
                'weight_kg' => '65.5',
                'complaint' => 'Nyeri kepala sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.',
                'drug_allergies' => 'Tidak ada yang diketahui',
            ],
        ])->post(route('consultations.store', $doctor), [
            'payment_method' => 'qris',
            'payment_channel' => 'online',
            'privacy_consent' => '1',
        ]);
        $response->assertRedirect();
        $patientUrl = $response->headers->get('Location');
        $patientToken = basename((string) parse_url($patientUrl, PHP_URL_PATH));
        $this->assertSame(64, strlen($patientToken));
        $patientPage = $this->get($patientUrl)
            ->assertOk()
            ->assertSee('Menunggu konfirmasi WhatsApp')
            ->assertSee('Konfirmasi Pembayaran')
            ->assertSee('access-confirm', false)
            ->assertSee('name="access_code"', false)
            ->assertSee('Mulai Konsultasi')
            ->assertSee('id="verifyAccessCode" type="button" disabled', false)
            ->assertSee('https://wa.me/628159111911', false);
        preg_match('/class="access-confirm" href="([^"]+)"/', $patientPage->getContent(), $adminUrlMatch);
        $this->assertNotEmpty($adminUrlMatch[1] ?? null);
        parse_str((string) parse_url(html_entity_decode($adminUrlMatch[1]), PHP_URL_QUERY), $adminMessageQuery);
        $this->assertSame(
            "Halo Apotek Medix, saya ingin konfirmasi pembayaran konsultasi.\n\n"
                . "Nama: Pasien Contoh\nNo. telepon: 081234567890\nAlamat: Jl. Contoh No. 12, Bogor\n\n"
                . "Saya sudah melakukan pembayaran sejumlah Rp 49.000 melalui QRIS (Online).\n"
                . "Bukti pembayaran saya lampirkan pada chat ini.\n\n"
                . "Mohon bantu verifikasi pembayaran dan kirimkan kode akses konsultasi setelah pembayaran dikonfirmasi. Terima kasih.",
            $adminMessageQuery['text'] ?? ''
        );
        $this->assertStringNotContainsString('127.0.0.1', $adminMessageQuery['text'] ?? '');
        $this->assertStringNotContainsString('Mohon bantu proses pembayaran', $adminMessageQuery['text'] ?? '');

        $consultation = DoctorConsultation::firstOrFail();
        $this->assertSame('Jl. Contoh No. 12, Bogor', $consultation->patient_address);
        $this->assertNull($consultation->payment_proof_path);
        $this->assertSame('awaiting_payment', $consultation->status);
        $this->assertSame('pending', $consultation->payment_status);
        $this->assertNull($consultation->starts_at);
        $this->assertSame(30, $consultation->session_duration_minutes);
        $this->assertSame('Nyeri kepala sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.', $consultation->complaint);
        $this->assertSame('170', $consultation->height_cm);
        $this->assertSame(65.5, (float) $consultation->weight_kg);
        $this->assertNull($consultation->requested_medicine);
        $this->assertNotSame(
            'Nyeri kepala sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.',
            DB::table('doctor_consultations')->where('id', $consultation->id)->value('complaint')
        );

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.consultations.access-codes.generate'), ['quantity' => 2])
            ->assertRedirect(route('admin.consultations.index', ['tab' => 'access-codes']))
            ->assertSessionHas('success', '2 kode akses konsultasi berhasil ditambahkan ke stok.');
        $this->assertSame(2, ConsultationAccessCode::query()->count());
        $this->assertSame(2, ConsultationAccessCode::query()->where('status', ConsultationAccessCode::STATUS_AVAILABLE)->count());
        $availableCodes = ConsultationAccessCode::query()
            ->where('status', ConsultationAccessCode::STATUS_AVAILABLE)
            ->latest()
            ->get()
            ->map(fn (ConsultationAccessCode $code) => $code->readCode())
            ->implode("\n");
        $this->get(route('admin.consultations.index', ['tab' => 'access-codes']))
            ->assertOk()
            ->assertSee('Salin semua kode tersedia')
            ->assertSee('id="all-available-access-codes"', false)
            ->assertSee(e($availableCodes), false)
            ->assertSee('navigator.clipboard.writeText(codes)', false)
            ->assertSee('Menampilkan 1–2 dari 2 kode akses')
            ->assertDontSee('Showing 1 to 2 of 2 results');

        $stockCode = ConsultationAccessCode::query()->where('status', ConsultationAccessCode::STATUS_AVAILABLE)->firstOrFail();
        $accessCode = $stockCode->readCode();
        $this->assertMatchesRegularExpression('/^0[0-9]{4}$/', (string) $accessCode);
        $this->assertSame(2, ConsultationAccessCode::query()->where('status', ConsultationAccessCode::STATUS_AVAILABLE)->count());
        $this->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi WhatsApp')
            ->assertDontSee('Verifikasi</button>')
            ->assertDontSee('Tidak diverifikasi');
        $this->get($patientUrl)
            ->assertOk()
            ->assertSee('Menunggu konfirmasi WhatsApp')
            ->assertSee('Masukkan kode 5 digit yang dikirim admin melalui WhatsApp')
            ->assertSee('id="verifyAccessCode" type="button"', false)
            ->assertSee('id="startWhatsApp" type="submit" disabled', false);

        $wrongCode = '0' . str_pad((string) (((int) substr((string) $accessCode, 1) + 1) % 10000), 4, '0', STR_PAD_LEFT);
        $this->postJson(route('consultations.patient.access-code.verify', ['token' => basename((string) parse_url($patientUrl, PHP_URL_PATH))]), [
            'access_code' => $wrongCode,
        ])->assertStatus(422);
        $this->post(route('consultations.patient.whatsapp.start', ['token' => $patientToken]), [
            'access_code' => $wrongCode,
        ])->assertSessionHasErrors('access_code');
        $this->post(route('consultations.patient.whatsapp.start', ['token' => $patientToken]), [])
            ->assertSessionHasErrors('access_code');
        $this->postJson(route('consultations.patient.access-code.verify', ['token' => $patientToken]), [
            'access_code' => $accessCode,
        ])->assertOk()->assertJsonPath('valid', true)
            ->assertJsonPath('message', 'Kode valid. Pembayaran dikonfirmasi melalui WhatsApp.');
        $consultation->refresh();
        $this->assertSame('awaiting_whatsapp', $consultation->status);
        $this->assertSame('paid', $consultation->payment_status);
        $this->assertNull($consultation->payment_verified_by_user_id);
        $this->assertSame($admin->id, $stockCode->fresh()->generated_by_user_id);
        $stockCode->refresh();
        $this->assertSame(ConsultationAccessCode::STATUS_ASSIGNED, $stockCode->status);
        $this->assertSame($consultation->id, $stockCode->doctor_consultation_id);
        $this->assertSame(1, ConsultationAccessCode::query()->where('status', ConsultationAccessCode::STATUS_AVAILABLE)->count());
        $this->assertTrue($consultation->access_code_expires_at->isFuture());
        $whatsappResponse = $this->post(route('consultations.patient.whatsapp.start', ['token' => $patientToken]), [
            'access_code' => $accessCode,
        ]);
        $whatsappResponse->assertRedirect();
        $doctorWhatsappUrl = $whatsappResponse->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $doctorWhatsappUrl);
        parse_str((string) parse_url($doctorWhatsappUrl, PHP_URL_QUERY), $doctorMessageQuery);
        $this->assertStringContainsString('Pasien Contoh', $doctorMessageQuery['text'] ?? '');
        $this->assertStringContainsString('Nyeri kepala sejak kemarin.', $doctorMessageQuery['text'] ?? '');
        $this->assertStringContainsString('Tidak ada yang diketahui', $doctorMessageQuery['text'] ?? '');

        $consultation->refresh();
        $this->assertNotNull($consultation->starts_at);
        $this->assertEquals(30, $consultation->starts_at->diffInMinutes($consultation->ends_at));
        $this->assertNotNull($consultation->access_code_used_at);
        $this->assertSame(ConsultationAccessCode::STATUS_USED, $stockCode->fresh()->status);
        $this->assertNotNull($stockCode->fresh()->used_at);
        $reuseResponse = $this->postJson(route('consultations.patient.access-code.verify', ['token' => $patientToken]), [
            'access_code' => $accessCode,
        ]);
        $this->assertContains($reuseResponse->getStatusCode(), [422, 429]);
        $this->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertDontSee('Kirim tautan dokter')
            ->assertDontSee('Buat ulang tautan WA')
            ->assertSee('Konsultasi WhatsApp aktif');
        $this->actingAs($admin)
            ->get(route('admin.consultations.index', ['tab' => 'access-codes']))
            ->assertOk()
            ->assertDontSee($accessCode)
            ->assertDontSee('Terpakai (tidak berlaku)');
    }

    public function test_admin_can_resend_room_invitation_when_consultation_duration_is_missing(): void
    {
        $doctor = $this->createDoctor();
        $doctor->update(['session_duration_minutes' => 30]);
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Undangan',
            'patient_birth_date' => now()->subYears(27)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Keluhan konsultasi.',
            'drug_allergies' => 'Tidak ada',
            'amount' => 30000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(30),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.consultations.room-invitations', $consultation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Tautan room dokter baru dibuat. Tautan sebelumnya sudah tidak berlaku.')
            ->assertSessionHas('consultation_whatsapp_links.doctor', function (string $url): bool {
                return str_contains($url, 'Timer%2030%20menit');
            });

        $this->assertNotNull($consultation->fresh()->doctor_room_token_hash);
    }

    public function test_doctor_room_entry_starts_the_consultation_timer(): void
    {
        $doctor = $this->createDoctor();
        $doctor->update(['session_duration_minutes' => 30]);
        $doctorToken = bin2hex(random_bytes(32));
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'doctor_room_token_hash' => hash('sha256', $doctorToken),
            'patient_name' => 'Pasien Menunggu',
            'patient_birth_date' => now()->subYears(27)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Keluhan konsultasi.',
            'drug_allergies' => 'Tidak ada',
            'amount' => 30000,
            'session_duration_minutes' => 30,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
        ]);
        $this->assertTrue($consultation->isActive());
        $this->assertNull($consultation->starts_at);
        $this->assertNull($consultation->ends_at);

        $this->withSession(['consultation_room_access.' . $consultation->id => 'patient'])
            ->get(route('consultations.room', $consultation))
            ->assertOk()
            ->assertSee('Menunggu dokter bergabung');
        $this->getJson(route('consultations.room.updates', $consultation))
            ->assertOk()
            ->assertJsonPath('starts_at', null)
            ->assertJsonPath('ends_at', null);

        $this->get(route('consultations.room.join', ['token' => $doctorToken]))->assertOk();
        $this->post(route('consultations.room.redeem', ['token' => $doctorToken]))
            ->assertRedirect(route('consultations.room', $consultation));

        $consultation->refresh();
        $this->assertNotNull($consultation->starts_at);
        $this->assertEquals(30, $consultation->starts_at->diffInMinutes($consultation->ends_at));
    }

    public function test_admin_consultation_dashboard_shows_payment_status_without_confirmation_actions(): void
    {
        $doctor = $this->createDoctor();
        $patientToken = bin2hex(random_bytes(32));
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', $patientToken),
            'patient_name' => 'Pasien Ditolak',
            'patient_birth_date' => '1996-05-04',
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'Cash',
            'payment_channel' => 'online',
            'payment_status' => 'pending',
            'status' => 'awaiting_payment',
            'privacy_consent_at' => now(),
            'payment_due_at' => now()->addMinutes(30),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('Tunai')
            ->assertSee('Menunggu konfirmasi WhatsApp')
            ->assertDontSee('Tolak pembayaran')
            ->assertDontSee('Tidak diverifikasi')
            ->assertDontSee('Verifikasi</button>')
            ->assertDontSee(route('admin.consultations.payment-proof', $consultation), false);

        $consultation->refresh();
        $this->assertSame('pending', $consultation->payment_status);
        $this->assertSame('awaiting_payment', $consultation->status);

        $this->get(route('consultations.patient.show', ['token' => $patientToken]))
            ->assertOk()
            ->assertSee('Menunggu konfirmasi WhatsApp')
            ->assertSee('admin akan mengirim kode akses')
            ->assertSee('https://wa.me/' . config('contacts.customer_service.whatsapp'), false);
    }

    public function test_chat_flow_selects_patient_then_opens_payment_summary_before_creating_consultation(): void
    {
        $doctor = $this->createDoctor();
        DoctorConsultationPaymentSetting::create(['qris_image_path' => 'consultation-payments/qris.png']);
        $selectedProfile = [
            'name' => 'Pasien Terpilih',
            'birth_date' => now()->subYears(28)->toDateString(),
            'gender' => 'Perempuan',
            'phone' => '6281234567890',
            'email' => 'selected@example.test',
            'guardian_name' => null,
            'guardian_relationship' => null,
            'guardian_consent_at' => null,
            'privacy_consent_at' => now()->toDateTimeString(),
        ];
        $otherProfile = [...$selectedProfile, 'name' => 'Pasien Lain', 'email' => 'other@example.test'];
        $this->withSession([
            'doctor_consultation.patient' => $selectedProfile,
            'doctor_consultation.patient_profiles' => [
                'selected-profile' => $selectedProfile,
                'other-profile' => $otherProfile,
            ],
            'doctor_consultation.intake' => [
                'height_cm' => null,
                'weight_kg' => null,
                'complaint' => 'Nyeri kepala sejak kemarin.',
                'drug_allergies' => 'Tidak ada yang diketahui',
            ],
        ])
            ->get(route('consultations.details', $doctor))
            ->assertOk()
            ->assertSee('Pilih Pasien')
            ->assertSee('Pasien Terpilih')
            ->assertSee('+ Profil Baru');

        $this->get(route('consultations.patient.create', $doctor))
            ->assertOk()
            ->assertSee('Data pasien');
        $this->post(route('consultations.patient.profile.store', $doctor), [
            'patient_name' => 'Profil Baru',
            'patient_birth_date' => now()->subYears(12)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567891',
            'patient_email' => 'new-profile@example.test',
            'patient_address' => 'Jl. Contoh No. 13, Bogor',
            'privacy_consent' => '1',
        ])->assertRedirect(route('consultations.details', $doctor));
        $this->get(route('consultations.details', $doctor))
            ->assertOk()
            ->assertSee('Profil Baru')
            ->assertSee('Pasien Terpilih');

        $this->post(route('consultations.patient.select', $doctor), [
            'patient_profile' => 'selected-profile',
        ])->assertRedirect(route('consultations.payment', $doctor));

        $this->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertSee('Ringkasan Pembayaran')
            ->assertSee('Pasien Terpilih')
            ->assertSee('QRIS')
            ->assertSee('name="payment_channel"', false)
            ->assertSee('value="online"', false)
            ->assertSee('value="offline"', false)
            ->assertDontSee('name="payment_proof"', false)
            ->assertSee('Konfirmasi Pembayaran')
            ->assertSee('fa-brands fa-whatsapp', false)
            ->assertDontSee('wajib, maks. 5 MB');
        $this->assertDatabaseCount('doctor_consultations', 0);

        Storage::fake('local');
        $this->post(route('consultations.store', $doctor), [
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'payment_method' => 'qris',
            'payment_channel' => 'online',
            'privacy_consent' => '1',
        ])->assertRedirect();

        $consultation = DoctorConsultation::firstOrFail();
        $this->assertSame('Pasien Terpilih', $consultation->patient_name);
        $this->assertSame('selected@example.test', $consultation->patient_email);
        $this->assertSame('qris', $consultation->payment_method);
        $this->assertSame('online', $consultation->payment_channel);
        $this->assertNull($consultation->payment_proof_path);
    }

    public function test_payment_summary_shows_only_the_selected_payment_method_details(): void
    {
        $doctor = $this->createDoctor();
        DoctorConsultationPaymentSetting::create([
            'qris_image_path' => 'consultation-payments/qris.png',
            'bank_name' => 'BCA',
            'account_number' => '0658484265',
            'account_holder' => 'Apotek Medix',
        ]);
        $patientSession = [
            'doctor_consultation.patient' => [
                'name' => 'Pasien Contoh',
                'birth_date' => now()->subYears(30)->toDateString(),
                'gender' => 'Perempuan',
                'phone' => '081234567890',
                'email' => 'pasien@example.test',
                'guardian_name' => null,
                'guardian_relationship' => null,
                'guardian_consent_at' => null,
                'privacy_consent_at' => now()->toDateTimeString(),
            ],
            'doctor_consultation.intake' => [
                'height_cm' => null,
                'weight_kg' => null,
                'complaint' => 'Nyeri kepala sejak kemarin.',
                'drug_allergies' => 'Tidak ada yang diketahui',
            ],
        ];

        $this->withSession($patientSession)
            ->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertDontSee('value="online" checked', false)
            ->assertDontSee('value="offline" checked', false)
            ->assertDontSee('value="qris" checked', false)
            ->assertDontSee('payment-method-card is-selected', false)
            ->assertSee('data-payment-method-section hidden', false)
            ->assertSee('name="payment_method" value="qris" disabled required', false)
            ->assertSee('name="payment_method" value="transfer" disabled required', false)
            ->assertSee('value="qris"', false)
            ->assertSee('value="transfer"', false)
            ->assertSee('value="Cash"', false)
            ->assertSee('data-cash-option hidden', false)
            ->assertSee('Simpan QRIS')
            ->assertSee('payment-method-reveal', false)
            ->assertSee('.payment-method-card.is-selected .payment-method-reveal', false)
            ->assertDontSee('.payment-method-card:hover .payment-method-reveal', false)
            ->assertSee('makeRadiosToggleable(channelInputs)', false)
            ->assertSee('makeRadiosToggleable(paymentMethodInputs)', false)
            ->assertSee('if (selectedInput !== input)', false)
            ->assertSee('scrollIntoViewIfNeeded', false)
            ->assertSee("window.matchMedia('(prefers-reduced-motion: reduce)')", false)
            ->assertSee('class="payment-or" aria-hidden="true">atau..</span>', false)
            ->assertSee('data-cash-or aria-hidden="true" hidden>atau..</span>', false)
            ->assertSee('<dialog class="payment-reminder"', false)
            ->assertSee('Jangan lupa simpan bukti pembayaran Anda.')
            ->assertSee('aria-label="Tutup pengingat"', false)
            ->assertSee('data-reminder-confirm>Oke</button>', false)
            ->assertSee('paymentReminder?.showModal()', false)
            ->assertSee('paymentReminder.close()', false)
            ->assertSee('requestSubmit(paymentSubmitButton)', false)
            ->assertSee('Atas Nama:')
            ->assertSee('Apotek Medix')
            ->assertSee('Verifikasi dilakukan admin melalui WhatsApp.</p>', false);

        $this->withSession([...$patientSession, '_old_input' => ['payment_channel' => 'online', 'payment_method' => 'transfer']])
            ->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertDontSee('data-payment-method-section hidden', false)
            ->assertSee('value="online" checked', false)
            ->assertSee('value="transfer" checked', false)
            ->assertDontSee('value="transfer" disabled', false)
            ->assertSee('payment-method-card is-selected" data-payment-card', false)
            ->assertSee('BCA')
            ->assertSee('0658484265')
            ->assertSee('payment-account-number', false)
            ->assertSee('data-copy-account="0658484265"', false)
            ->assertSee('Salin nomor rekening')
            ->assertSee('Atas Nama:')
            ->assertSee('Nomor rekening')
            ->assertSee('Apotek Medix')
            ->assertSee('Verifikasi dilakukan admin melalui WhatsApp.');

        $this->withSession([...$patientSession, '_old_input' => ['payment_channel' => 'offline', 'payment_method' => 'Cash']])
            ->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertDontSee('data-payment-method-section hidden', false)
            ->assertSee('data-cash-option', false)
            ->assertDontSee('data-cash-option hidden', false)
            ->assertSee('value="qris"', false)
            ->assertSee('value="transfer"', false)
            ->assertSee('value="Cash" checked', false)
            ->assertSee('data-cash-or aria-hidden="true">atau..</span>', false)
            ->assertSee('payment-method-card is-selected" data-payment-card data-cash-option', false)
            ->assertSee('> Tunai <small>(khusus offline)</small>')
            ->assertSee('Pembayaran tunai dilakukan langsung di apotek.')
            ->assertDontSee('> Cash <small>(khusus offline)</small>');
    }

    public function test_consultation_payment_can_be_submitted_without_site_upload_for_online_or_offline(): void
    {
        $doctor = $this->createDoctor();
        $offlineDoctor = $this->createDoctor('offline-doctor@example.test');
        DoctorConsultationPaymentSetting::create(['qris_image_path' => 'consultation-payments/qris.png']);
        $patientSession = [
            'doctor_consultation.patient' => [
                'name' => 'Pasien Contoh',
                'birth_date' => now()->subYears(30)->toDateString(),
                'gender' => 'Perempuan',
                'phone' => '081234567890',
                'email' => 'pasien@example.test',
                'guardian_name' => null,
                'guardian_relationship' => null,
                'guardian_consent_at' => null,
                'privacy_consent_at' => now()->toDateTimeString(),
            ],
            'doctor_consultation.intake' => [
                'height_cm' => null,
                'weight_kg' => null,
                'complaint' => 'Nyeri kepala sejak kemarin.',
                'drug_allergies' => 'Tidak ada yang diketahui',
            ],
        ];
        $payload = [
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'payment_method' => 'qris',
            'privacy_consent' => '1',
        ];

        $this->withSession($patientSession)
            ->post(route('consultations.store', $doctor), [...$payload, 'payment_channel' => 'online'])
            ->assertRedirect();
        $onlineConsultation = DoctorConsultation::where('doctor_profile_id', $doctor->id)->firstOrFail();
        $this->assertSame('online', $onlineConsultation->payment_channel);
        $this->assertSame('qris', $onlineConsultation->payment_method);
        $this->assertNull($onlineConsultation->payment_proof_path);

        $offlineResponse = $this->withSession($patientSession)
            ->post(route('consultations.store', $offlineDoctor), [...$payload, 'payment_channel' => 'offline'])
            ->assertRedirect();
        $offlinePatientUrl = $offlineResponse->headers->get('Location');
        $offlinePatientPage = $this->get($offlinePatientUrl)
            ->assertOk()
            ->assertSee('Konfirmasi Pembayaran');
        preg_match('/class="access-confirm" href="([^"]+)"/', $offlinePatientPage->getContent(), $offlineAdminUrlMatch);
        $this->assertNotEmpty($offlineAdminUrlMatch[1] ?? null);
        parse_str((string) parse_url(html_entity_decode($offlineAdminUrlMatch[1]), PHP_URL_QUERY), $offlineMessageQuery);
        $this->assertStringContainsString('Metode pembayaran: QRIS', $offlineMessageQuery['text'] ?? '');
        $offlineConsultation = DoctorConsultation::where('doctor_profile_id', $offlineDoctor->id)->firstOrFail();
        $this->assertSame('offline', $offlineConsultation->payment_channel);
        $this->assertSame('qris', $offlineConsultation->payment_method);
        $this->assertNull($offlineConsultation->payment_proof_path);

        $cashDoctor = $this->createDoctor('cash-doctor@example.test');
        $this->withSession($patientSession)
            ->post(route('consultations.store', $cashDoctor), [...$payload, 'payment_channel' => 'offline', 'payment_method' => 'Cash'])
            ->assertRedirect();
        $cashConsultation = DoctorConsultation::where('doctor_profile_id', $cashDoctor->id)->firstOrFail();
        $this->assertSame('offline', $cashConsultation->payment_channel);
        $this->assertSame('Cash', $cashConsultation->payment_method);

        $onlineCashDoctor = $this->createDoctor('online-cash-doctor@example.test');
        $this->withSession($patientSession)
            ->post(route('consultations.store', $onlineCashDoctor), [...$payload, 'payment_channel' => 'online', 'payment_method' => 'Cash'])
            ->assertSessionHasErrors('payment_method');
    }

    public function test_patient_page_hides_website_chat_after_whatsapp_consultation_starts(): void
    {
        $doctor = $this->createDoctor();
        $patientToken = bin2hex(random_bytes(32));
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', $patientToken),
            'patient_name' => 'Pasien Lunas',
            'patient_birth_date' => '1996-05-04',
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'qris',
            'payment_channel' => 'online',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'access_code_used_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->withSession(['consultation_room_access.' . $consultation->id => 'doctor'])
            ->get(route('consultations.patient.show', ['token' => $patientToken]))
            ->assertOk()
            ->assertSee('Konsultasi sudah dimulai')
            ->assertDontSee('sessionMessages');
        $this->postJson(route('consultations.patient.messages.store', ['token' => $patientToken]), ['body' => 'Saya sudah masuk.'])
            ->assertNotFound();
        $this->get(route('consultations.room', $consultation))
            ->assertNotFound();
    }

    public function test_admin_consultation_dashboard_has_doctor_transaction_and_patient_tabs(): void
    {
        $doctor = $this->createDoctor();
        DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Terkonsultasi',
            'patient_birth_date' => '1996-05-04',
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Keluhan sensitif yang tidak boleh ditampilkan di daftar pasien.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'status' => 'awaiting_payment',
            'privacy_consent_at' => now(),
            'payment_due_at' => now()->addMinutes(30),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.consultations.index', ['tab' => 'doctors']))
            ->assertOk()
            ->assertSee('Data Dokter')
            ->assertSee('Tambah dokter')
            ->assertDontSee('Antrean pembayaran dan sesi');

        $this->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('Antrean pembayaran dan sesi')
            ->assertSee('id="doctorQrisCropButton"', false)
            ->assertSee('id="doctorQrisCropDialog"', false)
            ->assertSee('id="doctorQrisCropCanvas"', false)
            ->assertSee('id="doctorQrisCropConfirm"', false)
            ->assertSee('Pasien Terkonsultasi')
            ->assertDontSee('Data Pasien</h2>');

        $this->get(route('admin.consultations.index', ['tab' => 'patients']))
            ->assertOk()
            ->assertSee('Data Pasien')
            ->assertSee('Export Excel')
            ->assertSee('Pasien Terkonsultasi')
            ->assertSee('04-05-1996')
            ->assertSee('081234567890')
            ->assertSee('Dr. Contoh')
            ->assertDontSee('Keluhan sensitif yang tidak boleh ditampilkan di daftar pasien.');
    }

    public function test_admin_dashboard_and_sidebar_show_stock_and_pending_consultation_indicators(): void
    {
        $doctor = $this->createDoctor();
        Medicine::create([
            'nama_obat' => 'Stok Rendah Indikator',
            'satuan' => 'Box',
            'kategori' => 'Pabrik Contoh',
            'harga' => 1000,
            'stok' => 4,
            'stock_base_units' => 4,
            'deskripsi' => 'Produk untuk menguji indikator stok rendah.',
        ]);

        $consultationData = [
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Menunggu',
            'patient_birth_date' => '1996-05-04',
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Demam.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'status' => 'awaiting_payment',
            'privacy_consent_at' => now(),
        ];
        DoctorConsultation::create(array_replace($consultationData, ['payment_due_at' => now()->addMinutes(30)]));
        DoctorConsultation::create(array_replace($consultationData, [
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'payment_due_at' => now()->subMinute(),
        ]));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="sidebar-low-stock-count"', false)
            ->assertSee('aria-label="1 produk stok rendah"', false)
            ->assertSee('id="sidebar-pending-consultation-count"', false)
            ->assertSee('aria-label="1 konsultasi menunggu"', false)
            ->assertSee('id="stat-pending-consultations">1</div>', false)
            ->assertSee('Lihat laporan stok')
            ->assertSee('Buka antrean dokter');

        $this->get(route('admin.dashboard.stats'))
            ->assertOk()
            ->assertJsonPath('lowStok', 1)
            ->assertJsonPath('pendingConsultations', 1);

        $this->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('class="consult-tab-count" aria-label="1 menunggu verifikasi"', false);
    }

    public function test_admin_can_export_all_patient_form_data_to_excel(): void
    {
        $doctor = $this->createDoctor();
        $lastComplaint = 'Keluhan khusus pasien terakhir yang harus ikut diekspor.';
        for ($index = 1; $index <= 26; $index++) {
            DoctorConsultation::create([
                'doctor_profile_id' => $doctor->id,
                'patient_access_hash' => hash('sha256', "export-patient-{$index}"),
                'patient_name' => "Pasien Excel {$index}",
                'patient_birth_date' => '1996-05-04',
                'patient_gender' => 'Perempuan',
                'patient_phone' => '081234567890',
                'patient_email' => "pasien{$index}@example.test",
                'height_cm' => '165',
                'weight_kg' => '60.5',
                'complaint' => $index === 26 ? $lastComplaint : "Keluhan pasien {$index}.",
                'drug_allergies' => 'Tidak ada yang diketahui',
                'amount' => 49000,
                'payment_method' => 'transfer',
                'payment_status' => 'paid',
                'status' => 'completed',
                'privacy_consent_at' => now(),
                'paid_at' => now(),
                'starts_at' => now()->subMinutes(20),
                'ends_at' => now()->subMinutes(5),
                'completed_at' => now()->subMinutes(5),
            ]);
        }

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)
            ->get(route('admin.consultations.patients.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertMatchesRegularExpression(
            '/^attachment; filename="data_pasien_konsultasi_\d{4}-\d{2}-\d{2}_\d{6}\.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );
        $this->assertStringStartsWith("PK\x03\x04", $response->getContent());
        $this->assertStringContainsString('Nama lengkap', $response->getContent());
        $this->assertStringContainsString('Keluhan dan obat yang diinginkan', $response->getContent());
        $this->assertStringContainsString('Persetujuan penggunaan data', $response->getContent());
        $this->assertStringContainsString('Pasien Excel 26', $response->getContent());
        $this->assertStringContainsString($lastComplaint, $response->getContent());
    }

    public function test_admin_can_create_doctor_profile_without_login_credentials(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.consultations.doctors.store'), [
                'display_name' => 'Dr. Tanpa Akun',
                'whatsapp_phone' => '085814009654',
                'specialty' => 'Dokter Umum',
                'str_number' => 'STR-NEW-123',
                'experience_years' => 5,
                'consultation_price' => 50000,
                'session_duration_minutes' => 45,
                'str_verified' => '1',
                'is_active' => '1',
                'is_online' => '1',
            ])
            ->assertRedirect(route('admin.consultations.index'));

        $doctor = DoctorProfile::where('display_name', 'Dr. Tanpa Akun')->firstOrFail();
        $this->assertNull($doctor->user_id);
        $this->assertSame('6285814009654', $doctor->whatsapp_phone);
        $this->assertSame(45, $doctor->session_duration_minutes);
    }

    public function test_admin_doctor_form_uses_patient_specialty_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.consultations.doctors.create'))
            ->assertOk()
            ->assertSee('<select id="specialty" name="specialty" required>', false)
            ->assertSee('<option value="Dokter Umum">Dokter Umum</option>', false)
            ->assertSee('<option value="Spesialis Anak">Spesialis Anak</option>', false)
            ->assertSee('id="session_duration_minutes" name="session_duration_minutes" type="number" min="5" max="240"', false);

        $this->post(route('admin.consultations.doctors.store'), [
            'display_name' => 'Dr. Kategori Tidak Valid',
            'whatsapp_phone' => '085814009654',
            'specialty' => 'Spesialis yang tidak tersedia',
            'str_number' => 'STR-INVALID-123',
            'experience_years' => 5,
            'consultation_price' => 50000,
        ])
            ->assertSessionHasErrors('specialty');

        $this->assertDatabaseMissing('doctor_profiles', ['display_name' => 'Dr. Kategori Tidak Valid']);
    }

    public function test_patient_selects_a_specialty_category_before_doctor_profiles_are_shown(): void
    {
        $doctor = $this->createDoctor();
        $patientSession = [
            'doctor_consultation.patient' => [
                'name' => 'Pasien Contoh',
                'birth_date' => now()->subYears(30)->toDateString(),
                'gender' => 'Perempuan',
                'phone' => '6281234567890',
                'email' => 'pasien@example.test',
                'guardian_name' => null,
                'guardian_relationship' => null,
                'guardian_consent_at' => null,
                'privacy_consent_at' => now()->toDateTimeString(),
            ],
        ];

        $patientSession['doctor_consultation.intake'] = [
            'height_cm' => '170',
            'weight_kg' => '65',
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
        ];

        $this->get(route('consultations.doctors'))
            ->assertRedirect(route('consultations.intake'));

        $this->withSession($patientSession)
            ->get(route('consultations.doctors'))
            ->assertOk()
            ->assertSee('Dr. Contoh')
            ->assertDontSee('Dokter Hewan')
            ->assertSee(route('consultations.payment', $doctor), false);

        $this->withSession($patientSession)
            ->get(route('consultations.doctors', ['specialty' => $doctor->specialty]))
            ->assertOk()
            ->assertSee('Dr. Contoh')
            ->assertSee('Chat Dokter')
            ->assertSee(route('consultations.payment', $doctor), false)
            ->assertDontSee('Semua kategori');

        $this->withSession($patientSession)
            ->get(route('consultations.doctors', ['specialty' => 'Dokter Hewan']))
            ->assertOk()
            ->assertSee('Dr. Contoh')
            ->assertDontSee('Dokter Belum Tersedia dan akan segera Tersedia');
    }

    public function test_doctor_room_link_is_confirmed_then_consumed_once(): void
    {
        $doctor = $this->createDoctor();
        $doctorToken = bin2hex(random_bytes(32));
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'doctor_room_token_hash' => hash('sha256', $doctorToken),
            'patient_name' => 'Pasien Konsultasi',
            'patient_birth_date' => now()->subYears(27)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Batuk sejak dua hari.',
            'drug_allergies' => 'Tidak ada',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->get(route('consultations.room.join', ['token' => $doctorToken]))
            ->assertOk()
            ->assertSee('Masuk room konsultasi');
        $this->assertNull($consultation->fresh()->doctor_room_token_used_at);

        $this->post(route('consultations.room.redeem', ['token' => $doctorToken]))
            ->assertRedirect(route('consultations.room', $consultation))
            ->assertSessionHas('consultation_room_access.' . $consultation->id, 'doctor');

        $this->assertNotNull($consultation->fresh()->doctor_room_token_used_at);
        $this->post(route('consultations.room.redeem', ['token' => $doctorToken]))
            ->assertNotFound();
    }

    public function test_admin_can_update_doctor_availability_while_payment_is_pending(): void
    {
        $doctor = $this->createDoctor();
        DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Contoh',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'status' => 'awaiting_payment',
            'privacy_consent_at' => now(),
            'payment_due_at' => now()->addMinutes(30),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->put(route('admin.consultations.doctors.update', $doctor), [
                'display_name' => $doctor->display_name,
                'whatsapp_phone' => $doctor->whatsapp_phone,
                'specialty' => $doctor->specialty,
                'str_number' => $doctor->str_number,
                'experience_years' => $doctor->experience_years,
                'consultation_price' => $doctor->consultation_price,
                'session_duration_minutes' => 15,
                'str_verified' => '1',
                'is_active' => '1',
                'is_online' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', "Profil {$doctor->display_name} berhasil diperbarui.");

        $this->assertFalse($doctor->fresh()->is_online);
        $this->assertSame('pending', DoctorConsultation::query()->firstOrFail()->payment_status);
    }

    public function test_admin_can_end_an_active_consultation_and_stop_both_sides_from_sending_messages(): void
    {
        $doctor = $this->createDoctor();
        $patientToken = bin2hex(random_bytes(32));
        $doctorToken = bin2hex(random_bytes(32));
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', $patientToken),
            'doctor_room_token_hash' => hash('sha256', $doctorToken),
            'patient_name' => 'Pasien Konsultasi',
            'patient_birth_date' => now()->subYears(27)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Batuk sejak dua hari.',
            'drug_allergies' => 'Tidak ada',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.consultations.end-session', $consultation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Sesi konsultasi berhasil diakhiri.');

        $consultation->refresh();
        $this->assertSame('completed', $consultation->status);
        $this->assertNotNull($consultation->completed_at);
        $this->assertNotNull($consultation->ends_at);
        $this->assertTrue($consultation->ends_at->isPast() || $consultation->ends_at->equalTo(now()));
        $this->assertNull($consultation->doctor_room_token_hash);

        $this->withSession(['consultation_room_access.' . $consultation->id => 'patient'])
            ->postJson(route('consultations.room.messages.store', $consultation), ['body' => 'Pesan setelah sesi'])
            ->assertStatus(409);
        $this->postJson(route('consultations.patient.messages.store', ['token' => $patientToken]), ['body' => 'Pesan setelah sesi'])
            ->assertStatus(409);
        $this->get(route('consultations.room.join', ['token' => $doctorToken]))
            ->assertNotFound();
    }

    public function test_admin_can_view_and_delete_completed_session_with_its_chat_history(): void
    {
        Storage::fake('local');
        $paymentProofPath = UploadedFile::fake()
            ->create('receipt.pdf', 100, 'application/pdf')
            ->store('consultation-payment-proofs', 'local');
        $doctor = $this->createDoctor();
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Selesai',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Keluhan konsultasi untuk sesi yang sudah selesai.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_proof_path' => $paymentProofPath,
            'payment_status' => 'paid',
            'status' => 'completed',
            'privacy_consent_at' => now(),
            'paid_at' => now()->subMinutes(20),
            'starts_at' => now()->subMinutes(20),
            'ends_at' => now()->subMinutes(5),
            'completed_at' => now()->subMinutes(5),
        ]);
        $patientMessage = $consultation->messages()->create([
            'sender_type' => 'patient',
            'body' => 'Saya mengalami sakit kepala.',
        ]);
        $doctorMessage = $consultation->messages()->create([
            'sender_type' => 'doctor',
            'body' => 'Silakan cukup istirahat dan perhatikan gejalanya.',
        ]);
        $activeConsultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Aktif',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'aktif@example.test',
            'complaint' => 'Keluhan konsultasi yang masih aktif.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.consultations.index', ['tab' => 'transactions']))
            ->delete(route('admin.consultations.delete', $activeConsultation))
            ->assertSessionHasErrors('consultation');
        $this->assertDatabaseHas('doctor_consultations', ['id' => $activeConsultation->id]);

        $this->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('Lihat chat')
            ->assertSee('Hapus sesi');

        $this->get(route('admin.consultations.chat', $consultation))
            ->assertOk()
            ->assertJsonPath('patient_name', 'Pasien Selesai')
            ->assertJsonPath('doctor_name', 'Dr. Contoh')
            ->assertJsonPath('messages.0.body', 'Saya mengalami sakit kepala.')
            ->assertJsonPath('messages.1.body', 'Silakan cukup istirahat dan perhatikan gejalanya.');

        $this->delete(route('admin.consultations.delete', $consultation))
            ->assertRedirect(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertSessionHas('success', 'Sesi konsultasi dan riwayat chat berhasil dihapus.');

        $this->assertDatabaseMissing('doctor_consultations', ['id' => $consultation->id]);
        $this->assertDatabaseMissing('doctor_consultation_messages', ['id' => $patientMessage->id]);
        $this->assertDatabaseMissing('doctor_consultation_messages', ['id' => $doctorMessage->id]);
        Storage::disk('local')->assertMissing($paymentProofPath);
    }

    public function test_admin_dashboard_completes_expired_sessions_and_shows_chat_and_delete_actions(): void
    {
        $doctor = $this->createDoctor();
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Sesi Kedaluwarsa',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'expired@example.test',
            'complaint' => 'Keluhan pada sesi yang waktunya sudah habis.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now()->subMinutes(20),
            'starts_at' => now()->subMinutes(20),
            'ends_at' => now()->subMinutes(5),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('Lihat chat')
            ->assertSee('Hapus sesi');

        $consultation->refresh();
        $this->assertSame('completed', $consultation->status);
        $this->assertNotNull($consultation->completed_at);
    }

    public function test_doctor_can_end_only_their_active_room_session(): void
    {
        $doctor = $this->createDoctor();
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Room',
            'patient_birth_date' => now()->subYears(30)->toDateString(),
            'patient_gender' => 'Perempuan',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Sakit gigi dan ingin berkonsultasi tentang obat.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'height_cm' => '165',
            'weight_kg' => '60',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->withSession(['consultation_room_access.' . $consultation->id => 'patient'])
            ->post(route('consultations.room.end', $consultation))
            ->assertNotFound();

        $this->withSession(['consultation_room_access.' . $consultation->id => 'doctor'])
            ->get(route('consultations.room', $consultation))
            ->assertOk()
            ->assertSee('Identitas & kontak')
            ->assertSee('Informasi konsultasi')
            ->assertSee('Keluhan & obat yang diinginkan')
            ->assertSee('Sakit gigi dan ingin berkonsultasi tentang obat.')
            ->assertSee('Akhiri sesi');

        $this->post(route('consultations.room.end', $consultation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Sesi konsultasi berhasil diakhiri.');

        $consultation->refresh();
        $this->assertSame('completed', $consultation->status);
        $this->assertNotNull($consultation->completed_at);
        $this->assertTrue($consultation->ends_at->isPast() || $consultation->ends_at->equalTo(now()));

        $this->postJson(route('consultations.room.messages.store', $consultation), ['body' => 'Pesan setelah sesi'])
            ->assertStatus(409);
    }

    public function test_room_session_grants_only_the_redeemed_role_access_to_messages(): void
    {
        $doctor = $this->createDoctor();
        $patientToken = bin2hex(random_bytes(32));
        $doctorToken = bin2hex(random_bytes(32));
        $consultation = DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', $patientToken),
            'doctor_room_token_hash' => hash('sha256', $doctorToken),
            'patient_name' => 'Pasien Konsultasi',
            'patient_birth_date' => now()->subYears(27)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Batuk sejak dua hari.',
            'drug_allergies' => 'Tidak ada',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->get(route('consultations.room', $consultation))
            ->assertNotFound();

        $this->get(route('consultations.room.join', ['token' => $doctorToken]))->assertOk();
        $this->post(route('consultations.room.redeem', ['token' => $doctorToken]))
            ->assertRedirect(route('consultations.room', $consultation));

        $this->postJson(route('consultations.room.messages.store', $consultation), ['body' => 'Selamat siang.'])
            ->assertCreated()
            ->assertJsonPath('sender_type', 'doctor');

        $this->getJson(route('consultations.room.updates', $consultation))
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Selamat siang.');

        $this->getJson(route('consultations.patient.updates', ['token' => $patientToken]))
            ->assertOk()
            ->assertJsonPath('messages', []);
        $this->getJson(route('consultations.patient.updates', ['token' => str_repeat('a', 64)]))
            ->assertNotFound();
        $this->assertNotSame(
            'Selamat siang.',
            DB::table('doctor_consultation_messages')->where('doctor_consultation_id', $consultation->id)->value('body')
        );
    }

    public function test_doctor_issues_online_prescription_and_checkout_requires_the_approved_items(): void
    {
        $doctor = $this->createDoctor();
        $medicine = Medicine::create([
            'nama_obat' => 'Amlodipine 5 mg',
            'logo' => 'K',
            'golongan' => 'Obat Keras',
            'is_resep' => true,
            'satuan' => 'Box',
            'kategori' => 'Obat',
            'harga' => 12000,
            'harga_retail' => 12000,
            'stok' => 8,
            'stock_base_units' => 8,
            'deskripsi' => 'Produk resep untuk pengujian.',
        ]);
        $consultation = $this->createPrescriptionConsultation($doctor);
        $doctorSession = ['consultation_room_access.' . $consultation->id => 'doctor'];

        $this->actingAs(User::findOrFail($doctor->user_id))
            ->withSession($doctorSession)
            ->getJson(route('consultations.room.products', ['consultation' => $consultation, 'q' => 'Amlodipine']))
            ->assertOk()
            ->assertJsonPath('products.0.id', $medicine->id);

        $this->withSession($doctorSession)
            ->postJson(route('consultations.room.prescriptions.store', $consultation), [
                'items' => [[
                    'id' => $medicine->id,
                    'quantity' => 2,
                    'instructions' => 'Minum satu tablet sehari setelah makan.',
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('items.0.quantity', 2);

        $prescription = $consultation->fresh();
        $this->assertMatchesRegularExpression('/^RSP-[A-Z0-9]{12}$/', $prescription->prescription_number);

        $this->withSession(['consultation_room_access.' . $consultation->id => 'patient'])
            ->getJson(route('consultations.room.updates', $consultation))
            ->assertOk()
            ->assertJsonPath('prescription.number', $prescription->prescription_number)
            ->assertJsonPath('prescription.items.0.instructions', 'Minum satu tablet sehari setelah makan.');

        $checkout = [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pasien Konsultasi',
            'phone' => '081234567890',
            'address' => 'Alamat Pasien',
            'price_channel' => 'b2c',
            'payment_method' => 'Cash',
            'total' => 36000,
            'consultation_prescription_number' => $prescription->prescription_number,
            'items' => [[
                'id' => $medicine->id,
                'quantity' => 3,
                'sale_unit' => 'box',
                'catatan' => 'Petunjuk yang diubah pasien.',
            ]],
        ];
        $this->postJson(route('orders.history.store'), $checkout)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consultation_prescription_number');
        $this->assertNull($prescription->fresh()->prescription_used_at);

        $checkout['items'][0]['quantity'] = 2;
        $checkout['total'] = 24000;
        $result = $this->postJson(route('orders.history.store'), $checkout)
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertSame(
            $prescription->prescription_number,
            DB::table('purchase_histories')->where('id', $result->json('id'))->value('consultation_prescription_number')
        );
        $savedItems = json_decode(
            DB::table('purchase_histories')->where('id', $result->json('id'))->value('items'),
            true
        );
        $this->assertSame('Minum satu tablet sehari setelah makan.', $savedItems[0]['catatan']);
        $this->assertNotNull($prescription->fresh()->prescription_used_at);
        $this->get($result->json('invoice_url'))
            ->assertOk()
            ->assertSee($prescription->prescription_number);

        $this->postJson(route('orders.history.store'), $checkout)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consultation_prescription_number');
    }

    public function test_offline_consultation_cannot_issue_a_product_prescription(): void
    {
        $doctor = $this->createDoctor();
        $consultation = $this->createPrescriptionConsultation($doctor, 'offline');
        $this->actingAs(User::findOrFail($doctor->user_id))
            ->withSession(['consultation_room_access.' . $consultation->id => 'doctor'])
            ->postJson(route('consultations.room.prescriptions.store', $consultation), [
                'items' => [[
                    'id' => 1,
                    'quantity' => 1,
                    'instructions' => 'Sesuai aturan pakai.',
                ]],
            ])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.consultations.prescription.store', $consultation), ['items' => []])
            ->assertConflict();

        $this->assertNull($consultation->fresh()->prescription_number);
    }

    public function test_admin_can_create_online_prescription_checkout_with_patient_identity_and_delivery_only(): void
    {
        $doctor = $this->createDoctor();
        $consultation = $this->createPrescriptionConsultation($doctor);
        $medicine = Medicine::create([
            'nama_obat' => 'Obat Resep Admin',
            'logo' => 'K',
            'golongan' => 'Obat Keras',
            'is_resep' => true,
            'satuan' => 'Box',
            'kategori' => 'Obat',
            'harga' => 12000,
            'harga_retail' => 12000,
            'stok' => 8,
            'stock_base_units' => 8,
            'deskripsi' => 'Produk resep untuk pengujian.',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->post(route('admin.consultations.prescription.store', $consultation), [
                'items' => [[
                    'id' => $medicine->id,
                    'quantity' => 1,
                    'instructions' => 'Minum satu kali sehari.',
                ]],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Resep berhasil dibuat. Kirim tautan checkout kepada pasien.');

        $consultation->refresh();
        $this->assertNotNull($consultation->prescription_number);
        $this->assertSame(1, $consultation->prescribed_items[0]['quantity']);
        $checkoutUrl = $response->getSession()->get('consultation_prescription_link.url');
        $this->assertIsString($checkoutUrl);

        $this->get($checkoutUrl)
            ->assertOk()
            ->assertSee('value="Pasien Resep"', false)
            ->assertSee('value="081234567890"', false)
            ->assertSee('placeholder="Masukkan alamat pengiriman lengkap *"', false)
            ->assertDontSee('tabPickupBtn');

        $checkoutPayload = [
            'buyer_type' => 'umum',
            'buyer_name' => 'Pasien Resep',
            'phone' => '081234567890',
            'address' => 'Alamat Pasien',
            'delivery_method' => 'pickup',
            'price_channel' => 'b2c',
            'payment_method' => 'Cash',
            'total' => 12000,
            'consultation_prescription_number' => $consultation->prescription_number,
            'items' => [[
                'id' => $medicine->id,
                'quantity' => 1,
                'sale_unit' => 'box',
            ]],
        ];
        $this->postJson(route('orders.history.store'), $checkoutPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consultation_prescription_number');

        $checkoutPayload['delivery_method'] = 'delivery';
        $checkoutPayload['buyer_name'] = 'Nama Tidak Sesuai';
        $this->postJson(route('orders.history.store'), $checkoutPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consultation_prescription_number');

        $checkoutPayload['buyer_name'] = 'Pasien Resep';
        $checkoutPayload['delivery_method'] = 'delivery';
        $checkoutPayload['address'] = '';
        $this->postJson(route('orders.history.store'), $checkoutPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('consultation_prescription_number');
    }

    private function createPrescriptionConsultation(DoctorProfile $doctor, string $paymentChannel = 'online'): DoctorConsultation
    {
        return DoctorConsultation::create([
            'doctor_profile_id' => $doctor->id,
            'patient_access_hash' => hash('sha256', bin2hex(random_bytes(32))),
            'patient_name' => 'Pasien Resep',
            'patient_birth_date' => now()->subYears(27)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'pasien@example.test',
            'complaint' => 'Keluhan untuk konsultasi resep.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'amount' => 49000,
            'payment_method' => 'transfer',
            'payment_channel' => $paymentChannel,
            'payment_status' => 'paid',
            'status' => 'active',
            'privacy_consent_at' => now(),
            'paid_at' => now(),
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);
    }

    private function createDoctor(string $email = 'doctor@example.test'): DoctorProfile
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'user', 'is_cashier' => false]);

        return $user->doctorProfile()->create([
            'display_name' => 'Dr. Contoh',
            'whatsapp_phone' => '6281234567890',
            'specialty' => 'Dokter Umum',
            'str_number' => 'STR-TEST-123',
            'str_verified' => true,
            'experience_years' => 8,
            'consultation_price' => 49000,
            'is_online' => true,
            'is_active' => true,
        ]);
    }
}
