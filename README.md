# Flux Audio Track Component

Audio player components for Flux UI with inline playback and a detachable floating player.

## Architecture

This component follows Flux Pro's architecture:

- **Custom Elements**: Uses `<ui-audio-track>` and `<ui-audio-player>` Web Components
- **Blade Stubs**: Located in `stubs/resources/views/flux/audio-track/`
- **JavaScript**: Standalone `dist/audio-player.js` (similar to `editor.js`)

## Installation

### 1. Copy Files

Copy the following to your Flux Pro package:

```
dist/audio-player.js              -> vendor/livewire/flux-pro/dist/
stubs/resources/views/flux/audio-track/  -> vendor/livewire/flux-pro/stubs/resources/views/flux/
```

### 2. Load the JavaScript

Add the script after `@fluxScripts` in your layout:

```blade
@fluxScripts
<script src="{{ asset('vendor/livewire/flux-pro/dist/audio-player.js') }}"></script>
```

Or publish and include via Vite.

### 3. Add Player to Layout

Include the floating player component once in your layout:

```blade
<body>
    {{ $slot }}

    <flux:audio-track.player />
</body>
```

## Components

| Component | Description |
|-----------|-------------|
| `flux:audio-track` | Inline audio track with playback controls |
| `flux:audio-track.player` | Floating mini-player (include once in layout) |
| `flux:audio-track.waveform` | Standalone waveform visualization |
| `flux:audio-track.action` | Action button for custom actions slot |

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
        <flux:audio-track.action icon="heart" @click="favorite()" />
        <flux:audio-track.action icon="share" @click="share()" />
    </x-slot:actions>
</flux:audio-track>
```

### Draggable Floating Player

```blade
<flux:audio-track.player draggable />
```

## Props Reference

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
| `waveform` | slot | null | Custom waveform content |
| `actions` | slot | null | Custom action buttons |

### `flux:audio-track.player`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `draggable` | bool | false | Allow dragging the player |

### `flux:audio-track.waveform`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `bars` | int | 60 | Number of waveform bars |

## JavaScript API

The floating player exposes a global `window.fluxAudioPlayer` object:

```javascript
// Load a track
fluxAudioPlayer.load(src, title, artist, image, startTime, autoplay);

// Control playback
fluxAudioPlayer.toggle();
fluxAudioPlayer.seek(0.5); // 50%
fluxAudioPlayer.close();

// Get state
fluxAudioPlayer.state.playing;
fluxAudioPlayer.state.progress;
fluxAudioPlayer.state.currentTime;
fluxAudioPlayer.state.duration;
```

## File Structure

```
flux-audio-track/
├── README.md
├── dist/
│   └── audio-player.js           # Web Components
└── stubs/
    └── resources/
        └── views/
            └── flux/
                └── audio-track/
                    ├── index.blade.php    # Main track component
                    ├── player.blade.php   # Floating player
                    ├── waveform.blade.php # Waveform visualization
                    └── action.blade.php   # Action button
```

## Integration Notes

This component is designed to integrate seamlessly with Flux Pro:

1. **Web Components**: Uses `<ui-audio-track>` and `<ui-audio-player>` following Flux's `<ui-*>` pattern
2. **Blade Conventions**: Uses `@blaze`, `Flux::classes()`, `<?php if ?>` syntax
3. **Styling**: Uses Tailwind CSS with dark mode support
4. **Slots**: Supports `waveform` and `actions` slots

To bundle into Flux Pro's `flux.js`, the Web Component classes can be added to the main bundle and the custom element registrations included in the initialization.

---

*Proposed for Flux UI*
