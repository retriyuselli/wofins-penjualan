<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contract_templates')) {
            return;
        }

        $rows = DB::table('contract_templates')
            ->select('id', 'intro_after_parties')
            ->whereNotNull('intro_after_parties')
            ->get();

        foreach ($rows as $row) {
            $updated = preg_replace(
                '/\s+di\s+(?:<b>)?\{\{prospect_venue\}\}(?:<\/b>)?/u',
                '',
                (string) $row->intro_after_parties,
            );

            if (! is_string($updated) || $updated === $row->intro_after_parties) {
                continue;
            }

            DB::table('contract_templates')
                ->where('id', $row->id)
                ->update(['intro_after_parties' => $updated]);
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan: lokasi acara tetap ada di Pasal 2 / Waktu Kegiatan.
    }
};
