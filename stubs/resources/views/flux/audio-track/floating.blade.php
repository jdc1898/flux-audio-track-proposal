@blaze

{{--
    Floating Audio Player

    This component renders a floating mini-player at the bottom of the screen.
    It connects to the global Alpine.js audio player store.

    Props:
        - draggable: bool (default: true) - Allow user to drag the player around the screen

    Requirements:
        - audio-track.js plugin must be loaded
        - Include <flux:audio-track.provider /> in your layout
--}}

@props([
    'draggable' => false,
])

@php
$classes = Flux::classes()
    ->add('fixed z-50')
    ->add('flex items-center gap-3')
    ->add('bg-white dark:bg-zinc-900')
    ->add('border border-zinc-200 dark:border-white/10')
    ->add('rounded-full shadow-lg')
    ->add('pl-1.5 pr-4 py-1.5')
    ->add('min-w-[280px] max-w-[400px]')
    ;

$imageClasses = Flux::classes()
    ->add('relative shrink-0 size-10 rounded-full overflow-hidden cursor-pointer')
    ->add('group')
    ->add([
        'after:absolute after:inset-0 after:inset-ring-[1px] after:inset-ring-black/7 dark:after:inset-ring-white/10',
        'after:rounded-full',
    ])
    ;

$contentClasses = Flux::classes()
    ->add('flex-1 min-w-0')
    ->add($draggable ? 'cursor-grab active:cursor-grabbing' : '')
    ;

$progressClasses = Flux::classes()
    ->add('absolute bottom-0 left-0 right-0 h-0.5 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden')
    ;
@endphp

<div
    x-data="{
        draggable: {{ $draggable ? 'true' : 'false' }},
        isDragging: false,
        hasDragged: false,
        position: { x: null, y: null },
        dragOffset: { x: 0, y: 0 },

        startDrag(e) {
            if (!this.draggable) return;
            if (e.target.closest('button')) return;

            this.isDragging = true;
            const rect = this.$el.getBoundingClientRect();

            if (!this.hasDragged) {
                this.position.x = rect.left;
                this.position.y = rect.top;
            }

            this.dragOffset.x = e.clientX - this.position.x;
            this.dragOffset.y = e.clientY - this.position.y;

            document.addEventListener('mousemove', this.onDrag.bind(this));
            document.addEventListener('mouseup', this.stopDrag.bind(this));
            e.preventDefault();
        },

        onDrag(e) {
            if (!this.isDragging) return;

            this.hasDragged = true;
            const maxX = window.innerWidth - this.$el.offsetWidth;
            const maxY = window.innerHeight - this.$el.offsetHeight;

            this.position.x = Math.max(0, Math.min(e.clientX - this.dragOffset.x, maxX));
            this.position.y = Math.max(0, Math.min(e.clientY - this.dragOffset.y, maxY));
        },

        stopDrag() {
            this.isDragging = false;
            document.removeEventListener('mousemove', this.onDrag.bind(this));
            document.removeEventListener('mouseup', this.stopDrag.bind(this));
        },

        resetPosition() {
            this.hasDragged = false;
            this.position = { x: null, y: null };
        }
    }"
    x-show="typeof $store.audioPlayer !== 'undefined' && $store.audioPlayer.active"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    x-init="$watch('$store.audioPlayer.active', (active) => { if (!active) resetPosition(); })"
    x-cloak
    :style="hasDragged
        ? 'left: ' + position.x + 'px; top: ' + position.y + 'px; transform: none;'
        : 'left: 50%; bottom: 1rem; transform: translateX(-50%);'"
    {{ $attributes->class($classes) }}
    data-flux-audio-track-floating
>
    {{-- Album Art with Play/Pause --}}
    <div class="{{ $imageClasses }}" @click="$store.audioPlayer.toggle()">
        <template x-if="$store.audioPlayer.image">
            <img class="h-full w-full object-cover" :src="$store.audioPlayer.image" :alt="$store.audioPlayer.title">
        </template>
        <template x-if="!$store.audioPlayer.image">
            <div class="h-full w-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                <flux:icon name="musical-note" class="size-5 text-zinc-400" />
            </div>
        </template>

        {{-- Play/Pause Overlay --}}
        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <flux:icon x-show="!$store.audioPlayer.playing" name="play" variant="solid" class="size-5 text-white" />
            <flux:icon x-show="$store.audioPlayer.playing" x-cloak name="pause" variant="solid" class="size-5 text-white" />
        </div>
    </div>

    {{-- Track Info (Drag Handle) --}}
    <div
        class="{{ $contentClasses }}"
        @mousedown="startDrag($event)"
        @if($draggable) title="{{ __('Drag to move') }}" @endif
    >
        <div class="text-sm font-medium text-zinc-800 dark:text-white truncate select-none" x-text="$store.audioPlayer.title || 'Unknown Track'"></div>
        <div class="text-xs text-zinc-500 dark:text-zinc-400 truncate select-none" x-text="$store.audioPlayer.artist || 'Unknown Artist'"></div>
    </div>

    {{-- Time Display --}}
    <div class="text-xs text-zinc-500 dark:text-zinc-400 tabular-nums shrink-0">
        <span x-text="$store.audioPlayer.formatTime($store.audioPlayer.currentTime)">0:00</span>
        <span class="text-zinc-300 dark:text-zinc-600">/</span>
        <span x-text="$store.audioPlayer.formatTime($store.audioPlayer.duration)">0:00</span>
    </div>

    {{-- Close Button --}}
    <flux:button variant="ghost" size="sm" square @click="$store.audioPlayer.close()" aria-label="{{ __('Close player') }}">
        <flux:icon name="x-mark" class="size-4" />
    </flux:button>

    {{-- Progress Bar --}}
    <div class="{{ $progressClasses }}" @click="$store.audioPlayer.seek($event)">
        <div
            class="h-full bg-zinc-800 dark:bg-white transition-all duration-100"
            :style="'width: ' + $store.audioPlayer.progress + '%'"
        ></div>
    </div>
</div>
