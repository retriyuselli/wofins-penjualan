<article class="min-w-0 rounded-2xl border border-[var(--wf-line)] bg-white p-4">
    <p class="break-words text-sm font-bold text-[var(--wf-navy)]">
        {{ $item->vendor ? $facilityName($item->vendor->name) : 'Fasilitas' }}
        @if ((int) ($item->quantity ?? 1) > 1)
            <span class="font-semibold text-[var(--wf-muted)]">× {{ (int) $item->quantity }}</span>
        @endif
    </p>
    @if ($item->vendor && $item->vendor->category)
        <p class="mt-0.5 text-xs text-[var(--wf-muted)]">{{ $item->vendor->category->name }}</p>
    @endif
    @if (filled($included = $includedHtml($item->description)))
        <div class="wf-include mt-2 break-words text-sm leading-relaxed text-[var(--wf-ink)]">
            {!! $included !!}
        </div>
    @endif
</article>
