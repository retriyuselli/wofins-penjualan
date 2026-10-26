<?php

use App\Support\ContractTemplateDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contract_template_sections')) {
            return;
        }

        $rows = DB::table('contract_template_sections')
            ->select('id', 'body')
            ->whereNotNull('body')
            ->get();

        foreach ($rows as $row) {
            $updated = ContractTemplateDefaults::tidySectionBody((string) $row->body);
            if ($updated === $row->body) {
                continue;
            }

            DB::table('contract_template_sections')
                ->where('id', $row->id)
                ->update(['body' => $updated]);
        }
    }

    public function down(): void
    {
        // Rujukan pasal yang sudah dirapikan tidak dikembalikan.
    }
};
