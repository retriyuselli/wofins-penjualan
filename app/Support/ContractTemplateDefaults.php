<?php

namespace App\Support;

class ContractTemplateDefaults
{
    /**
     * Klausul template kontrak lama — tidak dipakai di SPK.
     *
     * @return list<string>
     */
    public static function legacySectionKeys(): array
    {
        return [
            'detail_pelayanan',
            'sebelum_hari_h',
            'hari_h',
            'konfirmasi',
            'pembayaran',
            'vendor',
            'pembatalan',
            'force_majeure',
        ];
    }

    /**
     * @return list<string>
     */
    public static function preambleKeys(): array
    {
        return [
            'pasal_1_maksud',
            'pasal_2_waktu',
            'pasal_3_kewajiban_pertama',
            'pasal_4_kewajiban_kedua',
        ];
    }

    /**
     * Klausul ketentuan tambahan pada layout default lama.
     *
     * @return list<string>
     */
    public static function legacyTermsKeys(): array
    {
        return [
            'konfirmasi',
            'pembayaran',
            'vendor',
            'pembatalan',
            'force_majeure',
        ];
    }

    /**
     * @return array{nomor: string, keterangan: ?string}
     */
    public static function pasalParts(?string $title, ?string $keterangan = null): array
    {
        $title = trim((string) $title);
        $keterangan = trim((string) $keterangan);

        if ($keterangan !== '') {
            return [
                'nomor' => self::normalizePasalNomor($title) ?? $title,
                'keterangan' => $keterangan,
            ];
        }

        if (preg_match('/^(Pasal\s+\d+)\s*[—\-\–|:]\s*(.+)$/iu', $title, $matches)) {
            return [
                'nomor' => self::normalizePasalNomor($matches[1]) ?? $matches[1],
                'keterangan' => $matches[2],
            ];
        }

        return [
            'nomor' => self::normalizePasalNomor($title) ?? $title,
            'keterangan' => null,
        ];
    }

    public static function normalizePasalNomor(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (preg_match('/(\d+)/', $value, $matches)) {
            return 'Pasal '.$matches[1];
        }

        return trim($value);
    }

    /**
     * Template global / default sistem — posisi dan isi seperti kontrak semula.
     *
     * @return array<string, mixed>
     */
    public static function templateAttributes(): array
    {
        return [
            'name' => 'Default Kontrak Pernikahan',
            'title' => 'KONTRAK KERJASAMA PERNIKAHAN',
            'package_section_title' => 'Dream Wedding Packages',
            'package_price_label' => 'DREAM WEDDING PACKAGE',
            'facilities_heading' => 'DENGAN RINCIAN FASILITAS SEBAGAI BERIKUT :',
            'intro_pihak_pertama' => 'Bertindak untuk dan atas nama {{company_name}} beralamat di {{company_address}}, selanjutnya disebut PIHAK PERTAMA.',
            'intro_pihak_kedua' => 'Bertindak untuk dan atas nama diri sendiri, selanjutnya disebut PIHAK KEDUA.',
            'intro_after_parties' => 'Sehubungan dengan akan diadakannya Pernikahan <b>{{prospect_cpw}} & {{prospect_cpp}}</b> di <b>{{prospect_venue}}</b>, berikut adalah rincian dan ketentuan Paket Pernikahannya :',
            'closing_text' => 'Demikianlah Kontrak Kerjasama Paket Pernikahan ini dibuat dalam 2 (dua) rangkap dan ditandatangani oleh kedua belah pihak.',
            'is_system_default' => true,
            'is_active' => true,
        ];
    }

