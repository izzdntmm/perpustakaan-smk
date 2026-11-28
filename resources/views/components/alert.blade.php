@php
    $types = [
        ['success', 'alert-success', 'check-circle'],
        ['error', 'alert-error', 'x-circle'],
        ['warning', 'alert-warning', 'exclamation-triangle'],
        ['info', 'alert-info', 'information-circle'],
    ];

    $hasMessage = session()->hasAny(['success', 'error', 'warning', 'info']) || $errors->any();
@endphp

@if ($hasMessage)
    <div class="mb-4 space-y-2">
        @foreach ($types as [$key, $class, $icon])
            @if (session($key))
                <div role="alert" class="alert {{ $class }}">
                    @svg('heroicon-o-'.$icon, 'size-5 shrink-0')
                    <span class="flex-1">{{ session($key) }}</span>
                    <button type="button" class="btn btn-ghost btn-xs btn-square" aria-label="Tutup pesan"
                            onclick="this.closest('[role=alert]').remove()">
                        @svg('heroicon-o-x-mark', 'size-4')
                    </button>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div role="alert" class="alert alert-error items-start">
                @svg('heroicon-o-exclamation-triangle', 'size-5 shrink-0')
                <div class="flex-1">
                    <p class="font-semibold">Periksa kembali isian kamu:</p>
                    <ul class="mt-1 list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn btn-ghost btn-xs btn-square" aria-label="Tutup pesan"
                        onclick="this.closest('[role=alert]').remove()">
                    @svg('heroicon-o-x-mark', 'size-4')
                </button>
            </div>
        @endif
    </div>
@endif