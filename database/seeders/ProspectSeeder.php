<?php

namespace Database\Seeders;

use App\Models\Prospect;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProspectSeeder extends Seeder
{
    public function run(): void
    {
        $accountManagers = User::role('Account Manager')->orderBy('id')->get();
        if ($accountManagers->isEmpty()) {
            $accountManagers = User::query()->orderBy('id')->get();
        }

        if ($accountManagers->isEmpty()) {
            $this->command->error('User belum ada. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $created = 0;

        foreach ($this->prospects() as $index => $data) {
            $am = $accountManagers->firstWhere('email', $data['am_email'])
                ?? $accountManagers[$index % $accountManagers->count()];

            unset($data['am_email']);
            $data['user_id'] = $am->id;
            $data['phone'] = preg_replace('/^(\+62|0)/', '', (string) $data['phone']);
            $data = $this->fillTimesForDates($data);

            Prospect::updateOrCreate(
                [
                    'name_event' => $data['name_event'],
                    'name_cpp' => $data['name_cpp'],
                    'name_cpw' => $data['name_cpw'],
                ],
                $data
            );

            $created++;
        }

        $this->command->info("✅ ProspectSeeder: {$created} prospek dibuat/diperbarui sesuai form.");
    }

    /**
     * 10 prospek — field mengikuti ProspectForm (termasuk jam & telepon tanpa 0).
     * Lima nama pertama dipakai SimulasiProdukSeeder.
     *
     * @return list<array<string, mixed>>
     */
    private function prospects(): array
    {
        $now = Carbon::now()->startOfDay();

        return [
            [
                'name_event' => 'Wedding Andi & Sari',
                'name_cpp' => 'Andi Pratama',
                'name_cpw' => 'Sari Dewi',
                'date_lamaran' => $now->copy()->addMonths(2)->toDateString(),
                'time_lamaran' => '19:00',
                'date_akad' => $now->copy()->addMonths(4)->toDateString(),
                'time_akad' => '08:00',
                'date_resepsi' => $now->copy()->addMonths(4)->toDateString(),
                'time_resepsi' => '11:00',
                'venue' => 'Grand Ballroom Hotel Indonesia Kempinski, Jakarta',
                'phone' => '81234567801',
                'address' => 'Jl. Sudirman No. 123, Menteng, Jakarta Pusat 10310',
                'total_penawaran' => 185000000,
                'notes' => 'Paket Ballroom Grand 500 pax, palet gold & putih, VIP 50 tamu. Follow-up simulasi produk.',
                'am_email' => 'rama.dhona@wofins.com',
            ],
            [
                'name_event' => 'Wedding Budi & Maya',
                'name_cpp' => 'Budi Santoso',
                'name_cpw' => 'Maya Putri',
                'date_lamaran' => $now->copy()->addMonths(1)->toDateString(),
                'time_lamaran' => '18:30',
                'date_akad' => $now->copy()->addMonths(3)->toDateString(),
                'time_akad' => '09:00',
                'date_resepsi' => $now->copy()->addMonths(3)->toDateString(),
                'time_resepsi' => '16:00',
                'venue' => 'Garden Puncak, Cisarua, Bogor',
                'phone' => '81234567802',
                'address' => 'Jl. Raya Puncak KM 79, Cisarua, Bogor 16750',
                'total_penawaran' => 95000000,
                'notes' => 'Garden outdoor 200 pax, cadangan tenda jika hujan, dekorasi natural.',
                'am_email' => 'rina.mardiana@wofins.com',
            ],
            [
                'name_event' => 'Wedding Dedi & Rina',
                'name_cpp' => 'Dedi Kurniawan',
                'name_cpw' => 'Rina Sari',
                'date_lamaran' => $now->copy()->addWeeks(3)->toDateString(),
                'time_lamaran' => '19:00',
                'date_akad' => $now->copy()->addMonths(2)->toDateString(),
                'time_akad' => '10:00',
                'date_resepsi' => $now->copy()->addMonths(2)->toDateString(),
                'time_resepsi' => '18:00',
                'venue' => 'Chapel The Sanctoo, Ubud',
                'phone' => '81234567803',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan 12950',
                'total_penawaran' => 72000000,
                'notes' => 'Intimate chapel 150 pax, tamu terbatas keluarga dan sahabat.',
                'am_email' => 'adel@wofins.com',
            ],
            [
                'name_event' => 'Wedding Eko & Fitri',
                'name_cpp' => 'Eko Prasetyo',
                'name_cpw' => 'Fitri Handayani',
                'date_lamaran' => $now->copy()->addMonths(1)->addWeeks(2)->toDateString(),
                'time_lamaran' => '19:30',
                'date_akad' => $now->copy()->addMonths(5)->toDateString(),
                'time_akad' => '08:30',
                'date_resepsi' => $now->copy()->addMonths(5)->toDateString(),
                'time_resepsi' => '12:00',
                'venue' => 'Ballroom Thamrin, Jakarta Pusat',
                'phone' => '81234567804',
                'address' => 'Jl. Thamrin No. 12, Jakarta Pusat 10310',
                'total_penawaran' => 145000000,
                'notes' => 'Ballroom Thamrin 300 pax, DP 20%, pelunasan H-1 bulan.',
                'am_email' => 'sari.ananda@wofins.com',
            ],
            [
                'name_event' => 'Wedding Fajar & Indira',
                'name_cpp' => 'Fajar Nugroho',
                'name_cpw' => 'Indira Salsabila',
                'date_lamaran' => $now->copy()->addWeeks(6)->toDateString(),
                'time_lamaran' => '20:00',
                'date_akad' => $now->copy()->addMonths(6)->toDateString(),
                'time_akad' => '09:00',
                'date_resepsi' => $now->copy()->addMonths(6)->addDay()->toDateString(),
                'time_resepsi' => '19:00',
                'venue' => 'The Ritz-Carlton Pacific Place, Jakarta',
                'phone' => '81234567805',
                'address' => 'Jl. HR Rasuna Said Kav. C-22, Jakarta 12940',
                'total_penawaran' => 220000000,
                'notes' => 'Varian Gold Ballroom Grand 500, florist, live streaming, coordinator hari H.',
                'am_email' => 'devi.kartika@wofins.com',
            ],
            [
                'name_event' => 'Wedding Galih & Hana',
                'name_cpp' => 'Galih Ramadhan',
                'name_cpw' => 'Hana Pertiwi',
                'date_lamaran' => $now->copy()->addMonths(1)->addWeek()->toDateString(),
                'time_lamaran' => '18:00',
                'date_akad' => $now->copy()->addMonths(4)->addWeeks(2)->toDateString(),
                'time_akad' => '07:30',
                'date_resepsi' => $now->copy()->addMonths(4)->addWeeks(2)->toDateString(),
                'time_resepsi' => '11:30',
                'venue' => 'Grand Sahid Jaya, Jakarta',
                'phone' => '81234567806',
                'address' => 'Jl. Sudirman Kav. 86, Jakarta Pusat 10220',
                'total_penawaran' => 90000000,
                'notes' => 'Tema Sunda, live degung, catering 350 pax.',
                'am_email' => 'rama.dhona@wofins.com',
            ],
            [
                'name_event' => 'Wedding Ivan & Julia',
                'name_cpp' => 'Ivan Kurniawan',
                'name_cpw' => 'Julia Maharani',
                'date_lamaran' => $now->copy()->addWeeks(4)->toDateString(),
                'time_lamaran' => '19:00',
                'date_akad' => $now->copy()->addMonths(3)->addWeeks(2)->toDateString(),
                'time_akad' => '16:00',
                'date_resepsi' => $now->copy()->addMonths(3)->addWeeks(2)->addDay()->toDateString(),
                'time_resepsi' => '18:00',
                'venue' => 'Mulia Resort Nusa Dua, Bali',
                'phone' => '81234567807',
                'address' => 'Jl. Melawai Raya No. 18, Jakarta Selatan 12160',
                'total_penawaran' => 150000000,
                'notes' => 'Destination wedding Bali, 3 hari 2 malam, 200 pax.',
                'am_email' => 'rina.mardiana@wofins.com',
            ],
            [
                'name_event' => 'Wedding Khalil & Lina',
                'name_cpp' => 'Khalil Ahmad',
                'name_cpw' => 'Lina Safitri',
                'date_lamaran' => $now->copy()->addMonths(2)->addWeek()->toDateString(),
                'time_lamaran' => '19:00',
                'date_akad' => $now->copy()->addMonths(5)->addWeek()->toDateString(),
                'time_akad' => '08:00',
                'date_resepsi' => $now->copy()->addMonths(5)->addWeek()->toDateString(),
                'time_resepsi' => '12:00',
                'venue' => 'JCC Senayan, Jakarta',
                'phone' => '81234567808',
                'address' => 'Jl. Tebet Raya No. 27, Jakarta Selatan 12810',
                'total_penawaran' => 65000000,
                'notes' => 'Akad di masjid, resepsi JCC, catering halal 400 pax.',
                'am_email' => 'adel@wofins.com',
            ],
            [
                'name_event' => 'Wedding Made & Ni Kadek',
                'name_cpp' => 'Made Wirawan',
                'name_cpw' => 'Ni Kadek Sari',
                'date_lamaran' => $now->copy()->addWeeks(2)->toDateString(),
                'time_lamaran' => '17:00',
                'date_akad' => $now->copy()->addMonths(3)->toDateString(),
                'time_akad' => '09:00',
                'date_resepsi' => $now->copy()->addMonths(3)->toDateString(),
                'time_resepsi' => '18:00',
                'venue' => 'Bali Beach Resort, Tabanan',
                'phone' => '81234567809',
                'address' => 'Jl. Denpasar–Gilimanuk, Tabanan, Bali 82111',
                'total_penawaran' => 110000000,
                'notes' => 'Adat Bali, venue pantai, catering tradisional 300 pax.',
                'am_email' => 'sari.ananda@wofins.com',
            ],
            [
                'name_event' => 'Wedding Nurdin & Olivia',
                'name_cpp' => 'Nurdin Hakim',
                'name_cpw' => 'Olivia Tan',
                'date_lamaran' => $now->copy()->addMonths(2)->addWeeks(2)->toDateString(),
                'time_lamaran' => '19:00',
                'date_akad' => $now->copy()->addMonths(6)->toDateString(),
                'time_akad' => '10:00',
                'date_resepsi' => $now->copy()->addMonths(6)->toDateString(),
                'time_resepsi' => '19:00',
                'venue' => 'Shangri-La Hotel, Palembang',
                'phone' => '81234567810',
                'address' => 'Jl. Jenderal Sudirman No. 88, Palembang 30129',
                'total_penawaran' => 88000000,
                'notes' => 'Resepsi malam, live band, 250 pax. Tidak ada catatan khusus lain.',
                'am_email' => 'devi.kartika@wofins.com',
            ],
        ];
    }

    /**
     * Jika tanggal diisi, jam wajib ikut. Format H:i:s agar kolom TIME + TimePicker terisi.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function fillTimesForDates(array $data): array
    {
        $defaults = [
            'date_lamaran' => '19:00:00',
            'date_akad' => '08:00:00',
            'date_resepsi' => '11:00:00',
        ];

        foreach ($defaults as $dateKey => $defaultTime) {
            $timeKey = str_replace('date_', 'time_', $dateKey);

            if (filled($data[$dateKey] ?? null)) {
                $time = $data[$timeKey] ?? $defaultTime;
                $data[$timeKey] = strlen((string) $time) === 5 ? "{$time}:00" : $time;
            } else {
                $data[$timeKey] = null;
            }
        }

        return $data;
    }
}
