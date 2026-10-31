<?php

namespace App\Http\Controllers;

use App\Models\DoctorConsultation;
use App\Models\ConsultationAccessCode;
use App\Models\DoctorConsultationMessage;
use App\Models\DoctorConsultationPaymentSetting;
use App\Models\DoctorProfile;
use App\Models\Medicine;
use App\Support\StockAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorConsultationController extends Controller
{
    public function intake()
    {
        return view('consultations.patient-intake', ['doctor' => null]);
    }

    public function storePatient(Request $request)
    {
        $validated = $this->validatePatient($request);
        $consultationDetails = $request->validate([
            'height_cm' => ['nullable', 'integer', 'between:30,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:2,300'],
            'complaint' => ['required', 'string', 'min:5', 'max:3000'],
            'drug_allergies' => ['required', 'string', 'max:2000'],
        ]);
        $patient = $this->patientData($validated);
        $profileId = bin2hex(random_bytes(8));

        $request->session()->put('doctor_consultation.patient', $patient);
        $request->session()->put('doctor_consultation.patient_profiles', [$profileId => $patient]);
        $request->session()->put('doctor_consultation.patient_profile_id', $profileId);
        $request->session()->put('doctor_consultation.intake', $consultationDetails);

        return redirect()->route('consultations.doctors');
    }

    public function intakeDetails(Request $request, DoctorProfile $doctor)
    {
        $this->assertPatientAndDoctorAvailable($request, $doctor);
        $profiles = $request->session()->get('doctor_consultation.patient_profiles', []);

        if (!is_array($profiles) || $profiles === []) {
            $profileId = bin2hex(random_bytes(8));
            $profiles = [$profileId => $request->session()->get('doctor_consultation.patient')];
            $request->session()->put('doctor_consultation.patient_profiles', $profiles);
            $request->session()->put('doctor_consultation.patient_profile_id', $profileId);
        }
        $selectedProfileId = $request->session()->get('doctor_consultation.patient_profile_id');
        if (!is_string($selectedProfileId) || !array_key_exists($selectedProfileId, $profiles)) {
            $selectedProfileId = array_key_first($profiles);
        }

        return view('consultations.select-patient', compact('doctor', 'profiles', 'selectedProfileId'));
    }

    public function selectPatient(Request $request, DoctorProfile $doctor)
    {
        $this->assertPatientAndDoctorAvailable($request, $doctor);
        $profiles = $request->session()->get('doctor_consultation.patient_profiles', []);
        $validated = $request->validate([
            'patient_profile' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys($profiles))],
        ]);
        $patient = $profiles[$validated['patient_profile']] ?? null;
        abort_unless(is_array($patient), 404, 'Profil pasien tidak ditemukan.');
        $request->session()->put('doctor_consultation.patient', $patient);
        $request->session()->put('doctor_consultation.patient_profile_id', $validated['patient_profile']);

        return redirect()->route('consultations.payment', $doctor);
    }

    public function newPatientProfile(Request $request, DoctorProfile $doctor)
    {
        $this->assertDoctorAvailable($doctor);

        return view('consultations.patient-intake', [
            'doctor' => $doctor,
            'isNewConsultation' => true,
        ]);
    }

    public function storePatientProfile(Request $request, DoctorProfile $doctor)
    {
        if ($request->boolean('doctor_consultation_intake')) {
            $this->assertDoctorAvailable($doctor);
            $consultationDetails = $request->validate([
                'height_cm' => ['nullable', 'integer', 'between:30,250'],
                'weight_kg' => ['nullable', 'numeric', 'between:2,300'],
                'complaint' => ['required', 'string', 'min:5', 'max:3000'],
                'drug_allergies' => ['required', 'string', 'max:2000'],
            ]);
        } else {
            $this->assertPatientAndDoctorAvailable($request, $doctor);
        }
        $validated = $this->validatePatient($request);
        $patient = $this->patientData($validated);

        if ($request->boolean('doctor_consultation_intake')) {
            $profileId = bin2hex(random_bytes(8));
            $profiles = $request->session()->get('doctor_consultation.patient_profiles', []);
            $profiles[$profileId] = $patient;
            $request->session()->put('doctor_consultation.patient', $patient);
            $request->session()->put('doctor_consultation.patient_profiles', $profiles);
            $request->session()->put('doctor_consultation.patient_profile_id', $profileId);
            $request->session()->put('doctor_consultation.intake', $consultationDetails);

            return redirect()->route('consultations.payment', $doctor);
        }

        $profileId = bin2hex(random_bytes(8));
        $profiles = $request->session()->get('doctor_consultation.patient_profiles', []);
        $profiles[$profileId] = $patient;

        $request->session()->put('doctor_consultation.patient_profiles', $profiles);
        $request->session()->put('doctor_consultation.patient', $patient);
        $request->session()->put('doctor_consultation.patient_profile_id', $profileId);

        return redirect()->route('consultations.details', $doctor);
    }

    public function paymentSummary(Request $request, DoctorProfile $doctor)
    {
        $this->assertPatientAndDoctorAvailable($request, $doctor);
        $settings = DoctorConsultationPaymentSetting::query()->first();

        return view('consultations.details', compact('doctor', 'settings'));
    }

    private function validatePatient(Request $request): array
    {
        return $request->validate([
            'patient_name' => ['required', 'string', 'max:150'],
            'patient_birth_date' => ['required', 'date', 'before_or_equal:today'],
            'patient_gender' => ['required', 'in:Laki-laki,Perempuan'],
            'patient_phone' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $digits = preg_replace('/\D+/', '', (string) $value);
                    if (!preg_match('/^(?:62|0)?8[0-9]{7,12}$/', $digits)) {
                        $fail('Nomor WhatsApp harus menggunakan nomor ponsel Indonesia yang valid.');
                    }
                },
            ],
            'patient_email' => ['required', 'email', 'max:255'],
            'patient_address' => ['required', 'string', 'max:500'],
            'privacy_consent' => ['required', 'accepted'],
        ]);
    }

    private function patientData(array $validated): array
    {
        return [
            'name' => $validated['patient_name'],
            'birth_date' => $validated['patient_birth_date'],
            'gender' => $validated['patient_gender'],
            'phone' => $this->normalizeIndonesianPhone($validated['patient_phone']),
            'email' => $validated['patient_email'],
            'address' => $validated['patient_address'],
            'guardian_name' => null,
            'guardian_relationship' => null,
            'guardian_consent_at' => null,
            'privacy_consent_at' => now()->toDateTimeString(),
        ];
    }

    public function doctors(Request $request)
    {
        if (
            !is_array($request->session()->get('doctor_consultation.patient'))
            || !is_array($request->session()->get('doctor_consultation.intake'))
        ) {
            return redirect()->route('consultations.intake');
        }

        $doctors = $this->doctorDirectoryQuery()
            ->orderBy('display_name')
            ->paginate(12)
            ->withQueryString();

        return view('consultations.doctors', compact('doctors'));
    }

    public function doctor(Request $request, DoctorProfile $doctor)
    {
        abort_unless($doctor->is_active && $doctor->str_verified && filled($doctor->whatsapp_phone), 404);
        $hasPatientProfile = is_array($request->session()->get('doctor_consultation.patient'))
            && is_array($request->session()->get('doctor_consultation.intake'));

        return view('consultations.doctor-profile', compact('doctor', 'hasPatientProfile'));
    }

    private function assertPatientAndDoctorAvailable(Request $request, DoctorProfile $doctor): void
    {
        abort_unless(
            is_array($request->session()->get('doctor_consultation.patient'))
                && is_array($request->session()->get('doctor_consultation.intake')),
            403,
            'Lengkapi data pasien dan informasi konsultasi terlebih dahulu.'
        );
        $this->assertDoctorAvailable($doctor);
    }

    private function assertDoctorAvailable(DoctorProfile $doctor): void
    {
        abort_unless($doctor->is_active && $doctor->str_verified && filled($doctor->whatsapp_phone), 404);
    }

    public function store(Request $request, DoctorProfile $doctor)
    {
        abort_unless(
            is_array($request->session()->get('doctor_consultation.patient'))
                && is_array($request->session()->get('doctor_consultation.intake')),
            403,
            'Lengkapi data pasien dan informasi konsultasi terlebih dahulu.'
        );
        abort_unless($doctor->is_active && $doctor->str_verified, 404);

        $consultationRules = [
            'height_cm' => ['nullable', 'integer', 'between:30,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:2,300'],
            'complaint' => ['required', 'string', 'min:5', 'max:3000'],
            'drug_allergies' => ['required', 'string', 'max:2000'],
        ];
        $consultationDetails = $request->session()->get('doctor_consultation.intake');
        if (!is_array($consultationDetails)) {
            $consultationDetails = $request->validate($consultationRules);
        } else {
            $consultationDetails = validator($consultationDetails, $consultationRules)->validate();
        }
        $validated = $request->validate([
            'payment_channel' => ['required', 'in:online,offline'],
            'payment_method' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $allowedMethods = $request->input('payment_channel') === 'offline'
                        ? ['qris', 'transfer', 'Cash']
                        : ['qris', 'transfer'];
                    if (!in_array($value, $allowedMethods, true)) {
                        $fail('Metode pembayaran Cash hanya tersedia untuk jalur Offline.');
                    }
                },
            ],
            'privacy_consent' => ['required', 'accepted'],
        ]);
        $settings = DoctorConsultationPaymentSetting::query()->first();
        if (
            $validated['payment_method'] !== 'Cash'
            && !$this->paymentMethodConfigured($settings, $validated['payment_method'])
        ) {
            return back()->withErrors([
                'payment_method' => 'Metode pembayaran ini belum dikonfigurasi. Hubungi admin Apotek Medix.',
            ])->withInput();
        }

        $patient = $request->session()->get('doctor_consultation.patient');
        $token = bin2hex(random_bytes(32));

        $consultation = DB::transaction(function () use ($doctor, $patient, $consultationDetails, $validated, $token): DoctorConsultation {
                $lockedDoctor = DoctorProfile::query()->lockForUpdate()->findOrFail($doctor->id);
                abort_unless(
                    $lockedDoctor->is_active && $lockedDoctor->str_verified && filled($lockedDoctor->whatsapp_phone),
                    409,
                    'Profil dokter berubah dan tidak dapat menerima permintaan baru.'
                );

                return DoctorConsultation::create([
                    'doctor_profile_id' => $lockedDoctor->id,
                    'patient_access_hash' => hash('sha256', $token),
                    'patient_name' => $patient['name'],
                    'patient_birth_date' => $patient['birth_date'],
                    'patient_gender' => $patient['gender'],
                    'patient_phone' => $patient['phone'],
                    'patient_email' => $patient['email'],
                    'patient_address' => $patient['address'] ?? null,
                    'guardian_name' => $patient['guardian_name'],
                    'guardian_relationship' => $patient['guardian_relationship'],
                    'height_cm' => $consultationDetails['height_cm'] ?? null,
                    'weight_kg' => $consultationDetails['weight_kg'] ?? null,
                    'complaint' => $consultationDetails['complaint'],
                    'drug_allergies' => $consultationDetails['drug_allergies'],
                    'requested_medicine' => $consultationDetails['requested_medicine'] ?? null,
                    'amount' => $lockedDoctor->consultation_price,
                    'session_duration_minutes' => $lockedDoctor->session_duration_minutes ?: DoctorConsultation::DURATION_MINUTES,
                    'payment_method' => $validated['payment_method'],
                    'payment_channel' => $validated['payment_channel'],
                    'payment_proof_path' => null,
                    'payment_status' => 'pending',
                    'status' => 'awaiting_payment',
                    'privacy_consent_at' => now(),
                    'guardian_consent_at' => $patient['guardian_consent_at'],
                    'payment_due_at' => now()->addMinutes(30),
                ]);
            });

        return redirect()->route('consultations.patient.show', ['token' => $token]);
    }

    public function patientShow(Request $request, string $token)
    {
        $consultation = $this->findByPatientToken($token);
        $this->expirePaymentIfExpired($consultation);
        $this->completeIfExpired($consultation);
        $consultation->load('doctorProfile');
        $request->session()->forget('consultation_room_access.' . $consultation->id);
        $adminWhatsappUrl = $consultation->payment_status === 'pending'
            ? $this->adminPaymentWhatsAppUrl($consultation)
            : null;
        return response()
            ->view('consultations.patient-access', compact('consultation', 'token', 'adminWhatsappUrl'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function verifyAccessCode(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'access_code' => ['required', 'regex:/^0[0-9]{4}$/'],
        ]);
        $valid = DB::transaction(function () use ($token, $validated): bool {
            $consultation = DoctorConsultation::query()
                ->where('patient_access_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->firstOrFail();

            return $this->activateAccessCode($consultation, $validated['access_code']);
        });

        if (!$valid) {
            return response()->json(['message' => 'Kode akses salah, sudah digunakan, atau sudah kedaluwarsa.'], 422)
                ->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'valid' => true,
            'message' => 'Kode valid. Pembayaran dikonfirmasi melalui WhatsApp.',
        ])
            ->header('Cache-Control', 'no-store');
    }

    public function startWhatsAppConsultation(Request $request, string $token)
    {
        $validated = $request->validate([
            'access_code' => ['required', 'regex:/^0[0-9]{4}$/'],
        ]);
        $result = DB::transaction(function () use ($token, $validated): ?array {
            $consultation = DoctorConsultation::query()
                ->where('patient_access_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->firstOrFail();

            if (!$this->activateAccessCode($consultation, $validated['access_code'])) {
                return null;
            }
            $stockCode = ConsultationAccessCode::query()
                ->where('doctor_consultation_id', $consultation->id)
                ->where('code_hash', DoctorConsultation::hashAccessCode($validated['access_code']))
                ->where('status', ConsultationAccessCode::STATUS_ASSIGNED)
                ->lockForUpdate()
                ->first();

            $doctor = DoctorProfile::query()->lockForUpdate()->findOrFail($consultation->doctor_profile_id);
            $startedAt = now();
            $duration = (int) ($consultation->session_duration_minutes ?: DoctorConsultation::DURATION_MINUTES);
            $consultation->update([
                'access_code_used_at' => $startedAt,
                'status' => 'active',
                'starts_at' => $startedAt,
                'ends_at' => $startedAt->copy()->addMinutes($duration),
            ]);
            $stockCode?->update([
                'status' => ConsultationAccessCode::STATUS_USED,
                'used_at' => $startedAt,
            ]);

            return [
                'doctor_phone' => $doctor->whatsapp_phone,
                'message' => $this->doctorWhatsAppMessage($consultation),
            ];
        });

        if (!$result) {
            return back()->withErrors([
                'access_code' => 'Kode akses salah, sudah digunakan, atau sudah kedaluwarsa.',
            ])->withInput();
        }
        return redirect()->away(
            'https://wa.me/' . $result['doctor_phone'] . '?text=' . rawurlencode($result['message'])
        );
    }

    public function sharedPaymentProof(DoctorConsultation $consultation)
    {
        $path = $consultation->payment_proof_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
        $mimeType = Storage::disk('local')->mimeType($path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "inline; filename=\"bukti-pembayaran-{$consultation->id}.{$extension}\"",
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function patientUpdates(Request $request, string $token): JsonResponse
    {
        $consultation = $this->findByPatientToken($token);
        abort_if($consultation->access_code_used_at !== null, 404);
        $this->expirePaymentIfExpired($consultation);
        $this->completeIfExpired($consultation);
        if ($consultation->status !== 'awaiting_payment') {
            $hasPatientAccess = $consultation->payment_status === 'paid'
                && in_array($consultation->status, ['active', 'completed'], true);
            if (!$hasPatientAccess && $this->roomRole($request, $consultation) !== 'patient') {
                return response()->json([
                    'status' => $consultation->status,
                    'payment_status' => $consultation->payment_status,
                    'ends_at' => $consultation->ends_at?->toIso8601String(),
                    'server_now' => now()->toIso8601String(),
                    'messages' => [],
                ])->header('Cache-Control', 'private, no-store');
            }
        }

        return $this->updatesResponse($consultation, $request->integer('after_id'));
    }

    public function patientMessage(Request $request, string $token): JsonResponse
    {
        $consultation = $this->findByPatientToken($token);
        abort_unless(
            $consultation->access_code_used_at === null
                && $consultation->payment_status === 'paid'
                && in_array($consultation->status, ['active', 'completed'], true),
            404
        );

        return $this->storeMessage($request, $consultation, 'patient');
    }

    public function roomJoin(string $token)
    {
        $access = $this->findRoomAccess($token);

        return response()
            ->view('consultations.join-room', $access)
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function redeemRoomLink(Request $request, string $token)
    {
        abort_unless(preg_match('/^[a-f0-9]{64}$/', $token) === 1, 404);
        $tokenHash = hash('sha256', $token);

        $access = DB::transaction(function () use ($tokenHash): ?array {
            $consultation = DoctorConsultation::query()
                ->where('patient_room_token_hash', $tokenHash)
                ->orWhere('doctor_room_token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (!$consultation || !$consultation->isActive()) {
                return null;
            }

            if (hash_equals((string) $consultation->patient_room_token_hash, $tokenHash)) {
                if ($consultation->patient_room_token_used_at) {
                    return null;
                }

                $consultation->update([
                    'patient_room_token_hash' => null,
                    'patient_room_token_used_at' => now(),
                ]);

                return ['consultation_id' => $consultation->id, 'role' => 'patient'];
            }

            if (hash_equals((string) $consultation->doctor_room_token_hash, $tokenHash)) {
                if ($consultation->doctor_room_token_used_at) {
                    return null;
                }

                $consultation->loadMissing('doctorProfile');
                $startsAt = $consultation->starts_at ?? now();
                $durationMinutes = (int) (
                    $consultation->session_duration_minutes
                    ?: $consultation->doctorProfile->session_duration_minutes
                    ?: DoctorConsultation::DURATION_MINUTES
                );
                $consultation->update([
                    'doctor_room_token_hash' => null,
                    'doctor_room_token_used_at' => now(),
                    'starts_at' => $startsAt,
                    'ends_at' => $consultation->ends_at ?? $startsAt->copy()->addMinutes($durationMinutes),
                ]);

                return ['consultation_id' => $consultation->id, 'role' => 'doctor'];
            }

            return null;
        });

        abort_unless($access, 404, 'Tautan room tidak valid, sudah dipakai, atau sesi telah berakhir.');
        $request->session()->put(
            'consultation_room_access.' . $access['consultation_id'],
            $access['role']
        );

        return redirect()->route('consultations.room', $access['consultation_id']);
    }

    public function room(Request $request, DoctorConsultation $consultation)
    {
        $role = $this->roomRole($request, $consultation);
        abort_unless($role !== null, 404);
        abort_unless(in_array($consultation->status, ['active', 'completed'], true), 404);
        $this->completeIfExpired($consultation);
        $consultation->load('doctorProfile');

        if ($role === 'doctor') {
            return response()
                ->view('doctor.consultation', compact('consultation'))
                ->header('Cache-Control', 'private, no-store')
                ->header('Referrer-Policy', 'no-referrer');
        }

        $settings = null;
        $token = null;
        $hasRoomAccess = true;

        return response()
            ->view('consultations.patient-session', compact('consultation', 'settings', 'token', 'hasRoomAccess'))
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function roomUpdates(Request $request, DoctorConsultation $consultation): JsonResponse
    {
        abort_unless($this->roomRole($request, $consultation) !== null, 404);
        $this->completeIfExpired($consultation);

        return $this->updatesResponse($consultation, $request->integer('after_id'));
    }

    public function roomMessage(Request $request, DoctorConsultation $consultation): JsonResponse
    {
        $role = $this->roomRole($request, $consultation);
        abort_unless($role !== null, 404);

        return $this->storeMessage($request, $consultation, $role);
    }

    public function doctorProducts(Request $request, DoctorConsultation $consultation): JsonResponse
    {
        abort_unless($this->roomRole($request, $consultation) === 'doctor', 404);
        abort_unless($consultation->payment_channel === 'online', 403);
        abort_unless($consultation->payment_status === 'paid', 404);
        $this->completeIfExpired($consultation);
        abort_unless($consultation->isActive(), 409, 'Sesi konsultasi telah berakhir.');

        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $products = Medicine::query()
            ->nonPbf()
            ->availableForB2c()
            ->search($validated['q'])
            ->orderBy('nama_obat')
            ->limit(20)
            ->get();
        $availableUnits = StockAvailability::unitsByMedicineId($products);

        $results = $products->map(function (Medicine $medicine) use ($availableUnits): ?array {
            $factor = $medicine->getSaleUnitFactor('box');
            $availableQuantity = $factor > 0
                ? intdiv((int) ($availableUnits[$medicine->id] ?? 0), $factor)
                : 0;
            $price = (int) round($medicine->getChannelBasePrice('b2c'));

            if ($availableQuantity < 1 || $price < 1) {
                return null;
            }

            return [
                'id' => $medicine->id,
                'name' => $medicine->nama_obat,
                'brand' => $medicine->brand ?: $medicine->kategori,
                'image' => $medicine->image_url,
                'price' => $price,
                'discount_percent' => $medicine->getPromoPercent('b2c'),
                'available_quantity' => $availableQuantity,
                'unit_label' => $medicine->package_unit_label ?: 'Kemasan',
                'requires_prescription' => $medicine->hasRedLogo(),
            ];
        })->filter()->values();

        return response()->json(['products' => $results])
            ->header('Cache-Control', 'private, no-store');
    }

    public function issuePrescription(Request $request, DoctorConsultation $consultation): JsonResponse
    {
        abort_unless($this->roomRole($request, $consultation) === 'doctor', 404);
        abort_unless($consultation->payment_channel === 'online', 403);
        abort_unless($consultation->payment_status === 'paid', 404);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:10'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:medicines,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.instructions' => ['required', 'string', 'max:500'],
        ]);
        $productIds = collect($validated['items'])->pluck('id')->map(fn ($id): int => (int) $id);
        $products = Medicine::query()
            ->nonPbf()
            ->availableForB2c()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
        abort_unless($products->count() === $productIds->count(), 422, 'Produk resep tidak tersedia di katalog B2C.');

        $availableUnits = StockAvailability::unitsByMedicineId($products);
        $prescribedItems = [];
        foreach ($validated['items'] as $item) {
            $medicine = $products->get((int) $item['id']);
            $factor = $medicine?->getSaleUnitFactor('box') ?? 0;
            $quantity = (int) $item['quantity'];
            $price = (int) round($medicine?->getChannelBasePrice('b2c') ?? 0);
            if (
                !$medicine
                || $factor < 1
                || $price < 1
                || ($availableUnits[$medicine->id] ?? 0) < $factor * $quantity
            ) {
                return response()->json([
                    'message' => 'Stok atau harga produk resep sudah berubah. Cari ulang produk sebelum menyetujui resep.',
                ], 422);
            }

            $prescribedItems[] = [
                'id' => $medicine->id,
                'name' => $medicine->nama_obat,
                'brand' => $medicine->brand ?: $medicine->kategori,
                'image' => $medicine->image_url,
                'quantity' => $quantity,
                'sale_unit' => 'box',
                'unit_label' => $medicine->package_unit_label ?: 'Kemasan',
                'price' => $price,
                'discount' => 0,
                'instructions' => trim($item['instructions']),
                'requires_prescription' => $medicine->hasRedLogo(),
                'available_quantity' => intdiv((int) $availableUnits[$medicine->id], $factor),
            ];
        }

        $this->completeIfExpired($consultation);
        $issued = DB::transaction(function () use ($consultation, $request, $prescribedItems): array {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if (!$locked->isActive()) {
                return ['error' => 'Sesi konsultasi telah berakhir.'];
            }
            if ($locked->payment_channel !== 'online') {
                return ['error' => 'Resep produk hanya dapat diterbitkan untuk konsultasi Online.'];
            }
            if ($locked->payment_status !== 'paid') {
                return ['error' => 'Pembayaran konsultasi belum dikonfirmasi.'];
            }
            if ($locked->prescription_number !== null) {
                return ['error' => 'Resep untuk konsultasi ini sudah diterbitkan.'];
            }

            $number = 'RSP-' . Str::upper(Str::random(12));
            $locked->update([
                'prescription_number' => $number,
                'prescribed_items' => $prescribedItems,
                'prescription_approved_at' => now(),
            ]);
            $locked->messages()->create([
                'sender_type' => 'doctor',
                'sender_user_id' => $request->user()?->id ?? $locked->doctorProfile->user_id,
                'body' => "Dokter menyetujui resep dan menerbitkan nomor resep {$number}. Produk resep dapat ditambahkan ke keranjang untuk checkout.",
            ]);

            return ['number' => $number];
        });

        if (isset($issued['error'])) {
            return response()->json(['message' => $issued['error']], 409);
        }

        return response()->json([
            'number' => $issued['number'],
            'items' => $prescribedItems,
            'used' => false,
        ], 201)->header('Cache-Control', 'private, no-store');
    }

    public function endRoomSession(Request $request, DoctorConsultation $consultation)
    {
        abort_unless($this->roomRole($request, $consultation) === 'doctor', 404);

        $ended = DB::transaction(function () use ($consultation): bool {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if (!$locked->isActive()) {
                return false;
            }

            $endedAt = now();
            $locked->update([
                'status' => 'completed',
                'ends_at' => $endedAt,
                'completed_at' => $endedAt,
                'patient_room_token_hash' => null,
                'doctor_room_token_hash' => null,
            ]);

            return true;
        });

        if (!$ended) {
            return back()->withErrors(['session' => 'Sesi ini sudah berakhir.']);
        }

        return back()->with('success', 'Sesi konsultasi berhasil diakhiri.');
    }

    private function doctorDirectoryQuery()
    {
        return DoctorProfile::query()
            ->where('is_active', true)
            ->where('str_verified', true)
            ->whereNotNull('whatsapp_phone')
            ->where('whatsapp_phone', '!=', '');
    }

    private function hasValidAccessCode(DoctorConsultation $consultation, string $code): bool
    {
        $validConsultationCode = $consultation->payment_status === 'paid'
            && $consultation->status === 'awaiting_whatsapp'
            && $consultation->access_code_used_at === null
            && $consultation->access_code_expires_at?->isFuture() === true
            && $consultation->access_code_hash !== null
            && hash_equals($consultation->access_code_hash, DoctorConsultation::hashAccessCode($code));

        if (!$validConsultationCode) {
            return false;
        }

        $stockCodesExist = ConsultationAccessCode::query()
            ->where('doctor_consultation_id', $consultation->id)
            ->exists();
        if (!$stockCodesExist) {
            return true;
        }

        $stockCode = ConsultationAccessCode::query()
            ->where('doctor_consultation_id', $consultation->id)
            ->where('code_hash', DoctorConsultation::hashAccessCode($code))
            ->first();

        return $stockCode !== null && (
            $stockCode->status === ConsultationAccessCode::STATUS_ASSIGNED
            && $stockCode->expires_at?->isFuture() === true
        );
    }

    private function activateAccessCode(DoctorConsultation $consultation, string $code): bool
    {
        if ($consultation->status === 'awaiting_payment' && $consultation->payment_status === 'pending') {
            if ($consultation->payment_due_at?->isPast()) {
                $consultation->update(['status' => 'cancelled', 'payment_status' => 'expired']);

                return false;
            }
            $stockCode = ConsultationAccessCode::query()
                ->where('code_hash', DoctorConsultation::hashAccessCode($code))
                ->where('status', ConsultationAccessCode::STATUS_AVAILABLE)
                ->lockForUpdate()
                ->first();
            if (!$stockCode) {
                return false;
            }

            $verifiedAt = now();
            $expiresAt = $verifiedAt->copy()->addHours(24);
            $stockCode->update([
                'status' => ConsultationAccessCode::STATUS_ASSIGNED,
                'doctor_consultation_id' => $consultation->id,
                'expires_at' => $expiresAt,
            ]);
            $consultation->update([
                'payment_status' => 'paid',
                'status' => 'awaiting_whatsapp',
                'paid_at' => $verifiedAt,
                'payment_verified_at' => $verifiedAt,
                'access_code_hash' => $stockCode->code_hash,
                'access_code_expires_at' => $expiresAt,
                'access_code_used_at' => null,
            ]);
            $consultation->refresh();
        }

        return $this->hasValidAccessCode($consultation, $code);
    }

    private function doctorWhatsAppMessage(DoctorConsultation $consultation): string
    {
        return implode("\n", [
            'Halo Dokter ' . $consultation->doctorProfile->display_name . ', saya ingin memulai konsultasi.',
            '',
            'Data pasien:',
            'Nama: ' . $consultation->patient_name,
            'Tanggal lahir: ' . $consultation->patient_birth_date,
            'Jenis kelamin: ' . $consultation->patient_gender,
            'Nomor WhatsApp: ' . $consultation->patient_phone,
            'Email: ' . $consultation->patient_email,
            'Tinggi badan: ' . ($consultation->height_cm ?: '-'),
            'Berat badan: ' . ($consultation->weight_kg ?: '-'),
            '',
            'Keluhan: ' . $consultation->complaint,
            'Alergi obat: ' . ($consultation->drug_allergies ?: '-'),
            'ID konsultasi: #' . $consultation->id,
        ]);
    }

    private function adminPaymentWhatsAppUrl(DoctorConsultation $consultation): string
    {
        $paymentDetails = strtoupper($consultation->payment_method) . ' (' . ucfirst($consultation->payment_channel) . ')';
        $paymentMessage = $consultation->payment_method === 'Cash'
            ? 'Saya akan melakukan pembayaran sejumlah Rp ' . number_format($consultation->amount, 0, ',', '.') . ' secara tunai di apotek.'
            : 'Saya sudah melakukan pembayaran sejumlah Rp ' . number_format($consultation->amount, 0, ',', '.') . ' melalui ' . $paymentDetails . '.';
        $message = [
            'Halo Apotek Medix, saya ingin konfirmasi pembayaran konsultasi.',
            '',
            'Nama: ' . $consultation->patient_name,
            'No. telepon: ' . $consultation->patient_phone,
            'Alamat: ' . ($consultation->patient_address ?: '-'),
            '',
            $paymentMessage,
        ];
        if ($consultation->payment_method !== 'Cash') {
            $message[] = 'Bukti pembayaran saya lampirkan pada chat ini.';
        }
        $message[] = '';
        $message[] = 'Mohon bantu verifikasi pembayaran dan kirimkan kode akses konsultasi setelah pembayaran dikonfirmasi. Terima kasih.';
        $message = implode("\n", $message);
        $adminWhatsapp = config('contacts.customer_service.whatsapp');

        return 'https://wa.me/' . $adminWhatsapp . '?text=' . rawurlencode($message);
    }

    private function paymentMethodConfigured(?DoctorConsultationPaymentSetting $settings, string $method): bool
    {
        if (!$settings) {
            return false;
        }

        return $method === 'qris'
            ? filled($settings->qris_image_path)
            : filled($settings->bank_name) && filled($settings->account_number) && filled($settings->account_holder);
    }

    private function findByPatientToken(string $token): DoctorConsultation
    {
        abort_unless(preg_match('/^[a-f0-9]{64}$/', $token) === 1, 404);

        return DoctorConsultation::query()
            ->where('patient_access_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    private function findRoomAccess(string $token): array
    {
        abort_unless(preg_match('/^[a-f0-9]{64}$/', $token) === 1, 404);
        $tokenHash = hash('sha256', $token);
        $consultation = DoctorConsultation::query()
            ->where('patient_room_token_hash', $tokenHash)
            ->orWhere('doctor_room_token_hash', $tokenHash)
            ->with('doctorProfile')
            ->firstOrFail();
        abort_unless($consultation->isActive(), 404, 'Sesi konsultasi sudah berakhir.');

        $role = hash_equals((string) $consultation->patient_room_token_hash, $tokenHash)
            ? 'patient'
            : 'doctor';

        return compact('consultation', 'role');
    }

    private function roomRole(Request $request, DoctorConsultation $consultation): ?string
    {
        $role = $request->session()->get('consultation_room_access.' . $consultation->id);

        return in_array($role, ['patient', 'doctor'], true) ? $role : null;
    }

    private function normalizeIndonesianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return str_starts_with($digits, '8') ? '62' . $digits : $digits;
    }

    private function completeIfExpired(DoctorConsultation $consultation): void
    {
        if ($consultation->status === 'active' && $consultation->ends_at?->isPast()) {
            $consultation->update(['status' => 'completed', 'completed_at' => now()]);
            $consultation->refresh();
        }
    }

    private function expirePaymentIfExpired(DoctorConsultation $consultation): void
    {
        if (
            $consultation->status === 'awaiting_payment'
            && $consultation->payment_due_at?->isPast()
        ) {
            $consultation->update(['status' => 'cancelled', 'payment_status' => 'expired']);
            $consultation->refresh();
        }
    }

    private function updatesResponse(DoctorConsultation $consultation, int $afterId = 0): JsonResponse
    {
        $messages = $consultation->messages()
            ->where('id', '>', max(0, $afterId))
            ->orderBy('id')
            ->limit(100)
            ->get(['id', 'sender_type', 'body', 'created_at'])
            ->map(fn (DoctorConsultationMessage $message): array => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender_type === 'doctor'
                    ? $consultation->doctorProfile->display_name
                    : $consultation->patient_name,
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'status' => $consultation->status,
            'payment_status' => $consultation->payment_status,
            'starts_at' => $consultation->starts_at?->toIso8601String(),
            'ends_at' => $consultation->ends_at?->toIso8601String(),
            'server_now' => now()->toIso8601String(),
            'messages' => $messages,
            'prescription' => $consultation->payment_channel === 'online' && $consultation->prescription_number
                ? [
                    'number' => $consultation->prescription_number,
                    'items' => $consultation->prescribed_items ?? [],
                    'used' => $consultation->prescription_used_at !== null,
                ]
                : null,
        ])->header('Cache-Control', 'private, no-store');
    }

    private function storeMessage(Request $request, DoctorConsultation $consultation, string $senderType): JsonResponse
    {
        $this->completeIfExpired($consultation);
        abort_unless($consultation->isActive(), 409, 'Sesi konsultasi telah berakhir.');
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $consultation->messages()->create([
            'sender_type' => $senderType,
            'sender_user_id' => $senderType === 'doctor'
                ? ($request->user()?->id ?? $consultation->doctorProfile->user_id)
                : null,
            'body' => trim($validated['body']),
        ]);

        return response()->json([
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'sender_name' => $senderType === 'doctor' ? $consultation->doctorProfile->display_name : $consultation->patient_name,
            'body' => $message->body,
            'created_at' => $message->created_at?->toIso8601String(),
        ], 201)->header('Cache-Control', 'private, no-store');
    }
}
