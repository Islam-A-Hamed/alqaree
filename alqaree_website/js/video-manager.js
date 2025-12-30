/**
 * Advanced YouTube Video Manager
 * Handles video parsing, lazy loading, and UX optimization
 */

class VideoManager {
    constructor() {
        this.currentPlayingVideo = null;
        this.init();
    }

    init() {
        this.initializeVideos();
        this.setupGlobalEventListeners();
    }

    /**
     * Extract video ID from various YouTube URL formats
     */
    extractVideoId(input) {
        if (!input) return null;

        // Handle iframe embed code
        if (input.includes('<iframe')) {
            const srcMatch = input.match(/src="([^"]*)"/);
            if (srcMatch) {
                input = srcMatch[1];
            }
        }

        // Remove query parameters and fragments
        const url = input.split('?')[0].split('#')[0];

        // Regular expressions for different YouTube URL formats
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
            /youtube\.com\/v\/([^&\n?#]+)/,
            /youtube\.com\/embed\/([^&\n?#]+)/,
            /youtube\.com\/v\/([^&\n?#]+)/
        ];

        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match && match[1]) {
                return match[1];
            }
        }

        return null;
    }

    /**
     * Build optimized YouTube embed URL
     */
    buildEmbedUrl(videoId) {
        const baseUrl = `https://www.youtube-nocookie.com/embed/${videoId}`;
        const params = new URLSearchParams({
            modestbranding: '1',
            rel: '0',
            showinfo: '0',
            iv_load_policy: '3',
            disablekb: '1',
            fs: '0',
            playsinline: '1',
            enablejsapi: '1',
            autoplay: '0'
        });

        return `${baseUrl}?${params.toString()}`;
    }

    /**
     * Get video thumbnail URL
     */
    getThumbnailUrl(videoId, quality = 'maxresdefault') {
        return `https://img.youtube.com/vi/${videoId}/${quality}.jpg`;
    }

    /**
     * Create video thumbnail element
     */
    createThumbnailElement(videoId, title = '') {
        const thumbnailUrl = this.getThumbnailUrl(videoId);
        const fallbackUrl = this.getThumbnailUrl(videoId, 'hqdefault');

        return `
            <div class="video-thumbnail" data-video-id="${videoId}">
                <img
                    src="${thumbnailUrl}"
                    alt="${title || 'فيديو يوتيوب'}"
                    onerror="this.src='${fallbackUrl}'"
                    loading="lazy"
                >
                <div class="play-button">
                    <svg width="68" height="48" viewBox="0 0 68 48">
                        <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#FF0000"/>
                        <path d="M 45,24 27,14 27,34" fill="#FFFFFF"/>
                    </svg>
                </div>
                <div class="thumbnail-overlay"></div>
            </div>
        `;
    }

    /**
     * Create video iframe element
     */
    createIframeElement(videoId, title = '') {
        const embedUrl = this.buildEmbedUrl(videoId);

        return `
            <iframe
                src="${embedUrl}"
                title="${title || 'فيديو يوتيوب'}"
                frameborder="0"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                sandbox="allow-scripts allow-same-origin allow-presentation allow-forms"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
            ></iframe>
        `;
    }

    /**
     * Initialize all video containers on the page
     * Note: Disabled for direct video display mode
     */
    initializeVideos() {
        // Videos are now displayed directly without thumbnail system
        // This maintains backward compatibility but doesn't interfere with direct display
        console.log('Video Manager: Direct display mode active');
    }

    /**
     * Load video iframe when thumbnail is clicked
     */
    loadVideo(container, videoId, title) {
        // Stop any currently playing video
        this.stopCurrentVideo();

        // Show loading state
        this.showVideoLoading(container);

        // Create and load iframe
        const iframeHTML = this.createIframeElement(videoId, title);
        container.innerHTML = iframeHTML;

        // Set as current playing video
        this.currentPlayingVideo = container;

        // Listen for video ready
        const iframe = container.querySelector('iframe');
        iframe.addEventListener('load', () => {
            this.hideVideoLoading(container);
        });
    }

    /**
     * Stop currently playing video
     */
    stopCurrentVideo() {
        if (!this.currentPlayingVideo) return;

        const iframe = this.currentPlayingVideo.querySelector('iframe');
        if (iframe && iframe.contentWindow) {
            // Try to pause the video using YouTube API
            try {
                iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            } catch (e) {
                console.log('Could not pause video:', e);
            }
        }

        // Convert back to thumbnail
        const videoId = iframe ? this.extractVideoId(iframe.src) : null;
        if (videoId) {
            const card = this.currentPlayingVideo.closest('.recitation-item, .sermon-item');
            const title = card ? card.querySelector('h3')?.textContent || '' : '';
            this.currentPlayingVideo.innerHTML = this.createThumbnailElement(videoId, title);

            // Re-initialize the thumbnail click event
            const thumbnail = this.currentPlayingVideo.querySelector('.video-thumbnail');
            thumbnail.addEventListener('click', () => {
                this.loadVideo(this.currentPlayingVideo, videoId, title);
            });
        }

        this.currentPlayingVideo = null;
    }

    /**
     * Show loading state
     */
    showVideoLoading(container) {
        const existingLoading = container.querySelector('.video-loading-state');
        if (existingLoading) return;

        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'video-loading-state';
        loadingDiv.innerHTML = `
            <div class="loading-skeleton">
                <div class="skeleton-pulse"></div>
                <div class="play-button-placeholder">
                    <div class="skeleton-circle"></div>
                </div>
            </div>
        `;

        container.appendChild(loadingDiv);
    }

    /**
     * Hide loading state
     */
    hideVideoLoading(container) {
        const loadingState = container.querySelector('.video-loading-state');
        if (loadingState) {
            loadingState.remove();
        }
    }

    /**
     * Setup global event listeners
     */
    setupGlobalEventListeners() {
        // Listen for YouTube API messages
        window.addEventListener('message', (event) => {
            if (event.origin !== 'https://www.youtube.com' &&
                event.origin !== 'https://www.youtube-nocookie.com') return;

            try {
                const data = JSON.parse(event.data);
                if (data.event === 'onStateChange' && data.info === 1) {
                    // Video started playing, stop other videos
                    this.handleVideoStateChange(event.source);
                }
            } catch (e) {
                // Ignore non-JSON messages
            }
        });

        // Handle page visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopCurrentVideo();
            }
        });
    }

    /**
     * Handle video state changes
     */
    handleVideoStateChange(source) {
        // Find the container that contains this iframe
        const iframes = document.querySelectorAll('.video-container iframe');
        let playingContainer = null;

        iframes.forEach(iframe => {
            if (iframe.contentWindow === source) {
                playingContainer = iframe.closest('.video-container');
            }
        });

        // If a different video is playing, stop it
        if (playingContainer && playingContainer !== this.currentPlayingVideo) {
            this.stopCurrentVideo();
            this.currentPlayingVideo = playingContainer;
        }
    }
}

// Static utility functions for global use
VideoManager.parseYouTubeUrl = function(input) {
    const manager = new VideoManager();
    return manager.extractVideoId(input);
};

VideoManager.getEmbedUrl = function(videoId) {
    const manager = new VideoManager();
    return manager.buildEmbedUrl(videoId);
};

VideoManager.getThumbnail = function(videoId, quality = 'maxresdefault') {
    const manager = new VideoManager();
    return manager.getThumbnailUrl(videoId, quality);
};

// Initialize when DOM is ready
// Note: Video manager is available but thumbnails are disabled for direct video display
document.addEventListener('DOMContentLoaded', () => {
    window.videoManager = new VideoManager();
    console.log('Video System: Direct display mode - 3 videos per row');
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VideoManager;
}
