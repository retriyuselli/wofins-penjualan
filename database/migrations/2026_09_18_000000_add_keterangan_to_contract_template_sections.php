<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_template_sections', function (Blueprint $table) {
            $table->string('keterangan')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('contract_template_sections', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
