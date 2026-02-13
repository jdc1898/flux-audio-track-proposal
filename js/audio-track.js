/**
 * Flux Audio Track Component
 *
 * Provides audio playback functionality with a detachable floating player.
 *
 * Usage:
 *   1. Include this script after Alpine.js is loaded
 *   2. Add <flux:audio-track.provider /> to your layout
 *   3. Use <flux:audio-track.playable /> components anywhere
 */

export default function (Alpine) {
    // Register the global audio player store
    Alpine.store('audioPlayer', {
        // State
        active: false,
        playing: false,
        progress: 0,
        currentTime: 0,
        duration: 0,
        src: null,
        title: null,
        artist: null,
        image: null,
        audio: null,

        /**
         * Initialize the audio player
         */
        init() {
            this.audio = document.createElement('audio');
            this.audio.preload = 'metadata';
            document.body.appendChild(this.audio);

            this.audio.addEventListener('timeupdate', () => {
                this.currentTime = this.audio.currentTime;
                this.duration = this.audio.duration || 0;
                this.progress = this.duration > 0 ? (this.currentTime / this.duration) * 100 : 0;
            });

            this.audio.addEventListener('ended', () => {
                this.playing = false;
                this.progress = 0;
                this.currentTime = 0;
            });

            this.audio.addEventListener('loadedmetadata', () => {
                this.duration = this.audio.duration;
            });
        },

        /**
         * Load a track into the floating player
         *
         * @param {string} src - Audio file URL
         * @param {string} title - Track title
         * @param {string} artist - Artist name
         * @param {string} image - Album art URL
         * @param {number} startTime - Start position in seconds
         * @param {boolean} autoplay - Whether to auto-play
         */
        load(src, title, artist, image, startTime = 0, autoplay = false) {
            // Pause all other audio elements on the page
            document.querySelectorAll('audio').forEach(a => {
                if (a !== this.audio) a.pause();
            });

            this.src = src;
            this.title = title;
            this.artist = artist;
            this.image = image;
            this.active = true;

            if (this.audio.src !== src) {
                this.audio.src = src;
                this.audio.load();
            }

            const onCanPlay = () => {
                this.audio.currentTime = startTime;
                if (autoplay) {
                    this.audio.play();
                    this.playing = true;
                }
                this.audio.removeEventListener('canplay', onCanPlay);
            };

            if (this.audio.readyState >= 3) {
                this.audio.currentTime = startTime;
                if (autoplay) {
                    this.audio.play();
                    this.playing = true;
                }
            } else {
                this.audio.addEventListener('canplay', onCanPlay);
            }
        },

        /**
         * Toggle play/pause
         */
        toggle() {
            if (!this.audio || !this.src) return;

            if (this.playing) {
                this.audio.pause();
            } else {
                this.audio.play();
            }
            this.playing = !this.playing;
        },

        /**
         * Seek to position based on click event
         *
         * @param {MouseEvent} e - Click event on progress bar
         */
        seek(e) {
            if (!this.audio || !this.duration) return;

            const rect = e.currentTarget.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.audio.currentTime = percent * this.duration;
        },

        /**
         * Close the floating player
         */
        close() {
            if (this.audio) {
                this.audio.pause();
            }
            this.active = false;
            this.playing = false;
            this.src = null;
            this.progress = 0;
            this.currentTime = 0;
        },

        /**
         * Format seconds to MM:SS
         *
         * @param {number} seconds
         * @returns {string}
         */
        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + secs.toString().padStart(2, '0');
        }
    });

    // Initialize the store
    Alpine.store('audioPlayer').init();
}

/**
 * Auto-register if Alpine is available globally
 */
if (typeof window !== 'undefined' && window.Alpine) {
    window.Alpine.plugin(exports.default);
}
