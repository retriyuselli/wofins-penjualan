<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prospects')) {
            return;
        }

        Schema::table('prospects', function (Blueprint $table) {
            if (! Schema::hasColumn('prospects', 'phone_cpp')) {
                $table->string('phone_cpp')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('prospects', 'phone_cpw')) {
                $table->string('phone_cpw')->nullable()->after('phone_cpp');
            }
            if (! Schema::hasColumn('prospects', 'address_cpp')) {
                $table->string('address_cpp')->nullable()->after('address');
            }
            if (! Schema::hasColumn('prospects', 'address_cpw')) {
                $table->string('address_cpw')->nullable()->after('address_cpp');
            }
        });

        DB::table('prospects')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $row) {
                $updates = [];
                if (blank($row->phone_cpp ?? null) && filled($row->phone ?? null)) {
                    $updates['phone_cpp'] = $row->phone;
                }
                if (blank($row->phone_cpw ?? null) && filled($row->phone ?? null)) {
                    $updates['phone_cpw'] = $row->phone;
                }
                if (blank($row->address_cpp ?? null) && filled($row->address ?? null)) {
                    $updates['address_cpp'] = $row->address;
                }
                if (blank($row->address_cpw ?? null) && filled($row->address ?? null)) {
                    $updates['address_cpw'] = $row->address;
                }
                if ($updates !== []) {
                    DB::table('prospects')->where('id', $row->id)->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('prospects')) {
            return;
        }

        Schema::table('prospects', function (Blueprint $table) {
            foreach (['phone_cpp', 'phone_cpw', 'address_cpp', 'address_cpw'] as $column) {
                if (Schema::hasColumn('prospects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
