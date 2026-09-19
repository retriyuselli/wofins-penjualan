<?php

namespace App\Services;

use App\Models\AppLicense;
use App\Models\Company;
use Illuminate\Support\Str;

class SubscriptionAgreementService
{
    /**
     * @return array<string, mixed>
     */
    public function viewData(Company $company): array
    {
        $license = app(AppLicenseService::class)->current();
        $ownerTitle = filled($company->jabatan_owner)
            ? $company->jabatan_owner
            : 'Pemilik / Penanggung Jawab';

        $legalName = trim(implode(' ', array_filter([
            $company->legal_entity_type,
            $company->company_name,
        ]))) ?: ($company->company_name ?: '—');

        $addressParts = array_filter([
            $company->address,
            $company->city,
            $company->province,
            $company->postal_code,
        ]);

        $startsAt = $license?->starts_at ?? $company->created_at?->copy()->startOfDay();
        $endsAt = $license?->ends_at;

        return [
            'provider' => $this->provider(),
            'company' => $company,
            'order' => null,
            'contract_number' => sprintf('WOFINS/PKS/%s/%04d', now()->format('Y'), $company->id),
            'place' => 'Palembang',
            'signed_on' => now(),
            'legal_name' => $legalName,
            'owner_name' => $company->owner_name ?: '—',
            'owner_title' => $ownerTitle,
            'address' => $addressParts ? implode(', ', $addressParts) : '—',
            'email' => $company->email ?: '—',
            'phone' => $company->phone ?: '—',
            'website' => $company->website ?: ($license?->domain ?: request()->getHost()),
            'nib' => $company->nib_number ?: '—',
            'npwp' => $company->npwp_number ?: '—',
            'business_license' => $company->business_license ?: '—',
            'plan_name' => $license?->package ?: 'Enterprise / instalasi khusus',
            'plan_scope' => 'Instalasi WOFINS untuk perusahaan Pihak Kedua, termasuk domain produksi yang terikat lisensi.',
            'billing_label' => $this->licenseDurationLabel($license),
            'amount' => 0,
            'amount_label' => 'Sesuai invoice / Item Purchase Code yang disetujui',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'order_code' => $license?->code ?: '—',
            'articles' => $this->articles(),
        ];
    }

    public function filename(Company $company): string
    {
        $slug = Str::slug($company->company_name ?: 'perusahaan');

        return 'Perjanjian-Berlangganan-WOFINS-'.$slug.'.pdf';
    }

    public function version(): string
    {
        return (string) config('wofins.agreement.version', '2026-09-19');
    }

