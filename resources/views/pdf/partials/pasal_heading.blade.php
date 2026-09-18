@php
    $parts = \App\Support\ContractTemplateDefaults::pasalParts($nomor ?? null, $keterangan ?? null);
@endphp
<div class="pasal-heading">
    <div class="pasal-nomor">{{ $parts['nomor'] }}</div>
    @if (! empty($parts['keterangan']))
        <div class="pasal-keterangan">{{ $parts['keterangan'] }}</div>
    @endif
</div>
