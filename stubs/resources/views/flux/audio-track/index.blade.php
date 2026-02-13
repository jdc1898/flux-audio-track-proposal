@blaze

@props([
    'image' => null,
    'title' => null,
    'artist' => null,
    'duration' => null,
    'plays' => null,
    'src' => null,
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
    ->add('relative shrink-0 size-12 rounded-md overflow-hidden')
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
    ->add('flex-1 flex items-center justify-center')
    ->add('h-8 min-w-0')
    ;

$statsClasses = Flux::classes()
    ->add('flex items-center gap-4')
    ->add('shrink-0')
    ->add('text-sm text-zinc-500 dark:text-zinc-400')
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-audio-track>
    {{-- Album Art --}}
    <?php if ($image): ?>
        <div class="{{ $imageClasses }}">
            <img class="h-full w-full object-cover" src="{{ $image }}" alt="{{ $title }}">
        </div>
    <?php else: ?>
        <div class="{{ $imageClasses }} bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
            <flux:icon name="musical-note" class="size-6 text-zinc-400" />
        </div>
    <?php endif; ?>

    {{-- Title & Artist --}}
    <div class="{{ $contentClasses }}" data-slot="content">
        <?php if ($title): ?>
            <div class="text-sm font-medium text-zinc-800 dark:text-white truncate">{{ $title }}</div>
        <?php endif; ?>

        <?php if ($artist): ?>
            <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $artist }}</div>
        <?php endif; ?>
    </div>

    {{-- Waveform --}}
    <div class="{{ $waveformClasses }}" data-slot="waveform">
        <?php if ($waveform): ?>
            {{ $waveform }}
        <?php else: ?>
            <flux:audio-track.waveform />
        <?php endif; ?>
    </div>

    {{-- Stats --}}
    <div class="{{ $statsClasses }}" data-slot="stats">
        <?php if ($duration): ?>
            <span class="tabular-nums">{{ $duration }}</span>
        <?php endif; ?>

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
    <?php endif; ?>
</div>
