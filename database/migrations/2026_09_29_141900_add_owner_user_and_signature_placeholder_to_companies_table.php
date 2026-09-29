<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('owner_user_id')
                ->nullable()
                ->after('owner_name')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('signature_placeholder_url')->nullable()->after('legal_document_status');
            $table->string('signature_placeholder_type')->nullable()->after('signature_placeholder_url');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropColumn([
                'signature_placeholder_url',
                'signature_placeholder_type',
            ]);
        });
    }
};
