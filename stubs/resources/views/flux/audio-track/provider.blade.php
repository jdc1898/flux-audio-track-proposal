@blaze

{{--
    Audio Track Provider

    Include this component once in your layout to enable the global audio player.
    Requires the audio-track.js plugin to be loaded.

    Props:
        - draggable: bool (default: true) - Allow user to drag the floating player

    Usage:
        <flux:audio-track.provider />
        <flux:audio-track.provider :draggable="false" />
--}}

@props([
    'draggable' => false,
])

{{-- Render the floating player --}}
<flux:audio-track.floating :draggable="$draggable" />
