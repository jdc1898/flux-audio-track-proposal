# Flux Audio Track Component Proposal

A set of audio player components for Flux UI with support for inline playback and a detachable floating player.

## Components

| Component | Description |
|-----------|-------------|
| `flux:audio-track` | Static display of track info (title, artist, waveform, duration) |
| `flux:audio-track.playable` | Interactive player with play/pause and seeking |
| `flux:audio-track.floating` | Floating mini-player that persists across navigation |
| `flux:audio-track.provider` | Layout wrapper that initializes the floating player |
| `flux:audio-track.waveform` | Standalone waveform visualization |
| `flux:audio-track.action` | Action button for use in actions slot |

## Installation

### 1. Copy Blade Components

Copy the contents of `stubs/resources/views/flux/audio-track/` to your Flux views directory.

### 2. Register the Alpine.js Plugin

The audio player functionality requires an Alpine.js plugin. Add it to your JavaScript bundle:

```javascript
// resources/js/app.js
import Alpine from 'alpinejs'
import audioTrack from './plugins/audio-track'

Alpine.plugin(audioTrack)

Alpine.start()
```

Copy `js/audio-track.js` to `resources/js/plugins/audio-track.js` (or your preferred location).

### 3. Add the Provider to Your Layout

Include the provider component once in your layout (typically in the body):

```blade
<body>
    {{ $slot }}

    <flux:audio-track.provider />
</body>
```

## Usage

### Basic Static Display

```blade
<flux:audio-track
    image="/path/to/album-art.jpg"
    title="Song Title"
    artist="Artist Name"
    duration="3:45"
    plays="1.2K"
/>
```

### Playable Track

```blade
<flux:audio-track.playable
    image="/path/to/album-art.jpg"
    title="Song Title"
    artist="Artist Name"
    src="/path/to/audio.mp3"
/>
```

### With Custom Actions

```blade
<flux:audio-track.playable
    title="Song Title"
    artist="Artist Name"
    src="/path/to/audio.mp3"
    :detachable="false"
>
    <x-slot:actions>
        <flux:audio-track.action icon="link" @click="copyLink()" />
        <flux:audio-track.action icon="heart" @click="favorite()" />
    </x-slot:actions>
</flux:audio-track.playable>
```

### Disable Detach Button

```blade
<flux:audio-track.playable
    title="Song Title"
    src="/path/to/audio.mp3"
    :detachable="false"
/>
```

### Draggable Floating Player

The floating player can be made draggable, allowing users to click and drag the track info area to reposition it anywhere on screen. The position resets when the player is closed.

To enable dragging:

```blade
<flux:audio-track.provider :draggable="true" />
```

## Props Reference

### `flux:audio-track`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `image` | string | null | URL to album art |
| `title` | string | null | Track title |
| `artist` | string | null | Artist name |
| `duration` | string | null | Duration display (e.g., "3:45") |
| `plays` | string | null | Play count display |
| `waveform` | slot | null | Custom waveform content |
| `actions` | slot | null | Custom action buttons |

### `flux:audio-track.playable`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `image` | string | null | URL to album art |
| `title` | string | null | Track title |
| `artist` | string | null | Artist name |
| `duration` | string | null | Initial duration display |
| `plays` | string | null | Play count display |
| `src` | string | null | Audio file URL |
| `detachable` | bool | true | Show detach button |
| `actions` | slot | null | Custom action buttons (replaces detach button) |

### `flux:audio-track.provider`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `draggable` | bool | false | Allow user to drag the floating player |

### `flux:audio-track.floating`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `draggable` | bool | false | Allow user to drag the player around the screen |

### `flux:audio-track.waveform`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `bars` | int | 60 | Number of waveform bars |
| `progress` | int | 0 | Progress percentage (0-100) |
| `interactive` | bool | false | Enable click-to-seek |

## Architecture Notes

### JavaScript Store

The audio player uses an Alpine.js store (`$store.audioPlayer`) for global state management. This allows:

- Multiple playable tracks on a page
- Seamless handoff to floating player
- Persistent playback across page navigations (with Livewire/SPA)

### State Properties

```javascript
$store.audioPlayer = {
    active: false,      // Floating player visible
    playing: false,     // Currently playing
    progress: 0,        // Playback progress (0-100)
    currentTime: 0,     // Current position in seconds
    duration: 0,        // Total duration in seconds
    src: null,          // Audio source URL
    title: null,        // Track title
    artist: null,       // Artist name
    image: null,        // Album art URL
}
```

### Store Methods

```javascript
// Load a track into the floating player
$store.audioPlayer.load(src, title, artist, image, startTime, autoplay)

// Toggle play/pause
$store.audioPlayer.toggle()

// Seek to position (pass click event)
$store.audioPlayer.seek(event)

// Close the floating player
$store.audioPlayer.close()

// Format seconds to MM:SS
$store.audioPlayer.formatTime(seconds)
```

## Note for Caleb

**This is a working prototype.** We've implemented this using an Alpine.js store and plugin pattern, which differs from Flux Pro's architecture.

### Current Implementation (Alpine.js)
- Requires users to import and register `js/audio-track.js` as an Alpine plugin
- Uses `$store.audioPlayer` for global state management
- Works, but requires an extra setup step for users

### Flux Pro's Pattern (Web Components)
- Flux Pro uses custom `<ui-*>` Web Components (e.g., `<ui-modal>`, `<ui-dropdown>`)
- These are bundled into `@fluxScripts` automatically
- Zero configuration required from users

### Our Offer
If you'd like to include this component in Flux, we're happy to convert the Alpine.js store to a `<ui-audio-player>` Web Component that integrates seamlessly with `@fluxScripts`. The Blade templates follow Flux conventions and should require minimal changes.

Just let us know your preferred approach!

---

## Design Decisions

### Why We Used Alpine.js (For Now)

We chose Alpine.js stores for this prototype because:

1. **Faster Prototyping**: Allowed us to validate the UX quickly
2. **Laravel Ecosystem Familiarity**: Alpine.js is well-known in the Livewire community
3. **Easier to Demonstrate**: No build step required to test the components

This can be refactored to Web Components for Flux core integration.

### Waveform Generation

The waveform uses randomly generated bar heights with a bell-curve distribution (higher in the middle) for a natural look. For real audio analysis, the component could accept actual waveform data via a prop.

### Draggable Floating Player

The floating player supports drag-and-drop repositioning:

- **Drag handle**: The track info area (title/artist) serves as the drag handle
- **Boundary constraints**: Player stays within viewport bounds
- **Position reset**: Position resets to center-bottom when player is closed
- **Cursor feedback**: Shows grab/grabbing cursors when draggable

## File Structure

```
flux-audio-track-proposal/
├── README.md
├── js/
│   └── audio-track.js          # Alpine.js plugin
└── stubs/
    └── resources/
        └── views/
            └── flux/
                └── audio-track/
                    ├── index.blade.php       # Static display
                    ├── playable.blade.php    # Interactive player
                    ├── floating.blade.php    # Floating mini-player
                    ├── provider.blade.php    # Layout provider
                    ├── waveform.blade.php    # Waveform visualization
                    └── action.blade.php      # Action button
```

## Future Enhancements

- Real waveform data support (from audio analysis)
- Playlist/queue support
- Volume controls
- Keyboard shortcuts
- Media session API integration (OS media controls)
- Custom progress bar styles
- Touch/mobile drag support

---

*Proposed for inclusion in Flux UI by the iC team.*