    /**
     * @return list<array{key: string, title: string, keterangan: ?string, body: string, is_enabled: bool}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'detail_pelayanan',
                'title' => 'DETAIL PELAYANAN',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>1 Event Manager dan 15 kru (Akad + Resepsi)</li>
<li>Wedding Planner (Pelayanan pengantin dimulai dari persiapan sampai dengan selesai acara (0% - 100%))</li>
<li>Wedding Checklist</li>
<li>Konsultasi acara akad dan resepsi</li>
<li>Konsultasi budget calon pengantin</li>
<li>Meeting dengan team wo dan vendor</li>
<li>Gratis pembuatan foto slide untuk acara resepsi</li>
<li>Memonitor semua pelaksanaan Akad dan Resepsi sesuai dengan rundown acara yang telah disepakati sebelumnya</li>
</ol>',
            ],
            [
                'key' => 'sebelum_hari_h',
                'title' => 'SEBELUM HARI H',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>Free Konsultasi.</li>
<li>Pertemuan secara berkala untuk pembahasan konsep acara dan waktu pelaksanaan.</li>
<li>Membuat dan memantau “wedding checklist”.</li>
<li>Follow up dan koordinasi dengan vendor terkait.</li>
<li>Mengatur dan berkoordinasi dengan mempelai dan vendor untuk waktu Technical meeting.</li>
<li>Konfirmasi ke orang tua, bestman, bridesmaid, pihak keluarga yang menjadi panitia.</li>
<li>Pertemuan dengan pihak keluarga (jika diperlukan).</li>
</ol>',
            ],
            [
                'key' => 'hari_h',
                'title' => 'HARI “H” — WEDDING DAY',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>Tim organizer terdiri dari 6 orang (akad) dan 15 orang (resepsi).</li>
<li>Standby 2 jam sebelum prosesi awal masing -masing mempelai (rumah / apartemen / hotel)</li>
<li>Koordinasi &amp; briefing dengan pihak keluarga mengenai prosesi pelepasan dan pertemuan pengantin.</li>
<li>Menyediakan time Keeper agar acara berlangsung sesuai rundown. (Prosesi pertemuan ke-2 pengantin, persiapan resepsi &amp; acara resepsi).</li>
<li>Membantu acara akad nikah dan berkoordinasi dengan pihak yang bersangkutan.</li>
<li>Pengawasan kinerja para vendor selama acara berlangsung untuk hasil yang maksimal.</li>
<li>Gladire sik sebelum acara berlangsung.</li>
</ol>',
            ],
            [
                'key' => 'konfirmasi',
                'title' => 'KONFIRMASI',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>PIHAK PERTAMA harus menerima konfirmasi dari PIHAK KEDUA tentang acara/event tersebut di atas selambat-lambatnya 3 (tiga) hari kerja dari Kontrak Kerjasama Paket Pernikahan ini dibuat.</li>
<li>Pembatalan secara mendadak setelah Kontrak Kerjasama Paket Pernikahan ini ditandatangani akan dikenakan biaya sebesar 50% dari total biaya yang tercantum di Kontrak Kerjasama Paket Pernikahan.</li>
<li>Kontrak Kerjasama Paket Pernikahan ini juga berlaku sebagai Jaminan atas Pembayaran dari PIHAK KEDUA.</li>
<li>PIHAK PERTAMA akan tetap mengikuti kebijakan pihak Gedung yang menjadi lokasi pernikahan yang dipilih oleh PIHAK KEDUA.</li>
</ol>',
            ],
            [
                'key' => 'pembayaran',
                'title' => 'PEMBAYARAN',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>Pembayaran DP (Down Payment) sebesar {{dp_amount}} sebagai Booking Date.</li>
<li>Pembayaran termin dilakukan sesuai simulasi pembayaran: {{termin_list}}</li>
<li>Pelunasan pembayaran paling lambat H-14 (Empat Belas Hari) sebelum acara dilaksanakan.</li>
<li>Pembayaran dapat dilakukan melalui transfer ke rekening:<br><br>Bank <b>{{bank_name}}</b><br>No. Rekening: <b>{{bank_account}}</b><br>A.n: <b>{{bank_holder}}</b></li>
<li>Bukti transfer dapat di email ke {{company_email}} atau datang langsung ke kantor {{company_name}} dengan menunjukkan bukti ke bagian administrasi.</li>
<li>Pembayaran secara tunai dilakukan langsung ke bagian administrasi di kantor {{company_name}} dan PIHAK KEDUA akan menerima bukti pembayaran atau pelunasan yang telah ditandatangani oleh bagian keuangan atau bisa langsung menghubungi saudari <b>{{finance_name}} di nomor {{finance_phone}}</b>.</li>
<li>Tidak dibenarkan melakukan pembayaran di luar dengan cara menitipkan kepada pihak lain selain yang ditunjuk oleh PIHAK PERTAMA.</li>
</ol>',
            ],
            [
                'key' => 'vendor',
                'title' => 'VENDOR',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>Vendor pernikahan yang telah dipilih oleh PIHAK KEDUA, wajib bertanggung jawab terhadap fasilitas yang telah diberikan sesuai dengan paket yang telah dipilih. PIHAK PERTAMA bersedia membantu sebagai mediator dalam berdiskusi dan koordinasi jika terjadi kendala dengan vendor.</li>
<li>PIHAK PERTAMA akan memberikan daftar rekomendasi vendor yang telah sesuai dengan kriteria sehingga dapat dijadikan pilihan oleh PIHAK KEDUA dalam menentukan vendor pernikahan.</li>
<li>PIHAK KEDUA dapat melakukan perubahan vendor diluar rekomendasi yang telah disampaikan dengan menyesuaikan perhitungan dari paket sebelumnya.</li>
<li>Apabila diperlukan, para vendor akan diminta untuk membuat kontrak kerjasama yang isinya mengenai pertanggungjawaban para vendor terhadap keberhasilan acara pernikahan sesuai dengan ketentuan yang telah disepakati sebelumnya antara vendor dan PIHAK KEDUA.</li>
<li>Jika vendor yang telah dipilih PIHAK KEDUA tidak mampu mengikuti kesepakatan dari PIHAK KEDUA mengenai pertanggung jawaban, maka PIHAK PERTAMA akan memberikan rekomendasi vendor lain yang mampu mengikuti kesepakatan PIHAK PERTAMA dan PIHAK KEDUA</li>
</ol>',
            ],
            [
                'key' => 'pembatalan',
                'title' => 'PEMBATALAN',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>Apabila terjadi pembatalan sepihak dari konsumen (keluarga/pengantin) PIHAK KEDUA, maka uang yang telah disetorkan dapat dikembalikan dengan syarat sebagai berikut :</li>
<li>Jika pembatalan 3 (tiga) bulan sebelum acara berlangsung maka akan dikenakan biaya 50% dari total biaya yang telah disepakati.</li>
<li>Jika pembatalan 1 (satu) bulan sebelum acara berlangsung, maka akan dikenakan biaya 100% dari total biaya yang telah disepakati.</li>
<li>Jika pembatalan dilakukan setelah ada pembayaran ke beberapa vendor, maka uang yang telah disetor ke vendor akan mengikuti kebijakan dari masing - masing vendor dalam hal pengembalian uang.</li>
<li>Uang muka sebagai tanda jadi atau down payment (DP) yang telah dibayarkan tidak dapat dikembalikan.</li>
</ol>',
            ],
            [
                'key' => 'force_majeure',
                'title' => 'FORCE MAJEURE',
                'keterangan' => null,
                'is_enabled' => true,
                'body' => '<ol>
<li>Force Majeure yang dimaksud adalah suatu keadaan memaksa diluar batas kemampuan kedua belah pihak yang dapat menggangu bahkan menggagalkan terlaksananya event, seperti bencana alam, pandemi penyakit berbahaya, peperangan, pemogokan, sabotase, pemberontakan masyarakat, blokade, kebijaksanaan pemerintah dan khususnya yang disebabkan diluar batas kemampuan manusia.</li>
<li>Terhadap pembatalan akibat dari Force Majeure, PIHAK PERTAMA dan PIHAK KEDUA sepakat untuk menanggung kerugiannya masing – masing.</li>
</ol>',
            ],
        ];
    }

    /**
     * Template SPK — perbaikan layout sesuai kontrak sekarang.
     *
     * @return array<string, mixed>
     */
    public static function spkTemplateAttributes(): array
    {
        return [
            'name' => 'Surat Perjanjian Kerja Paket Pernikahan',
            'title' => 'SURAT PERJANJIAN KERJA',
            'package_section_title' => 'PAKET PERNIKAHAN {{company_name}}',
            'package_price_label' => 'Total Biaya jasa pernikahan',
            'facilities_heading' => 'Pasal 5 — Hak Pihak Kedua / Fasilitas Jasa Pernikahan',
            'intro_pihak_pertama' => 'Dalam hal ini bertindak untuk dan atas nama {{company_name}} selaku penyedia jasa paket pernikahan, selanjutnya disebut PIHAK PERTAMA.',
            'intro_pihak_kedua' => 'Dalam hal ini bertindak selaku klien paket pernikahan, selanjutnya disebut PIHAK KEDUA.',
            'intro_after_parties' => '<p>Pihak Pertama dan Pihak Kedua menerangkan dahulu hal-hal sebagai berikut:</p><ol><li>Pihak Pertama adalah pihak yang membantu pelaksanaan acara pernikahan.</li><li>Jangka waktu pelayanan dimulai sejak diterbitkannya invoice setelah dibayarkannya DP dan ditandatanganinya surat perjanjian ini hingga selesai pelaksanaan pernikahan.</li><li>Pihak Pertama menawarkan jasa paket {{product_name}} pada Pihak Kedua senilai {{package_price}}. Pelaksanaan acara sesuai jadwal pada Pasal 2.</li><li>Pihak Kedua bersedia menerima penawaran dari Pihak Pertama dengan segala rinciannya.</li></ol><p>Untuk pelaksanaan perjanjian, Pihak Pertama dan Pihak Kedua sepakat untuk bekerjasama dalam perjanjian ini dengan syarat-syarat dan ketentuan sebagai berikut:</p>',
            'closing_text' => '',
            'is_system_default' => false,
            'is_active' => true,
        ];
    }

