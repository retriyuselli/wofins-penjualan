<?php

namespace Database\Seeders;

use App\Models\Sop;
use App\Models\SopCategory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SopSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->orderBy('id')->first();
        if (! $user) {
            $this->command->error('User belum ada. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $categories = SopCategory::query()->get()->keyBy('name');
        if ($categories->isEmpty()) {
            $this->command->error('Kategori SOP belum ada. Jalankan SopCategorySeeder terlebih dahulu.');

            return;
        }

        $created = 0;

        Sop::withoutEvents(function () use ($categories, $user, &$created) {
            foreach ($this->sops() as $data) {
                $category = $categories->get($data['category_name']);
                if (! $category) {
                    $this->command->warn("Kategori '{$data['category_name']}' tidak ditemukan, dilewati.");

                    continue;
                }

                $title = $data['title'];
                $documents = $this->seedSupportingDocuments($data);
                unset($data['category_name']);

                Sop::updateOrCreate(
                    ['title' => $title],
                    array_merge($data, [
                        'title' => $title,
                        'category_id' => $category->id,
                        'supporting_documents' => $documents,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ])
                );

                $created++;
            }
        });

        $this->command->info("✅ SopSeeder: {$created} SOP dibuat/diperbarui sesuai form.");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sops(): array
    {
        return [
            [
                'title' => 'Prosedur Pengajuan Reimbursement',
                'category_name' => 'Keuangan',
                'description' => 'Panduan pengajuan reimbursement biaya operasional, perjalanan dinas, dan keperluan event wedding melalui sistem.',
                'keywords' => 'reimbursement, kwitansi, nota dinas, penggantian, biaya, keuangan',
                'version' => '1.0',
                'is_active' => true,
                'effective_date' => now()->toDateString(),
                'review_date' => now()->addMonths(6)->toDateString(),
                'steps' => [
                    $this->step(1, 'Persiapan Dokumen', '<p>Kumpulkan kwitansi atau nota asli. Pastikan ada tanggal, nama merchant, dan nominal yang jelas.</p><ul><li>Kwitansi asli atau scan berkualitas</li><li>Bukti transfer jika pembayaran non-tunai</li></ul>', '<p>Kwitansi yang tidak lengkap akan ditolak.</p>'),
                    $this->step(2, 'Login ke Sistem', '<p>Masuk ke portal karyawan menggunakan akun yang diberikan IT.</p>'),
                    $this->step(3, 'Isi Form Pengajuan', '<p>Pilih menu reimbursement, lalu isi tanggal transaksi, nominal, kategori, dan deskripsi pengeluaran.</p>', '<p>Pastikan kategori pengeluaran sesuai chart of account.</p>'),
                    $this->step(4, 'Upload Dokumen', '<p>Unggah foto atau scan kwitansi (JPG, PNG, atau PDF, maksimal 5MB).</p>'),
                    $this->step(5, 'Submit dan Tunggu Approval', '<p>Kirim pengajuan dan pantau status di dashboard hingga disetujui atasan.</p>', '<p>Proses approval maksimal 3 hari kerja.</p>'),
                ],
            ],
            [
                'title' => 'Prosedur Rekrutmen Karyawan Baru',
                'category_name' => 'SDM',
                'description' => 'Panduan rekrutmen dari pengajuan kebutuhan SDM, seleksi, interview, hingga onboarding karyawan baru.',
                'keywords' => 'rekrutmen, hiring, karyawan, interview, onboarding, sdm',
                'version' => '2.1',
                'is_active' => true,
                'effective_date' => now()->subDays(30)->toDateString(),
                'review_date' => now()->addYear()->toDateString(),
                'steps' => [
                    $this->step(1, 'Analisis Kebutuhan SDM', '<p>Departemen mengajukan request ke HRD: posisi, kualifikasi, dan budget.</p>', '<p>Request harus disetujui manager departemen.</p>'),
                    $this->step(2, 'Pembuatan Job Description', '<p>HRD menyusun job description bersama user departemen.</p>'),
                    $this->step(3, 'Posting Lowongan', '<p>Publikasikan di website, job portal, dan media sosial perusahaan.</p>', '<p>Periode posting minimal 2 minggu untuk posisi senior.</p>'),
                    $this->step(4, 'Seleksi CV', '<p>Screening CV berdasarkan kualifikasi minimum.</p>'),
                    $this->step(5, 'Interview', '<p>Interview HRD, lalu interview manager departemen. Dokumentasikan hasil di form evaluasi.</p>'),
                    $this->step(6, 'Background Check', '<p>Verifikasi dokumen, referensi kerja, dan rekam jejak kandidat terpilih.</p>'),
                    $this->step(7, 'Onboarding', '<p>Orientasi, setup akun sistem, dan pengenalan SOP internal.</p>', '<p>Probation 3 bulan dengan evaluasi bulanan.</p>'),
                ],
            ],
            [
                'title' => 'Prosedur Backup Data Harian',
                'category_name' => 'IT',
                'description' => 'Prosedur backup otomatis dan manual agar data aplikasi, database, dan dokumen perusahaan tetap aman.',
                'keywords' => 'backup, data, database, sistem, it, keamanan',
                'version' => '1.3',
                'is_active' => true,
                'effective_date' => now()->subDays(10)->toDateString(),
                'review_date' => now()->addMonths(3)->toDateString(),
                'steps' => [
                    $this->step(1, 'Verifikasi Backup Otomatis', '<p>Cek status backup otomatis pukul 02:00 WIB di dashboard monitoring.</p>', '<p>Jika gagal, segera lakukan backup manual.</p>'),
                    $this->step(2, 'Backup Database Manual', '<p>Gunakan mysqldump atau tools yang disediakan IT.</p>', '<p>Format nama: backup_YYYYMMDD_HHMMSS.sql</p>'),
                    $this->step(3, 'Backup File Sistem', '<p>Salin folder aplikasi, dokumen, dan konfigurasi ke storage eksternal.</p>'),
                    $this->step(4, 'Verifikasi Integritas', '<p>Uji restore sample data untuk memastikan backup bisa dipulihkan.</p>', '<p>Test restore minimal 1 kali seminggu.</p>'),
                    $this->step(5, 'Dokumentasi dan Laporan', '<p>Catat hasil di log sistem dan laporkan kendala ke supervisor IT.</p>', '<p>Simpan log backup minimal 30 hari.</p>'),
                ],
            ],
            [
                'title' => 'Prosedur Pembukaan dan Penutupan Kantor',
                'category_name' => 'Operasional',
                'description' => 'Panduan operasional harian untuk membuka dan menutup kantor dengan aman, termasuk cek ruang meeting dan peralatan event.',
                'keywords' => 'operasional, kantor, keamanan, harian, buka, tutup',
                'version' => '1.0',
                'is_active' => true,
                'effective_date' => now()->subDays(5)->toDateString(),
                'review_date' => now()->addMonths(12)->toDateString(),
                'steps' => [
                    $this->step(1, 'Pembukaan Kantor Pagi', '<p>Matikan alarm, nyalakan lampu dan AC, lalu cek kondisi umum kantor.</p>', '<p>Jam operasional: 08:00–17:00 WIB.</p>'),
                    $this->step(2, 'Persiapan Ruang Kerja', '<p>Pastikan ruang meeting, pantry, dan peralatan kantor siap pakai.</p>'),
                    $this->step(3, 'Penutupan Kantor Sore', '<p>Pastikan tim sudah pulang. Matikan elektronik kecuali server dan CCTV.</p>', '<p>Cek dua kali pintu dan jendela.</p>'),
                    $this->step(4, 'Aktivasi Keamanan', '<p>Aktifkan alarm dan pastikan semua akses terkunci.</p>', '<p>Tunggu konfirmasi alarm aktif sebelum meninggalkan gedung.</p>'),
                ],
            ],
            [
                'title' => 'Prosedur Simulasi Produk dan Follow Up Prospek',
                'category_name' => 'Penjualan',
                'description' => 'Standar Account Manager saat membuat simulasi produk, presentasi ke klien, dan follow up hingga closing.',
                'keywords' => 'simulasi, prospek, account manager, penjualan, follow up, closing',
                'version' => '1.1',
                'is_active' => true,
                'effective_date' => now()->subDays(14)->toDateString(),
                'review_date' => now()->addMonths(6)->toDateString(),
                'steps' => [
                    $this->step(1, 'Input Data Prospek', '<p>Catat nama pasangan, tanggal acara, pax akad/resepsi, dan lokasi di modul prospek.</p>'),
                    $this->step(2, 'Susun Simulasi Produk', '<p>Pilih paket produk, sesuaikan fasilitas vendor, pengurangan, dan penambahan harga.</p>', '<p>Pastikan harga publish dan harga vendor terisi.</p>'),
                    $this->step(3, 'Kirim Tautan Simulasi', '<p>Bagikan slug simulasi ke klien dan jelaskan isi paket secara ringkas.</p>'),
                    $this->step(4, 'Follow Up', '<p>Hubungi klien maksimal H+2 setelah pengiriman. Catat feedback di catatan prospek.</p>'),
                    $this->step(5, 'Closing dan Draft Kontrak', '<p>Jika disetujui, lanjutkan ke order dan generate draft kontrak sesuai template aktif.</p>', '<p>DP dan termin harus sesuai simulasi yang disepakati.</p>'),
                ],
            ],
            [
                'title' => 'Prosedur Draft Kontrak Wedding',
                'category_name' => 'Administrasi',
                'description' => 'Tata cara memakai template kontrak, mengisi placeholder, dan menerbitkan draft PDF untuk klien.',
                'keywords' => 'kontrak, template, draft, administrasi, placeholder, pdf',
                'version' => '1.0',
                'is_active' => true,
                'effective_date' => now()->subDays(7)->toDateString(),
                'review_date' => now()->addMonths(9)->toDateString(),
                'steps' => [
                    $this->step(1, 'Pilih Template Aktif', '<p>Pastikan perusahaan memakai template kontrak yang aktif di menu Administrasi.</p>'),
                    $this->step(2, 'Cek Data Order', '<p>Lengkapi nama klien, venue, pax, DP, dan daftar termin sebelum generate.</p>'),
                    $this->step(3, 'Generate Draft', '<p>Unduh PDF draft dan periksa placeholder seperti nama perusahaan, DP, dan daftar termin.</p>', '<p>Jika masih ada teks {{...}}, lengkapi data lalu generate ulang.</p>'),
                    $this->step(4, 'Review dan Kirim', '<p>Admin meninjau draft, lalu kirim ke klien untuk tanda tangan.</p>'),
                    $this->step(5, 'Arsip', '<p>Simpan file signed di folder dokumen order.</p>'),
                ],
            ],
            [
                'title' => 'Prosedur Publikasi Konten Wedding',
                'category_name' => 'Marketing',
                'description' => 'Alur publikasi foto/video hasil event ke media sosial, termasuk izin klien dan credit vendor.',
                'keywords' => 'marketing, konten, instagram, publikasi, vendor, klien',
                'version' => '1.0',
                'is_active' => true,
                'effective_date' => now()->subDays(3)->toDateString(),
                'review_date' => now()->addMonths(4)->toDateString(),
                'steps' => [
                    $this->step(1, 'Minta Izin Klien', '<p>Pastikan klien menyetujui publikasi foto/video di kontrak atau form terpisah.</p>'),
                    $this->step(2, 'Kurasi Materi', '<p>Pilih 5–10 foto terbaik dan 1 highlight video dari vendor dokumentasi.</p>'),
                    $this->step(3, 'Tulis Caption', '<p>Sertakan nama pasangan (jika diizinkan), venue, dan credit vendor.</p>'),
                    $this->step(4, 'Jadwalkan Tayang', '<p>Publish H+7 sampai H+14 setelah acara, di luar jam sibuk klien.</p>', '<p>Hindari spoil jika klien minta embargo.</p>'),
                    $this->step(5, 'Laporkan Performa', '<p>Catat insight engagement untuk evaluasi kampanye bulanan.</p>'),
                ],
            ],
        ];
    }

    /**
     * @return array{step_number: int, title: string, description: string, notes: string|null}
     */
    private function step(int $number, string $title, string $description, ?string $notes = null): array
    {
        return [
            'step_number' => $number,
            'title' => $title,
            'description' => $description,
            'notes' => $notes,
        ];
    }

    /**
     * @param  array<string, mixed>  $sop
     * @return list<string>
     */
    private function seedSupportingDocuments(array $sop): array
    {
        $slug = Str::slug($sop['title']);
        $pdfPath = 'sop-documents/'.$slug.'.pdf';
        $oldPng = 'sop-documents/'.$slug.'.png';

        $stepsHtml = collect($sop['steps'] ?? [])
            ->map(function (array $step): string {
                $number = e((string) ($step['step_number'] ?? ''));
                $title = e((string) ($step['title'] ?? ''));
                $description = strip_tags((string) ($step['description'] ?? ''));

                return '<li><strong>'.$number.'. '.$title.'</strong><br>'.e($description).'</li>';
            })
            ->implode('');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #6b7280; margin-bottom: 16px; }
        ol { padding-left: 18px; }
        li { margin-bottom: 10px; }
        .footer { margin-top: 24px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Lampiran SOP</h1>
    <div class="meta">
        Kategori: {$sop['category_name']} &nbsp;|&nbsp; Versi: {$sop['version']}
    </div>
    <h2>{$sop['title']}</h2>
    <p>{$sop['description']}</p>
    <p><strong>Ringkasan langkah:</strong></p>
    <ol>{$stepsHtml}</ol>
    <p class="footer">Dokumen ini adalah lampiran resmi SOP dan dapat diunduh dari form Dokumen Pendukung.</p>
</body>
</html>
HTML;

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        Storage::disk('public')->put($pdfPath, $pdf->output());

        if (Storage::disk('public')->exists($oldPng)) {
            Storage::disk('public')->delete($oldPng);
        }

        return [$pdfPath];
    }
}
