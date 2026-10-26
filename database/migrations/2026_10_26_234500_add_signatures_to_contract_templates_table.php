<?php

use App\Support\ContractTemplateDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contract_templates')) {
            return;
        }

        Schema::table('contract_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('contract_templates', 'signature_place')) {
                $table->string('signature_place')->nullable()->after('closing_text');
            }
            if (! Schema::hasColumn('contract_templates', 'signatures')) {
                $table->json('signatures')->nullable()->after('signature_place');
            }
        });

        $spkIds = Schema::hasTable('contract_template_sections')
            ? DB::table('contract_template_sections')
                ->where('key', 'like', 'pasal_%')
                ->distinct()
                ->pluck('contract_template_id')
                ->all()
            : [];

        $place = ContractTemplateDefaults::spkTemplateAttributes()['signature_place'];
        $spk = ContractTemplateDefaults::spkSignatures();
        $legacy = ContractTemplateDefaults::legacySignatures();

        foreach (DB::table('contract_templates')->select('id')->get() as $row) {
            $isSpk = in_array($row->id, $spkIds, true);
            DB::table('contract_templates')->where('id', $row->id)->update([
                'signature_place' => $place,
                'signatures' => json_encode($isSpk ? $spk : $legacy),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contract_templates')) {
            return;
        }

        Schema::table('contract_templates', function (Blueprint $table) {
            if (Schema::hasColumn('contract_templates', 'signatures')) {
                $table->dropColumn('signatures');
            }
            if (Schema::hasColumn('contract_templates', 'signature_place')) {
                $table->dropColumn('signature_place');
            }
        });
    }
};
