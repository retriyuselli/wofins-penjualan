<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Support\DocumentNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();
        $creator = $users->first();

        if (! $creator) {
            $this->command->error('User belum ada. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        if (DocumentCategory::query()->doesntExist()) {
            $this->call(DocumentCategorySeeder::class);
        }

        $categories = DocumentCategory::query()->get()->keyBy('code');
        if ($categories->isEmpty()) {
            $this->command->error('Kategori dokumen belum ada. Jalankan DocumentCategorySeeder terlebih dahulu.');

            return;
        }

        $recipientIds = $users->pluck('id')->all();
        $created = 0;

        foreach ($this->documents() as $data) {
            $category = $categories->get($data['category_code']);
            if (! $category) {
                $this->command->warn("Kategori '{$data['category_code']}' tidak ditemukan, dilewati.");

                continue;
            }

            $title = $data['title'];
            $existing = Document::withTrashed()->where('title', $title)->first();

            $document = Document::withTrashed()->updateOrCreate(
                ['title' => $title],
                [
                    'category_id' => $category->id,
                    'document_number' => $existing?->document_number ?: $this->documentNumber($category, 1),
                    'summary' => $data['summary'],
                    'content' => $data['content'],
                    'date_effective' => $data['date_effective'],
                    'date_expired' => $data['date_expired'],
                    'status' => $data['status'],
                    'confidentiality' => $data['confidentiality'],
                    'use_digital_signature' => $data['use_digital_signature'],
                    'show_confidentiality_warning' => $data['show_confidentiality_warning'],
                    'metadata' => $data['metadata'],
                    'created_by' => $creator->id,
                ]
            );

            if ($document->trashed()) {
                $document->restore();
            }

            $document->recipientsList()->sync($recipientIds);
            $this->syncAttachment($document, $data);

            $created++;
        }

        $this->command->info("✅ DocumentSeeder: {$created} dokumen dibuat/diperbarui sesuai form.");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function documents(): array
    {
        return [
            [
                'title' => 'Surat Keputusan Standar Vendor Wedding',
                'category_code' => 'SK',
                'confidentiality' => 'internal',
                'use_digital_signature' => true,
                'show_confidentiality_warning' => false,
                'status' => 'published',
                'date_effective' => now()->subDays(10)->toDateString(),
                'date_expired' => now()->addYears(2)->toDateString(),
                'summary' => 'Ketentuan pemilihan, evaluasi, dan perpanjangan kerja sama vendor wedding di lingkungan perusahaan.',
                'content' => '<h2>Maksud dan Tujuan</h2><p>SK ini mengatur standar vendor yang boleh masuk ke simulasi produk dan order.</p><ul><li>Vendor induk wajib punya PIC, telepon, dan rekening.</li><li>Produk vendor wajib punya harga publish dan harga vendor.</li><li>Evaluasi vendor dilakukan setiap akhir tahun.</li></ul><h3>Penutup</h3><p>SK berlaku sejak tanggal efektif dan dievaluasi paling lambat dua tahun kemudian.</p>',
                'metadata' => [
                    'keywords' => 'sk, vendor, standar, wedding',
                    'version' => '1.0',
                    'priority' => 'high',
                ],
            ],
            [
                'title' => 'Surat Tugas Survey Venue Ballroom Thamrin',
                'category_code' => 'ST',
                'confidentiality' => 'internal',
                'use_digital_signature' => true,
                'show_confidentiality_warning' => false,
                'status' => 'published',
                'date_effective' => now()->subDays(5)->toDateString(),
                'date_expired' => now()->addMonths(1)->toDateString(),
                'summary' => 'Penugasan tim operasional untuk survey kapasitas, lighting, dan parkir venue ballroom 300–500 pax.',
                'content' => '<h2>Tugas</h2><p>Tim wajib meninjau ballroom, ruang akad, dan jalur loading vendor.</p><ul><li>Ukur kapasitas duduk 300 dan 500 pax.</li><li>Cek cadangan genset dan loading dock.</li><li>Laporkan hasil ke Account Manager dalam 2 hari kerja.</li></ul><h3>Penanggung jawab</h3><p>Event Manager berkoordinasi dengan PIC venue.</p>',
                'metadata' => [
                    'keywords' => 'surat tugas, survey, venue, ballroom',
                    'version' => '1.0',
                    'priority' => 'normal',
                ],
            ],
            [
                'title' => 'Memo Internal Briefing Rundown Hari H',
                'category_code' => 'MEMO',
                'confidentiality' => 'internal',
                'use_digital_signature' => false,
                'show_confidentiality_warning' => false,
                'status' => 'published',
                'date_effective' => now()->subDays(2)->toDateString(),
                'date_expired' => now()->addMonths(3)->toDateString(),
                'summary' => 'Pengingat briefing rundown, titik kumpul crew, dan kanal komunikasi di hari H.',
                'content' => '<h2>Isi Memo</h2><p>Briefing dilakukan H-1 pukul 16.00 di kantor.</p><ul><li>Crew wajib bawa rundown tercetak.</li><li>Channel utama: grup WhatsApp event.</li><li>Titik kumpul H: lobi venue 06.00.</li></ul><p>Mohon dibaca seluruh Account Manager dan crew lapangan.</p>',
                'metadata' => [
                    'keywords' => 'memo, rundown, briefing, crew',
                    'version' => '1.1',
                    'priority' => 'normal',
                ],
            ],
            [
                'title' => 'Berita Acara Serah Terima Event Garden Puncak',
                'category_code' => 'BA',
                'confidentiality' => 'confidential',
                'use_digital_signature' => true,
                'show_confidentiality_warning' => true,
                'status' => 'approved',
                'date_effective' => now()->subDays(1)->toDateString(),
                'date_expired' => now()->addYear()->toDateString(),
                'summary' => 'Berita acara serah terima venue, tenda, dan peralatan setelah event outdoor 200 pax.',
                'content' => '<h2>Yang Diserahkan</h2><p>Tim operasional menyerahkan kondisi venue dan inventaris ke PIC garden.</p><ul><li>Tenda dan flooring dalam kondisi baik.</li><li>Tidak ada kerusakan lighting venue.</li><li>Area parkir dikembalikan bersih.</li></ul><h3>Catatan</h3><p>Kekurangan minor pada 2 kursi lipat sudah dicatat untuk klaim deposit.</p>',
                'metadata' => [
                    'keywords' => 'berita acara, serah terima, garden, outdoor',
                    'version' => '1.0',
                    'priority' => 'high',
                ],
            ],
            [
                'title' => 'Surat Konfirmasi Paket Wedding kepada Klien',
                'category_code' => 'OUT',
                'confidentiality' => 'confidential',
                'use_digital_signature' => true,
                'show_confidentiality_warning' => true,
                'status' => 'published',
                'date_effective' => now()->toDateString(),
                'date_expired' => now()->addMonths(6)->toDateString(),
                'summary' => 'Konfirmasi paket, pax, dan jadwal pembayaran DP serta termin kepada klien.',
                'content' => '<h2>Konfirmasi</h2><p>Bersama ini kami konfirmasi paket sesuai simulasi yang disetujui.</p><ul><li>Resepsi 300 pax, akad 100 pax.</li><li>DP sesuai draft kontrak.</li><li>Perubahan vendor wajib tertulis.</li></ul><p>Surat ini dilampiri ringkasan fasilitas paket.</p>',
                'metadata' => [
                    'keywords' => 'surat keluar, konfirmasi, klien, dp',
                    'version' => '1.0',
                    'priority' => 'high',
                ],
            ],
            [
                'title' => 'Tembusan Penawaran Catering Premium Bogor',
                'category_code' => 'IN',
                'confidentiality' => 'internal',
                'use_digital_signature' => false,
                'show_confidentiality_warning' => false,
                'status' => 'draft',
                'date_effective' => now()->subDays(3)->toDateString(),
                'date_expired' => now()->addMonths(2)->toDateString(),
                'summary' => 'Tembusan penawaran menu prasmanan 300 pax dari vendor catering untuk arsip internal.',
                'content' => '<h2>Ringkasan Surat Masuk</h2><p>Vendor mengirim revisi menu dan harga publish paket catering 300 pax.</p><ul><li>Include welcome drink dan dessert.</li><li>Harga berlaku 90 hari.</li><li>Perlu dicocokkan dengan harga di master vendor.</li></ul><p>Finance dan Account Manager diminta meninjau sebelum update harga.</p>',
                'metadata' => [
                    'keywords' => 'surat masuk, catering, penawaran, arsip',
                    'version' => '1.0',
                    'priority' => 'normal',
                ],
            ],
        ];
    }

    private function documentNumber(DocumentCategory $category, int $seq): string
    {
        return DocumentNumber::format($category, $seq);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncAttachment(Document $document, array $data): void
    {
        $slug = Str::slug($document->title);
        $path = 'documents/'.$slug.'.pdf';
        $fileName = 'Lampiran '.$document->title.'.pdf';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; line-height: 1.5; }
        h1 { font-size: 16px; }
        .meta { color: #6b7280; margin-bottom: 16px; }
    </style>
</head>
<body>
    <h1>Lampiran Dokumen</h1>
    <div class="meta">{$document->document_number}</div>
    <h2>{$document->title}</h2>
    <p>{$data['summary']}</p>
    <p>Lampiran ini diunggah melalui tab Attachments &amp; Metadata.</p>
</body>
</html>
HTML;

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        Storage::disk('public')->put($path, $pdf->output());

        DocumentAttachment::query()
            ->where('document_id', $document->id)
            ->delete();

        DocumentAttachment::create([
            'document_id' => $document->id,
            'file_path' => $path,
            'file_name' => $fileName,
            'mime_type' => 'application/pdf',
            'file_size' => Storage::disk('public')->size($path),
        ]);
    }
}
