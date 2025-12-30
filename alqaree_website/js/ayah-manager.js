// Random Quran Verse Functionality
console.log('Ayah manager loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('Ayah manager DOMContentLoaded');
    const ayahContent = document.getElementById('randomAyahContent');
    const ayahLoading = document.getElementById('ayahLoading');
    const ayahError = document.getElementById('ayahError');
    const ayahText = document.getElementById('ayahText');
    const ayahReference = document.getElementById('ayahReference');
    const surahName = document.getElementById('surahName');
    const ayahNumber = document.getElementById('ayahNumber');
    const revelationType = document.getElementById('revelationType');
    const juzNumber = document.getElementById('juzNumber');
    const refreshBtn = document.getElementById('refreshAyah');
    const retryBtn = document.getElementById('retryAyah');

    let refreshInterval;

    // Function to show loading state
    function showLoading() {
        ayahLoading.style.display = 'flex';
        ayahContent.style.display = 'none';
        ayahError.style.display = 'none';
    }

    // Function to show content
    function showContent() {
        ayahLoading.style.display = 'none';
        ayahContent.style.display = 'block';
        ayahError.style.display = 'none';
    }

    // Function to show error
    function showError() {
        ayahLoading.style.display = 'none';
        ayahContent.style.display = 'none';
        ayahError.style.display = 'flex';
    }

    // Function to load random ayah
    function loadRandomAyah() {
        console.log('loadRandomAyah called');
        showLoading();

        fetch('get_random_ayah.php', {
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
                console.log('Received data:', data);
                if (data.success && data.ayah) {
                    console.log('Ayah data:', data.ayah);
                    const ayah = data.ayah;

                    // Update content with smooth animation
                    ayahContent.style.opacity = '0';
                    ayahContent.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        console.log('Updating DOM elements...');
                        console.log('ayahText element:', ayahText);
                        console.log('ayahContent element:', ayahContent);

                        if (ayahText) {
                            ayahText.textContent = ayah.ayah_text;
                            console.log('Set ayahText to:', ayah.ayah_text);
                        }
                        if (ayahReference) {
                            ayahReference.textContent = `صدق الله العظيم - ${ayah.reference}`;
                        }
                        if (surahName) {
                            surahName.textContent = ayah.surah_name_arabic;
                        }
                        if (ayahNumber) {
                            ayahNumber.textContent = ayah.ayah_number || ayah.ayah_id || 'غير محدد';
                        }
                        if (revelationType) {
                            revelationType.textContent = ayah.revelation_type_arabic;
                        }
                        if (juzNumber) {
                            juzNumber.textContent = ayah.juz_number || 'غير محدد';
                        }

                        ayahContent.style.opacity = '1';
                        ayahContent.style.transform = 'translateY(0)';
                        showContent();
                        console.log('Content should now be visible');
                    }, 300);
                } else {
                    throw new Error(data.error || 'فشل في تحميل الآية');
                }
            })
            .catch(error => {
                console.error('Error loading ayah:', error);
                console.error('Error details:', error.message);
                showError();
            });
    }

    // Make function globally available
    window.loadRandomAyah = loadRandomAyah;

    // Event listeners
    refreshBtn.addEventListener('click', function() {
        loadRandomAyah();
        // Reset the automatic refresh timer
        clearInterval(refreshInterval);
        refreshInterval = setInterval(loadRandomAyah, 180000); // 3 minutes
    });

    retryBtn.addEventListener('click', loadRandomAyah);

    // Initial load
    loadRandomAyah();

    // Set up automatic refresh every 3 minutes (180000 milliseconds)
    refreshInterval = setInterval(loadRandomAyah, 180000);

    // Add some interactive effects
    ayahText.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.02)';
    });

    ayahText.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});