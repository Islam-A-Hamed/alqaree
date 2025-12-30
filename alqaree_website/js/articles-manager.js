// Articles Functionality
console.log('Articles manager loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('Articles manager DOMContentLoaded');
    const articlesContent = document.getElementById('randomArticlesContent');
    const articlesLoading = document.getElementById('articlesLoading');
    const articlesError = document.getElementById('articlesError');
    const articlesNoContent = document.getElementById('articlesNoContent');
    const articleContentSection = document.getElementById('articleContentSection');
    const articleTitle = document.getElementById('articleTitle');
    const articleExcerpt = document.getElementById('articleExcerpt');
    const articleAuthor = document.getElementById('articleAuthor');
    const articleDate = document.getElementById('articleDate');
    const articleCategory = document.getElementById('articleCategory');
    const refreshArticleBtn = document.getElementById('refreshArticle');
    const retryArticleBtn = document.getElementById('retryArticle');

    // Article Modal
    const modal = document.getElementById('articleModal');
    const modalTitle = document.getElementById('modalArticleTitle');
    const modalAuthor = document.getElementById('modalArticleAuthor');
    const modalDate = document.getElementById('modalArticleDate');
    const modalCategory = document.getElementById('modalArticleCategory');
    const modalContent = document.getElementById('modalArticleContent');
    const closeBtn = document.querySelector('.article-modal-close');

    let refreshArticleInterval;
    let currentArticleData = null; // متغير لحفظ بيانات المقالة الحالية

    // Function to open article modal
    window.openArticleModal = function() {
        if (modal && modalTitle && modalAuthor && modalDate && modalCategory && modalContent) {
            // استخدام بيانات المقالة المعروضة حالياً
            if (currentArticleData) {
                modalTitle.textContent = currentArticleData.title || 'عنوان غير محدد';
                modalAuthor.textContent = (currentArticleData.author_name || 'مجهول');
                modalDate.textContent = (currentArticleData.publish_date || 'غير محدد');
                modalCategory.textContent = (currentArticleData.category || 'عام');
                modalContent.innerHTML = (currentArticleData.content || 'المحتوى غير متوفر').replace(/\n/g, '<br>');
            } else {
                // في حالة عدم وجود بيانات محفوظة، استخدام البيانات من العناصر المعروضة
                modalTitle.textContent = articleTitle ? articleTitle.textContent : 'عنوان غير محدد';
                modalAuthor.textContent = (articleAuthor ? articleAuthor.textContent.replace('بقلم: ', '') : 'مجهول');
                modalDate.textContent = (articleDate ? articleDate.textContent : 'غير محدد');
                modalCategory.textContent = (articleCategory ? articleCategory.textContent : 'عام');
                modalContent.innerHTML = 'المحتوى الكامل غير متوفر حالياً. يرجى إعادة تحميل الصفحة.';
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Smooth scroll to top when modal opens
            setTimeout(() => {
                modal.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    };

    // Function to show loading state
    function showArticleLoading() {
        articlesLoading.style.display = 'flex';
        articlesContent.style.display = 'none';
        articlesError.style.display = 'none';
    }

    // Function to show content
    function showArticleContent() {
        if (articlesLoading) articlesLoading.style.display = 'none';
        if (articlesContent) articlesContent.style.display = 'block';
        if (articlesError) articlesError.style.display = 'none';
    }

    // Function to show error
    function showArticleError() {
        articlesLoading.style.display = 'none';
        articlesContent.style.display = 'none';
        articlesError.style.display = 'flex';
    }

    // Function to load random article
    function loadRandomArticle() {
        showArticleLoading();

        fetch('get_random_article.php', {
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
                if (data.success && data.article) {
                    const article = data.article;

                    // حفظ بيانات المقالة الكاملة للاستخدام في النافذة المنبثقة
                    currentArticleData = article;

                    // Update content with smooth animation
                    articlesContent.style.opacity = '0';
                    articlesContent.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        // إخفاء رسالة عدم التوقر وإظهار المقالة
                        if (articlesNoContent) articlesNoContent.style.display = 'none';
                        if (articleContentSection) articleContentSection.style.display = 'block';

                        if (articleTitle) articleTitle.textContent = article.title || 'عنوان غير محدد';
                        if (articleExcerpt) {
                            const content = article.content || '';
                            const excerpt = content.length > 150 ? content.substring(0, 150) + '...' : content;
                            articleExcerpt.textContent = excerpt;
                        }
                        if (articleAuthor) articleAuthor.textContent = 'بقلم: ' + (article.author_name || 'مجهول');
                        if (articleDate) articleDate.textContent = article.publish_date || 'غير محدد';
                        if (articleCategory) articleCategory.textContent = article.category || 'عام';

                        articlesContent.style.opacity = '1';
                        articlesContent.style.transform = 'translateY(0)';
                        showArticleContent();
                    }, 300);
                } else {
                    // إظهار رسالة عدم التوقر
                    setTimeout(() => {
                        if (articlesNoContent) articlesNoContent.style.display = 'block';
                        if (articleContentSection) articleContentSection.style.display = 'none';
                        if (articlesLoading) articlesLoading.style.display = 'none';
                        if (articlesContent) articlesContent.style.display = 'block';
                    }, 500);
                }
            })
            .catch(error => {
                console.error('Error loading article:', error);
                console.error('Error details:', error.message);

                // إظهار رسالة عدم التوقر بدلاً من رسالة الخطأ
                setTimeout(() => {
                    if (articlesNoContent) articlesNoContent.style.display = 'block';
                    if (articleContentSection) articleContentSection.style.display = 'none';
                    if (articlesLoading) articlesLoading.style.display = 'none';
                    if (articlesContent) articlesContent.style.display = 'block';
                }, 500);
            });
    }

    // Make function globally available
    window.loadRandomArticle = loadRandomArticle;

    // Event listeners
    if (refreshArticleBtn) {
        refreshArticleBtn.addEventListener('click', function() {
            loadRandomArticle();
            // Reset the automatic refresh timer
            clearInterval(refreshArticleInterval);
            refreshArticleInterval = setInterval(loadRandomArticle, 180000); // 3 minutes
        });
    }

    if (retryArticleBtn) {
        retryArticleBtn.addEventListener('click', loadRandomArticle);
    }

    // Initial load
    loadRandomArticle();

    // Set up automatic refresh every 3 minutes (180000 milliseconds)
    refreshArticleInterval = setInterval(loadRandomArticle, 180000);

    // Modal functionality
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'flex') {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    }


    // Add some interactive effects
    const articleCard = document.querySelector('.article-card');
    if (articleCard) {
        articleCard.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
            this.style.transform = 'scale(1.02)';
        });

        articleCard.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    }
});