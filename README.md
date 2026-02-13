# Audio Track Component

Audio player components with inline playback and a detachable floating player.

## Components

| Component | Description |
|-----------|-------------|
| `flux:audio-track` | Inline audio track with playback controls |
| `flux:audio-track.player` | Floating mini-player (include once in layout) |
| `flux:audio-track.waveform` | Standalone waveform visualization |
| `flux:audio-track.action` | Action button for custom actions slot |

## Setup

Include the floating player component once in your layout:

```blade
<body>
    {{ $slot }}

    <flux:audio-track.player />
</body>
```

## Usage

### Basic Track

```blade
<flux:audio-track
    image="/albums/cover.jpg"
    title="Song Title"
    artist="Artist Name"
    src="/audio/track.mp3"
/>
```

### Without Detach Button

```blade
<flux:audio-track
    title="Song Title"
    artist="Artist Name"
    src="/audio/track.mp3"
    :detachable="false"
/>
```

### With Custom Actions

```blade
<flux:audio-track
    title="Song Title"
    src="/audio/track.mp3"
    :detachable="false"
>
    <x-slot:actions>
        <flux:audio-track.action icon="heart" />
        <flux:audio-track.action icon="share" />
    </x-slot:actions>
</flux:audio-track>
```

### Draggable Floating Player

```blade
<flux:audio-track.player draggable />
```

## Props

### `flux:audio-track`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `image` | string | null | Album art URL |
| `title` | string | null | Track title |
| `artist` | string | null | Artist name |
| `duration` | string | null | Duration display (e.g., "3:45") |
| `plays` | string | null | Play count display |
| `src` | string | null | Audio file URL |
| `detachable` | bool | true | Show detach button |
| `waveform` | slot | — | Custom waveform content |
| `actions` | slot | — | Custom action buttons |

### `flux:audio-track.player`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `draggable` | bool | false | Allow dragging the player |

### `flux:audio-track.waveform`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `bars` | int | 60 | Number of waveform bars |

## JavaScript API

The floating player exposes `window.fluxAudioPlayer`:

```javascript
// Load a track
fluxAudioPlayer.load(src, title, artist, image, startTime, autoplay);

// Control playback
fluxAudioPlayer.toggle();
fluxAudioPlayer.seek(0.5); // 50%
fluxAudioPlayer.close();

// State
fluxAudioPlayer.state.playing;
fluxAudioPlayer.state.progress;
fluxAudioPlayer.state.currentTime;
fluxAudioPlayer.state.duration;
```
