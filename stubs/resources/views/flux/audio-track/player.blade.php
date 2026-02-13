@blaze

{{--
    Floating Audio Player

    Include this component once in your layout to enable the global floating audio player.
    Requires audio-player.js to be loaded.

    Usage:
        <flux:audio-track.player />
        <flux:audio-track.player draggable />
--}}

@props([
    'draggable' => false,
])

<ui-audio-player
    {{ $attributes }}
    @if($draggable) draggable @endif
    class="
        fixed z-50
        flex items-center gap-3
        bg-white dark:bg-zinc-900
        border border-zinc-200 dark:border-white/10
        rounded-full shadow-lg
        pl-1.5 pr-4 py-1.5
        min-w-[280px] max-w-[400px]
        bottom-4 left-1/2 -translate-x-1/2
        opacity-0 translate-y-4
        transition-all duration-200
        [&.active]:opacity-100 [&.active]:translate-y-0
    "
    style="display: none;"
    data-flux-audio-player
></ui-audio-player>
