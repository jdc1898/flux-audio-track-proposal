@blaze

@props([
    'bars' => 60,
    'progress' => 0,
    'interactive' => false,
])

@php
$classes = Flux::classes()
    ->add('flex items-center justify-center gap-[2px] h-full w-full')
    ;

$waveformData = collect(range(1, $bars))->map(function ($i) use ($bars) {
    $center = $bars / 2;
    $distance = abs($i - $center) / $center;
    $baseHeight = 0.3 + (0.7 * (1 - $distance * 0.5));
    $random = (mt_rand(30, 100) / 100);
    return min(1, max(0.15, $baseHeight * $random));
});
@endphp

<div {{ $attributes->class($classes) }} data-flux-audio-track-waveform>
    @foreach ($waveformData as $index => $height)
        <div
            class="w-[2px] rounded-full transition-colors"
            @class([
                'bg-zinc-800 dark:bg-white' => ($index / $bars) * 100 < $progress,
                'bg-zinc-300 dark:bg-zinc-600' => ($index / $bars) * 100 >= $progress,
            ])
            style="height: {{ $height * 100 }}%"
        ></div>
    @endforeach
</div>
