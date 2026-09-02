<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ContractTemplate;
use App\Models\SimulasiProduk;
use App\Models\User;
use App\Support\SafeHtml;

class ContractTemplateService
{
    public function resolve(?Company $company): ContractTemplate
    {
        if ($company) {
            $custom = ContractTemplate::query()
                ->where('company_id', $company->id)
                ->where('is_active', true)
                ->with(['sections' => fn ($q) => $q->orderBy('sort_order')])
                ->first();

            if ($custom) {
                return $custom;
            }
        }

        $default = ContractTemplate::query()
            ->where('is_system_default', true)
            ->with(['sections' => fn ($q) => $q->orderBy('sort_order')])
            ->first();

        if ($default) {
            return $default;
        }

        return ContractTemplate::makeFromDefaults();
    }

    /**
     * @return array{
     *     title: string,
     *     package_section_title: string,
     *     package_price_label: string,
     *     facilities_heading: string,
     *     intro_pihak_pertama: string,
     *     intro_pihak_kedua: string,
     *     intro_after_parties: string,
     *     closing_text: string,
     *     sections: list<array{key: string, title: string, html: string}>
     * }
     */
    public function render(ContractTemplate $template, array $variables): array
    {
        $replace = function (?string $text) use ($variables): string {
            $text = $text ?? '';
            foreach ($variables as $key => $value) {
                $text = str_replace('{{'.$key.'}}', (string) $value, $text);
            }

            return SafeHtml::fromRichText($text);
        };

        $sections = [];
        foreach ($template->sections as $section) {
            if (! $section->is_enabled) {
                continue;
            }

            $sections[] = [
                'key' => $section->key,
                'title' => $section->title,
                'html' => $replace($section->body),
            ];
        }

        return [
            'title' => strip_tags($replace($template->title)),
            'package_section_title' => strip_tags($replace($template->package_section_title)),
            'package_price_label' => strip_tags($replace($template->package_price_label)),
            'facilities_heading' => strip_tags($replace($template->facilities_heading)),
            'intro_pihak_pertama' => $replace($template->intro_pihak_pertama),
            'intro_pihak_kedua' => $replace($template->intro_pihak_kedua),
            'intro_after_parties' => $replace($template->intro_after_parties),
            'closing_text' => $replace($template->closing_text),
            'sections' => $sections,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function variablesFor(
        SimulasiProduk $record,
        ?Company $company,
        string $nomorSurat,
        ?User $financeUser,
        string $companyBankName,
        string $companyBankAccount,
        string $companyBankHolder,
    ): array {
        $prospect = $record->prospect;
        $termins = $record->payment_simulation ?? [];
        $terminHtml = 'dengan ketentuan yang disepakati bersama oleh kedua belah pihak.';

        if (is_array($termins) && $termins !== []) {
            $items = '';
            foreach ($termins as $index => $termin) {
                $line = 'Termin '.($index + 1);
                if (isset($termin['nominal'])) {
                    $line .= ' sebesar Rp. '.number_format((float) $termin['nominal'], 0, ',', '.').',-';
                }
                if (isset($termin['persen'])) {
                    $line .= ' yaitu '.rtrim(rtrim(number_format((float) $termin['persen'], 2, ',', '.'), '0'), ',').'%';
                }
                if (! empty($termin['bulan'])) {
                    $line .= ' pada Bulan '.e((string) $termin['bulan']);
                    if (! empty($termin['tahun'])) {
                        $line .= ' '.e((string) $termin['tahun']);
                    }
                }
                $items .= '<li>'.$line.'</li>';
            }
            $terminHtml = '<ol type="a" class="termin-list">'.$items.'</ol>';
        }

        return [
            'company_name' => e($company?->company_name ?? config('app.name')),
            'company_address' => e($company?->address ?? '-'),
            'company_email' => e($company?->email ?? '-'),
            'company_phone' => e($company?->phone ?? '-'),
            'company_city' => e($company?->city ?? 'Palembang'),
            'owner_name' => e($company?->owner_name ?? '-'),
            'owner_position' => e($company?->jabatan_owner ?? '-'),
            'nomor_surat' => e($nomorSurat),
            'prospect_cpw' => e($prospect?->name_cpw ?? '...'),
            'prospect_cpp' => e($prospect?->name_cpp ?? '...'),
            'prospect_venue' => e($prospect?->venue ?? '...'),
            'event_name' => e($prospect?->name_event ?? '-'),
            'product_name' => e($record->product?->name ?? '-'),
            'dp_amount' => 'Rp. '.number_format((float) ($record->payment_dp_amount ?? 0), 0, ',', '.').',-',
            'termin_list' => $terminHtml,
            'bank_name' => e($companyBankName),
            'bank_account' => e($companyBankAccount),
            'bank_holder' => e($companyBankHolder),
            'finance_name' => e($financeUser?->name ?? 'Finance'),
            'finance_phone' => e($financeUser?->phone_number ?? '-'),
        ];
    }

    public function ensureSystemDefault(): ContractTemplate
    {
        $existing = ContractTemplate::query()
            ->where('is_system_default', true)
            ->with('sections')
            ->first();

        if ($existing) {
            return $existing;
        }

        return ContractTemplate::makeFromDefaults();
    }
}
