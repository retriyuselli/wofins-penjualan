<?php

namespace Database\Seeders;

use App\Models\DataPribadi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataPribadiSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;

        foreach ($this->members() as $data) {
            $email = $data['email'];
            $data['foto'] = $this->seedFoto($email);
            $data['nomor_telepon'] = preg_replace('/^(\+62|0)/', '', (string) $data['nomor_telepon']);

            DataPribadi::updateOrCreate(
                ['email' => $email],
                $data
            );

            $created++;
        }

        $this->command->info("✅ DataPribadiSeeder: {$created} data pribadi dibuat/diperbarui sesuai form.");
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function members(): array
    {
        return [
            [
                'nama_lengkap' => 'Sarah Wijaya Sari',
                'email' => 'sarah.wijaya@maknaonline.com',
                'nomor_telepon' => '81234567890',
                'tanggal_lahir' => '1988-03-15',
                'tanggal_mulai_gabung' => '2020-01-01',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Sudirman No. 123, Menteng, Jakarta Pusat 10310',
                'pekerjaan' => 'CEO & Founder',
                'gaji' => 25000000,
                'motivasi_kerja' => 'Ingin menciptakan momen pernikahan yang tak terlupakan bagi setiap pasangan dan membangun standar wedding organizer bertaraf internasional.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Wedding Planning Certification - IAWP (2019)</li><li>Luxury Event Management - Singapore Hotel Association (2020)</li><li>Digital Marketing for Wedding Business - Google Digital Garage (2021)</li><li>Leadership &amp; Team Management - Dale Carnegie Indonesia (2022)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Michael Chen Wijaya',
                'email' => 'michael.chen@maknaonline.com',
                'nomor_telepon' => '81234567891',
                'tanggal_lahir' => '1990-07-22',
                'tanggal_mulai_gabung' => '2020-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Gatot Subroto No. 456, Kuningan, Jakarta Selatan 12950',
                'pekerjaan' => 'COO & Co-Founder',
                'gaji' => 20000000,
                'motivasi_kerja' => 'Mengoptimalkan operasional perusahaan agar layanan wedding tetap efisien, terukur, dan mudah diikuti tim di lapangan.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Operations Management Certification - PMI Indonesia (2020)</li><li>Financial Management for SME - LPPI (2021)</li><li>Project Management Professional (PMP) - PMI (2021)</li><li>Business Process Optimization - McKinsey Academy (2022)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Rani Sari Dewi Putri',
                'email' => 'rani.sari@maknaonline.com',
                'nomor_telepon' => '81234567892',
                'tanggal_lahir' => '1992-11-08',
                'tanggal_mulai_gabung' => '2021-03-15',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Kemang Raya No. 789, Kemang, Jakarta Selatan 12560',
                'pekerjaan' => 'Senior Account Manager',
                'gaji' => 12000000,
                'motivasi_kerja' => 'Membangun hubungan yang kuat dengan klien dan memastikan setiap detail pernikahan terlaksana sesuai janji di simulasi.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Customer Relationship Management - Salesforce Trailhead (2021)</li><li>Advanced Wedding Consultation - WeddingWire Academy (2021)</li><li>Luxury Service Excellence - Ritz Carlton Leadership Center (2022)</li><li>Conflict Resolution &amp; Negotiation - Harvard Business School Online (2023)</li></ul>',
            ],
            [
                'nama_lengkap' => 'David Pranata Kusuma',
                'email' => 'david.pranata@maknaonline.com',
                'nomor_telepon' => '81234567893',
                'tanggal_lahir' => '1993-05-12',
                'tanggal_mulai_gabung' => '2021-08-01',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Senopati No. 321, Kebayoran Baru, Jakarta Selatan 12190',
                'pekerjaan' => 'Account Manager',
                'gaji' => 10000000,
                'motivasi_kerja' => 'Terus belajar menangani berbagai tipe klien agar menjadi wedding consultant yang bisa diandalkan dari survey hingga hari H.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Wedding Planning Fundamentals - Wedding Planning Institute (2021)</li><li>Sales Techniques for Service Industry - Dale Carnegie (2022)</li><li>Event Budgeting &amp; Cost Management (2022)</li><li>Digital Portfolio Management - Adobe Creative Suite (2023)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Luna Kartika Sari',
                'email' => 'luna.kartika@maknaonline.com',
                'nomor_telepon' => '81234567894',
                'tanggal_lahir' => '1989-09-30',
                'tanggal_mulai_gabung' => '2020-06-01',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Pejaten Raya No. 654, Pasar Minggu, Jakarta Selatan 12520',
                'pekerjaan' => 'Senior Event Manager',
                'gaji' => 13000000,
                'motivasi_kerja' => 'Memastikan eksekusi event sesuai rundown, termasuk koordinasi vendor, crew, dan cadangan cuaca.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Event Coordination Mastery - ILEA (2020)</li><li>Vendor Management Excellence (2021)</li><li>Crisis Management in Events (2021)</li><li>Advanced Timeline Management - Wedding MBA (2022)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Agus Hermawan Saputra',
                'email' => 'agus.hermawan@maknaonline.com',
                'nomor_telepon' => '81234567895',
                'tanggal_lahir' => '1991-12-18',
                'tanggal_mulai_gabung' => '2021-01-10',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Fatmawati No. 987, Cilandak, Jakarta Selatan 12430',
                'pekerjaan' => 'Event Manager',
                'gaji' => 11000000,
                'motivasi_kerja' => 'Fokus pada outdoor dan destination wedding, termasuk logistik venue, tenda, dan lighting lapangan.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Outdoor Event Management (2021)</li><li>Destination Wedding Planning (2021)</li><li>Weather Contingency Planning (2022)</li><li>Logistics Coordination (2022)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Sinta Maharani Putri',
                'email' => 'sinta.maharani@maknaonline.com',
                'nomor_telepon' => '81234567896',
                'tanggal_lahir' => '1994-04-25',
                'tanggal_mulai_gabung' => '2021-11-01',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Radio Dalam No. 147, Kebayoran Baru, Jakarta Selatan 12140',
                'pekerjaan' => 'Finance Manager',
                'gaji' => 9500000,
                'motivasi_kerja' => 'Mengelola fee, budget event, dan laporan keuangan agar setiap proyek tetap transparan dan profitable.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Financial Management for Creative Industry (2021)</li><li>Event Budgeting &amp; Financial Planning (2022)</li><li>Tax Planning for SME (2022)</li><li>Digital Accounting Systems - Accurate (2023)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Eko Prasetyo Nugroho',
                'email' => 'eko.prasetyo@maknaonline.com',
                'nomor_telepon' => '81234567897',
                'tanggal_lahir' => '1995-08-14',
                'tanggal_mulai_gabung' => '2022-02-14',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. Cilandak KKO No. 258, Cilandak, Jakarta Selatan 12560',
                'pekerjaan' => 'Senior Crew & Setup Coordinator',
                'gaji' => 7500000,
                'motivasi_kerja' => 'Memastikan dekorasi, sound, dan teknis terpasang sesuai konsep, dengan setup yang aman dan rapi.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Event Setup &amp; Production (2022)</li><li>Floral Design &amp; Decoration (2022)</li><li>Audio Visual Technical Training (2023)</li><li>Safety &amp; Security in Events (2023)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Maya Indira Sari',
                'email' => 'maya.indira@maknaonline.com',
                'nomor_telepon' => '81234567898',
                'tanggal_lahir' => '1996-01-20',
                'tanggal_mulai_gabung' => '2022-06-01',
                'jenis_kelamin' => 'Perempuan',
                'alamat' => 'Jl. Ampera Raya No. 369, Kemang, Jakarta Selatan 12550',
                'pekerjaan' => 'Crew & Catering Coordinator',
                'gaji' => 6500000,
                'motivasi_kerja' => 'Menjaga kualitas catering, alur service, dan koordinasi vendor makanan agar tamu terlayani dengan baik.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Food &amp; Beverage Service Excellence (2022)</li><li>Catering Coordination &amp; Quality Control (2022)</li><li>Halal Food Certification Management (2023)</li><li>Customer Service in F&amp;B (2023)</li></ul>',
            ],
            [
                'nama_lengkap' => 'Rizki Aditya Pratama',
                'email' => 'rizki.aditya@maknaonline.com',
                'nomor_telepon' => '81234567899',
                'tanggal_lahir' => '1997-10-05',
                'tanggal_mulai_gabung' => '2023-03-01',
                'jenis_kelamin' => 'Laki-laki',
                'alamat' => 'Jl. TB Simatupang No. 123, Cilandak, Jakarta Selatan 12430',
                'pekerjaan' => 'Junior Crew & Digital Content Creator',
                'gaji' => 5500000,
                'motivasi_kerja' => 'Belajar operasional wedding sambil mendokumentasikan momen tim dan klien untuk kebutuhan konten.',
                'pelatihan' => '<p><strong>Pelatihan yang telah diikuti:</strong></p><ul><li>Basic Wedding Coordination (2023)</li><li>Social Media Content Creation (2023)</li><li>Photography &amp; Videography Basics (2023)</li><li>Event Documentation (2023)</li></ul>',
            ],
        ];
    }

    private function seedFoto(string $email): ?string
    {
        $sources = [
            public_path('images/logomki.png'),
            public_path('images/logo.png'),
            public_path('logo.png'),
        ];

        $source = collect($sources)->first(fn (string $path) => File::exists($path));
        if (! $source) {
            return null;
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION) ?: 'png');
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            $extension = 'png';
        }

        $path = 'data-pribadi-fotos/'.Str::slug(Str::before($email, '@')).'.'.$extension;
        Storage::disk('public')->put($path, File::get($source));

        return $path;
    }
}
