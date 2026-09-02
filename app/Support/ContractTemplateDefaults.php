<?php

namespace App\Support;

class ContractTemplateDefaults
{
    /**
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
     * @return list<array{key: string, title: string, body: string, is_enabled: bool}>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'detail_pelayanan',
                'title' => 'DETAIL PELAYANAN',
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
                'is_enabled' => true,
                'body' => '<ol class="konfirmasi-list">
<li>PIHAK PERTAMA harus menerima konfirmasi dari PIHAK KEDUA tentang acara/event tersebut di atas selambat-lambatnya 3 (tiga) hari kerja dari Kontrak Kerjasama Paket Pernikahan ini dibuat.</li>
<li>Pembatalan secara mendadak setelah Kontrak Kerjasama Paket Pernikahan ini ditandatangani akan dikenakan biaya sebesar 50% dari total biaya yang tercantum di Kontrak Kerjasama Paket Pernikahan.</li>
<li>Kontrak Kerjasama Paket Pernikahan ini juga berlaku sebagai Jaminan atas Pembayaran dari PIHAK KEDUA.</li>
<li>PIHAK PERTAMA akan tetap mengikuti kebijakan pihak Gedung yang menjadi lokasi pernikahan yang dipilih oleh PIHAK KEDUA.</li>
</ol>',
            ],
            [
                'key' => 'pembayaran',
                'title' => 'PEMBAYARAN',
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
                'is_enabled' => true,
                'body' => '<ol>
<li>Force Majeure yang dimaksud adalah suatu keadaan memaksa diluar batas kemampuan kedua belah pihak yang dapat menggangu bahkan menggagalkan terlaksananya event, seperti bencana alam, pandemi penyakit berbahaya, peperangan, pemogokan, sabotase, pemberontakan masyarakat, blokade, kebijaksanaan pemerintah dan khususnya yang disebabkan diluar batas kemampuan manusia.</li>
<li>Terhadap pembatalan akibat dari Force Majeure, PIHAK PERTAMA dan PIHAK KEDUA sepakat untuk menanggung kerugiannya masing – masing.</li>
</ol>',
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
            '{{prospect_cpw}}',
            '{{prospect_cpp}}',
            '{{prospect_venue}}',
            '{{event_name}}',
            '{{product_name}}',
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
