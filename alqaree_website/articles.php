<?php
$page_title = 'المقالات الدينية';
$page_description = 'مجموعة من المقالات الإسلامية المتنوعة في الفقه والعقيدة والسيرة والمعرفة الإسلامية';

include 'includes/db_connect.php';
include 'includes/header.php';

/* ========= دالة لتقصير النص ========= */
function truncateText($text, $maxLength = 300) {
    if (mb_strlen($text, 'UTF-8') > $maxLength) {
        return mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
    }
    return $text;
}
?>

<section class="page-content islamic-decor">

    <div class="main-details" style="width:100%; display:flex; flex-direction:column; align-items:center; padding: 60px 0 80px;">
        <div class="basmala-text sacred-float" style="font-size:5rem; margin-bottom: 30px;">﷽</div>
        <h1 class="sacred-text quran-reveal" style="margin-bottom: 20px;">المقالات الدينية</h1>
        <p style="font-size:1.4rem; margin-bottom: 0; max-width: 800px; line-height: 1.6;">
            اقرأ مقالات إسلامية متنوعة في الفقه والعقيدة والسيرة والمعرفة الإسلامية
        </p>
    </div>

    <!-- Advanced Search and Filter Form -->
    <div class="islamic-border" style="margin: 40px auto; padding: 30px; max-width: 1200px; width: 90%;">
        <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 25px; font-family: var(--heading-font);">
            بحث متقدم وفلترة المقالات
        </h2>
        <form action="" method="GET" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="text" name="search_query" placeholder="ابحث في المقالات (العنوان, المحتوى, الكاتب)"
                   value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>"
                   style="padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; text-align: right; direction: rtl;">

            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                <!-- Category Filter -->
                <select name="category_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">التصنيف (الكل)</option>
                    <?php
                    // Fetch categories dynamically
                    $categories = [];
                    if ($conn) {
                        $cat_result = $conn->query("SELECT DISTINCT category FROM articles WHERE category IS NOT NULL AND category != ''");
                        while ($cat_row = $cat_result->fetch_assoc()) {
                            $categories[] = $cat_row['category'];
                        }
                    }
                    foreach ($categories as $cat):
                        $selected = (isset($_GET['category_filter']) && $_GET['category_filter'] == $cat) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Author Filter -->
                <select name="author_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">الكاتب (الكل)</option>
                    <?php
                    // Fetch authors dynamically
                    $authors = [];
                    if ($conn) {
                        $author_result = $conn->query("SELECT DISTINCT author_name FROM articles WHERE author_name IS NOT NULL AND author_name != ''");
                        while ($author_row = $author_result->fetch_assoc()) {
                            $authors[] = $author_row['author_name'];
                        }
                    }
                    foreach ($authors as $author):
                        $selected = (isset($_GET['author_filter']) && $_GET['author_filter'] == $author) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($author); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($author); ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Date Range Filter (simple example) -->
                <select name="date_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">تاريخ النشر (الكل)</option>
                    <option value="past_week" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'past_week') ? 'selected' : ''; ?>>آخر أسبوع</option>
                    <option value="past_month" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'past_month') ? 'selected' : ''; ?>>آخر شهر</option>
                    <option value="past_year" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'past_year') ? 'selected' : ''; ?>>آخر سنة</option>
                </select>
            </div>

            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="submit" style="background: var(--accent-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    بحث وتصفية 🔎
                </button>
                <a href="articles.php" style="background: #dc3545; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    إزالة الفلاتر
                </a>
            </div>
        </form>
    </div>

<?php
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : '';
$author_filter = isset($_GET['author_filter']) ? $_GET['author_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$sql = "SELECT * FROM articles";
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= "sss";
}

if (!empty($category_filter)) {
    $where_clauses[] = "category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($author_filter)) {
    $where_clauses[] = "author_name = ?";
    $params[] = $author_filter;
    $types .= "s";
}