    public function pdfContents(Company $company): string
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.perjanjian_berlangganan', $this->viewData($company));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf->output();
    }

    /**
     * @return array<string, string>
     */
    public function provider(): array
    {
        return [
            'legal_name' => (string) config('wofins.provider.legal_name', 'Makna Kreatif Indonesia'),
            'brand' => (string) config('wofins.provider.brand', 'WOFINS'),
            'address' => (string) config('wofins.provider.address', 'Jl. Sintraman Jaya I No. 2148, 20 Ilir D II, Kec. Kemuning, Kota Palembang, Sumatera Selatan 30137'),
            'email' => (string) config('wofins.provider.email', 'office@wofins.id'),
            'support_email' => (string) config('wofins.provider.support_email', 'support@wofins.id'),
            'whatsapp' => (string) config('wofins.provider.whatsapp', '+62 813-7318-3794'),
            'website' => (string) config('wofins.provider.website', 'https://wofins.id'),
            'app_url' => (string) config('wofins.provider.app_url', 'https://app.wofins.id'),
            'signatory_name' => (string) config('wofins.provider.signatory_name', 'Kuasa Pengelola WOFINS'),
            'signatory_title' => (string) config('wofins.provider.signatory_title', 'Penyedia Layanan'),
        ];
    }

    private function licenseDurationLabel(?AppLicense $license): string
    {
        if (! $license?->starts_at || ! $license?->ends_at) {
            return 'Mengikuti masa aktif lisensi';
        }

        $months = (int) $license->starts_at->diffInMonths($license->ends_at);

        return $months > 0 ? $months.' bulan (lisensi)' : 'Mengikuti masa aktif lisensi';
    }

    /**
     * @return list<array{title: string, body: string}>
     */
    public function articles(): array
    {
        return [
            [
                'title' => 'Pasal 1 — Definisi',
                'body' => '<ol>
<li><strong>Layanan</strong> adalah perangkat lunak WOFINS (Wedding Organizer Financial Information System) yang disediakan secara berlangganan (SaaS), termasuk aplikasi web, aplikasi seluler yang diaktifkan, API yang disediakan Penyedia, serta pembaruan yang dirilis dari waktu ke waktu.</li>
<li><strong>Paket</strong> adalah tingkatan layanan Starter, Professional, Business, atau Enterprise beserta kuota, fitur, dan harga yang berlaku pada saat pemesanan.</li>
<li><strong>Masa Berlangganan</strong> adalah jangka waktu akses yang dibayar Pihak Kedua sebagaimana tercantum pada identitas perjanjian ini atau perpanjangannya.</li>
<li><strong>Data Pelanggan</strong> adalah data bisnis yang dimasukkan Pihak Kedua atau penggunanya, termasuk prospek, klien, proyek, keuangan, dokumen, dan file unggahan.</li>
<li><strong>Pengguna</strong> adalah individu yang diberi akses oleh Pihak Kedua (pemilik, staf, atau pihak yang diundang).</li>
<li><strong>Domain Khusus</strong> adalah nama domain dan/atau instalasi terpisah pada Paket Enterprise atau kesepakatan tertulis lain, terikat satu kode lisensi untuk satu domain produksi.</li>
</ol>',
            ],
            [
                'title' => 'Pasal 2 — Objek dan sifat layanan',
                'body' => '<p>Penyedia memberikan kepada Pihak Kedua hak akses non-eksklusif, tidak dapat dipindahtangankan, dan terbatas untuk memakai Layanan selama Masa Berlangganan aktif, sesuai Paket yang dibayar. Perjanjian ini <strong>bukan</strong> jual beli kode sumber, bukan pengalihan hak cipta, dan bukan perjanjian kerja sama operasional wedding.</p>
<p>WOFINS disediakan, sesuai Paket, untuk pengelolaan prospek, vendor, produk, proyek, invoice, simulasi, pencatatan keuangan, kas/bank, nota dinas, rekonsiliasi, aset, payroll, dokumen, SOP, undangan crew freelance, dan laporan, serta dukungan teknis sesuai tingkat Paket.</p>
<p>Fitur yang tidak termasuk dalam Paket tidak menjadi kewajiban Penyedia hingga Pihak Kedua melakukan peningkatan Paket atau kesepakatan tertulis.</p>',
            ],
            [
                'title' => 'Pasal 3 — Paket, kuota, dan harga',
                'body' => '<p>Harga acuan pada saat Perjanjian ini disusun adalah:</p>
<table>
<thead><tr><th>Paket</th><th>Harga / bulan</th><th>Pengguna</th><th>Cakupan utama</th></tr></thead>
<tbody>
<tr><td>Starter</td><td>Rp 110.000</td><td>1 (pemilik)</td><td>Proyek, invoice, kas, nota dinas, laporan dasar</td></tr>
<tr><td>Professional</td><td>Rp 180.000</td><td>1 (pemilik)</td><td>Semua Starter + simulasi, draf kontrak kerja, aset, rekonsiliasi, payroll</td></tr>
<tr><td>Business</td><td>Rp 295.000</td><td>Hingga 3</td><td>Semua Professional + crew freelance, dokumen &amp; SOP, laporan AM, onboarding tim</td></tr>
<tr><td>Enterprise</td><td>Rp 333.333</td><td>Tidak dibatasi kuota paket</td><td>Semua Business + domain, hosting, SSL, cadangan, kustomisasi, support pengembang. Minimal 24 bulan (Rp 8.000.000)</td></tr>
</tbody>
</table>
<p>Nilai yang mengikat Pihak Kedua adalah nilai pada identitas perjanjian / invoice / pesanan yang disetujui. Tidak ada biaya instalasi untuk Paket Starter, Professional, dan Business pada platform bersama. Harga belum termasuk pajak yang diwajibkan, kecuali dinyatakan lain pada invoice. Perubahan harga hanya berlaku pada perpanjangan berikutnya, kecuali disepakati lain secara tertulis.</p>',
            ],
            [
                'title' => 'Pasal 4 — Pendaftaran, aktivasi, dan akun',
                'body' => '<ol>
<li>Pihak Kedua wajib memberikan data yang benar, termasuk nama penanggung jawab yang berwenang mengambil keputusan pembelian perangkat lunak. Penandatangan Perjanjian ini menyatakan berwenang mewakili badan usaha Pihak Kedua.</li>
<li>Akses diberikan setelah pembayaran dikonfirmasi Penyedia dan, jika berlaku, lisensi diaktifkan.</li>
<li>Untuk instalasi Domain Khusus, satu Item Purchase Code hanya berlaku untuk satu domain produksi. Pemindahan domain memerlukan penerbitan ulang sesuai prosedur Penyedia.</li>
<li>Pihak Kedua bertanggung jawab atas seluruh aktivitas pada akun perusahaan, pengelolaan peran, dan kerahasiaan kata sandi atau token perangkat.</li>
<li>WOFINS ditujukan untuk pengguna bisnis berusia 18 tahun atau lebih.</li>
</ol>',
            ],
            [
                'title' => 'Pasal 5 — Pembayaran',
                'body' => '<ol>
<li>Pembayaran dilakukan dengan transfer bank sesuai instruksi checkout, dilampiri bukti, atau cara lain yang disetujui Penyedia.</li>
<li>Paket aktif setelah status pesanan disetujui. Keterlambatan konfirmasi karena bukti tidak jelas menjadi tanggung jawab Pihak Kedua.</li>
<li>Perpanjangan dilakukan sebelum tanggal berakhir. Jika masa aktif habis, akses dashboard dapat ditangguhkan; Data Pelanggan tidak dihapus semata-mata karena kedaluwarsa, kecuali Pasal 12 berlaku.</li>
<li>Pembayaran yang telah diterima bersifat <strong>tidak dapat dikembalikan</strong> (non-refundable), termasuk sisa masa yang tidak terpakai karena pengakhiran oleh Pihak Kedua, kecuali pembayaran ganda yang terbukti, atau Layanan tidak dapat disediakan oleh Penyedia tanpa kesalahan Pihak Kedua dan para pihak tidak dapat menyediakan pengganti yang wajar dalam 14 hari kerja.</li>
<li>Peningkatan Paket dapat dilakukan setiap saat; selisih biaya dihitung secara proporsional menurut kebijakan operasional Penyedia pada saat pengajuan.</li>
</ol>',
            ],
            [
                'title' => 'Pasal 6 — Kewajiban Penyedia',
                'body' => '<p>Penyedia akan, dengan upaya wajar secara komersial:</p>
<ul>
<li>menyediakan Layanan sesuai Paket;</li>
<li>menerapkan kontrol akses berbasis peran, koneksi terenkripsi, dan pengamanan yang wajar;</li>
<li>melakukan pemeliharaan, pembaruan, dan perbaikan gangguan;</li>
<li>memberikan dukungan sesuai tingkat Paket: Starter pada jam kerja; Professional dengan target respons 1 hari kerja; Business melalui saluran prioritas termasuk WhatsApp; Enterprise melalui dukungan langsung pengembang;</li>
<li>pada Paket Enterprise, membantu setup domain, hosting, SSL, dan cadangan selama Masa Berlangganan aktif, sesuai ketersediaan nama domain.</li>
</ul>
<p>Layanan tidak dijamin bebas gangguan, bebas kesalahan, atau tersedia 100% setiap saat. Pemeliharaan terjadwal atau keadaan di luar kendali wajar Penyedia tidak dianggap wanprestasi semata-mata karena adanya jeda akses.</p>',
            ],
            [
                'title' => 'Pasal 7 — Kewajiban dan larangan Pihak Kedua',
                'body' => '<p>Pihak Kedua wajib:</p>
<ul>
<li>memakai Layanan hanya untuk kegiatan usaha yang sah;</li>
<li>memastikan kebenaran data yang diunggah dan memiliki dasar hukum untuk memproses data klien, vendor, dan karyawan;</li>
<li>tidak melebihi kuota Paket, kecuali disepakati tertulis;</li>
<li>tidak membagikan akun secara tidak sah, tidak melakukan reverse engineering, scraping berlebihan, atau merusak keamanan sistem;</li>
<li>tidak menempatkan malware, konten melanggar hukum, atau data yang Pihak Kedua tidak berhak memproses;</li>
<li>mematuhi hukum Indonesia, termasuk perlindungan data pribadi.</li>
</ul>',
            ],
            [
                'title' => 'Pasal 8 — Data Pelanggan dan privasi',
                'body' => '<ol>
<li>Data Pelanggan tetap milik Pihak Kedua. Penyedia memproses data tersebut untuk menyediakan Layanan, keamanan, dukungan, dan kewajiban hukum.</li>
<li>Pihak Kedua adalah pengendali data atas data klien/vendor/karyawannya. Penyedia bertindak sebagai pemroses sebatas keperluan Layanan.</li>
<li>Penyedia tidak menjual Data Pelanggan dan tidak memakai data bisnis untuk iklan lintas aplikasi.</li>
<li>Rincian pemrosesan data mengikuti Kebijakan Privasi di wofins.id yang merupakan bagian tidak terpisahkan dari Perjanjian ini.</li>
<li>Pihak Kedua dapat meminta ekspor atau penghapusan data yang memenuhi syarat melalui email dukungan resmi Penyedia, dengan verifikasi identitas dan persetujuan administrator perusahaan.</li>
</ol>',
            ],
            [
                'title' => 'Pasal 9 — Kekayaan intelektual',
                'body' => '<p>Nama, merek, desain, kode, dokumentasi, dan seluruh kekayaan intelektual WOFINS milik Penyedia atau pemberi lisensinya. Pihak Kedua hanya memperoleh hak pakai terbatas selama Masa Berlangganan. Dilarang menyalin, menyewakan, mensublisensikan, atau membuat karya turunan dari perangkat lunak tanpa izin tertulis.</p>
<p>Template dokumen yang dihasilkan Layanan (invoice, simulasi, draf kontrak kerja wedding, dan sejenisnya) boleh dipakai Pihak Kedua untuk operasional usahanya sendiri, tanpa mengalihkan hak atas perangkat lunak.</p>',
            ],
            [
                'title' => 'Pasal 10 — Kerahasiaan',
                'body' => '<p>Para pihak menjaga kerahasiaan informasi non-publik yang diperoleh karena Perjanjian ini, kecuali informasi yang sudah umum diketahui, wajib diungkapkan hukum, atau diizinkan pihak pemilik informasi.</p>',
            ],
            [
                'title' => 'Pasal 11 — Batasan tanggung jawab',
                'body' => '<ol>
<li>Layanan disediakan “sebagaimana adanya” dan “sebagaimana tersedia”.</li>
<li>Penyedia tidak bertanggung jawab atas keputusan bisnis Pihak Kedua, keakuratan data yang diinput Pengguna, sengketa Pihak Kedua dengan klien/vendornya, atau kerugian karena kata sandi yang bocor di sisi Pihak Kedua.</li>
<li>Sejauh diizinkan hukum, tanggung jawab kumulatif Penyedia atas klaim yang timbul dari Perjanjian ini dibatasi sebesar biaya langganan yang benar-benar dibayar Pihak Kedua kepada Penyedia untuk 12 (dua belas) bulan terakhir sebelum klaim.</li>
<li>Penyedia tidak bertanggung jawab atas kerugian tidak langsung, kehilangan keuntungan, kehilangan data yang dapat dicegah dengan cadangan wajar di sisi Pihak Kedua, atau kerusakan reputasi.</li>
</ol>',
            ],
            [
                'title' => 'Pasal 12 — Jangka waktu dan pengakhiran',
                'body' => '<ol>
<li>Perjanjian berlaku sejak aktivasi hingga akhir Masa Berlangganan, dan berlanjut jika diperpanjang.</li>
<li>Pihak Kedua dapat berhenti memakai Layanan kapan saja; sisa iuran tidak dikembalikan sesuai Pasal 5.</li>
<li>Penyedia dapat menangguhkan atau mengakhiri akses jika Pihak Kedua wanprestasi, menyalahgunakan Layanan, atau pembayaran tidak sah, setelah pemberitahuan yang wajar kecuali ada risiko keamanan mendesak.</li>
<li>Setelah pengakhiran, Pihak Kedua dapat meminta salinan Data Pelanggan dalam format yang wajar dalam 14 hari kalender. Setelah itu Penyedia boleh menghapus data dari sistem produksi sesuai siklus cadangan.</li>
</ol>',
            ],
            [
                'title' => 'Pasal 13 — Perubahan layanan dan ketentuan',
                'body' => '<p>Penyedia berhak memperbaiki, menambah, atau menonaktifkan fitur sepanjang fungsi utama Paket tetap tersedia secara wajar, atau menawarkan alternatif. Perubahan materiil atas Perjanjian ini akan diumumkan di situs atau melalui email. Pemakaian Layanan setelah tanggal berlaku perubahan merupakan persetujuan, kecuali Pihak Kedua mengakhiri langganan sebelum tanggal tersebut.</p>',
            ],
            [
                'title' => 'Pasal 14 — Keadaan kahar',
                'body' => '<p>Tidak ada pihak yang wanprestasi semata-mata karena kegagalan memenuhi kewajiban akibat peristiwa di luar kendali wajar, termasuk bencana, gangguan listrik atau jaringan nasional, tindakan pemerintah, atau serangan siber yang tidak dapat dicegah dengan pengamanan yang wajar.</p>',
            ],
            [
                'title' => 'Pasal 15 — Hukum yang berlaku dan sengketa',
                'body' => '<p>Perjanjian ini tunduk pada hukum Republik Indonesia. Sengketa diselesaikan terlebih dahulu secara musyawarah dalam 30 hari kalender. Apabila tidak tercapai kesepakatan, sengketa diajukan ke pengadilan di wilayah hukum Kota Palembang, kecuali peraturan memaksa menentukan lain.</p>',
            ],
            [
                'title' => 'Pasal 16 — Ketentuan lain',
                'body' => '<ul>
<li>Apabila suatu pasal tidak sah, pasal lainnya tetap berlaku.</li>
<li>Kegagalan menegakkan suatu hak tidak berarti pengesampingan hak tersebut.</li>
<li>Pihak Kedua tidak boleh mengalihkan Perjanjian tanpa persetujuan tertulis Penyedia. Penyedia boleh mengalihkan kepada afiliasi atau penerus usaha dengan pemberitahuan.</li>
<li>Perjanjian ini, Kebijakan Privasi, invoice/pesanan yang disetujui, dan lampiran tertulis merupakan kesepakatan lengkap para pihak mengenai Layanan.</li>
</ul>',
            ],
        ];
    }
}
