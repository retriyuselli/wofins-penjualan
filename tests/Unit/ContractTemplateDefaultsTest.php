<?php

namespace Tests\Unit;

use App\Support\ContractTemplateDefaults;
use PHPUnit\Framework\TestCase;

class ContractTemplateDefaultsTest extends TestCase
{
    public function test_strips_venue_wrapped_in_strong(): void
    {
        $html = '<p>Pernikahan <strong>{{prospect_cpw}} &amp; {{prospect_cpp}}</strong> di <strong>{{prospect_venue}}</strong>, berikut paketnya :</p>';

        $this->assertSame(
            '<p>Pernikahan <strong>{{prospect_cpw}} &amp; {{prospect_cpp}}</strong>, berikut paketnya :</p>',
            ContractTemplateDefaults::tidyIntroAfterParties($html),
        );
    }

    public function test_replaces_leftover_spk_venue_sentence(): void
    {
        $html = '<li><p>Pihak Pertama menawarkan jasa paket {{product_name}} pada Pihak Kedua. Acara diadakan. Acara pada tanggal {{akad_date}} senilai {{package_price}}.</p></li>';

        $this->assertSame(
            '<li><p>Pihak Pertama menawarkan jasa paket {{product_name}} pada Pihak Kedua senilai {{package_price}}. Pelaksanaan acara sesuai jadwal pada Pasal 2.</p></li>',
            ContractTemplateDefaults::tidyIntroAfterParties($html),
        );
    }

    public function test_tidies_wanprestasi_pasal_references(): void
    {
        $html = 'sebagaimana tersebut pada Pasal 6 Pihak Kedua dan sebagaimana yang tersebut pada Pasal 4 kepada Pihak Kedua';

        $this->assertSame(
            'sebagaimana tersebut pada Pasal 7 Pihak Kedua dan sebagaimana yang tersebut pada Pasal 3 kepada Pihak Kedua',
            ContractTemplateDefaults::tidySectionBody($html),
        );
    }

    public function test_display_product_name_replaces_underscores(): void
    {
        $this->assertSame(
            'Nisa Rama Ballroom Grand 500 Gold',
            ContractTemplateDefaults::displayProductName('Nisa Rama_Ballroom Grand_500 Gold'),
        );
        $this->assertSame('-', ContractTemplateDefaults::displayProductName(''));
    }
}
