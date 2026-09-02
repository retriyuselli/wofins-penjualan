<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('title')->default('KONTRAK KERJASAMA PERNIKAHAN');
            $table->string('package_section_title')->default('Dream Wedding Packages');
            $table->string('package_price_label')->default('DREAM WEDDING PACKAGE');
            $table->string('facilities_heading')->default('DENGAN RINCIAN FASILITAS SEBAGAI BERIKUT :');
            $table->text('intro_pihak_pertama')->nullable();
            $table->text('intro_pihak_kedua')->nullable();
            $table->text('intro_after_parties')->nullable();
            $table->text('closing_text')->nullable();
            $table->boolean('is_system_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        Schema::create('contract_template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_template_id')->constrained('contract_templates')->cascadeOnDelete();
            $table->string('key');
            $table->string('title');
            $table->longText('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['contract_template_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_template_sections');
        Schema::dropIfExists('contract_templates');
    }
};
