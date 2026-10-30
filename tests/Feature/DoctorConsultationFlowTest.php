<?php

namespace Tests\Feature;

use App\Models\DoctorConsultation;
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

    public function test_patient_data_is_required_before_doctor_search_without_guardian_checkbox(): void
    {
        $this->get(route('consultations.doctors'))->assertForbidden();

        $this->post(route('consultations.patient.store'), [
            'patient_name' => 'Pasien Anak',
            'patient_birth_date' => now()->subYears(10)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567890',
            'patient_email' => 'wali@example.test',
            'height_cm' => '140',
            'weight_kg' => '35.5',
            'complaint' => 'Demam sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'privacy_consent' => '1',
        ])
            ->assertRedirect(route('consultations.doctors'))
            ->assertSessionHas('doctor_consultation.intake.complaint', 'Demam sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.')
            ->assertSessionHas('doctor_consultation.intake.height_cm', '140');
    }

    public function test_consultation_uses_configured_session_duration_after_admin_payment_verification(): void
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
            'payment_proof' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
            'privacy_consent' => '1',
        ]);
        $response->assertRedirect();
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Menunggu verifikasi pembayaran')
            ->assertSee('Timer sesi 30 menit dimulai saat dokter masuk ke room.')
            ->assertSee('Bukti pembayaran sudah dikirim');

        $consultation = DoctorConsultation::firstOrFail();
        $this->assertNotNull($consultation->payment_proof_path);
        Storage::disk('local')->assertExists($consultation->payment_proof_path);
        $this->assertSame('awaiting_payment', $consultation->status);
        $this->assertSame('pending', $consultation->payment_status);
        $this->assertNull($consultation->starts_at);
        $this->assertSame(30, $consultation->session_duration_minutes);
        $this->assertSame('Nyeri kepala sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.', $consultation->complaint);
        $this->assertSame(170, $consultation->height_cm);
        $this->assertSame(65.5, (float) $consultation->weight_kg);
        $this->assertNull($consultation->requested_medicine);
        $this->assertNotSame(
            'Nyeri kepala sejak kemarin. Obat yang diinginkan: Paracetamol 500 mg.',
            DB::table('doctor_consultations')->where('id', $consultation->id)->value('complaint')
        );

        $admin = User::factory()->create(['role' => 'admin']);
        $invitation = null;
        $this->actingAs($admin)
            ->post(route('admin.consultations.mark-paid', $consultation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Pembayaran diverifikasi. Timer sesi 30 menit dimulai saat dokter masuk ke room.')
            ->assertSessionHas('consultation_whatsapp_links.doctor', function ($url) use (&$invitation): bool {
                if (!is_string($url)) {
                    return false;
                }

                $invitation = $url;

                return true;
            })
            ->assertSessionMissing('consultation_whatsapp_links.patient');

        $consultation->refresh();
        $this->assertSame('active', $consultation->status);
        $this->assertSame('paid', $consultation->payment_status);
        $this->assertNull($consultation->starts_at);
        $this->assertNull($consultation->ends_at);
        $this->assertSame($admin->id, $consultation->payment_verified_by_user_id);
        $this->assertNull($consultation->patient_room_token_hash);
        $this->assertNotNull($consultation->doctor_room_token_hash);
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Menunggu dokter bergabung');

        $this->assertNotEmpty($invitation);
        parse_str((string) parse_url($invitation, PHP_URL_QUERY), $invitationQuery);
        preg_match('#https?://[^ ]+#', $invitationQuery['text'] ?? '', $roomLink);
        $this->assertNotEmpty($roomLink);
        $roomToken = basename((string) parse_url($roomLink[0], PHP_URL_PATH));
        $this->get(route('consultations.room.join', ['token' => $roomToken]))->assertOk();
        $this->post(route('consultations.room.redeem', ['token' => $roomToken]))
            ->assertRedirect(route('consultations.room', $consultation));

        $consultation->refresh();
        $this->assertNotNull($consultation->starts_at);
        $this->assertEquals(30, $consultation->starts_at->diffInMinutes($consultation->ends_at));
        $this->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee('Kirim tautan dokter')
            ->assertSee('Buat ulang tautan WA')
            ->assertDontSee('Kirim tautan room lewat WhatsApp');
        $this->actingAs($admin)
            ->get(route('admin.consultations.payment-proof', $consultation))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', "inline; filename=\"bukti-pembayaran-{$consultation->id}.pdf\"");
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

    public function test_admin_can_view_payment_proof_and_reject_payment_with_patient_refund_guidance(): void
    {
        Storage::fake('local');
        $doctor = $this->createDoctor();
        $patientToken = bin2hex(random_bytes(32));
        $proofPath = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf')->store('consultation-payment-proofs', 'local');
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
            'payment_method' => 'transfer',
            'payment_channel' => 'online',
            'payment_proof_path' => $proofPath,
            'payment_status' => 'pending',
            'status' => 'awaiting_payment',
            'privacy_consent_at' => now(),
            'payment_due_at' => now()->addMinutes(30),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.consultations.index', ['tab' => 'transactions']))
            ->assertOk()
            ->assertSee(route('admin.consultations.payment-proof', $consultation), false)
            ->assertSee('Tolak pembayaran');

        $this->get(route('admin.consultations.payment-proof', $consultation))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->post(route('admin.consultations.reject-payment', $consultation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Pembayaran ditolak. Pasien akan diarahkan untuk menghubungi admin terkait pengembalian dana.');

        $consultation->refresh();
        $this->assertSame('rejected', $consultation->payment_status);
        $this->assertSame('cancelled', $consultation->status);

        $this->get(route('consultations.patient.show', ['token' => $patientToken]))
            ->assertOk()
            ->assertSee('Pembayaran ditolak.')
            ->assertSee('Jika saldo Anda sudah terpotong, pengembaliannya akan diproses manual oleh admin.')
            ->assertSee('Kembali ke daftar dokter')
            ->assertSee('Hubungi admin')
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
        ])
            ->get(route('consultations.details', $doctor))
            ->assertOk()
            ->assertSee('Pilih Pasien')
            ->assertSee('Pasien Terpilih')
            ->assertSee('+ Profil Baru');

        $this->get(route('consultations.patient.create', $doctor))
            ->assertOk()
            ->assertSee('Tambah profil pasien');
        $this->post(route('consultations.patient.profile.store', $doctor), [
            'patient_name' => 'Profil Baru',
            'patient_birth_date' => now()->subYears(12)->toDateString(),
            'patient_gender' => 'Laki-laki',
            'patient_phone' => '081234567891',
            'patient_email' => 'new-profile@example.test',
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
            ->assertSee('name="payment_proof"', false)
            ->assertSee('Konfirmasi Konsultasi');
        $this->assertDatabaseCount('doctor_consultations', 0);

        $this->post(route('consultations.store', $doctor), [
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'payment_method' => 'qris',
            'payment_channel' => 'offline',
            'privacy_consent' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('doctor_consultations', [
            'patient_name' => 'Pasien Terpilih',
            'patient_email' => 'selected@example.test',
            'payment_method' => 'qris',
            'payment_channel' => 'offline',
            'payment_proof_path' => null,
        ]);
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
        ];

        $this->withSession($patientSession)
            ->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertSee('data-payment-method-panel="qris"', false)
            ->assertSee('data-payment-method-panel="transfer" hidden', false)
            ->assertSee('Atas nama Apotek Medix')
            ->assertSee('Tunggu Verifikasi Admin');

        $this->withSession([...$patientSession, '_old_input' => ['payment_method' => 'transfer']])
            ->get(route('consultations.payment', $doctor))
            ->assertOk()
            ->assertSee('data-payment-method-panel="qris" hidden', false)
            ->assertSee('data-payment-method-panel="transfer"', false)
            ->assertSee('BCA:')
            ->assertSee('0658484265')
            ->assertSee('Atas nama Apotek Medix')
            ->assertSee('Tunggu Verifikasi Admin');
    }

    public function test_online_payment_requires_proof_while_offline_payment_does_not(): void
    {
        $doctor = $this->createDoctor();
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
        ];
        $payload = [
            'complaint' => 'Nyeri kepala sejak kemarin.',
            'drug_allergies' => 'Tidak ada yang diketahui',
            'payment_method' => 'qris',
            'privacy_consent' => '1',
        ];

        $this->withSession($patientSession)
            ->post(route('consultations.store', $doctor), [...$payload, 'payment_channel' => 'online'])
            ->assertSessionHasErrors('payment_proof');
        $this->assertDatabaseCount('doctor_consultations', 0);

        $this->withSession($patientSession)
            ->post(route('consultations.store', $doctor), [...$payload, 'payment_channel' => 'offline'])
            ->assertRedirect();
        $this->assertDatabaseHas('doctor_consultations', [
            'patient_name' => 'Pasien Contoh',
            'payment_channel' => 'offline',
            'payment_proof_path' => null,
            'payment_status' => 'pending',
        ]);
    }

    public function test_patient_token_stays_in_patient_room_and_cannot_reuse_doctor_room_session(): void
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
            'starts_at' => now(),
            'ends_at' => now()->addMinutes(15),
        ]);

        $this->withSession(['consultation_room_access.' . $consultation->id => 'doctor'])
            ->get(route('consultations.patient.show', ['token' => $patientToken]))
            ->assertOk()
            ->assertSee('Chat dokter')
            ->assertDontSee('Buka ruang chat dokter');
        $this->postJson(route('consultations.patient.messages.store', ['token' => $patientToken]), ['body' => 'Saya sudah masuk.'])
            ->assertCreated()
            ->assertJsonPath('sender_type', 'patient')
            ->assertJsonPath('sender_name', 'Pasien Lunas');
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

        $this->withSession($patientSession)
            ->get(route('consultations.doctors'))
            ->assertOk()
            ->assertSee('Dokter Umum')
            ->assertSee('Dokter Hewan')
            ->assertSeeText('Dokter Belum Tersedia dan akan segera Tersedia')
            ->assertDontSee('Dr. Contoh');

        $this->withSession($patientSession)
            ->get(route('consultations.doctors', ['specialty' => $doctor->specialty]))
            ->assertOk()
            ->assertSee('Dr. Contoh')
            ->assertSee('Chat')
            ->assertDontSee('Lihat profil')
            ->assertDontSee('Dokter Hewan');

        $this->withSession($patientSession)
            ->get(route('consultations.doctors', ['specialty' => 'Dokter Hewan']))
            ->assertOk()
            ->assertSeeText('Dokter Belum Tersedia dan akan segera Tersedia untuk kategori Dokter Hewan');
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

    public function test_admin_cannot_take_doctor_offline_while_a_payment_reserves_their_slot(): void
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
                'str_verified' => '1',
                'is_active' => '1',
                'is_online' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('is_active');

        $this->assertTrue($doctor->fresh()->is_online);
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

        $this->assertNull($consultation->fresh()->prescription_number);
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
