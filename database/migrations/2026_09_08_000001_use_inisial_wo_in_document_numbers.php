<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_categories') && Schema::hasColumn('document_categories', 'format_number')) {
            foreach (DB::table('document_categories')->orderBy('id')->get() as $row) {
                $format = (string) ($row->format_number ?? '');
                $updated = str_replace(['MKI-OUT', '/MKI/'], ['{WO}-OUT', '/{WO}/'], $format);

                if ($updated !== $format) {
                    DB::table('document_categories')->where('id', $row->id)->update([
                        'format_number' => $updated,
                    ]);
                }
            }
        }

        if (! Schema::hasTable('documents') || ! Schema::hasColumn('documents', 'document_number')) {
            return;
        }

        $wo = '';
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'inisial_wo')) {
            $wo = strtoupper(trim((string) (DB::table('companies')->value('inisial_wo') ?: '')));
        }
        if ($wo === '' || $wo === 'MKI') {
            return;
        }

        foreach (DB::table('documents')->orderBy('id')->get() as $row) {
            $number = (string) ($row->document_number ?? '');
            $updated = str_replace(['MKI-OUT', '/MKI/'], [$wo.'-OUT', '/'.$wo.'/'], $number);

            if ($updated !== $number) {
                DB::table('documents')->where('id', $row->id)->update([
                    'document_number' => $updated,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_categories') && Schema::hasColumn('document_categories', 'format_number')) {
            foreach (DB::table('document_categories')->orderBy('id')->get() as $row) {
                $format = (string) ($row->format_number ?? '');
                $updated = str_replace(['{WO}-OUT', '/{WO}/'], ['MKI-OUT', '/MKI/'], $format);

                if ($updated !== $format) {
                    DB::table('document_categories')->where('id', $row->id)->update([
                        'format_number' => $updated,
                    ]);
                }
            }
        }
    }
};
