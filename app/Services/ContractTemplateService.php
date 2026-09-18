<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ContractTemplate;
use App\Models\Prospect;
use App\Models\SimulasiProduk;
use App\Models\User;
use App\Support\BankAccount;
use App\Support\ContractTemplateDefaults;
use App\Support\Rupiah;
use App\Support\SafeHtml;
use Carbon\Carbon;

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
     *     sections: list<array{key: string, title: string, keterangan: ?string, html: string}>
     * }
     */
    public function render(ContractTemplate $template, array $variables): array
    {
        $isSpk = $this->isSpkTemplate($template);
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

            if ($isSpk && in_array($section->key, ContractTemplateDefaults::legacySectionKeys(), true)) {
                continue;
            }

            $html = $replace($section->body);
            if ($section->key === 'pasal_2_waktu') {
                $html = $replace($this->pasal2OpeningHtml($section->body)).($variables['jadwal_acara'] ?? '');
            }

            $sections[] = [
                'key' => $section->key,
                'title' => $section->title,
                'keterangan' => $section->keterangan,
                'html' => $html,
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
            'contract_date' => strip_tags((string) ($variables['contract_date'] ?? '')),
            'jadwal_acara' => $variables['jadwal_acara'] ?? '',
            'is_spk' => $isSpk,
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

        $packagePrice = Rupiah::format((float) ($record->grand_total ?? $record->total_price ?? 0), true).',-';
        $contractDate = ($record->created_at ?? Carbon::now('Asia/Jakarta'))
            ->copy()
            ->setTimezone('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('l, d F Y');
        $jadwal = $this->jadwalAcaraHtml($prospect);

        return [
            'company_name' => e($company?->company_name ?? config('app.name')),
            'company_address' => e($company?->address ?? '-'),
            'company_email' => e($company?->email ?? '-'),
            'company_phone' => e($company?->phone ?? '-'),
            'company_city' => e($company?->city ?? 'Palembang'),
            'owner_name' => e($company?->owner_name ?? '-'),
            'owner_position' => e($company?->jabatan_owner ?? '-'),
            'nomor_surat' => e($nomorSurat),
            'contract_date' => e($contractDate),
            'prospect_cpw' => e($prospect?->name_cpw ?? '...'),
            'prospect_cpp' => e($prospect?->name_cpp ?? '...'),
            'prospect_venue' => e($prospect?->venue ?? '...'),
            'event_name' => e($prospect?->name_event ?? '-'),
            'product_name' => e($record->product?->name ?? '-'),
            'akad_date' => e($jadwal['akad_date']),
            'akad_time' => e($jadwal['akad_time']),
            'lamaran_date' => e($jadwal['lamaran_date']),
            'lamaran_time' => e($jadwal['lamaran_time']),
            'resepsi_date' => e($jadwal['resepsi_date']),
            'resepsi_time' => e($jadwal['resepsi_time']),
            'jadwal_acara' => $jadwal['html'],
            'package_price' => e($packagePrice),
            'dp_amount' => 'Rp. '.number_format((float) ($record->payment_dp_amount ?? 0), 0, ',', '.').',-',
            'termin_list' => $terminHtml,
            'bank_name' => e($companyBankName),
            'bank_account' => e(BankAccount::digits($companyBankAccount) ?? (string) $companyBankAccount),
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

    public function syncSystemDefault(): ContractTemplate
    {
        $template = ContractTemplate::query()
            ->where('is_system_default', true)
            ->first();

        if (! $template) {
            return ContractTemplate::makeFromDefaults();
        }

        $template->fill(ContractTemplateDefaults::templateAttributes());
        $template->is_system_default = true;
        $template->save();

        $template->sections()->delete();
        foreach (ContractTemplateDefaults::sections() as $index => $section) {
            $template->sections()->create([
                ...$section,
                'sort_order' => $index + 1,
            ]);
        }

        return $template->load('sections');
    }

    public function isSpkTemplate(ContractTemplate $template): bool
    {
        $template->loadMissing('sections');

        return $template->sections->contains(
            fn ($section): bool => str_starts_with((string) $section->key, 'pasal_')
        );
    }

    public function applySpkStructure(ContractTemplate $template): ContractTemplate
    {
        $attributes = ContractTemplateDefaults::spkTemplateAttributes();
        unset($attributes['is_system_default'], $attributes['name'], $attributes['is_active']);
        $template->fill($attributes);
        $template->save();

        $template->sections()->delete();
        foreach (ContractTemplateDefaults::spkSections() as $index => $section) {
            $template->sections()->create([
                ...$section,
                'sort_order' => $index + 1,
            ]);
        }

        return $template->load('sections');
    }

    public function refreshSpkScheduleText(): void
    {
        $intro = ContractTemplateDefaults::spkTemplateAttributes()['intro_after_parties'];
        $sectionBodies = collect(ContractTemplateDefaults::spkSections())->keyBy('key');

        ContractTemplate::query()
            ->with('sections')
            ->get()
            ->each(function (ContractTemplate $template) use ($intro, $sectionBodies): void {
                if (! $this->isSpkTemplate($template)) {
                    return;
                }

                $template->update(['intro_after_parties' => $intro]);

                foreach (['pasal_2_waktu', 'pasal_6_biaya'] as $key) {
                    if (! isset($sectionBodies[$key])) {
                        continue;
                    }

                    $template->sections()->where('key', $key)->update([
                        'body' => $sectionBodies[$key]['body'],
                    ]);
                }
            });
    }

    /**
     * Jika default sistem masih bergaya SPK, salin dulu ke template perusahaan
     * supaya perbaikan kontrak sekarang tidak hilang saat default dikembalikan.
     */
    public function preserveSpkAsCompanyTemplate(): ?ContractTemplate
    {
        $system = ContractTemplate::query()
            ->where('is_system_default', true)
            ->with('sections')
            ->first();

        if (! $system || ! $this->isSpkTemplate($system)) {
            return null;
        }

        $alreadyHasSpk = ContractTemplate::query()
            ->where('is_system_default', false)
            ->with('sections')
            ->get()
            ->contains(fn (ContractTemplate $template): bool => $this->isSpkTemplate($template));

        if ($alreadyHasSpk) {
            return null;
        }

        $company = Company::query()->first();

        return ContractTemplate::copyFrom($system, [
            'company_id' => $company?->id,
            'is_system_default' => false,
            'is_active' => true,
            'name' => ContractTemplateDefaults::spkTemplateAttributes()['name'],
        ]);
    }

    private function pasal2OpeningHtml(?string $body): string
    {
        $body = (string) $body;
        $parts = preg_split('/<p>\s*<b>\s*(Lamaran|Akad|Resepsi)/iu', $body, 2);
        $opening = trim($parts[0] ?? '');
        $opening = preg_replace('/<ol[\s\S]*$/i', '', $opening) ?? $opening;
        $opening = trim(str_replace('{{jadwal_acara}}', '', $opening));

        if ($opening === '') {
            return '<p>Waktu pelayanan dimulai sejak diterbitkannya invoice setelah dibayarkannya DP dan ditandatanganinya surat perjanjian ini hingga selesai pelaksanaan pernikahan. Sedangkan, pelaksanaan pernikahan dilaksanakan pada:</p>';
        }

        return $opening;
    }

    /**
     * @return array{
     *     html: string,
     *     akad_date: string,
     *     akad_time: string,
     *     lamaran_date: string,
     *     lamaran_time: string,
     *     resepsi_date: string,
     *     resepsi_time: string
     * }
     */
    private function jadwalAcaraHtml(mixed $prospect): array
    {
        $empty = [
            'html' => '',
            'akad_date' => '',
            'akad_time' => '',
            'lamaran_date' => '',
            'lamaran_time' => '',
            'resepsi_date' => '',
            'resepsi_time' => '',
        ];

        if (! $prospect) {
            return $empty;
        }

        $blocks = '';

        foreach (Prospect::pasal2EventDefinitions() as $event) {
            $dateValue = $prospect->{$event['date']} ?? null;
            if (! $this->hasEventDate($dateValue)) {
                continue;
            }

            $formattedDate = $this->formatEventDate($dateValue);
            $formattedTime = $this->formatEventTime($prospect->{$event['time']} ?? null);

            if ($event['key'] === 'lamaran') {
                $empty['lamaran_date'] = $formattedDate;
                $empty['lamaran_time'] = $formattedTime;
            } elseif ($event['key'] === 'akad') {
                $empty['akad_date'] = $formattedDate;
                $empty['akad_time'] = $formattedTime;
            } elseif ($event['key'] === 'resepsi') {
                $empty['resepsi_date'] = $formattedDate;
                $empty['resepsi_time'] = $formattedTime;
            }

            $blocks .= $this->eventScheduleBlock(
                $event['label'],
                $formattedDate,
                $formattedTime,
                (string) ($prospect->{$event['venue']} ?? ''),
            );
        }

        $empty['html'] = $blocks;

        return $empty;
    }

    private function hasEventDate(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === '-') {
            return false;
        }

        try {
            return Carbon::parse($value)->year >= 1990;
        } catch (\Throwable) {
            return false;
        }
    }

    private function formatEventDate(mixed $value): string
    {
        return Carbon::parse($value)->locale('id')->translatedFormat('l, d F Y');
    }

    private function formatEventTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'pukul akan menyesuaikan sampai dengan selesai';
        }

        try {
            return 'Pukul '.Carbon::parse($value)->format('H.i').' sampai dengan selesai';
        } catch (\Throwable) {
            return 'pukul akan menyesuaikan sampai dengan selesai';
        }
    }

    private function eventScheduleBlock(string $heading, string $date, string $time, string $venue): string
    {
        $html = '<p><b>'.e($heading).'</b></p><ol>';
        $html .= '<li>Tanggal : '.e($date).'</li>';
        $html .= '<li>Waktu : '.e($time).'</li>';
        if (trim($venue) !== '') {
            $html .= '<li>Lokasi : '.e($venue).'</li>';
        }
        $html .= '</ol>';

        return $html;
    }
}