if (!empty($date_filter)) {
    $current_date = date('Y-m-d H:i:s');
    switch ($date_filter) {
        case 'past_week':
            $where_clauses[] = "publish_date >= DATE_SUB(?, INTERVAL 1 WEEK)";
            $params[] = $current_date;
            $types .= "s";
            break;
        case 'past_month':
            $where_clauses[] = "publish_date >= DATE_SUB(?, INTERVAL 1 MONTH)";
            $params[] = $current_date;
            $types .= "s";
            break;
        case 'past_year':
            $where_clauses[] = "publish_date >= DATE_SUB(?, INTERVAL 1 YEAR)";
            $params[] = $current_date;
            $types .= "s";
            break;
    }
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY publish_date DESC, created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        /* ===== Flex Wrapper (مرة واحدة فقط) ===== */
        echo '<div class="articles-flex">';

        while ($row = $result->fetch_assoc()) {

        $content = nl2br($row['content'] ?? '');
        $short_content = truncateText(strip_tags($content), 500);

        echo '<div class="article-item card open-modal-card" data-article-id="' . $row['id'] . '" data-title="' . htmlspecialchars($row['title']) . '" data-author="' . htmlspecialchars($row['author_name']) . '" data-category="' . htmlspecialchars($row['category'] ?: 'عام') . '" data-publish-date="' . htmlspecialchars($row['publish_date'] ?: 'غير محدد') . '" data-content="' . $content . '">';

        echo '  <div class="article-content-wrapper">';

        echo '      <div class="article-info">';
        echo '          <div class="article-header">';
        echo '              <div class="article-meta">';
        echo '                  <p><strong>الكاتب:</strong> ' . $row['author_name'] . '</p>';
        echo '                  <p><strong>التصنيف:</strong> ' . ($row['category'] ?: 'عام') . '</p>';
        echo '                  <p><strong>تاريخ النشر:</strong> ' . ($row['publish_date'] ?: 'غير محدد') . '</p>';
        echo '              </div>';
        echo '              <div class="article-title-meta">';
        echo '                  <h3>' . $row['title'] . '</h3>';
        echo '              </div>';
        echo '          </div>';

        if (!empty($content)) {
            echo '<p class="article-preview">' . htmlspecialchars($short_content) . '</p>';
        }

        echo '          <button class="read-more-btn" type="button">اقرأ المزيد ←</button>';
        echo '      </div>';
        echo '  </div>';
        echo '</div>';
    }

        echo '</div>'; // end articles-flex

        // Modal for articles
        echo '<div id="articleModal" class="modal">';
        echo '    <div class="modal-content article-modal-content">';
        echo '        <button class="close-btn">&times;</button>';
        echo '        <div class="modal-header">';
        echo '            <h2 id="articleModalTitle"></h2>';
        echo '            <div class="modal-meta">';
        echo '                <p><strong>الكاتب:</strong> <span id="articleModalAuthor"></span></p>';
        echo '                <p><strong>التصنيف:</strong> <span id="articleModalCategory"></span></p>';
        echo '                <p><strong>تاريخ النشر:</strong> <span id="articleModalDate"></span></p>';
        echo '            </div>';
        echo '        </div>';
        echo '        <div class="modal-body">';
        echo '            <div id="articleModalContent" class="article-modal-text"></div>';
        echo '        </div>';
        echo '    </div>';
        echo '</div>';

    } else {
        // No articles found after search/filter
        echo '<div class="no-articles-message">';
        echo '<div class="sacred-text" style="font-size: 4rem; margin-bottom: 20px;">📚</div>';
        echo '<h3>لا توجد مقالات مطابقة لبحثك أو فلاترك.</h3>';
        echo '<p>حاول بكلمات بحث أو فلاتر مختلفة.</p>';
        echo '</div>';
    }
    $stmt->close(); // Close statement here
} else {
    // Handle prepare error, e.g., log it and display a user-friendly message
    error_log("Failed to prepare statement: " . $conn->error);
    echo '<div class="no-articles-message">';
    echo '<div class="sacred-text" style="font-size: 4rem; margin-bottom: 20px;">⚠️</div>';
    echo '<h3>حدث خطأ أثناء إعداد استعلام البحث.</h3>';
    echo '<p>الرجاء المحاولة مرة أخرى لاحقًا.</p>';
    echo '</div>';
}
?>

</section>


<style>
.articles-flex {
    display: flex;
    flex-direction: column;
    gap: 40px;
    margin-top: 40px;
}

.article-item {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    width: 100%;
    min-height: 350px;
}

.article-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    border-color: var(--accent-color);
}

.article-content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.article-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.article-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
    margin-bottom: 25px;
}

.article-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 0.9rem;
    color: var(--text-color);
    text-align: left;
    width: 280px;
    flex-shrink: 0;
}

.article-title-meta h3 {
    color: var(--primary-color);
    font-family: var(--heading-font);
    font-size: 2.2rem;
    font-weight: 600;
    line-height: 1.2;
    margin: 0;
    text-align: right;
}

.article-meta p {
    margin: 0;
}

.article-preview {
    color: var(--text-color);
    line-height: 1.8;
    font-size: 1rem;
    font-family: var(--body-font);
    text-align: justify;
    margin-bottom: 20px;
}

.read-more-btn {
    align-self: flex-start;
    background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.read-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
}

.no-articles-message {
    text-align: center;
    padding: 60px 20px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    margin-top: 40px;
}

.no-articles-message h3 {
    color: var(--primary-color);
    font-family: var(--heading-font);
    margin-bottom: 15px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(20, 20, 20, 0.8) 100%);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    animation: modalFadeIn 0.3s ease-out;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    to {
        opacity: 1;
        backdrop-filter: blur(15px);
    }
}

