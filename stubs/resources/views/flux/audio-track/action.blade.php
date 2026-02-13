@blaze

{{--
    Audio Track Action Button

    A styled button for use within the audio track actions slot.

    Usage:
        <x-slot:actions>
            <flux:audio-track.action icon="heart" />
            <flux:audio-track.action icon="share" />
        </x-slot:actions>
--}}

@props([
    'icon' => null,
])

<flux:button
    variant="ghost"
    size="sm"
    square
    {{ $attributes }}
>
    <?php if ($icon): ?>
        <flux:icon :name="$icon" class="size-4" />
    <?php else: ?>
        {{ $slot }}
    <?php endif; ?>
</flux:button>
