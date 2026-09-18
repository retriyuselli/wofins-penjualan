<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('venue_lamaran')->nullable()->after('time_lamaran');
            $table->string('venue_akad')->nullable()->after('time_akad');
            $table->date('date_pengajian')->nullable()->after('time_resepsi');
            $table->time('time_pengajian')->nullable()->after('date_pengajian');
            $table->string('venue_pengajian')->nullable()->after('time_pengajian');
            $table->date('date_ngunduh_mantu')->nullable()->after('venue_pengajian');
            $table->time('time_ngunduh_mantu')->nullable()->after('date_ngunduh_mantu');
            $table->string('venue_ngunduh_mantu')->nullable()->after('time_ngunduh_mantu');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn([
                'venue_lamaran',
                'venue_akad',
                'date_pengajian',
                'time_pengajian',
                'venue_pengajian',
                'date_ngunduh_mantu',
                'time_ngunduh_mantu',
                'venue_ngunduh_mantu',
            ]);
        });
    }
};
