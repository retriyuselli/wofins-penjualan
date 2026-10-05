<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('companies', 'show_products_on_web')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->boolean('show_products_on_web')->default(true)->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'show_products_on_web')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('show_products_on_web');
            });
        }
    }
};