.article-modal-content {
    width: 92%;
    max-width: 1100px;
    max-height: 88vh;
    overflow-y: auto;
    background: linear-gradient(145deg,
        rgba(255, 255, 255, 0.98) 0%,
        rgba(250, 252, 255, 0.96) 25%,
        rgba(255, 250, 245, 0.98) 50%,
        rgba(248, 252, 255, 0.96) 75%,
        rgba(255, 255, 255, 0.98) 100%);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border: 3px solid rgba(212, 175, 55, 0.4);
    border-radius: 35px;
    box-shadow:
        0 40px 120px rgba(0, 0, 0, 0.4),
        0 20px 60px rgba(212, 175, 55, 0.3),
        0 0 100px rgba(212, 175, 55, 0.2),
        inset 0 2px 0 rgba(255, 255, 255, 0.3),
        inset 0 -2px 0 rgba(212, 175, 55, 0.1);
    position: relative;
    text-align: right;
    padding: 45px;
    scrollbar-width: thin;
    scrollbar-color: rgba(212, 175, 55, 0.6) transparent;
    animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: center;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(30px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.article-modal-content::-webkit-scrollbar {
    width: 10px;
}

.article-modal-content::-webkit-scrollbar-track {
    background: rgba(212, 175, 55, 0.1);
    border-radius: 5px;
}

.article-modal-content::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(212, 175, 55, 0.6) 0%, rgba(212, 175, 55, 0.8) 100%);
    border-radius: 5px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.article-modal-content::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, rgba(212, 175, 55, 0.8) 0%, rgba(212, 175, 55, 1) 100%);
}

.close-btn {
    position: absolute;
    top: 25px;
    right: 30px;
    font-size: 2.2rem;
    font-weight: bold;
    color: var(--primary-color);
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid rgba(212, 175, 55, 0.3);
    cursor: pointer;
    z-index: 1001;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.close-btn:hover {
    background: rgba(212, 175, 55, 0.1);
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 6px 25px rgba(212, 175, 55, 0.3);
    border-color: rgba(212, 175, 55, 0.5);
}

.modal-header {
    margin-bottom: 35px;
    padding: 25px 35px;
    background: linear-gradient(135deg,
        rgba(212, 175, 55, 0.08) 0%,
        rgba(212, 175, 55, 0.05) 50%,
        rgba(212, 175, 55, 0.08) 100%);
    border-radius: 20px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    position: relative;
    overflow: hidden;
}

.modal-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
    border-radius: 20px 20px 0 0;
}

.modal-header h2 {
    color: var(--primary-color);
    font-size: 2.5rem;
    margin-bottom: 20px;
    font-weight: 800;
    line-height: 1.3;
    text-shadow: 0 3px 8px rgba(0,0,0,0.15);
    font-family: var(--heading-font);
    position: relative;
    z-index: 1;
}

