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
        $default = $service->syncSystemDefault();
        $spk = $service->ensureSpkTemplate();
        $service->refreshSpkScheduleText();

        $this->command?->info('✅ ContractTemplateSeeder: 2 template siap.');
        $this->command?->info('   1. '.$default->name.' (default sistem)');
        $this->command?->info('   2. '.$spk->name.($preserved ? ' (disalin dari default SPK lama)' : ''));
    }
}
