<?php
// Force UTF-8 encoding
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

// Ensure proper encoding for database connections
if (!defined('MYSQL_CHARSET')) {
    define('MYSQL_CHARSET', 'utf8mb4');
}

$page_title = 'الصفحة الرئيسية';
$page_description = 'موقع القارئ - منصة إسلامية شاملة تجمع التلاوات القرآنية، الحكم والمواعظ، والقرآن الكريم في مكان واحد';
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero-section">

        <div class="hero-background">
            <div class="hero-particles">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
                <div class="particle particle-5"></div>
            </div>
        </div>

        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-basmala">
                    <span class="basmala-text">مُحَمَّدٌ رَسُولُ اللَّهِ</span>
                </div>

                <div class="hero-main">
                    <h1 class="hero-title">
                        <span class="title-main">موقع القارئ</span>
                        <span class="title-accent">منصة إسلامية شاملة</span>
                    </h1>


                    <p class="hero-description">
                        منصة إسلامية شاملة للقرآن الكريم والمحتوى الديني المتنوع
                    </p>
                </div>
            </div>
        </div>

        <div class="hero-waves">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="hero-wave">
                <path d="M0,60 C300,100 600,20 900,60 C1050,80 1200,40 1200,60 L1200,120 L0,120 Z" fill="rgba(255,255,255,0.1)"></path>
            </svg>
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="hero-wave hero-wave-delayed">
                <path d="M0,80 C200,40 400,80 600,40 C800,80 1000,20 1200,60 L1200,120 L0,120 Z" fill="rgba(255,255,255,0.05)"></path>
            </svg>
        </div>
    </section>

    <!-- Main Navigation Cards -->
    <section class="main-nav-section" id="main-nav">
        <div class="main-nav-container">
            <a href="quran.php" class="main-nav-card" target="_blank">
                <div class="nav-card-header">
                    <h3 class="nav-card-title">القرآن الكريم</h3>
                </div>
                <div class="nav-card-content">
                    <p class="nav-card-description">
                        تصفح كتاب الله بوضوح ودقة مع التفسير والشرح
                    </p>
                </div>
            </a>

            <a href="tilawat.php" class="main-nav-card" target="_blank">
                <div class="nav-card-header">
                    <h3 class="nav-card-title">تلاوات خاشعة</h3>
                </div>
                <div class="nav-card-content">
                    <p class="nav-card-description">
                        مجموعة من أجمل التلاوات لأشهر القراء والشيوخ
                    </p>
                </div>
            </a>

            <a href="hekum.php" class="main-nav-card" target="_blank">
                <div class="nav-card-header">
                    <h3 class="nav-card-title">حكم ومواعظ</h3>
                </div>
                <div class="nav-card-content">
                    <p class="nav-card-description">
                        دروس وعبر من السنة والسيرة النبوية الشريفة
                    </p>
                </div>
            </a>

            <a href="articles.php" class="main-nav-card" target="_blank">
                <div class="nav-card-header">
                    <h3 class="nav-card-title">مقالات دينية</h3>
                </div>
                <div class="nav-card-content">
                    <p class="nav-card-description">
                        مقالات إسلامية متنوعة في مختلف المجالات الدينية والعلمية
                    </p>
                </div>
            </a>
        </div>
    </section>

    <!-- Random Quran Verse Section -->
    <section class="random-ayah-section" id="quran-section">
        <div class="random-ayah-content">
            <div class="ayah-header">
                <h2 class="section-title main-title">آية قرآنية</h2>
                <div class="title-underline"></div>
            </div>

            <div class="ayah-loading" id="ayahLoading">
                <div class="loading-spinner"></div>
                <p>جاري تحميل الآية المباركة...</p>
            </div>

            <div class="ayah-error" id="ayahError" style="display: none;">
                <span class="error-icon">⚠️</span>
                <p>حدث خطأ في تحميل الآية</p>
                <button class="retry-btn" id="retryAyah" onclick="loadRandomAyah()">إعادة المحاولة</button>
            </div>

            <div class="random-ayah-content" id="randomAyahContent" style="display: none;">
                <!-- البسملة فوق الآية -->
                <div class="ayah-basmala-above">
                    <span class="sacred-text">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</span>
                </div>

                <div class="ayah-text" id="ayahText">
                    آية مباركة من كتاب الله
                </div>

                <div class="ayah-info">
                    <div class="ayah-reference" id="ayahReference">
                        صدق الله العظيم - سورة البقرة: 255
                    </div>

                    <div class="ayah-details">
                        <div class="detail-item">
                            <span class="detail-label">السورة:</span>
                            <span class="detail-value" id="surahName">البقرة</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">رقم الآية:</span>
                            <span class="detail-value" id="ayahNumber">255</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">نوع النزول:</span>
                            <span class="detail-value" id="revelationType">مدنية</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">الجزء:</span>
                            <span class="detail-value" id="juzNumber">3</span>
                        </div>
                    </div>

                    <div class="ayah-actions">
                        <button class="refresh-ayah-btn" id="refreshAyah" onclick="loadRandomAyah()">
                            <span>آية جديدة</span>
                        </button>
                        <a href="quran.php" class="view-quran-btn" target="_blank">
                            <span>تصفح القرآن</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ayah-decoration ayah-decoration-top-left"></div>
            <div class="ayah-decoration ayah-decoration-bottom-right"></div>
            <div class="ayah-decoration ayah-decoration-center-glow"></div>
        </div>
    </section>

    <!-- Random Tilawat Section -->
    <section class="random-tilawat-section" id="tilawat-section">
        <div class="random-tilawat-content">
            <div class="tilawat-header">
                <h2 class="section-title main-title">تلاوة قرآنية</h2>
                <div class="title-underline"></div>
            </div>

            <div class="tilawat-loading" id="tilawatLoading">
                <div class="loading-spinner"></div>
                <p>جاري تحميل التلاوة المباركة...</p>
            </div>

            <div class="tilawat-error" id="tilawatError" style="display: none;">
                <p>حدث خطأ في تحميل التلاوة</p>
                <button class="retry-btn" id="retryTilawat" onclick="loadRandomTilawat()">إعادة المحاولة</button>
            </div>

            <div class="random-tilawat-content" id="randomTilawatContent" style="display: none;">
                <div class="tilawat-video-container" id="tilawatVideoSection">
                    <div id="tilawatNoVideo" class="no-content-message">
                        <p>لا توجد تلاوة متاحة حالياً</p>
                    </div>
                </div>

                <div class="tilawat-info">
                    <div class="tilawat-title">
                        <h3 id="tilawatTitle">تلاوة مباركة</h3>
                    </div>

                    <div class="tilawat-details">
                        <div class="detail-item">
                            <span class="detail-label">السورة:</span>
                            <span class="detail-value" id="tilawatSurahName">البقرة</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">القارئ:</span>
                            <span class="detail-value" id="tilawatReciterName">الشيخ عبدالرحمن السديس</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">المدة:</span>
                            <span class="detail-value" id="tilawatDuration">5:30</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">التاريخ:</span>
                            <span class="detail-value" id="tilawatPublishDate">2024-01-15</span>
                        </div>
                    </div>

                    <div class="tilawat-description">
                        <p id="tilawatDescription">وصف التلاوة والسورة المقروءة...</p>
                        <button class="show-more-description-btn" id="showMoreDescription" style="display: none;">
                            عرض المزيد
                        </button>
                    </div>

                    <div class="tilawat-actions">
                        <button class="refresh-tilawat-btn" id="refreshTilawat" onclick="loadRandomTilawat()">
                            <span>تلاوة جديدة</span>
                        </button>
                        <a href="tilawat.php" class="view-tilawat-btn" target="_blank">
                            <span>جميع التلاوات</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="tilawat-decoration tilawat-decoration-top-left"></div>
            <div class="tilawat-decoration tilawat-decoration-bottom-right"></div>
            <div class="tilawat-decoration tilawat-decoration-center-glow"></div>
        </div>
    </section>

    <!-- Random Hekum Section -->
    <section class="random-hekum-section" id="hekum-section">
        <div class="random-hekum-content">
            <div class="hekum-header">
                <h2 class="section-title main-title">موعظة</h2>
                <div class="title-underline"></div>
            </div>

            <div class="hekum-loading" id="hekumLoading">
                <div class="loading-spinner"></div>
                <p>جاري تحميل الموعظة المباركة...</p>
            </div>

            <div class="hekum-error" id="hekumError" style="display: none;">
                <p>حدث خطأ في تحميل الموعظة</p>
                <button class="retry-btn" id="retryHekum" onclick="loadRandomHekum()">إعادة المحاولة</button>
            </div>

            <div class="random-hekum-content" id="randomHekumContent" style="display: none;">
                <div class="hekum-video-container" id="hekumVideoSection">
                    <div id="hekumNoVideo" class="no-content-message">
                        <p>لا توجد موعظة متاحة حالياً</p>
                    </div>
                </div>

                <div class="hekum-info">
                    <div class="hekum-title">
                        <h3 id="hekumTitle">موعظة مباركة</h3>
                    </div>

                    <div class="hekum-details">
                        <div class="detail-item">
                            <span class="detail-label">المتحدث:</span>
                            <span class="detail-value" id="hekumSpeakerName">الشيخ سعود الشريم</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">المدة:</span>
                            <span class="detail-value" id="hekumDuration">10:45</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">التاريخ:</span>
                            <span class="detail-value" id="hekumPublishDate">2024-01-15</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">التصنيف:</span>
                            <span class="detail-value" id="hekumCategory">وعظ وإرشاد</span>
                        </div>
                    </div>

                    <div class="hekum-description">
                        <p id="hekumDescription">وصف الموعظة والموضوع المطروح...</p>
                        <button class="show-more-description-btn" id="showMoreHekumDescription" style="display: none;">
                            عرض المزيد
                        </button>
                    </div>

                    <div class="hekum-actions">
                        <button class="refresh-hekum-btn" id="refreshHekum" onclick="loadRandomHekum()">
                            <span>موعظة جديدة</span>
                        </button>
                        <a href="hekum.php" class="view-hekum-btn" target="_blank">
                            <span>جميع المواعظ</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="hekum-decoration hekum-decoration-top-left"></div>
            <div class="hekum-decoration hekum-decoration-bottom-right"></div>
            <div class="hekum-decoration hekum-decoration-center-glow"></div>
        </div>
    </section>

    <!-- Random Articles Section -->
    <section class="random-articles-section" id="articles-section">
        <div class="random-articles-content">
            <div class="articles-header">
                <h2 class="section-title main-title">مقال ديني</h2>
                <div class="title-underline"></div>
            </div>

            <div class="articles-loading" id="articlesLoading">
                <div class="loading-spinner"></div>
                <p>جاري تحميل المقالة المباركة...</p>
            </div>

            <div class="articles-error" id="articlesError" style="display: none;">
                <p>حدث خطأ في تحميل المقالة</p>
                <button class="retry-btn" id="retryArticle" onclick="loadRandomArticle()">إعادة المحاولة</button>
            </div>

            <div class="random-articles-content" id="randomArticlesContent" style="display: none;">
                <div class="articles-content-container" id="articleContentSection">
                    <div id="articlesNoContent" class="no-content-message">
                        <p>لا توجد مقالات متاحة حالياً</p>
                    </div>

                    <div class="article-card" id="articleCard">
                        <div class="article-header">
                            <div class="article-category" id="articleCategory">عام</div>
                            <div class="article-badge">مقالة إسلامية</div>
                        </div>

                        <h3 class="article-title" id="articleTitle">عنوان المقالة</h3>
                        <p class="article-excerpt" id="articleExcerpt">مختصر من المقالة...</p>

                        <div class="article-meta">
                            <span class="article-author" id="articleAuthor">بقلم: مجهول</span>
                            <span class="article-date" id="articleDate">غير محدد</span>
                        </div>

                        <button class="read-article-btn" id="readArticleBtn" onclick="openArticleModal()">
                            قراءة المقالة كاملة
                        </button>
                    </div>
                </div>

                <div class="articles-actions">
                    <button class="refresh-articles-btn" id="refreshArticle" onclick="loadRandomArticle()">
                        <span>مقالة جديدة</span>
                    </button>
                    <a href="articles.php" class="view-articles-btn" target="_blank">
                        <span>جميع المقالات</span>
                    </a>
                </div>
            </div>

            <div class="articles-decoration articles-decoration-top-left"></div>
            <div class="articles-decoration articles-decoration-bottom-right"></div>
            <div class="articles-decoration articles-decoration-center-glow"></div>
        </div>
    </section>

<link rel="stylesheet" href="css/index-page.css">

<!-- JavaScript Files -->
<script src="js/ayah-manager.js" defer></script>
<script src="js/articles-manager.js" defer></script>
<script src="js/tilawat-manager.js" defer></script>
<script src="js/hekum-manager.js" defer></script>

<!-- Article Modal -->
<div id="articleModal" class="modal">
    <div class="modal-content article-modal-content">
        <button class="close-btn article-modal-close">&times;</button>
        <div class="modal-header">
            <h2 id="modalArticleTitle"></h2>
            <div class="modal-meta">
                <p><strong>الكاتب:</strong> <span id="modalArticleAuthor"></span></p>
                <p><strong>التصنيف:</strong> <span id="modalArticleCategory"></span></p>
                <p><strong>تاريخ النشر:</strong> <span id="modalArticleDate"></span></p>
            </div>
        </div>
        <div class="modal-body">
            <div id="modalArticleContent" class="article-modal-text"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
