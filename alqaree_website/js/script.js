// YouTube Embed Enhancement Function
function enhanceYouTubeEmbed(embedCode) {
    // Extract Video ID using regex
    let videoId = null;

    // Check if it's already an iframe
    const iframeMatch = embedCode.match(/<iframe[^>]*src="([^"]*)"/i);
    let url = iframeMatch ? iframeMatch[1] : embedCode;

    // Extract video ID from various YouTube URL formats
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
        /youtube\.com\/v\/([^&\n?#]+)/,
        /youtube\.com\/embed\/([^&\n?#]+)/
    ];

    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            videoId = match[1];
            break;
        }
    }

    if (!videoId) {
        // Return original code if extraction fails
        return embedCode;
    }

    // Build new embed URL with required parameters
    const embedUrl = `https://www.youtube-nocookie.com/embed/${videoId}?rel=0&modestbranding=1&showinfo=0&iv_load_policy=3&disablekb=1&fs=0&playsinline=1`;

    // Create new iframe with controlled dimensions
    const newEmbed = `<iframe src="${embedUrl}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation allow-forms" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;

    return newEmbed;
}

// Fullscreen functionality
function setupFullscreen(buttonId, modalId) {
    const fullscreenBtn = document.getElementById(buttonId);
    const modal = document.getElementById(modalId);
    const modalBody = modal.querySelector('.modal-body');
    const videoContainer = modal.querySelector('.video-container');

    if (fullscreenBtn && modal && modalBody && videoContainer) {
        fullscreenBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent modal close

            if (!document.fullscreenElement) {
                // Enter fullscreen
                if (videoContainer.requestFullscreen) {
                    videoContainer.requestFullscreen();
                } else if (videoContainer.webkitRequestFullscreen) { // Safari
                    videoContainer.webkitRequestFullscreen();
                } else if (videoContainer.msRequestFullscreen) { // IE11
                    videoContainer.msRequestFullscreen();
                }

                modalBody.classList.add('fullscreen-video');
                fullscreenBtn.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z" fill="currentColor"/>
                    </svg>
                `;
                fullscreenBtn.title = "خروج من ملء الشاشة";
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { // Safari
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) { // IE11
                    document.msExitFullscreen();
                }

                modalBody.classList.remove('fullscreen-video');
                fullscreenBtn.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" fill="currentColor"/>
                    </svg>
                `;
                fullscreenBtn.title = "ملء الشاشة";
            }
        });

        // Handle fullscreen change events
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('msfullscreenchange', handleFullscreenChange);

        function handleFullscreenChange() {
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                // Exited fullscreen
                modalBody.classList.remove('fullscreen-video');
                fullscreenBtn.innerHTML = `
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" fill="currentColor"/>
                    </svg>
                `;
                fullscreenBtn.title = "ملء الشاشة";
            }
        }
    }
}

// Add JavaScript for active navigation link and Islamic effects
document.addEventListener('DOMContentLoaded', () => {
    // Highlight active navigation link
    const navLinks = document.querySelectorAll('.nav-links a');
    const currentPath = window.location.pathname.split('/').pop();

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }

        // Ensure navigation links work properly
        link.addEventListener('click', function(e) {
            // Don't prevent default for navigation links
            // This ensures they work even if other event listeners try to interfere
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                // For target="_blank" links, let them work naturally
                if (this.getAttribute('target') === '_blank') {
                    window.open(href, '_blank');
                    e.preventDefault();
                }
            }
        });
    });

    // Add Islamic symbols floating effect
    const body = document.body;
    body.insertAdjacentHTML('beforeend', '<div class="islamic-symbols"></div>');

    // Mouse follow effect
    const mouseFollower = document.createElement('div');
    mouseFollower.className = 'mouse-follow';
    document.body.appendChild(mouseFollower);

    let mouseX = 0;
    let mouseY = 0;
    let followerX = 0;
    let followerY = 0;

    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    function updateFollower() {
        const dx = mouseX - followerX;
        const dy = mouseY - followerY;
        followerX += dx * 0.1;
        followerY += dy * 0.1;

        mouseFollower.style.left = followerX - 10 + 'px';
        mouseFollower.style.top = followerY - 10 + 'px';

        requestAnimationFrame(updateFollower);
    }
    updateFollower();

    // Fade in on scroll effect
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-on-scroll');
            }
        });
    }, observerOptions);

    // Observe all cards and important elements
    document.querySelectorAll('.feature-item, .recitation-item, .sermon-item, .link-card').forEach(card => {
        observer.observe(card);
    });

    // Add loading animation to buttons
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });

    // Sacred text typing effect for important elements
    const sacredTexts = document.querySelectorAll('.sacred-text');
    sacredTexts.forEach((text, index) => {
        text.style.opacity = '0';
        setTimeout(() => {
            text.style.opacity = '1';
            text.style.animation = 'quran-reveal 1s ease-out';
        }, index * 200);
    });

    // Function to enhance YouTube embed for modal
    function enhanceYouTubeEmbedForModal(embedCode) {
        if (!embedCode) return '';

        let enhancedCode = embedCode;

        // Convert to privacy-enhanced mode
        enhancedCode = enhancedCode.replace('youtube.com/embed/', 'youtube-nocookie.com/embed/');

        // Add parameters if not present
        if (enhancedCode.includes('src="') && !enhancedCode.includes('modestbranding=1')) {
            enhancedCode = enhancedCode.replace(
                'src="',
                'src="?modestbranding=1&rel=0&showinfo=0&iv_load_policy=3&disablekb=1&playsinline=1&enablejsapi=1&'
            );
        }

        // Add security attributes
        enhancedCode = enhancedCode.replace(
            '<iframe',
            '<iframe loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation allow-forms" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen'
        );

        return enhancedCode;
    }

    // Generic Modal functionality for both tilawat and hekum pages
    const openModalCards = document.querySelectorAll('.open-modal-card');
    const recitationModal = document.getElementById('recitationModal');
    const sermonModal = document.getElementById('sermonModal');

    openModalCards.forEach(card => {
        card.addEventListener('click', (e) => {
            // Don't interfere with navigation links
            if (e.target.closest('.nav-links a')) {
                return;
            }

            // Prevent opening modal if clicking on a link or button inside the card
            // This ensures clicking the card itself opens the modal, not internal links
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' ||
                e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            e.preventDefault();
            const data = e.currentTarget.dataset; // Get data attributes from the clicked card

            let targetModal, modalTitle, modalVideoContainer, modalDescription;

            // Determine which modal to open and which elements to populate
            if (card.classList.contains('recitation-item')) {
                targetModal = recitationModal;
                modalTitle = document.getElementById('modalTitle');
                modalVideoContainer = document.getElementById('modalVideoContainer');
                modalDescription = document.getElementById('modalDescription');

                // Debug check for recitation modal elements
                console.log('Recitation modal elements:', {
                    modalTitle: modalTitle,
                    modalVideoContainer: modalVideoContainer,
                    modalDescription: modalDescription
                });

                if (modalTitle && modalVideoContainer && modalDescription) {
                    console.log('Populating recitation modal with data:', data);

                    modalTitle.textContent = data.title || '';
                    console.log('Set modalTitle:', data.title);
                    const enhancedVideo = enhanceYouTubeEmbed(data.youtube_embed_code || '');
                    modalVideoContainer.innerHTML = enhancedVideo;
                    console.log('Original video code:', data.youtube_embed_code);
                    console.log('Enhanced video code:', enhancedVideo);

                    const modalSurahName = document.getElementById('modalSurahName');
                    const modalReciterName = document.getElementById('modalReciterName');
                    const modalVideoDuration = document.getElementById('modalVideoDuration');
                    const modalPublishDate = document.getElementById('modalPublishDate');

                    console.log('Modal elements found:', {
                        modalSurahName: modalSurahName,
                        modalReciterName: modalReciterName,
                        modalVideoDuration: modalVideoDuration,
                        modalPublishDate: modalPublishDate
                    });

                    if (modalSurahName) {
                        modalSurahName.textContent = data.surah_name || '';
                        console.log('Set modalSurahName:', data.surah_name);
                    }
                    if (modalReciterName) {
                        modalReciterName.textContent = data.reciter_name || '';
                        console.log('Set modalReciterName:', data.reciter_name);
                    }
                    if (modalVideoDuration) {
                        modalVideoDuration.textContent = data.video_duration || '';
                        console.log('Set modalVideoDuration:', data.video_duration);
                    }
                    if (modalPublishDate) {
                        modalPublishDate.textContent = data.publish_date || '';
                        console.log('Set modalPublishDate:', data.publish_date);
                    }

                    modalDescription.innerHTML = data.description || '';
                    console.log('Set modalDescription:', data.description);

                    // Setup fullscreen functionality for recitation modal
                    setupFullscreen('modalFullscreenBtn', 'recitationModal');
                }
            } else if (card.classList.contains('sermon-item')) {
                targetModal = sermonModal;
                modalTitle = document.getElementById('modalSermonTitle');
                modalVideoContainer = document.getElementById('modalSermonVideoContainer');
                modalDescription = document.getElementById('modalSermonDescription');

                // Debug check for sermon modal elements
                console.log('Sermon modal elements:', {
                    modalTitle: modalTitle,
                    modalVideoContainer: modalVideoContainer,
                    modalDescription: modalDescription
                });

                if (modalTitle && modalVideoContainer && modalDescription) {
                    console.log('Populating sermon modal with data:', data);

                    modalTitle.textContent = data.title || '';
                    console.log('Set modalTitle:', data.title);
                    const enhancedVideo = enhanceYouTubeEmbed(data.youtube_embed_code || '');
                    modalVideoContainer.innerHTML = enhancedVideo;
                    console.log('Original video code:', data.youtube_embed_code);
                    console.log('Enhanced video code:', enhancedVideo);

                    const modalSpeakerName = document.getElementById('modalSpeakerName');
                    const modalSermonVideoDuration = document.getElementById('modalSermonVideoDuration');
                    const modalSermonPublishDate = document.getElementById('modalSermonPublishDate');

                    console.log('Sermon modal elements found:', {
                        modalSpeakerName: modalSpeakerName,
                        modalSermonVideoDuration: modalSermonVideoDuration,
                        modalSermonPublishDate: modalSermonPublishDate
                    });

                    if (modalSpeakerName) {
                        modalSpeakerName.textContent = data.speaker_name || '';
                        console.log('Set modalSpeakerName:', data.speaker_name);
                    }
                    if (modalSermonVideoDuration) {
                        modalSermonVideoDuration.textContent = data.video_duration || '';
                        console.log('Set modalSermonVideoDuration:', data.video_duration);
                    }
                    if (modalSermonPublishDate) {
                        modalSermonPublishDate.textContent = data.publish_date || '';
                        console.log('Set modalSermonPublishDate:', data.publish_date);
                    }

                    modalDescription.innerHTML = data.description || '';
                    console.log('Set modalDescription:', data.description);

                    // Setup fullscreen functionality for sermon modal
                    setupFullscreen('modalSermonFullscreenBtn', 'sermonModal');
                }
            }

            // Open the determined modal if it exists
                if (targetModal) {
                    // Enhance modal video with privacy settings
                    if (modalVideoContainer && data.youtubeEmbedCode) {
                        const enhancedEmbed = enhanceYouTubeEmbedForModal(data.youtubeEmbedCode);
                        modalVideoContainer.innerHTML = enhancedEmbed;
                    }

                    targetModal.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Prevent scrolling on the body

                    // Add event listeners for closing the modal
                    const closeBtn = targetModal.querySelector('.close-btn');
                    if (closeBtn) {
                        closeBtn.onclick = () => {
                            targetModal.classList.remove('active');
                            if (modalVideoContainer) modalVideoContainer.innerHTML = ''; // Stop video playback
                            document.body.style.overflow = 'auto'; // Restore scrolling
                        };
                    }

                    window.onclick = (event) => {
                        if (event.target === targetModal) {
                            targetModal.classList.remove('active');
                            if (modalVideoContainer) modalVideoContainer.innerHTML = ''; // Stop video playback
                            document.body.style.overflow = 'auto'; // Restore scrolling
                            window.onclick = null; // Remove this specific listener after use
                        }
                    };
            }
        });
    });

    // Close buttons for both modals, if they exist outside the dynamic click handler
    const allCloseBtns = document.querySelectorAll('.modal .close-btn');
    allCloseBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalToClose = btn.closest('.modal');
            if (modalToClose) {
                modalToClose.classList.remove('active');
                const videoContainer = modalToClose.querySelector('.video-container');
                if (videoContainer) videoContainer.innerHTML = '';
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Close modals by clicking outside (general handler)
    window.addEventListener('click', (event) => {
        // Don't close modals if clicking on navigation links
        if (event.target.closest('.nav-links a')) {
            return;
        }

        if (recitationModal && event.target === recitationModal) {
            recitationModal.classList.remove('active');
            const videoContainer = recitationModal.querySelector('.video-container');
            if (videoContainer) videoContainer.innerHTML = '';
            document.body.style.overflow = 'auto';
        } else if (sermonModal && event.target === sermonModal) {
            sermonModal.classList.remove('active');
            const videoContainer = sermonModal.querySelector('.video-container');
            if (videoContainer) videoContainer.innerHTML = '';
            document.body.style.overflow = 'auto';
        }
    });

    // Article Modal functionality
    const articleModal = document.getElementById('articleModal');
    const openArticleCards = document.querySelectorAll('.article-item.open-modal-card');

    openArticleCards.forEach(card => {
        card.addEventListener('click', (e) => {
            // Prevent opening modal if clicking on a link or button inside the card
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') {
                return;
            }
            e.preventDefault();

            const data = e.currentTarget.dataset;

            // Populate modal with article data
            document.getElementById('articleModalTitle').textContent = data.title || '';
            document.getElementById('articleModalAuthor').textContent = data.author || '';
            document.getElementById('articleModalCategory').textContent = data.category || '';
            document.getElementById('articleModalDate').textContent = data.publishDate || '';
            document.getElementById('articleModalContent').innerHTML = data.content || '';

            // Open modal
            if (articleModal) {
                articleModal.classList.add('active');
                document.body.style.overflow = 'hidden';

                // Add event listeners for closing the modal
                const closeBtn = articleModal.querySelector('.close-btn');
                if (closeBtn) {
                    closeBtn.onclick = () => {
                        articleModal.classList.remove('active');
                        document.body.style.overflow = 'auto';
                    };
                }

                // Close modal when clicking outside
                const closeModalHandler = (event) => {
                    if (event.target === articleModal) {
                        articleModal.classList.remove('active');
                        document.body.style.overflow = 'auto';
                        window.removeEventListener('click', closeModalHandler);
                    }
                };
                window.addEventListener('click', closeModalHandler);
            }
        });
    });

    // Random Article Auto-Refresh Functionality
    const randomArticleContainer = document.getElementById('randomArticlesContainer');
    const singleArticleCard = document.getElementById('singleArticleCard');
    const articleTitle = document.getElementById('articleTitle');
    const articleExcerpt = document.getElementById('articleExcerpt');
    const articleAuthor = document.getElementById('articleAuthor');
    const articleDate = document.getElementById('articleDate');
    const readArticleBtn = document.getElementById('readArticleBtn');

    if (randomArticleContainer && singleArticleCard) {
        // Function to load random article
        function loadRandomArticle() {
            fetch('get_random_article.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.article) {
                        const article = data.article;

                        // Update article content with smooth animation
                        singleArticleCard.style.opacity = '0';
                        singleArticleCard.style.transform = 'translateY(20px)';

                        setTimeout(() => {
                            // Update article data
                            if (articleTitle) articleTitle.textContent = article.title || '';
                            if (articleExcerpt) articleExcerpt.textContent = article.excerpt || '';
                            if (articleAuthor) articleAuthor.textContent = 'بقلم: ' + (article.author_name || '');
                            if (articleDate) articleDate.textContent = article.publish_date || '';

                            // Update button data attributes
                            if (readArticleBtn) {
                                readArticleBtn.setAttribute('data-title', article.title || '');
                                readArticleBtn.setAttribute('data-author', article.author_name || '');
                                readArticleBtn.setAttribute('data-category', article.category || '');
                                readArticleBtn.setAttribute('data-date', article.publish_date || '');
                                readArticleBtn.setAttribute('data-content', article.content || '');
                            }

                            // Update category in header
                            const categoryElement = singleArticleCard.querySelector('.article-category');
                            if (categoryElement) {
                                categoryElement.textContent = article.category || 'عام';
                            }

                            // Fade in animation
                            singleArticleCard.style.opacity = '1';
                            singleArticleCard.style.transform = 'translateY(0)';

                            console.log('Random article updated:', article.title);
                        }, 300);
                    } else {
                        console.error('Failed to load random article:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading random article:', error);
                });
        }

        // Auto-refresh every 3 minutes (180000 milliseconds)
        setInterval(loadRandomArticle, 180000);

        // Initial load after 3 minutes
        setTimeout(loadRandomArticle, 180000);
    }
});
