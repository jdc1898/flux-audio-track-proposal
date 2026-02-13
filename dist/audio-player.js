/**
 * Flux Audio Player
 * Custom Web Components for audio playback with detachable floating player.
 */

(() => {
    class UIAudioPlayer extends HTMLElement {
        audio = null;
        state = {
            active: false, playing: false, progress: 0,
            currentTime: 0, duration: 0, src: null,
            title: null, artist: null, image: null,
        };
        draggable = false;
        isDragging = false;
        hasDragged = false;
        position = { x: null, y: null };
        dragOffset = { x: 0, y: 0 };

        connectedCallback() {
            this.draggable = this.hasAttribute('draggable');
            this.audio = document.createElement('audio');
            this.audio.preload = 'metadata';
            document.body.appendChild(this.audio);
            this.audio.addEventListener('timeupdate', () => this.onTimeUpdate());
            this.audio.addEventListener('ended', () => this.onEnded());
            this.audio.addEventListener('loadedmetadata', () => this.onLoadedMetadata());
            this.render();
            this.bindEvents();
            window.fluxAudioPlayer = this;
        }

        disconnectedCallback() {
            if (this.audio) { this.audio.pause(); this.audio.remove(); }
            window.fluxAudioPlayer = null;
        }

        onTimeUpdate() {
            this.state.currentTime = this.audio.currentTime;
            this.state.duration = this.audio.duration || 0;
            this.state.progress = this.state.duration > 0 ? (this.state.currentTime / this.state.duration) * 100 : 0;
            this.updateDisplay();
            this.dispatchEvent(new CustomEvent('timeupdate', { detail: this.state }));
        }

        onEnded() {
            this.state.playing = false;
            this.state.progress = 0;
            this.state.currentTime = 0;
            this.updateDisplay();
            this.dispatchEvent(new CustomEvent('ended'));
        }

        onLoadedMetadata() {
            this.state.duration = this.audio.duration;
            this.updateDisplay();
        }

        load(src, title, artist, image, startTime = 0, autoplay = false) {
            document.querySelectorAll('audio').forEach(a => { if (a !== this.audio) a.pause(); });
            this.state.src = src;
            this.state.title = title;
            this.state.artist = artist;
            this.state.image = image;
            this.state.active = true;

            if (this.audio.src !== src) {
                this.audio.src = src;
                this.audio.load();
            }

            const onCanPlay = () => {
                this.audio.currentTime = startTime;
                if (autoplay) { this.audio.play(); this.state.playing = true; }
                this.audio.removeEventListener('canplay', onCanPlay);
                this.updateDisplay();
            };

            if (this.audio.readyState >= 3) {
                this.audio.currentTime = startTime;
                if (autoplay) { this.audio.play(); this.state.playing = true; }
                this.updateDisplay();
            } else {
                this.audio.addEventListener('canplay', onCanPlay);
            }

            this.show();
            this.dispatchEvent(new CustomEvent('load', { detail: this.state }));
        }

        toggle() {
            if (!this.audio || !this.state.src) return;
            if (this.state.playing) { this.audio.pause(); } else { this.audio.play(); }
            this.state.playing = !this.state.playing;
            this.updateDisplay();
        }

        seek(percent) {
            if (!this.audio || !this.state.duration) return;
            this.audio.currentTime = percent * this.state.duration;
        }

        seekFromEvent(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.seek(percent);
        }

        close() {
            if (this.audio) { this.audio.pause(); }
            this.state.active = false;
            this.state.playing = false;
            this.state.src = null;
            this.state.progress = 0;
            this.state.currentTime = 0;
            this.resetPosition();
            this.hide();
            this.dispatchEvent(new CustomEvent('close'));
        }

        show() { this.style.display = ''; this.classList.add('active'); }
        hide() { this.classList.remove('active'); }

        resetPosition() {
            this.hasDragged = false;
            this.position = { x: null, y: null };
            this.style.left = '';
            this.style.top = '';
            this.style.transform = '';
        }

        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + secs.toString().padStart(2, '0');
        }

        startDrag(e) {
            if (!this.draggable || e.target.closest('button')) return;
            this.isDragging = true;
            const rect = this.getBoundingClientRect();
            if (!this.hasDragged) { this.position.x = rect.left; this.position.y = rect.top; }
            this.dragOffset.x = e.clientX - this.position.x;
            this.dragOffset.y = e.clientY - this.position.y;
            document.addEventListener('mousemove', this.onDrag);
            document.addEventListener('mouseup', this.stopDrag);
            e.preventDefault();
        }

        onDrag = (e) => {
            if (!this.isDragging) return;
            this.hasDragged = true;
            const maxX = window.innerWidth - this.offsetWidth;
            const maxY = window.innerHeight - this.offsetHeight;
            this.position.x = Math.max(0, Math.min(e.clientX - this.dragOffset.x, maxX));
            this.position.y = Math.max(0, Math.min(e.clientY - this.dragOffset.y, maxY));
            this.style.left = this.position.x + 'px';
            this.style.top = this.position.y + 'px';
            this.style.transform = 'none';
        };

        stopDrag = () => {
            this.isDragging = false;
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('mouseup', this.stopDrag);
        };

        render() {
            this.innerHTML = `
                <div class="ui-audio-player-content">
                    <div class="ui-audio-player-image" data-action="toggle">
                        <img src="" alt="" />
                        <div class="ui-audio-player-image-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M19.952 1.651a.75.75 0 0 1 .298.599V16.303a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.403-4.909l2.311-.66a1.5 1.5 0 0 0 1.088-1.442V6.994l-9 2.572v9.737a3 3 0 0 1-2.176 2.884l-1.32.377a2.553 2.553 0 1 1-1.402-4.909l2.31-.66a1.5 1.5 0 0 0 1.088-1.442V5.25a.75.75 0 0 1 .544-.721l10.5-3a.75.75 0 0 1 .658.122Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ui-audio-player-overlay">
                            <svg class="play-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clip-rule="evenodd" /></svg>
                            <svg class="pause-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M6.75 5.25a.75.75 0 0 1 .75-.75H9a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H7.5a.75.75 0 0 1-.75-.75V5.25zm7.5 0A.75.75 0 0 1 15 4.5h1.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H15a.75.75 0 0 1-.75-.75V5.25z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                    <div class="ui-audio-player-info" data-action="drag">
                        <div class="ui-audio-player-title"></div>
                        <div class="ui-audio-player-artist"></div>
                    </div>
                    <div class="ui-audio-player-time">
                        <span class="current">0:00</span><span class="separator">/</span><span class="duration">0:00</span>
                    </div>
                    <button type="button" class="ui-audio-player-close" data-action="close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                    </button>
                    <div class="ui-audio-player-progress" data-action="seek">
                        <div class="ui-audio-player-progress-bar"></div>
                    </div>
                </div>
            `;
        }

        bindEvents() {
            this.querySelector('[data-action="toggle"]')?.addEventListener('click', () => this.toggle());
            this.querySelector('[data-action="close"]')?.addEventListener('click', () => this.close());
            this.querySelector('[data-action="seek"]')?.addEventListener('click', (e) => this.seekFromEvent(e));
            if (this.draggable) {
                this.querySelector('[data-action="drag"]')?.addEventListener('mousedown', (e) => this.startDrag(e));
            }
        }

        updateDisplay() {
            const img = this.querySelector('.ui-audio-player-image img');
            const placeholder = this.querySelector('.ui-audio-player-image-placeholder');
            const title = this.querySelector('.ui-audio-player-title');
            const artist = this.querySelector('.ui-audio-player-artist');
            const current = this.querySelector('.ui-audio-player-time .current');
            const duration = this.querySelector('.ui-audio-player-time .duration');
            const progressBar = this.querySelector('.ui-audio-player-progress-bar');

            if (img && placeholder) {
                if (this.state.image) {
                    img.src = this.state.image; img.alt = this.state.title || '';
                    img.style.display = ''; placeholder.style.display = 'none';
                } else {
                    img.style.display = 'none'; placeholder.style.display = '';
                }
            }
            if (title) title.textContent = this.state.title || 'Unknown Track';
            if (artist) artist.textContent = this.state.artist || 'Unknown Artist';
            if (current) current.textContent = this.formatTime(this.state.currentTime);
            if (duration) duration.textContent = this.formatTime(this.state.duration);
            if (progressBar) progressBar.style.width = this.state.progress + '%';
            this.classList.toggle('playing', this.state.playing);
        }
    }

    class UIAudioTrack extends HTMLElement {
        audio = null;
        state = { playing: false, progress: 0, currentTime: 0, duration: 0, detached: false };

        connectedCallback() {
            this.audio = this.querySelector('audio');
            if (this.audio) {
                this.audio.addEventListener('timeupdate', () => this.onTimeUpdate());
                this.audio.addEventListener('ended', () => this.onEnded());
                this.audio.addEventListener('loadedmetadata', () => this.onLoadedMetadata());
            }
            this.bindEvents();
            window.addEventListener('fluxAudioPlayer:load', (e) => this.onGlobalLoad(e));
            window.addEventListener('fluxAudioPlayer:close', () => this.onGlobalClose());
        }

        get src() { return this.audio?.src || this.dataset.src; }
        get title() { return this.dataset.title; }
        get artist() { return this.dataset.artist; }
        get image() { return this.dataset.image; }

        onTimeUpdate() {
            if (this.state.detached) return;
            this.state.currentTime = this.audio.currentTime;
            this.state.duration = this.audio.duration || 0;
            this.state.progress = this.state.duration > 0 ? (this.state.currentTime / this.state.duration) * 100 : 0;
            this.updateWaveform();
            this.updateTime();
        }

        onEnded() {
            if (this.state.detached) return;
            this.state.playing = false;
            this.state.progress = 0;
            this.classList.remove('playing');
        }

        onLoadedMetadata() { this.state.duration = this.audio.duration; }
        onGlobalLoad(e) { if (e.detail.src === this.src) { this.state.detached = true; this.classList.add('detached'); } }
        onGlobalClose() { if (this.state.detached) { this.state.detached = false; this.classList.remove('detached'); } }

        toggle() {
            if (this.state.detached && window.fluxAudioPlayer) { window.fluxAudioPlayer.toggle(); return; }
            if (this.state.playing) { this.audio?.pause(); } else { document.querySelectorAll('audio').forEach(a => a.pause()); this.audio?.play(); }
            this.state.playing = !this.state.playing;
            this.classList.toggle('playing', this.state.playing);
        }

        seek(percent) {
            if (this.state.detached && window.fluxAudioPlayer) { window.fluxAudioPlayer.seek(percent); return; }
            if (this.audio?.duration) { this.audio.currentTime = percent * this.audio.duration; }
        }

        seekFromEvent(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            this.seek((e.clientX - rect.left) / rect.width);
        }

        detach() {
            if (!window.fluxAudioPlayer) { console.warn('Audio player not found. Include <flux:audio-track.player /> in your layout.'); return; }
            window.fluxAudioPlayer.load(this.src, this.title, this.artist, this.image, this.audio?.currentTime || 0, this.state.playing);
            this.audio?.pause();
            this.state.detached = true;
            this.classList.add('detached');
        }

        bindEvents() {
            this.querySelector('[data-action="toggle"]')?.addEventListener('click', () => this.toggle());
            this.querySelector('[data-action="seek"]')?.addEventListener('click', (e) => this.seekFromEvent(e));
            this.querySelector('[data-action="detach"]')?.addEventListener('click', () => this.detach());
        }

        updateWaveform() {
            const bars = this.querySelectorAll('[data-waveform-bar]');
            bars.forEach((bar, index) => { bar.classList.toggle('active', (index / bars.length) * 100 < this.state.progress); });
        }

        updateTime() {
            const timeEl = this.querySelector('[data-time]');
            if (timeEl && (this.state.playing || this.state.currentTime > 0)) { timeEl.textContent = this.formatTime(this.state.currentTime); }
        }

        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            return Math.floor(seconds / 60) + ':' + Math.floor(seconds % 60).toString().padStart(2, '0');
        }
    }

    if (!customElements.get('ui-audio-player')) customElements.define('ui-audio-player', UIAudioPlayer);
    if (!customElements.get('ui-audio-track')) customElements.define('ui-audio-track', UIAudioTrack);
})();
