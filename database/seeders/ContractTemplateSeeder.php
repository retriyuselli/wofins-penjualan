<?php

namespace Database\Seeders;

use App\Services\ContractTemplateService;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ContractTemplateService::class);
        $preserved = $service->preserveSpkAsCompanyTemplate();
        $service->syncSystemDefault();
        $service->refreshSpkScheduleText();

        $this->command?->info('✅ ContractTemplateSeeder: template default/global dikembalikan ke layout semula.');
        if ($preserved) {
            $this->command?->info('   Template SPK disimpan sebagai template perusahaan agar perbaikan kontrak sekarang tetap ada.');
        }
    }
}
