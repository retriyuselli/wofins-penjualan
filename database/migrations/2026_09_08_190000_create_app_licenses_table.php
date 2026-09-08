<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_licenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();
            $table->string('company_name')->nullable();
            $table->string('package')->nullable();
            $table->string('domain')->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status')->default('unused');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->string('last_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_licenses');
    }
};