.modal-meta {
    display: flex;
    gap: 30px;
    font-size: 1rem;
    color: var(--text-color);
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.modal-meta p {
    margin: 0;
    font-weight: 500;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 20px;
    border: 1px solid rgba(212, 175, 55, 0.3);
    transition: all 0.3s ease;
}

.modal-meta p:hover {
    background: rgba(212, 175, 55, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.2);
}

.modal-meta strong {
    color: var(--primary-color);
    font-weight: 700;
    margin-left: 8px;
}

.modal-body {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    padding: 35px;
    border: 1px solid rgba(212, 175, 55, 0.15);
    box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
    line-height: 1.8;
}

.article-modal-text {
    font-size: 1.2rem;
    line-height: 2.2;
    color: var(--text-color);
    font-family: var(--body-font);
    text-align: justify;
    padding: 0;
    font-weight: 400;
    letter-spacing: 0.3px;
}

.article-modal-text p {
    margin-bottom: 2em;
    padding: 15px 0;
    border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    position: relative;
}

.article-modal-text p:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.article-modal-text p::first-letter {
    font-size: 2.5em;
    font-weight: 700;
    color: var(--primary-color);
    float: right;
    margin-left: 8px;
    margin-top: 2px;
    line-height: 0.8;
}

.article-modal-text br {
    display: none;
}

.article-modal-text ol, .article-modal-text ul {
    padding-right: 30px;
    margin: 2em 0;
    background: rgba(212, 175, 55, 0.05);
    padding: 20px 30px;
    border-radius: 15px;
    border-right: 4px solid var(--accent-color);
}

.article-modal-text li {
    margin-bottom: 1em;
    padding: 8px 0;
    position: relative;
    padding-right: 15px;
}

.article-modal-text li::marker {
    color: var(--primary-color);
    font-weight: 700;
}

.article-modal-text strong {
    color: var(--primary-color);
    font-weight: 700;
    background: rgba(212, 175, 55, 0.1);
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 1.1em;
}

.article-modal-text em {
    font-style: italic;
    color: var(--accent-color);
    font-weight: 500;
    border-bottom: 2px solid rgba(212, 175, 55, 0.3);
}

.article-modal-text blockquote {
    background: rgba(212, 175, 55, 0.08);
    border-right: 4px solid var(--primary-color);
    padding: 20px 25px;
    margin: 2em 0;
    border-radius: 0 15px 15px 0;
    font-style: italic;
    font-size: 1.1em;
    position: relative;
}

.article-modal-text blockquote::before {
    content: '"';
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 3em;
    color: var(--primary-color);
    opacity: 0.3;
}


@media (max-width: 768px) {
    .article-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
        padding: 10px 0;
        margin-bottom: 20px;
    }

    .article-meta {
        text-align: right;
        width: auto;
        font-size: 0.85rem;
        order: 2;
    }

    .article-title-meta h3 {
        text-align: left;
        font-size: 1.8rem;
        order: 1;
    }

    .article-preview {
        font-size: 0.95rem;
        line-height: 1.7;
    }

    .read-more-btn {
        align-self: stretch;
        text-align: center;
        padding: 14px 20px;
    }

    .article-item {
        padding: 25px;
        min-height: auto;
    }

    /* Modal responsive */
    .article-modal-content {
        width: 96%;
        max-width: 96%;
        padding: 30px 20px;
        max-height: 92vh;
        border-radius: 25px;
    }

    .modal-header {
        margin-bottom: 25px;
        padding: 20px 15px;
        border-radius: 15px;
    }

    .modal-header h2 {
        font-size: 2rem;
        margin-bottom: 15px;
    }

    .modal-meta {
        gap: 12px;
        font-size: 0.9rem;
        flex-direction: column;
        align-items: flex-start;
    }

    .modal-meta p {
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .modal-body {
        padding: 25px 15px;
    }

    .article-modal-text {
        font-size: 1rem;
        line-height: 1.9;
    }

    .article-modal-text p::first-letter {
        font-size: 2em;
        margin-left: 5px;
    }

    .close-btn {
        width: 45px;
        height: 45px;
        font-size: 1.8rem;
        top: 15px;
        right: 15px;
    }
}

@media (max-width: 480px) {
    .article-modal-content {
        width: 98%;
        padding: 25px 15px;
        border-radius: 20px;
    }

    .modal-header {
        padding: 15px 10px;
    }

    .modal-header h2 {
        font-size: 1.6rem;
        margin-bottom: 12px;
    }

    .modal-meta {
        gap: 8px;
        font-size: 0.8rem;
    }

    .modal-meta p {
        padding: 5px 10px;
        font-size: 0.75rem;
    }

    .modal-body {
        padding: 20px 10px;
    }

    .article-modal-text {
        font-size: 0.95rem;
        line-height: 1.7;
    }

    .article-modal-text ol, .article-modal-text ul {
        padding: 15px 20px;
    }

    .close-btn {
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
        top: 10px;
        right: 10px;
    }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const articleModal = document.getElementById('articleModal');
    const closeBtn = articleModal.querySelector('.close-btn');
    const articleModalTitle = document.getElementById('articleModalTitle');
    const articleModalAuthor = document.getElementById('articleModalAuthor');
    const articleModalCategory = document.getElementById('articleModalCategory');
    const articleModalDate = document.getElementById('articleModalDate');
    const articleModalContent = document.getElementById('articleModalContent');

    document.querySelectorAll('.open-modal-card').forEach(card => {
        card.addEventListener('click', function(event) {
            // Check if the click was on the button or the card itself
            if (event.target.closest('.read-more-btn') || event.target.closest('.article-item')) {
                event.preventDefault(); // Prevent default if it's a link or button
                
                const title = this.dataset.title;
                const author = this.dataset.author;
                const category = this.dataset.category;
                const publishDate = this.dataset.publishDate;
                const content = this.dataset.content;

                articleModalTitle.textContent = title;
                articleModalAuthor.textContent = author;
                articleModalCategory.textContent = category;
                articleModalDate.textContent = publishDate;
                articleModalContent.innerHTML = content; // Use innerHTML to render HTML content

                articleModal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scrolling background
            }
        });
    });

    closeBtn.addEventListener('click', function() {
        articleModal.classList.remove('active');
        document.body.style.overflow = 'auto'; // Re-enable background scrolling
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === articleModal) {
            articleModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });

    // Close modal on Escape key press
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && articleModal.classList.contains('active')) {
            articleModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>
