@php
    $signs = $contract['signatures'] ?? [];
    $place = trim((string) ($contract['signature_place'] ?? ''));
    $ownerSignatureSrc = $company?->ownerSignatureDataUriFor(\App\Enums\SignaturePlaceholderType::DraftKontrak);
    $count = count($signs);
    $colWidth = $count > 0 ? rtrim(rtrim(number_format(100 / $count, 4, '.', ''), '0'), '.').'%' : '100%';
@endphp
@if ($count > 0)
    <div class="signature-section">
        <br>
        @if ($place !== '')
            <div style="text-align: right; margin-bottom: 10px;">{{ $place }}</div>
        @endif
        <table class="signature-table">
            <tr>
                @foreach ($signs as $sign)
                    <td class="signature-label" style="width: {{ $colWidth }};">
                        {!! $sign['label'] !== '' ? nl2br(e($sign['label'])) : '&nbsp;' !!}
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach ($signs as $sign)
                    <td class="signature-slot">
                        @if (! empty($sign['stamp']) && $ownerSignatureSrc)
                            <img src="{{ $ownerSignatureSrc }}" alt="Tanda tangan owner">
                        @else
                            &nbsp;
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach ($signs as $sign)
                    <td class="signature-name">{{ $sign['name'] }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach ($signs as $sign)
                    <td><div class="signature-rule"></div></td>
                @endforeach
            </tr>
            @if (collect($signs)->contains(fn ($sign) => trim((string) ($sign['role'] ?? '')) !== ''))
                <tr>
                    @foreach ($signs as $sign)
                        <td class="signature-role">
                            @if (trim((string) ($sign['role'] ?? '')) !== '')
                                <b>{{ $sign['role'] }}</b>
                            @else
                                &nbsp;
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endif
        </table>
    </div>
@endif
