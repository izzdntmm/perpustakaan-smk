@props(['name', 'label', 'hint' => null])

<fieldset {{ $attributes->class('fieldset') }}>
    <legend class="fieldset-legend">{{ $label }}</legend>

    {{ $slot }}

    @error($name)
        <p class="label whitespace-normal text-error">{{ $message }}</p>
    @else
        @if ($hint)
            <p class="label whitespace-normal text-base-content/70">{{ $hint }}</p>
        @endif
    @enderror
</fieldset>