<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'show_on_web')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('show_on_web')->default(false)->after('is_approved');
                $table->index('show_on_web', 'idx_products_show_on_web');
            });
        }

        DB::table('products')->where('is_approved', true)->update(['show_on_web' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'show_on_web')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_show_on_web');
                $table->dropColumn('show_on_web');
            });
        }
    }
};
