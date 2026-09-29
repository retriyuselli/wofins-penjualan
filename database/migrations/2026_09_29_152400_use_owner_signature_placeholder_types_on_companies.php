<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->json('signature_placeholder_types')->nullable()->after('legal_document_status');
        });

        if (Schema::hasColumn('companies', 'signature_placeholder_type')) {
            $rows = DB::table('companies')->select('id', 'signature_placeholder_type')->get();

            foreach ($rows as $row) {
                $types = match ($row->signature_placeholder_type) {
                    'produk' => ['produk'],
                    'kontrak' => ['draft_kontrak'],
                    default => [],
                };

                DB::table('companies')->where('id', $row->id)->update([
                    'signature_placeholder_types' => $types === [] ? null : json_encode($types),
                ]);
            }
        }

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'signature_placeholder_user_id')) {
                $table->dropConstrainedForeignId('signature_placeholder_user_id');
            }
            if (Schema::hasColumn('companies', 'signature_placeholder_type')) {
                $table->dropColumn('signature_placeholder_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('signature_placeholder_user_id')
                ->nullable()
                ->after('legal_document_status')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('signature_placeholder_type')->nullable()->after('signature_placeholder_user_id');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('signature_placeholder_types');
        });
    }
};
