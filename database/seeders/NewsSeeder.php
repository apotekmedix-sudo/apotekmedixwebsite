<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Artikel 1: Kesehatan Diri
        News::create([
            'judul' => '10 Tips Menjaga Kesehatan Tubuh di Era Modern',
            'deskripsi' => 'Pelajari cara sederhana namun efektif untuk menjaga kesehatan tubuh Anda di tengah gaya hidup modern yang sibuk dan penuh tekanan.',
            'konten' => 'Kesehatan adalah investasi terpenting dalam hidup kita. Dengan gaya hidup modern yang semakin kompleks, menjaga kesehatan tubuh menjadi semakin penting. 

Berikut 10 tips sederhana untuk menjaga kesehatan:

1. **Istirahat yang Cukup** - Pastikan Anda tidur minimal 7-8 jam setiap malam.

2. **Olahraga Teratur** - Lakukan aktivitas fisik minimal 30 menit setiap hari.

3. **Minum Air Putih** - Konsumsi air putih minimal 8 gelas per hari.

4. **Nutrisi Seimbang** - Konsumsi makanan bergizi dari semua kelompok makanan.

5. **Mengelola Stres** - Praktik meditasi atau yoga untuk mengurangi tingkat stres.

6. **Hindari Kebiasaan Buruk** - Kurangi konsumsi alkohol dan jangan merokok.

7. **Check-up Rutin** - Lakukan pemeriksaan kesehatan berkala ke dokter.

8. **Jaga Kebersihan** - Cuci tangan dan menjaga kebersihan lingkungan sekitar.

9. **Batasi Gula** - Kurangi konsumsi gula dan makanan olahan.

10. **Keseimbangan Kerja dan Istirahat** - Pastikan ada waktu untuk bersantai dan menikmati kehidupan.

Mulai dari sekarang, terapkan tips-tips ini dalam kehidupan sehari-hari Anda untuk hidup yang lebih sehat dan bahagia!',
            'tipe' => 'artikel',
            'file' => null,
            'thumbnail' => null,
            'views' => rand(10, 150),
            'is_published' => true,
        ]);

        // Artikel 2: Obat-obatan
        News::create([
            'judul' => 'Pentingnya Konsultasi dengan Apoteker Sebelum Menggunakan Obat',
            'deskripsi' => 'Apoteker adalah profesi kesehatan yang penting untuk memastikan keamanan dan efektivitas penggunaan obat-obatan.',
            'konten' => 'Dalam era informasi ini, banyak orang yang mencari informasi kesehatan melalui internet. Namun, penting untuk diingat bahwa konsultasi dengan apoteker profesional sangat diperlukan sebelum menggunakan obat.

**Mengapa Perlu Konsultasi Apoteker?**

Apoteker memiliki pengetahuan mendalam tentang obat-obatan, termasuk:

- Efek samping dan interaksi obat
- Dosis yang tepat untuk kondisi Anda
- Cara penggunaan yang aman dan benar
- Kontraindikasi dengan kondisi kesehatan lain
- Obat alternatif yang mungkin lebih sesuai

**Manfaat Konsultasi Apoteker:**

1. **Keamanan** - Memastikan obat aman untuk Anda gunakan
2. **Efektivitas** - Memilih obat yang paling efektif untuk masalah Anda
3. **Edukasi** - Mendapatkan informasi tentang kesehatan Anda
4. **Pencegahan** - Menghindari efek samping yang tidak perlu

**Kapan Harus Konsultasi?**

- Ketika membeli obat tanpa resep
- Ketika sedang mengonsumsi obat lain
- Ketika punya alergi terhadap obat tertentu
- Ketika hamil atau menyusui
- Ketika punya kondisi kesehatan khusus

Apotek Medix siap membantu Anda dengan apoteker profesional yang berpengalaman. Jangan ragu untuk bertanya tentang obat-obatan Anda!',
            'tipe' => 'artikel',
            'file' => null,
            'thumbnail' => null,
            'views' => rand(20, 200),
            'is_published' => true,
        ]);

        // Galeri 3: Kampanye Kesehatan
        News::create([
            'judul' => 'Kampanye Hari Kesehatan Sedunia 2025',
            'deskripsi' => 'Ikuti kampanye kesehatan global kami untuk meningkatkan kesadaran tentang pentingnya kesehatan preventif.',
            'konten' => 'Hari Kesehatan Sedunia adalah momentum penting untuk meningkatkan kesadaran global tentang isu-isu kesehatan. Apotek Medix mengikuti kampanye ini dengan berbagai inisiatif lokal.

**Aktivitas Kampanye:**

- Konsultasi gratis dengan ahli kesehatan
- Pemeriksaan kesehatan dasar tanpa biaya
- Workshop tentang kesehatan preventif
- Distribusi pamflet edukasi kesehatan
- Media sosial awareness campaign

Bergabunglah dengan kami dalam misi untuk menciptakan komunitas yang lebih sehat!',
            'tipe' => 'galeri',
            'file' => null,
            'thumbnail' => null,
            'views' => rand(30, 250),
            'is_published' => true,
        ]);

        // Artikel 4: Informasi Musiman
        News::create([
            'judul' => 'Siap Menghadapi Musim Hujan: Panduan Pencegahan Penyakit',
            'deskripsi' => 'Musim hujan membawa peningkatan risiko penyakit tertentu. Pelajari cara mencegah penyakit yang sering terjadi saat musim hujan.',
            'konten' => 'Musim hujan adalah waktu yang rawan untuk berbagai penyakit. Perubahan cuaca yang drastis dan kelembaban tinggi menciptakan kondisi ideal bagi perkembangan bakteri dan virus.

**Penyakit yang Umum di Musim Hujan:**

1. **Demam Berdarah** - Ditularkan oleh nyamuk Aedes
2. **Flu dan Batuk** - Virus menyebar lebih cepat di udara lembab
3. **Diare** - Dari konsumsi makanan yang terkontaminasi
4. **Alergi** - Peningkatan kelembaban memicu alergi
5. **Infeksi Kulit** - Kondisi lembab mempercepat pertumbuhan jamur

**Tips Pencegahan:**

**Untuk Demam Berdarah:**
- Gunakan obat penggusur nyamuk
- Tutup tempat penampungan air
- Gunakan kelambu saat tidur
- Pakai pakaian panjang

**Untuk Flu dan Batuk:**
- Jaga kebersihan tangan
- Hindari keramaian
- Vaksinasi flu
- Konsumsi vitamin C

**Untuk Diare:**
- Minum air yang sudah matang/galon
- Cuci tangan sebelum makan
- Masak makanan dengan baik
- Hindari makanan di tempat yang tidak higienis

**Untuk Alergi dan Infeksi Kulit:**
- Jaga kebersihan badan
- Ganti pakaian basah dengan cepat
- Gunakan krim antibiotik jika ada luka
- Konsultasi dengan dokter untuk alergi

**Kapan Harus ke Dokter?**

Segera ke dokter jika mengalami:
- Demam tinggi (>39Â°C)
- Pendarahan spontan
- Sakit kepala parah
- Sesak napas
- Diare berkepanjangan

Apotek Medix menyediakan obat-obatan berkualitas untuk membantu Anda dan keluarga tetap sehat di musim hujan. Jangan ragu untuk berkonsultasi dengan apoteker kami!',
            'tipe' => 'artikel',
            'file' => null,
            'thumbnail' => null,
            'views' => rand(15, 180),
            'is_published' => true,
        ]);
    }
}

