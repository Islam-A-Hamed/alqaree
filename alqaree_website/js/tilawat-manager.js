// Random Tilawat Video Functionality
console.log('Tilawat manager loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tilawat manager DOMContentLoaded');
    const tilawatContent = document.getElementById('randomTilawatContent');
    const tilawatLoading = document.getElementById('tilawatLoading');
    const tilawatError = document.getElementById('tilawatError');
    const tilawatVideoContainer = document.getElementById('tilawatVideoSection');
    const tilawatTitle = document.getElementById('tilawatTitle');
    const tilawatSurahName = document.getElementById('tilawatSurahName');
    const tilawatReciterName = document.getElementById('tilawatReciterName');
    const tilawatDuration = document.getElementById('tilawatDuration');
    const tilawatPublishDate = document.getElementById('tilawatPublishDate');
    const tilawatDescription = document.getElementById('tilawatDescription');
    const showMoreDescriptionBtn = document.getElementById('showMoreDescription');
    const refreshTilawatBtn = document.getElementById('refreshTilawat');
    const retryTilawatBtn = document.getElementById('retryTilawat');

    // Check if elements exist before adding event listeners
    if (!refreshTilawatBtn || !retryTilawatBtn) {
        console.warn('Tilawat elements not found, skipping event listeners');
        return;
    }

    let refreshTilawatInterval;

    // Function to show loading state
    function showTilawatLoading() {
        tilawatLoading.style.display = 'flex';
        tilawatContent.style.display = 'none';
        tilawatError.style.display = 'none';
    }

    // Function to show content
    function showTilawatContent() {
        if (tilawatLoading) tilawatLoading.style.display = 'none';
        if (tilawatContent) tilawatContent.style.display = 'block';
        if (tilawatError) tilawatError.style.display = 'none';
    }

    // Function to show error
    function showTilawatError() {
        tilawatLoading.style.display = 'none';
        tilawatContent.style.display = 'none';
        tilawatError.style.display = 'flex';
    }

    // Function to load random tilawat
    function loadRandomTilawat() {
        showTilawatLoading();

        fetch('get_random_tilawat.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.tilawat) {
                    const tilawat = data.tilawat;

                    // Update content with smooth animation
                    tilawatContent.style.opacity = '0';
                    tilawatContent.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        // إخفاء رسالة عدم التوقر وإظهار الفيديو
                        const noVideoMsg = document.getElementById('tilawatNoVideo');
                        const videoSection = document.getElementById('tilawatVideoSection');
                        const infoSection = document.getElementById('tilawatInfoSection');

                        if (noVideoMsg) noVideoMsg.style.display = 'none';
                        if (videoSection) videoSection.style.display = 'block';
                        if (infoSection) infoSection.style.display = 'block';

                        if (tilawatVideoContainer) {
                            tilawatVideoContainer.innerHTML = tilawat.youtube_embed_code;

                            // Add error handling for iframe loading
                            const iframe = tilawatVideoContainer.querySelector('iframe');
                            if (iframe) {
                                iframe.addEventListener('load', function() {
                                    this.classList.add('loaded');
                                });

                                iframe.addEventListener('error', function() {
                                    console.warn('Tilawat iframe failed to load, showing fallback message');
                                    const noVideoMsg = document.getElementById('tilawatNoVideo');
                                    const videoSection = document.getElementById('tilawatVideoSection');
                                    const infoSection = document.getElementById('tilawatInfoSection');

                                    if (noVideoMsg) noVideoMsg.style.display = 'block';
                                    if (videoSection) videoSection.style.display = 'none';
                                    if (infoSection) infoSection.style.display = 'none';
                                });
                            }
                        }
                        tilawatTitle.textContent = tilawat.title;
                        tilawatSurahName.textContent = tilawat.surah_name;
                        tilawatReciterName.textContent = tilawat.reciter_name;
                        tilawatDuration.textContent = tilawat.video_duration;
                        tilawatPublishDate.textContent = tilawat.publish_date;

                        // Handle description with show more functionality
                        const fullDescriptionTilawat = tilawat.description || '';
                        const shortDescriptionTilawat = fullDescriptionTilawat.substring(0, 250);

                        if (fullDescriptionTilawat.length > 250) {
                            tilawatDescription.innerHTML = shortDescriptionTilawat + '...';
                            showMoreDescriptionBtn.style.display = 'inline-flex';
                            showMoreDescriptionBtn.textContent = 'عرض المزيد';
                            showMoreDescriptionBtn.onclick = function() {
                                if (tilawatDescription.innerHTML === shortDescriptionTilawat + '...') {
                                    tilawatDescription.innerHTML = fullDescriptionTilawat;
                                    showMoreDescriptionBtn.textContent = 'عرض أقل';
                                } else {
                                    tilawatDescription.innerHTML = shortDescriptionTilawat + '...';
                                    showMoreDescriptionBtn.textContent = 'عرض المزيد';
                                }
                            };
                        } else {
                            tilawatDescription.innerHTML = fullDescriptionTilawat;
                            showMoreDescriptionBtn.style.display = 'none';
                        }

                        tilawatContent.style.opacity = '1';
                        tilawatContent.style.transform = 'translateY(0)';
                        showTilawatContent();
                    }, 300);
                } else {
                    throw new Error(data.error || 'فشل في تحميل التلاوة');
                }
            })
            .catch(error => {
                console.error('Error loading tilawat:', error);
                console.error('Error details:', error.message);

                // إظهار رسالة عدم التوقر بدلاً من رسالة الخطأ
                if (tilawatLoading) tilawatLoading.style.display = 'flex';
                setTimeout(() => {
                    const noVideoMsg = document.getElementById('tilawatNoVideo');
                    const videoSection = document.getElementById('tilawatVideoSection');
                    const infoSection = document.getElementById('tilawatInfoSection');
                    const loading = document.getElementById('tilawatLoading');
                    const content = document.getElementById('randomTilawatContent');

                    if (noVideoMsg) noVideoMsg.style.display = 'block';
                    if (videoSection) videoSection.style.display = 'none';
                    if (infoSection) infoSection.style.display = 'none';
                    if (loading) loading.style.display = 'none';
                    if (content) content.style.display = 'block';
                }, 500);
            });
    }

    // Make function globally available
    window.loadRandomTilawat = loadRandomTilawat;

    // Event listeners
    refreshTilawatBtn.addEventListener('click', function() {
        loadRandomTilawat();
        // Reset the automatic refresh timer
        clearInterval(refreshTilawatInterval);
        refreshTilawatInterval = setInterval(loadRandomTilawat, 180000); // 3 minutes
    });

    retryTilawatBtn.addEventListener('click', loadRandomTilawat);

    // Initial load
    loadRandomTilawat();

    // Set up automatic refresh every 3 minutes (180000 milliseconds)
    refreshTilawatInterval = setInterval(loadRandomTilawat, 180000);

    // Add some interactive effects
    tilawatVideoContainer.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
    });

    tilawatVideoContainer.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});