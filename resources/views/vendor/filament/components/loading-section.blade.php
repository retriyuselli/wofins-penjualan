@props([
    'columnSpan' => [],
    'columnStart' => [],
    'height' => null,
    'loadingLabel' => null,
])

{{--
    Use a fresh Filament attribute bag. Livewire passes TableWidget public
    properties (arrays) into this placeholder view; putting those on
    $attributes makes Laravel's trim() throw TypeError.
--}}
<div
    role="status"
    aria-busy="true"
    {{
        (new \Filament\Support\View\ComponentAttributeBag)
            ->gridColumn($columnSpan, $columnStart)
            ->class(['fi-section fi-loading-section'])
            ->style(['height: ' . e($height ?? '8rem')])
    }}
>
    <span class="fi-sr-only">
        {{ $loadingLabel ?? __('filament::components/loading-section.label') }}
    </span>
</div>