    /**
     * @return list<array{key: string, title: string, keterangan: string, body: string, is_enabled: bool}>
     */
    public static function spkSections(): array
    {
        return [
            [
                'key' => 'pasal_1_maksud',
                'title' => 'Pasal 1',
                'keterangan' => 'Maksud Kerjasama',
                'is_enabled' => true,
                'body' => '<p>Pihak pertama menjadi pelaksana pernikahan Pihak Kedua yang berlangsung selama 1 hari. Pihak Kedua telah menyepakati semua jasa yang diberikan oleh Pihak Pertama sesuai dana yang disepakati.</p>',
            ],
            [
                'key' => 'pasal_2_waktu',
                'title' => 'Pasal 2',
                'keterangan' => 'Waktu Kegiatan',
                'is_enabled' => true,
                'body' => '<p>Waktu pelayanan dimulai sejak diterbitkannya invoice setelah dibayarkannya DP dan ditandatanganinya surat perjanjian ini hingga selesai pelaksanaan pernikahan. Sedangkan, pelaksanaan pernikahan dilaksanakan pada:</p>{{jadwal_acara}}',
            ],
            [
                'key' => 'pasal_3_kewajiban_pertama',
                'title' => 'Pasal 3',
                'keterangan' => 'Kewajiban Pihak Pertama',
                'is_enabled' => true,
                'body' => '<ol><li>Pihak Pertama berkewajiban atas segala persiapan semua vendor dibawah naungan pihak pertama.</li><li>Pihak Pertama bersedia Meeting, Technical Meeting dan melakukan gladi resik sebelum acara.</li><li>Pihak Pertama memberikan informasi segala keperluan acara kepada Pihak Kedua.</li><li>Pihak Pertama akan menjadi pelaksana acara.</li></ol>',
            ],
            [
                'key' => 'pasal_4_kewajiban_kedua',
                'title' => 'Pasal 4',
                'keterangan' => 'Kewajiban Pihak Kedua',
                'is_enabled' => true,
                'body' => '<p>Pihak Kedua berkewajiban untuk melakukan pembayaran jasa kepada Pihak Pertama sesuai jumlah yang disepakati.</p>',
            ],
            [
                'key' => 'pasal_6_biaya',
                'title' => 'Pasal 6',
                'keterangan' => 'Hak Pihak Pertama / Biaya',
                'is_enabled' => true,
                'body' => '<p>Total Biaya jasa pernikahan yang digunakan untuk acara {{event_name}} sesuai jadwal pada Pasal 2 adalah senilai {{package_price}}.</p>',
            ],
            [
                'key' => 'pasal_7_pembayaran',
                'title' => 'Pasal 7',
                'keterangan' => 'Metode Pembayaran',
                'is_enabled' => true,
                'body' => '<p>Metode pembayaran biaya jasa dan keperluan acara sebagaimana yang dimaksud, sebagai berikut:</p><ol><li>Pertama, sebagai uang muka sebanyak {{dp_amount}}.</li><li>Pembayaran selanjutnya: {{termin_list}}</li><li>Semua pembayaran hanya melalui rekening perusahaan: {{bank_name}} a.n {{bank_holder}} {{bank_account}} atau Cash di Kantor {{company_name}} kepada Admin officer yang ditandai dengan diberikannya kwitansi atau invoice digital.</li></ol>',
            ],
            [
                'key' => 'pasal_8_pembatalan',
                'title' => 'Pasal 8',
                'keterangan' => 'Pembatalan',
                'is_enabled' => true,
                'body' => '<ol><li>Jika terjadi musibah (kecelakaan/kematian terhadap orang tua mempelai) dalam jangka waktu 1 minggu sebelum acara dilaksanakan, maka uang muka akan dikembalikan sebesar (10%) atau senilai maksimal pembayaran yang terakhir dibayarkan jika sudah pelunasan apabila belum disetorkan ke vendor.</li><li>Pembayaran tidak akan dikembalikan jika terjadi bencana alam (gempa dan kebakaran), 3 hari sebelum acara dilaksanakan.</li><li>Jika terjadi pembatalan secara sepihak maka DP yang telah di bayarkan dianggap hangus.</li><li>Jika terjadi perubahan harga bahan baku dengan skala 0% – 50% menjadi tanggungan {{company_name}}. Apabila kenaikan bahan baku melebihi 50% akan dilakukan kesepakatan dengan pihak kedua.</li><li>Apabila terjadi perubahan tanggal karena situasi pandemi, maka akan disepakati secara bersama mengenai perubahan tanggal dan biaya secara rinci.</li><li>Apabila terjadi pemindahan tanggal ke tanggal lain dengan acara 1 hari sesuai perjanjian awal (karena pandemi dan di luar kuasa bersama) serta semua vendor bisa maka tidak ada tambahan. Namun, jika vendor terkait tidak bisa maka akan digantikan dengan vendor lain yang dipilihkan WO. Jika ingin dengan rate yang 100% sama, maka dikenakan biaya tambahan karena DP ke vendor sebelumnya otomatis hangus.</li><li>Jika ada hal-hal terkait perjanjian yang belum dituliskan maka akan didiskusikan secara bersama dan tidak memberatkan kedua pihak.</li><li>Pembatalan sepihak di rentang waktu 6 bulan dan dibawahnya dikenakan penalti 50%.</li></ol>',
            ],
            [
                'key' => 'pasal_9_wanprestasi',
                'title' => 'Pasal 9',
                'keterangan' => 'Wanprestasi',
                'is_enabled' => true,
                'body' => '<ol><li>Apabila dalam pembayaran sebagaimana tersebut pada Pasal 6 Pihak Kedua tidak dapat memenuhi kewajiban (wanprestasi) untuk melakukan pembayaran sesuai dengan jadwal yang telah ditentukan dan disertai tidak ada kepastian pembayaran setelah 3 hari dikonfirmasi sejak jatuh tempo, maka Pihak Kedua dianggap telah melakukan pembatalan secara sepihak dan Pihak Pertama berhak memberhentikan segala pemberian service/fasilitas.</li><li>Apabila Pihak Pertama di dalam pelaksanaan perjanjian ini tidak memenuhi kewajibannya sebagaimana yang tersebut pada Pasal 4 kepada Pihak Kedua, maka Pihak Pertama dianggap telah melakukan pembatalan secara sepihak dan wajib memberikan ganti rugi kepada Pihak Kedua (apabila muncul kerugian).</li><li>Pihak Kedua bersedia membuat surat pernyataan menerima resiko apabila jumlah tamu 175% dari kapasitas gedung (acara tidak maksimal).</li></ol>',
            ],
            [
                'key' => 'pasal_10_penutup',
                'title' => 'Pasal 10',
                'keterangan' => 'Penutup',
                'is_enabled' => true,
                'body' => '<p>Demikian surat perjanjian {{company_name}} ini dibuat dan ditandatangani oleh kedua pihak.</p>',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function placeholderHelp(): array
    {
        return [
            '{{company_name}}',
            '{{company_address}}',
            '{{company_email}}',
            '{{company_phone}}',
            '{{company_city}}',
            '{{owner_name}}',
            '{{owner_position}}',
            '{{nomor_surat}}',
            '{{contract_date}}',
            '{{prospect_cpw}}',
            '{{prospect_cpp}}',
            '{{prospect_venue}}',
            '{{event_name}}',
            '{{product_name}}',
            '{{akad_date}}',
            '{{akad_time}}',
            '{{lamaran_date}}',
            '{{lamaran_time}}',
            '{{resepsi_date}}',
            '{{resepsi_time}}',
            '{{jadwal_acara}}',
            '{{package_price}}',
            '{{dp_amount}}',
            '{{termin_list}}',
            '{{bank_name}}',
            '{{bank_account}}',
            '{{bank_holder}}',
            '{{finance_name}}',
            '{{finance_phone}}',
        ];
    }
}
