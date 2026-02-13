@blaze

@props([
    'image' => null,
    'title' => null,
    'artist' => null,
    'duration' => null,
    'plays' => null,
    'src' => null,
    'detachable' => true,
    'waveform' => null,
    'actions' => null,
])

@php
$classes = Flux::classes()
    ->add('flex items-center gap-4')
    ->add('bg-white dark:bg-white/10')
    ->add('border border-zinc-200 dark:border-white/10')
    ->add('rounded-lg px-4 py-3')
    ->add('w-full')
    ;

$imageClasses = Flux::classes()
    ->add('relative shrink-0 size-12 rounded-md overflow-hidden cursor-pointer')
    ->add('group')
    ->add([
        'after:absolute after:inset-0 after:inset-ring-[1px] after:inset-ring-black/7 dark:after:inset-ring-white/10',
        'after:rounded-md',
    ])
    ;

$contentClasses = Flux::classes()
    ->add('flex-shrink-0 min-w-0')
    ->add('max-w-[140px]')
    ;

$waveformClasses = Flux::classes()
    ->add('flex-1 h-8 min-w-0 cursor-pointer')
    ;

$statsClasses = Flux::classes()
    ->add('flex items-center gap-4')
    ->add('shrink-0')
    ->add('text-sm text-zinc-500 dark:text-zinc-400')
    ;

$bars = 60;
$waveformData = collect(range(1, $bars))->map(function ($i) use ($bars) {
    $center = $bars / 2;
    $distance = abs($i - $center) / $center;
    $baseHeight = 0.3 + (0.7 * (1 - $distance * 0.5));
    $random = (mt_rand(30, 100) / 100);
    return min(1, max(0.15, $baseHeight * $random));
});
@endphp

<ui-audio-track
    {{ $attributes->class($classes) }}
    data-src="{{ $src }}"
    data-title="{{ $title }}"
    data-artist="{{ $artist }}"
    data-image="{{ $image }}"
    data-flux-audio-track
>
    {{-- Album Art with Play Button --}}
    <div class="{{ $imageClasses }}" data-action="toggle">
        <?php if ($image): ?>
            <img class="h-full w-full object-cover" src="{{ $image }}" alt="{{ $title }}">
        <?php else: ?>
            <div class="h-full w-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                <flux:icon name="musical-note" class="size-6 text-zinc-400" />
            </div>
        <?php endif; ?>

        {{-- Play/Pause Overlay --}}
        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <flux:icon name="play" variant="solid" class="size-6 text-white [[ui-audio-track].playing_&]:hidden" />
            <flux:icon name="pause" variant="solid" class="size-6 text-white hidden [[ui-audio-track].playing_&]:block" />
        </div>
    </div>

    {{-- Title & Artist --}}
    <div class="{{ $contentClasses }}" data-slot="content">
        <?php if ($title): ?>
            <div class="text-sm font-medium text-zinc-800 dark:text-white truncate">{{ $title }}</div>
        <?php endif; ?>

        <?php if ($artist): ?>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $artist }}</div>
        <?php endif; ?>
    </div>

    {{-- Interactive Waveform --}}
    <div class="{{ $waveformClasses }}" data-action="seek" data-slot="waveform">
        <?php if ($waveform): ?>
            {{ $waveform }}
        <?php else: ?>
            <div class="flex items-center justify-center gap-[2px] h-full w-full">
                @foreach ($waveformData as $index => $height)
                    <div
                        class="w-[2px] rounded-full transition-colors bg-zinc-300 dark:bg-zinc-600 [&.active]:bg-zinc-800 dark:[&.active]:bg-white"
                        data-waveform-bar
                        style="height: {{ $height * 100 }}%"
                    ></div>
                @endforeach
            </div>
        <?php endif; ?>
    </div>

    {{-- Stats --}}
    <div class="{{ $statsClasses }}" data-slot="stats">
        <span class="tabular-nums" data-time>{{ $duration ?? '0:00' }}</span>

        <?php if ($plays !== null): ?>
            <span class="tabular-nums">{{ $plays }}</span>
        <?php endif; ?>
    </div>

    {{-- Actions --}}
    <?php if ($actions): ?>
        <div {{ $actions->attributes->class([
            'flex items-center gap-1',
            'shrink-0',
        ]) }} data-slot="actions">
            {{ $actions }}
        </div>
    <?php elseif ($detachable): ?>
        <div class="flex items-center gap-1 shrink-0" data-slot="actions">
            <flux:button variant="ghost" size="sm" square data-action="detach" aria-label="{{ __('Detach player') }}">
                <flux:icon name="arrow-top-right-on-square" class="size-4" />
            </flux:button>
        </div>
    <?php endif; ?>

    {{-- Hidden Audio Element --}}
    <?php if ($src): ?>
        <audio src="{{ $src }}" preload="metadata"></audio>
    <?php endif; ?>
</ui-audio-track>
