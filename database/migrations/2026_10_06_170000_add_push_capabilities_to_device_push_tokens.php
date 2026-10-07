<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('device_push_tokens')) {
            return;
        }

        Schema::table('device_push_tokens', function (Blueprint $table) {
            if (! Schema::hasColumn('device_push_tokens', 'purpose')) {
                $table->string('purpose', 16)->default('alert')->after('environment');
            }
            if (! Schema::hasColumn('device_push_tokens', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('purpose');
                $table->index(['user_id', 'purpose']);
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE device_push_tokens MODIFY token VARCHAR(200) NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('device_push_tokens')) {
            return;
        }

        Schema::table('device_push_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('device_push_tokens', 'order_id')) {
                $table->dropIndex(['user_id', 'purpose']);
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('device_push_tokens', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }
};
