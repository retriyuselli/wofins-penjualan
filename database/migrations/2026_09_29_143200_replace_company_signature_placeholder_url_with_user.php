<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('signature_placeholder_url');
            $table->foreignId('signature_placeholder_user_id')
                ->nullable()
                ->after('legal_document_status')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signature_placeholder_user_id');
            $table->string('signature_placeholder_url')->nullable()->after('legal_document_status');
        });
    }
};
