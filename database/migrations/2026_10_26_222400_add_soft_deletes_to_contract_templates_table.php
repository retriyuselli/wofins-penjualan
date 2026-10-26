<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contract_templates')) {
            return;
        }

        Schema::table('contract_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('contract_templates', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contract_templates')) {
            return;
        }

        Schema::table('contract_templates', function (Blueprint $table) {
            if (Schema::hasColumn('contract_templates', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
