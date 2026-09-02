<?php

namespace Database\Seeders;

use App\Models\ContractTemplate;
use App\Services\ContractTemplateService;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (ContractTemplate::query()->where('is_system_default', true)->exists()) {
            $this->command?->info('Contract template default sudah ada, dilewati.');

            return;
        }

        app(ContractTemplateService::class)->ensureSystemDefault();
        $this->command?->info('✅ ContractTemplateSeeder: template default kontrak pernikahan dibuat.');
    }
}
