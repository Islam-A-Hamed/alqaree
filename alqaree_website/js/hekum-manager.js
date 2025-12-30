// Random Hekum Video Functionality
console.log('Hekum manager loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('Hekum manager DOMContentLoaded');
    const hekumContent = document.getElementById('randomHekumContent');
    const hekumLoading = document.getElementById('hekumLoading');
    const hekumError = document.getElementById('hekumError');
    const hekumVideoContainer = document.getElementById('hekumVideoSection');
    const hekumTitle = document.getElementById('hekumTitle');
    const hekumSpeakerName = document.getElementById('hekumSpeakerName');
    const hekumDuration = document.getElementById('hekumDuration');
    const hekumPublishDate = document.getElementById('hekumPublishDate');
    const hekumDescription = document.getElementById('hekumDescription');
    const showMoreHekumDescriptionBtn = document.getElementById('showMoreHekumDescription');
    const refreshHekumBtn = document.getElementById('refreshHekum');
    const retryHekumBtn = document.getElementById('retryHekum');

    // Check if elements exist before adding event listeners
    if (!refreshHekumBtn || !retryHekumBtn) {
        console.warn('Hekum elements not found, skipping event listeners');
        return;
    }

    let refreshHekumInterval;

    // Function to show loading state
    function showHekumLoading() {
        hekumLoading.style.display = 'flex';
        hekumContent.style.display = 'none';
        hekumError.style.display = 'none';
    }

    // Function to show content
    function showHekumContent() {
        if (hekumLoading) hekumLoading.style.display = 'none';
        if (hekumContent) hekumContent.style.display = 'block';
        if (hekumError) hekumError.style.display = 'none';
    }

    // Function to show error
    function showHekumError() {
        hekumLoading.style.display = 'none';
        hekumContent.style.display = 'none';
        hekumError.style.display = 'flex';
    }

    // Function to load random hekum
    function loadRandomHekum() {
        showHekumLoading();

        fetch('get_random_hekum.php', {
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
                if (data.success && data.hekum) {
                    const hekum = data.hekum;

                    // Update content with smooth animation
                    hekumContent.style.opacity = '0';
                    hekumContent.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        // إخفاء رسالة عدم التوقر وإظهار الفيديو
                        const noVideoMsg = document.getElementById('hekumNoVideo');
                        const videoSection = document.getElementById('hekumVideoSection');
                        const infoSection = document.getElementById('hekumInfoSection');

                        if (noVideoMsg) noVideoMsg.style.display = 'none';
                        if (videoSection) videoSection.style.display = 'block';
                        if (infoSection) infoSection.style.display = 'block';

                        if (hekumVideoContainer) {
                            hekumVideoContainer.innerHTML = hekum.youtube_embed_code;

                            // Add error handling for iframe loading
                            const iframe = hekumVideoContainer.querySelector('iframe');
                            if (iframe) {
                                iframe.addEventListener('load', function() {
                                    this.classList.add('loaded');
                                });

                                iframe.addEventListener('error', function() {
                                    console.warn('Hekum iframe failed to load, showing fallback message');
                                    const noVideoMsg = document.getElementById('hekumNoVideo');
                                    const videoSection = document.getElementById('hekumVideoSection');
                                    const infoSection = document.getElementById('hekumInfoSection');

                                    if (noVideoMsg) noVideoMsg.style.display = 'block';
                                    if (videoSection) videoSection.style.display = 'none';
                                    if (infoSection) infoSection.style.display = 'none';
                                });
                            }
                        }
                        hekumTitle.textContent = hekum.title;
                        hekumSpeakerName.textContent = hekum.speaker_name;
                        hekumDuration.textContent = hekum.video_duration;
                        hekumPublishDate.textContent = hekum.publish_date;

                        // Handle description with show more functionality
                        const fullDescriptionHekum = hekum.description || '';
                        const shortDescriptionHekum = fullDescriptionHekum.substring(0, 250);

                        if (fullDescriptionHekum.length > 250) {
                            hekumDescription.innerHTML = shortDescriptionHekum + '...';
                            showMoreHekumDescriptionBtn.style.display = 'inline-flex';
                            showMoreHekumDescriptionBtn.textContent = 'عرض المزيد';
                            showMoreHekumDescriptionBtn.onclick = function() {
                                if (hekumDescription.innerHTML === shortDescriptionHekum + '...') {
                                    hekumDescription.innerHTML = fullDescriptionHekum;
                                    showMoreHekumDescriptionBtn.textContent = 'عرض أقل';
                                } else {
                                    hekumDescription.innerHTML = shortDescriptionHekum + '...';
                                    showMoreHekumDescriptionBtn.textContent = 'عرض المزيد';
                                }
                            };
                        } else {
                            hekumDescription.innerHTML = fullDescriptionHekum;
                            showMoreHekumDescriptionBtn.style.display = 'none';
                        }

                        hekumContent.style.opacity = '1';
                        hekumContent.style.transform = 'translateY(0)';
                        showHekumContent();
                    }, 300);
                } else {
                    throw new Error(data.error || 'فشل في تحميل الموعظة');
                }
            })
            .catch(error => {
                console.error('Error loading hekum:', error);
                console.error('Error details:', error.message);

                // إظهار رسالة عدم التوقر بدلاً من رسالة الخطأ
                if (typeof showHekumLoading === 'function') showHekumLoading();
                setTimeout(() => {
                    const noVideoMsg = document.getElementById('hekumNoVideo');
                    const videoSection = document.getElementById('hekumVideoSection');
                    const infoSection = document.getElementById('hekumInfoSection');
                    const loading = document.getElementById('hekumLoading');
                    const content = document.getElementById('randomHekumContent');

                    if (noVideoMsg) noVideoMsg.style.display = 'block';
                    if (videoSection) videoSection.style.display = 'none';
                    if (infoSection) infoSection.style.display = 'none';
                    if (loading) loading.style.display = 'none';
                    if (content) content.style.display = 'block';
                }, 500);
            });
    }

    // Make function globally available
    window.loadRandomHekum = loadRandomHekum;

    // Event listeners
    refreshHekumBtn.addEventListener('click', function() {
        loadRandomHekum();
        // Reset the automatic refresh timer
        clearInterval(refreshHekumInterval);
        refreshHekumInterval = setInterval(loadRandomHekum, 180000); // 3 minutes
    });

    retryHekumBtn.addEventListener('click', loadRandomHekum);

    // Initial load
    loadRandomHekum();

    // Set up automatic refresh every 3 minutes (180000 milliseconds)
    refreshHekumInterval = setInterval(loadRandomHekum, 180000);

    // Add some interactive effects
    hekumVideoContainer.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
    });

    hekumVideoContainer.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});