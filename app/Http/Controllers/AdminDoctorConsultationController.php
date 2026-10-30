<?php

namespace App\Http\Controllers;

use App\Models\DoctorConsultation;
use App\Models\DoctorConsultationPaymentSetting;
use App\Models\DoctorProfile;
use App\Support\DoctorConsultationSpecialties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminDoctorConsultationController extends Controller
{
    public function index(Request $request)
    {
        DoctorConsultation::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->update([
                'status' => 'completed',
                'completed_at' => DB::raw('COALESCE(completed_at, ends_at)'),
            ]);

        $activeTab = $request->query('tab', 'doctors');
        if (!in_array($activeTab, ['doctors', 'transactions', 'patients'], true)) {
            $activeTab = 'doctors';
        }

        $doctors = DoctorProfile::orderBy('display_name')->get();
        $consultations = DoctorConsultation::with(['doctorProfile', 'paymentVerifiedBy'])
            ->latest()
            ->paginate(25)
            ->withQueryString();
        $pendingConsultationsCount = DoctorConsultation::query()
            ->where('status', 'awaiting_payment')
            ->where('payment_status', 'pending')
            ->where('payment_due_at', '>', now())
            ->count();
        $settings = DoctorConsultationPaymentSetting::query()->first();

        return view('admin.consultations.index', compact('doctors', 'consultations', 'settings', 'activeTab', 'pendingConsultationsCount'));
    }

    public function exportPatients()
    {
        $consultations = DoctorConsultation::with('doctorProfile')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $headers = [
            'Waktu permintaan',
            'Nama lengkap',
            'Tanggal lahir',
            'Jenis kelamin',
            'Nomor ponsel',
            'Email',
            'Tinggi badan (cm)',
            'Berat badan (kg)',
            'Keluhan dan obat yang diinginkan',
            'Alergi obat',
            'Persetujuan penggunaan data',
            'Dokter',
        ];
        $rows = $consultations->map(fn (DoctorConsultation $consultation): array => [
            $consultation->created_at?->format('d-m-Y H:i') ?? '',
            $consultation->patient_name,
            $consultation->patient_birth_date
                ? \Illuminate\Support\Carbon::parse($consultation->patient_birth_date)->format('d-m-Y')
                : '',
            $consultation->patient_gender ?? '',
            $consultation->patient_phone ?? '',
            $consultation->patient_email ?? '',
            $consultation->height_cm ?? '',
            $consultation->weight_kg ?? '',
            $consultation->complaint,
            $consultation->drug_allergies ?? '',
            $consultation->privacy_consent_at?->format('d-m-Y H:i') ?? '',
            $consultation->doctorProfile->display_name,
        ])->all();

        return \App\Helpers\XlsxWriter::download(
            'data_pasien_konsultasi_' . now()->format('Y-m-d_His') . '.xlsx',
            $headers,
            $rows,
            [20, 28, 16, 18, 20, 30, 18, 18, 55, 40, 28, 28]
        );
    }

    public function createDoctor()
    {
        return view('admin.consultations.doctor-form', [
            'doctor' => null,
            'specialties' => $this->specialtyOptions(),
        ]);
    }

    public function storeDoctor(Request $request)
    {
        $validated = $request->validate($this->doctorRules());
        $photoPath = $request->file('photo')
            ? $this->storeImage($request->file('photo'), 'doctors')
            : null;

        $doctor = DoctorProfile::create($this->profileAttributes($validated, $photoPath));

        return redirect()->route('admin.consultations.index')->with('success', "Dokter {$doctor->display_name} berhasil ditambahkan.");
    }

    public function editDoctor(DoctorProfile $doctor)
    {
        return view('admin.consultations.doctor-form', [
            'doctor' => $doctor,
            'specialties' => $this->specialtyOptions(),
        ]);
    }

    public function updateDoctor(Request $request, DoctorProfile $doctor)
    {
        $validated = $request->validate($this->doctorRules());
        if (
            (!$request->boolean('is_active') || !$request->boolean('str_verified'))
            && $this->hasActiveConsultation($doctor)
        ) {
            return back()->withErrors([
                'is_active' => 'Dokter tidak dapat dinonaktifkan atau STR tidak dapat dibatalkan saat konsultasi sedang aktif.',
            ])->withInput();
        }
        if (
            (!$request->boolean('is_active') || !$request->boolean('str_verified') || !$request->boolean('is_online'))
            && $this->hasPendingPayment($doctor)
        ) {
            return back()->withErrors([
                'is_active' => 'Batalkan atau selesaikan antrean pembayaran dokter sebelum menonaktifkan profil, STR, atau status online.',
            ])->withInput();
        }

        $newPhotoPath = $request->file('photo')
            ? $this->storeImage($request->file('photo'), 'doctors')
            : null;

        $oldPhotoPath = $doctor->photo_path;
        DB::transaction(function () use ($doctor, $validated, $newPhotoPath): void {
            $doctor->update($this->profileAttributes($validated, $newPhotoPath ?? $doctor->photo_path));
        });

        if ($newPhotoPath && $oldPhotoPath !== $newPhotoPath) {
            $this->deleteStoredImage($oldPhotoPath);
        }

        return redirect()->route('admin.consultations.index')->with('success', "Profil {$doctor->display_name} berhasil diperbarui.");
    }

    public function deactivateDoctor(DoctorProfile $doctor)
    {
        if ($this->hasActiveConsultation($doctor) || $this->hasPendingPayment($doctor)) {
            return back()->withErrors(['doctor' => 'Selesaikan atau batalkan sesi aktif dan antrean pembayaran dokter sebelum dinonaktifkan.']);
        }

        $doctor->update(['is_active' => false, 'is_online' => false]);

        return back()->with('success', "Profil {$doctor->display_name} dinonaktifkan.");
    }

    public function updatePaymentSettings(Request $request)
    {
        $validated = $request->validate([
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'payment_instructions' => ['nullable', 'string', 'max:1500'],
            'remove_qris' => ['nullable', 'boolean'],
        ]);
        $settings = DoctorConsultationPaymentSetting::query()->first() ?? new DoctorConsultationPaymentSetting();
        $previousImage = $settings->qris_image_path;
        $imagePath = $request->file('qris_image')
            ? $this->storeImage($request->file('qris_image'), 'consultation-payments')
            : ($request->boolean('remove_qris') ? null : $previousImage);

        $settings->fill([
            'qris_image_path' => $imagePath,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'account_holder' => $validated['account_holder'] ?? null,
            'payment_instructions' => $validated['payment_instructions'] ?? null,
        ])->save();

        if ($previousImage && $previousImage !== $imagePath) {
            $this->deleteStoredImage($previousImage);
        }

        return back()->with('success', 'Instruksi pembayaran konsultasi berhasil disimpan.');
    }

    public function markPaid(Request $request, DoctorConsultation $consultation)
    {
        $result = DB::transaction(function () use ($consultation, $request): array {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if ($locked->status !== 'awaiting_payment' || $locked->payment_status !== 'pending') {
                return ['status' => 'not_pending'];
            }
            if ($locked->payment_due_at && $locked->payment_due_at->isPast()) {
                $locked->update(['status' => 'cancelled', 'payment_status' => 'expired']);

                return ['status' => 'expired'];
            }

            $doctor = DoctorProfile::query()->lockForUpdate()->findOrFail($locked->doctor_profile_id);
            if (!$doctor->is_active || !$doctor->str_verified || !$doctor->is_online || !$doctor->whatsapp_phone) {
                return ['status' => 'doctor_unavailable'];
            }
            $doctorBusy = DoctorConsultation::query()
                ->where('doctor_profile_id', $doctor->id)
                ->where('id', '!=', $locked->id)
                ->where('status', 'active')
                ->where(function ($query): void {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
                })
                ->exists();
            if ($doctorBusy) {
                return ['status' => 'doctor_busy'];
            }

            $verifiedAt = now();
            $doctorToken = $this->createDoctorRoomToken();
            $locked->update([
                'payment_status' => 'paid',
                'status' => 'active',
                'paid_at' => $verifiedAt,
                'payment_verified_by_user_id' => $request->user()->id,
                'payment_verified_at' => $verifiedAt,
                'starts_at' => null,
                'ends_at' => null,
                'patient_room_token_hash' => null,
                'doctor_room_token_hash' => hash('sha256', $doctorToken),
                'patient_room_token_used_at' => now(),
                'doctor_room_token_used_at' => null,
            ]);

            return [
                'status' => 'paid',
                'consultation_id' => $locked->id,
                'doctor_token' => $doctorToken,
                'doctor_phone' => $doctor->whatsapp_phone,
                'patient_name' => $locked->patient_name,
                'doctor_name' => $doctor->display_name,
                'session_duration_minutes' => (int) ($locked->session_duration_minutes ?: $doctor->session_duration_minutes ?: DoctorConsultation::DURATION_MINUTES),
            ];
        });

        if ($result['status'] !== 'paid') {
            return match ($result['status']) {
                'expired' => back()->withErrors(['payment' => 'Batas pembayaran 30 menit telah lewat; konsultasi dibatalkan.']),
                'doctor_busy' => back()->withErrors(['payment' => 'Dokter sedang menangani sesi lain. Verifikasi pembayaran setelah dokter tersedia.']),
                'doctor_unavailable' => back()->withErrors(['payment' => 'Dokter harus aktif, STR terverifikasi, online, dan memiliki nomor WhatsApp sebelum pembayaran dikonfirmasi.']),
                default => back()->withErrors(['payment' => 'Konsultasi ini tidak lagi menunggu pembayaran.']),
            };
        }

        return $this->redirectWithRoomLinks($result)
            ->with('success', "Pembayaran diverifikasi. Timer sesi {$result['session_duration_minutes']} menit dimulai saat dokter masuk ke room.");
    }

    public function resendRoomInvitations(DoctorConsultation $consultation)
    {
        $result = DB::transaction(function () use ($consultation): ?array {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if (!$locked->isActive()) {
                return null;
            }

            $doctor = DoctorProfile::query()->lockForUpdate()->findOrFail($locked->doctor_profile_id);
            if (!$doctor->whatsapp_phone) {
                return null;
            }

            $doctorToken = $this->createDoctorRoomToken();
            $locked->update([
                'doctor_room_token_hash' => hash('sha256', $doctorToken),
                'doctor_room_token_used_at' => null,
            ]);

            return [
                'consultation_id' => $locked->id,
                'doctor_token' => $doctorToken,
                'doctor_phone' => $doctor->whatsapp_phone,
                'patient_name' => $locked->patient_name,
                'doctor_name' => $doctor->display_name,
                'session_duration_minutes' => (int) (
                    $locked->session_duration_minutes
                    ?: $doctor->session_duration_minutes
                    ?: DoctorConsultation::DURATION_MINUTES
                ),
            ];
        });

        if (!$result) {
            return back()->withErrors(['payment' => 'Tautan baru hanya dapat dibuat untuk sesi aktif dan dokter yang memiliki nomor WhatsApp.']);
        }

        return $this->redirectWithRoomLinks($result)
            ->with('success', 'Tautan room dokter baru dibuat. Tautan sebelumnya sudah tidak berlaku.');
    }

    public function viewPaymentProof(DoctorConsultation $consultation)
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

    public function rejectPayment(DoctorConsultation $consultation)
    {
        $rejected = DB::transaction(function () use ($consultation): bool {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if ($locked->status !== 'awaiting_payment' || $locked->payment_status !== 'pending') {
                return false;
            }

            $locked->update([
                'status' => 'cancelled',
                'payment_status' => 'rejected',
            ]);

            return true;
        });

        if (!$rejected) {
            return back()->withErrors(['payment' => 'Hanya pembayaran yang masih menunggu verifikasi yang dapat ditolak.']);
        }

        return back()->with('success', 'Pembayaran ditolak. Pasien akan diarahkan untuk menghubungi admin terkait pengembalian dana.');
    }

    public function endSession(DoctorConsultation $consultation)
    {
        $ended = DB::transaction(function () use ($consultation): bool {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if ($locked->status !== 'active' || !$locked->isActive()) {
                return false;
            }

            $endedAt = now();
            $locked->update([
                'status' => 'completed',
                'ends_at' => $endedAt,
                'completed_at' => $endedAt,
                'doctor_room_token_hash' => null,
            ]);

            return true;
        });

        if (!$ended) {
            return back()->withErrors(['payment' => 'Sesi ini sudah berakhir atau tidak sedang aktif.']);
        }

        return back()->with('success', 'Sesi konsultasi berhasil diakhiri.');
    }

    public function chatHistory(DoctorConsultation $consultation)
    {
        abort_unless($consultation->status === 'completed', 404);

        $consultation->load('doctorProfile');
        $messages = $consultation->messages()->orderBy('id')->get();

        return response()->json([
            'patient_name' => $consultation->patient_name,
            'doctor_name' => $consultation->doctorProfile->display_name,
            'completed_at' => $consultation->completed_at?->format('d-m-Y H:i'),
            'messages' => $messages->map(fn ($message): array => [
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender_type === 'doctor'
                    ? $consultation->doctorProfile->display_name
                    : $consultation->patient_name,
                'body' => $message->body,
                'created_at' => $message->created_at?->format('d-m-Y H:i'),
            ])->values(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function deleteCompletedSession(DoctorConsultation $consultation)
    {
        $result = DB::transaction(function () use ($consultation): array {
            $locked = DoctorConsultation::query()->lockForUpdate()->findOrFail($consultation->id);
            if ($locked->status !== 'completed') {
                return ['deleted' => false, 'payment_proof_path' => null];
            }

            $paymentProofPath = $locked->payment_proof_path;
            $locked->delete();

            return ['deleted' => true, 'payment_proof_path' => $paymentProofPath];
        });

        if (!$result['deleted']) {
            return back()->withErrors(['consultation' => 'Hanya sesi yang sudah selesai yang dapat dihapus.']);
        }

        if ($result['payment_proof_path'] && !Storage::disk('local')->delete($result['payment_proof_path'])) {
            throw new \RuntimeException('Sesi berhasil dihapus, tetapi bukti pembayaran gagal dihapus dari penyimpanan.');
        }

        return redirect()
            ->route('admin.consultations.index', ['tab' => 'transactions'])
            ->with('success', 'Sesi konsultasi dan riwayat chat berhasil dihapus.');
    }

    public function cancel(DoctorConsultation $consultation)
    {
        if ($consultation->status !== 'awaiting_payment' || $consultation->payment_status !== 'pending') {
            return back()->withErrors(['payment' => 'Hanya konsultasi yang menunggu pembayaran yang dapat dibatalkan dari panel ini.']);
        }

        $consultation->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);

        return back()->with('success', 'Konsultasi dibatalkan.');
    }

    private function doctorRules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:150'],
            'whatsapp_phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?62|0)8[0-9]{7,12}$/'],
            'specialty' => ['required', 'string', Rule::in($this->specialtyOptions())],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'education' => ['nullable', 'string', 'max:255'],
            'practice_place' => ['nullable', 'string', 'max:255'],
            'practice_city' => ['nullable', 'string', 'max:150'],
            'str_number' => ['required', 'string', 'max:100'],
            'str_verified' => ['nullable', 'boolean'],
            'experience_years' => ['required', 'integer', 'between:0,80'],
            'consultation_price' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'session_duration_minutes' => ['required', 'integer', 'between:5,240'],
            'is_online' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    private function specialtyOptions(): array
    {
        return collect(DoctorConsultationSpecialties::all())
            ->merge(DoctorProfile::query()->distinct()->pluck('specialty'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function hasActiveConsultation(DoctorProfile $doctor): bool
    {
        return $doctor->consultations()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    private function hasPendingPayment(DoctorProfile $doctor): bool
    {
        return $doctor->consultations()
            ->where('status', 'awaiting_payment')
            ->where('payment_status', 'pending')
            ->where('payment_due_at', '>', now())
            ->exists();
    }

    private function profileAttributes(array $validated, ?string $photoPath): array
    {
        $phone = preg_replace('/\D+/', '', $validated['whatsapp_phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return [
            'display_name' => $validated['display_name'],
            'whatsapp_phone' => $phone,
            'specialty' => $validated['specialty'],
            'photo_path' => $photoPath,
            'bio' => $validated['bio'] ?? null,
            'education' => $validated['education'] ?? null,
            'practice_place' => $validated['practice_place'] ?? null,
            'practice_city' => $validated['practice_city'] ?? null,
            'str_number' => $validated['str_number'],
            'str_verified' => (bool) ($validated['str_verified'] ?? false),
            'experience_years' => (int) $validated['experience_years'],
            'consultation_price' => (int) $validated['consultation_price'],
            'session_duration_minutes' => (int) $validated['session_duration_minutes'],
            'is_online' => (bool) ($validated['is_online'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    private function createDoctorRoomToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function redirectWithRoomLinks(array $details)
    {
        $doctorRoomUrl = route('consultations.room.join', ['token' => $details['doctor_token']]);
        $doctorMessage = "Sesi konsultasi berbayar dengan pasien {$details['patient_name']} siap dimulai. Timer {$details['session_duration_minutes']} menit mulai berjalan saat Anda masuk ke room chat. Buka tautan room pribadi ini: {$doctorRoomUrl}";

        return back()->with('consultation_whatsapp_links', [
            'consultation_id' => $details['consultation_id'],
            'doctor' => 'https://wa.me/' . $details['doctor_phone'] . '?text=' . rawurlencode($doctorMessage),
        ]);
    }

    private function storeImage(\Illuminate\Http\UploadedFile $file, string $folder): string
    {
        $directory = storage_path($folder);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException("Tidak dapat membuat direktori penyimpanan {$folder}.");
        }

        $name = $file->hashName();
        $file->move($directory, $name);

        return $folder . '/' . $name;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (!$path || !preg_match('#^(doctors|consultation-payments)/[a-f0-9]{40}\.(jpg|jpeg|png|webp)$#i', $path)) {
            return;
        }

        $absolutePath = storage_path($path);
        if (is_file($absolutePath) && !unlink($absolutePath)) {
            throw new \RuntimeException('Gagal menghapus foto lama dari penyimpanan konsultasi.');
        }
    }
}
