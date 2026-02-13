@blaze

@props([
    'image' => null,
    'title' => null,
    'artist' => null,
    'duration' => null,
    'plays' => null,
    'src' => null,
    'detachable' => true,
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

<div
    {{ $attributes->class($classes) }}
    x-data="{
        playing: false,
        progress: 0,
        currentTime: 0,
        duration: 0,
        audio: null,
        detached: false,
        trackInfo: {
            image: {{ Js::from($image) }},
            title: {{ Js::from($title) }},
            artist: {{ Js::from($artist) }},
            src: {{ Js::from($src) }},
        },
        init() {
            this.audio = this.$refs.audio;
            if (!this.audio) return;

            this.audio.addEventListener('timeupdate', () => {
                if (this.detached) return;
                this.currentTime = this.audio.currentTime;
                this.duration = this.audio.duration || 0;
                this.progress = this.duration > 0 ? (this.currentTime / this.duration) * 100 : 0;
            });
            this.audio.addEventListener('ended', () => {
                if (this.detached) return;
                this.playing = false;
                this.progress = 0;
            });
            this.audio.addEventListener('loadedmetadata', () => {
                this.duration = this.audio.duration;
            });
        },
        toggle() {
            if (this.detached && typeof $store.audioPlayer !== 'undefined') {
                $store.audioPlayer.toggle();
                return;
            }
            if (this.playing) {
                this.audio.pause();
            } else {
                document.querySelectorAll('audio').forEach(a => a.pause());
                this.audio.play();
            }
            this.playing = !this.playing;
        },
        seek(e) {
            if (this.detached && typeof $store.audioPlayer !== 'undefined') {
                $store.audioPlayer.seek(e);
                return;
            }
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            if (this.audio.duration) {
                this.audio.currentTime = percent * this.audio.duration;
            }
        },
        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + secs.toString().padStart(2, '0');
        },
        detach() {
            if (typeof $store.audioPlayer === 'undefined') {
                console.warn('Audio player store not initialized. Ensure audio-track.js plugin is loaded.');
                return;
            }

            $store.audioPlayer.load(
                this.trackInfo.src,
                this.trackInfo.title,
                this.trackInfo.artist,
                this.trackInfo.image,
                this.audio.currentTime,
                this.playing
            );

            this.audio.pause();
            this.detached = true;
        }
    }"
    x-init="
        if (typeof $store.audioPlayer !== 'undefined') {
            $watch('$store.audioPlayer.src', (src) => {
                if (src === trackInfo.src) detached = true;
            });
            $watch('$store.audioPlayer.active', (active) => {
                if (!active && detached) detached = false;
            });
        }
    "
    data-flux-audio-track
>
    {{-- Album Art with Play Button --}}
    <div class="{{ $imageClasses }}" @click="toggle()">
        <?php if ($image): ?>
            <img class="h-full w-full object-cover" src="{{ $image }}" alt="{{ $title }}">
        <?php else: ?>
            <div class="h-full w-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                <flux:icon name="musical-note" class="size-6 text-zinc-400" />
            </div>
        <?php endif; ?>

        {{-- Play/Pause Overlay --}}
        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <template x-if="!detached">
                <div>
                    <flux:icon x-show="!playing" name="play" variant="solid" class="size-6 text-white" />
                    <flux:icon x-show="playing" x-cloak name="pause" variant="solid" class="size-6 text-white" />
                </div>
            </template>
            <template x-if="detached && typeof $store.audioPlayer !== 'undefined'">
                <div>
                    <flux:icon x-show="!$store.audioPlayer.playing" name="play" variant="solid" class="size-6 text-white" />
                    <flux:icon x-show="$store.audioPlayer.playing" x-cloak name="pause" variant="solid" class="size-6 text-white" />
                </div>
            </template>
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
    <div class="{{ $waveformClasses }}" @click="seek($event)" data-slot="waveform">
        <div class="flex items-center justify-center gap-[2px] h-full w-full">
            @foreach ($waveformData as $index => $height)
                <div
                    class="w-[2px] rounded-full transition-colors"
                    :class="({{ $index }} / {{ $bars }}) * 100 < (detached && typeof $store.audioPlayer !== 'undefined' ? $store.audioPlayer.progress : progress) ? 'bg-zinc-800 dark:bg-white' : 'bg-zinc-300 dark:bg-zinc-600'"
                    style="height: {{ $height * 100 }}%"
                ></div>
            @endforeach
        </div>
    </div>

    {{-- Stats --}}
    <div class="{{ $statsClasses }}" data-slot="stats">
        <span
            class="tabular-nums"
            x-text="detached && typeof $store.audioPlayer !== 'undefined'
                ? $store.audioPlayer.formatTime($store.audioPlayer.currentTime)
                : (playing || currentTime > 0 ? formatTime(currentTime) : {{ Js::from($duration ?? '0:00') }})"
        >{{ $duration ?? '0:00' }}</span>

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
            <flux:button variant="ghost" size="sm" square @click="detach()" aria-label="{{ __('Detach player') }}">
                <flux:icon name="arrow-top-right-on-square" class="size-4" />
            </flux:button>
        </div>
    <?php endif; ?>

    {{-- Hidden Audio Element --}}
    <?php if ($src): ?>
        <audio x-ref="audio" src="{{ $src }}" preload="metadata"></audio>
    <?php endif; ?>
</div>
