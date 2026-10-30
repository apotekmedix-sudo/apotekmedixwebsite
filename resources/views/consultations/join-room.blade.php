@extends('layouts.consultation-room')

@section('title', 'Masuk Room Konsultasi - Apotek Medix')

@section('content')
<section style="max-width:620px;margin:2rem auto;padding:clamp(1.2rem,4vw,2rem);border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 12px 32px #0f172a0d">
    <p style="margin:0;color:#08734f;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em">Akses privat · {{ $role === 'doctor' ? 'Dokter' : 'Pasien' }}</p>
    <h1 style="margin:.5rem 0;color:#10243e">Masuk room konsultasi</h1>
    <p style="color:#52657e;line-height:1.6">Room dengan {{ $consultation->doctorProfile->display_name }} berlangsung {{ $consultation->session_duration_minutes }} menit. Timer mulai berjalan saat dokter masuk ke room. Tautan khusus ini hanya dapat ditukar satu kali dan aksesnya akan terikat pada browser ini.</p>
    <p style="padding:.8rem 1rem;border:1px solid #fed7aa;border-radius:9px;background:#fff7ed;color:#9a3412;line-height:1.5">Jangan teruskan tautan ini kepada orang lain. Setelah masuk, jaga kerahasiaan perangkat dan tutup browser setelah selesai.</p>
    <form method="POST" action="{{ route('consultations.room.redeem', ['token' => request()->route('token')]) }}">
        @csrf
        <button type="submit" style="min-height:46px;padding:.7rem 1rem;border:0;border-radius:9px;background:#08734f;color:#fff;font:inherit;font-weight:800;cursor:pointer">Masuk room sekarang</button>
    </form>
</section>
@endsection
