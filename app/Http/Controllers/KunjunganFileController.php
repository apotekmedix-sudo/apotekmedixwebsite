<?php

namespace App\Http\Controllers;

use App\Models\KunjunganFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class KunjunganFileController extends Controller
{
    public function accessForm(): View
    {
        return view('kunjungan.access');
    }

    public function access(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_code' => ['required', 'string', 'max:100'],
        ]);
        $expectedCode = (string) config('kunjungan.access_code');
        if ($expectedCode === '' || !hash_equals($expectedCode, $validated['access_code'])) {
            return back()->withErrors(['access_code' => 'Kode akses tidak sesuai.']);
        }

        $request->session()->regenerate();
        $request->session()->put('kunjungan_room_access', true);

        return redirect()->route('kunjungan.room');
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (!$request->session()->get('kunjungan_room_access')) {
            return redirect()->route('kunjungan.access.form');
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:newest,oldest,name_asc,name_desc'],
        ]);
        $search = trim($validated['search'] ?? '');
        $sort = $validated['sort'] ?? 'newest';
        $query = KunjunganFile::query()
            ->when(
                $search !== '',
                fn ($query) => $query->where('original_name', 'like', '%' . $search . '%')
            );

        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'name_asc' => $query->orderByRaw('LOWER(original_name) ASC')->orderByDesc('created_at'),
            'name_desc' => $query->orderByRaw('LOWER(original_name) DESC')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $files = $query->paginate(30)->withQueryString();

        return view('kunjungan.room', compact('files', 'search', 'sort'));
    }

    public function upload(Request $request): RedirectResponse
    {
        $this->ensureRoomAccess($request);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:5'],
            'files.*' => ['required', 'file', 'max:' . (int) config('kunjungan.max_file_size_kb')],
        ], [
            'files.max' => 'Maksimal 5 file dapat diunggah sekaligus.',
            'files.*.max' => 'Ukuran setiap file maksimal 20 MB.',
        ]);

        foreach ($validated['files'] as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (preg_match('/^[a-z0-9]{1,10}$/', $extension) !== 1) {
                $extension = '';
            }
            $filename = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '');
            $path = 'kunjungan-room/' . $filename;
            if ($file->storeAs('kunjungan-room', $filename, 'local') === false) {
                Log::error('KUNJUNGAN room file could not be written to private storage.', [
                    'original_name' => $file->getClientOriginalName(),
                ]);

                return back()->with('error', 'File gagal disimpan. File yang sudah berhasil diunggah tetap tersedia.');
            }

            try {
                KunjunganFile::query()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size_bytes' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            } catch (Throwable $exception) {
                Storage::disk('local')->delete($path);
                Log::error('KUNJUNGAN room file metadata could not be saved.', [
                    'path' => $path,
                    'exception' => $exception,
                ]);

                throw $exception;
            }
        }

        return redirect()->route('kunjungan.room')->with('success', 'File berhasil diunggah ke KUNJUNGAN.');
    }

    public function download(Request $request, KunjunganFile $file)
    {
        $this->ensureRoomAccess($request);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->original_name, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function preview(Request $request, KunjunganFile $file)
    {
        $this->ensureRoomAccess($request);
        abort_unless($this->isPdf($file), 404);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->response($file->path, $file->original_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function leave(Request $request): RedirectResponse
    {
        $request->session()->forget('kunjungan_room_access');

        return redirect()->route('kunjungan.access.form')->with('success', 'Anda telah keluar dari KUNJUNGAN.');
    }

    private function ensureRoomAccess(Request $request): void
    {
        abort_unless($request->session()->get('kunjungan_room_access'), 403);
    }

    private function isPdf(KunjunganFile $file): bool
    {
        return strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf'
            && strtolower((string) $file->mime_type) === 'application/pdf';
    }
}
