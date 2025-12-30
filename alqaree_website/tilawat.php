<?php
$page_title = 'التلاوات القرآنية';
$page_description = 'استمع إلى أجمل التلاوات القرآنية لأشهر القراء والشيوخ من مختلف المدارس والأساليب';
include 'includes/db_connect.php'; // Establish database connection and include error reporting
include 'includes/header.php';

// Function to extract Video ID and rebuild YouTube embed
function enhanceYouTubeEmbed($embedCode) {
    // Extract Video ID using regex
    $videoId = null;

    // Check if it's already an iframe
    if (preg_match('/<iframe[^>]*src="([^"]*)"/i', $embedCode, $matches)) {
        $url = $matches[1];
    } else {
        $url = $embedCode;
    }

    // Extract video ID from various YouTube URL formats
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
        '/youtube\.com\/v\/([^&\n?#]+)/',
        '/youtube\.com\/embed\/([^&\n?#]+)/'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $videoId = $matches[1];
            break;
        }
    }

    if (!$videoId) {
        // Return original code if extraction fails
        return $embedCode;
    }

    // Build new embed URL with required parameters
    $embedUrl = "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0&modestbranding=1&showinfo=0&iv_load_policy=3&disablekb=1&fs=0&playsinline=1";

    // Create new iframe with controlled dimensions
    $newEmbed = '<iframe src="' . $embedUrl . '" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation allow-forms" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';

    return $newEmbed;
}

?>
<section class="page-content islamic-decor">

<div class="basmala-text sacred-float" style="font-size: 4rem;">﷽</div>
<h1 class="sacred-text quran-reveal">التلاوات القرآنية</h1>
<p class="fade-in-up" style="font-size: 1.3rem; color: var(--text-color); margin-bottom: 40px;">
    استمع إلى أروع التلاوات لكبار القراء والشيوخ من مختلف المدارس والأساليب.
</p>

    <!-- Advanced Search and Filter Form -->
    <div class="islamic-border" style="margin: 40px auto; padding: 30px; max-width: 1200px; width: 90%;">
        <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 25px; font-family: var(--heading-font);">
            بحث متقدم وفلترة التلاوات
        </h2>
        <form action="" method="GET" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="text" name="search_query" placeholder="ابحث في التلاوات (العنوان, السورة, القارئ)"
                   value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>"
                   style="padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; text-align: right; direction: rtl;">

            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                <!-- Reciter Filter -->
                <select name="reciter_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">القارئ (الكل)</option>
                    <?php
                    // Fetch reciters dynamically
                    $reciters = [];
                    if ($conn) {
                        $reciter_result = $conn->query("SELECT DISTINCT reciter_name FROM tilawat WHERE reciter_name IS NOT NULL AND reciter_name != ''");
                        while ($reciter_row = $reciter_result->fetch_assoc()) {
                            $reciters[] = $reciter_row['reciter_name'];
                        }
                    }
                    foreach ($reciters as $reciter):
                        $selected = (isset($_GET['reciter_filter']) && $_GET['reciter_filter'] == $reciter) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($reciter); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($reciter); ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Surah Name Filter -->
                <select name="surah_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">السورة (الكل)</option>
                    <?php
                    // Fetch surah names dynamically
                    $surah_names = [];
                    if ($conn) {
                        $surah_result = $conn->query("SELECT DISTINCT surah_name FROM tilawat WHERE surah_name IS NOT NULL AND surah_name != '' ORDER BY surah_name"); // Changed surah_name_arabic to surah_name
                        while ($surah_row = $surah_result->fetch_assoc()) {
                            $surah_names[] = $surah_row['surah_name'];
                        }
                    }
                    foreach ($surah_names as $s_name):
                        $selected = (isset($_GET['surah_filter']) && $_GET['surah_filter'] == $s_name) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($s_name); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($s_name); ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Date Range Filter (simple example) -->
                <select name="date_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">تاريخ النشر (الكل)</option>
                    <option value="past_week" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'past_week') ? 'selected' : ''; ?>>آخر أسبوع</option>
                    <option value="past_month" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'past_month') ? 'selected' : ''; ?>>آخر شهر</option>
                    <option value="past_year" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == 'past_year') ? 'selected' : ''; ?>>آخر سنة</option>
                </select>

                <!-- Video Duration Filter (example) -->
                <select name="duration_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">مدة الفيديو (الكل)</option>
                    <option value="short" <?php echo (isset($_GET['duration_filter']) && $_GET['duration_filter'] == 'short') ? 'selected' : ''; ?>>قصير (أقل من 15 دقيقة)</option>
                    <option value="medium" <?php echo (isset($_GET['duration_filter']) && $_GET['duration_filter'] == 'medium') ? 'selected' : ''; ?>>متوسط (15-60 دقيقة)</option>
                    <option value="long" <?php echo (isset($_GET['duration_filter']) && $_GET['duration_filter'] == 'long') ? 'selected' : ''; ?>>طويل (أكثر من 60 دقيقة)</option>
                </select>
            </div>

            <div style="display: flex; justify-content: center; gap: 15px;">
                <button type="submit" style="background: var(--accent-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    بحث وتصفية 🔎
                </button>
                <a href="tilawat.php" style="background: #dc3545; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    إزالة الفلاتر
                </a>
            </div>
        </form>
    </div>

<?php
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$reciter_filter = isset($_GET['reciter_filter']) ? $_GET['reciter_filter'] : '';
$surah_filter = isset($_GET['surah_filter']) ? $_GET['surah_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$duration_filter = isset($_GET['duration_filter']) ? $_GET['duration_filter'] : '';

$sql = "SELECT * FROM tilawat";
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE ? OR surah_name LIKE ? OR reciter_name LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= "ssss";
}

if (!empty($reciter_filter)) {
    $where_clauses[] = "reciter_name = ?";
    $params[] = $reciter_filter;
    $types .= "s";
}

if (!empty($surah_filter)) {
    $where_clauses[] = "surah_name = ?";
    $params[] = $surah_filter;
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

if (!empty($duration_filter)) {
    switch ($duration_filter) {
        case 'short': // Less than 15 minutes (900 seconds)
            $where_clauses[] = "TIME_TO_SEC(video_duration) < 900";
            break;
        case 'medium': // 15-60 minutes (900-3600 seconds)
            $where_clauses[] = "TIME_TO_SEC(video_duration) >= 900 AND TIME_TO_SEC(video_duration) <= 3600";
            break;
        case 'long': // More than 60 minutes (3600 seconds)
            $where_clauses[] = "TIME_TO_SEC(video_duration) > 3600";
            break;
    }
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY publish_date DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        /* ===== الديف الرئيسي للكروت ===== */
        echo '<div class="recitations-flex">';

        while ($row = $result->fetch_assoc()) {

            $description = nl2br($row['description'] ?? '');
            $short_description = mb_strlen(strip_tags($description), 'UTF-8') > 100
                ? mb_substr(strip_tags($description), 0, 100, 'UTF-8') . '...'
                : $description;

            $modal_data_attributes =
                'data-id="' . $row['id'] . '" ' .
                'data-title="' . htmlspecialchars($row['title']) . '" ' .
                'data-surah_name="' . htmlspecialchars($row['surah_name']) . '" ' .
                'data-reciter_name="' . htmlspecialchars($row['reciter_name']) . '" ' .
                'data-video_duration="' . htmlspecialchars($row['video_duration'] ?: 'غير محدد') . '" ' .
                'data-publish_date="' . htmlspecialchars($row['publish_date'] ?: 'غير محدد') . '" ' .
                'data-description="' . htmlspecialchars($description) . '" ' .
                'data-youtube_embed_code="' . htmlspecialchars($row['youtube_embed_code']) . '" ';

            echo '<div class="recitation-item card open-modal-card" ' . $modal_data_attributes . '>';

            echo '  <div class="video-content-wrapper">';
            echo '      <div class="video-container">';
            echo            enhanceYouTubeEmbed($row['youtube_embed_code']);
            echo '      </div>';

            echo '      <div class="video-info">';
            echo '          <h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '          <div class="video-meta">';
            echo '              <p><strong>السورة:</strong> ' . htmlspecialchars($row['surah_name']) . '</p>';
            echo '              <p><strong>القارئ:</strong> ' . htmlspecialchars($row['reciter_name']) . '</p>';
            echo '              <p><strong>المدة:</strong> ' . ($row['video_duration'] ?: 'غير محدد') . '</p>';
            echo '              <p><strong>تاريخ النشر:</strong> ' . ($row['publish_date'] ?: 'غير محدد') . '</p>';
            echo '          </div>';

            if (!empty($description)) {
                echo '      <p class="description-text"><strong>الوصف:</strong> ' . $short_description . '</p>';
            }

            echo '      </div>';
            echo '  </div>';
            echo '</div>';
        }

        echo '</div>'; // end recitations-flex

    } else {
        // No tilawat found after search/filter
        echo '<p>لا توجد تلاوات مطابقة لبحثك أو فلاترك.</p>';
    }
    $stmt->close(); // Close statement here
} else {
    // Handle prepare error, e.g., log it and display a user-friendly message
    error_log("Failed to prepare statement: " . $conn->error);
    echo '<p>حدث خطأ أثناء إعداد استعلام البحث. الرجاء المحاولة مرة أخرى لاحقًا.</p>';
}
?>

</section>

    <!-- The Modal -->
    <div id="recitationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"></h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <div class="video-container" id="modalVideoContainer">
                    <button class="fullscreen-btn" id="modalFullscreenBtn" title="ملء الشاشة">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <p><strong>السورة:</strong> <span id="modalSurahName"></span></p>
                <p><strong>القارئ:</strong> <span id="modalReciterName"></span></p>
                <p><strong>المدة:</strong> <span id="modalVideoDuration"></span></p>
                <p><strong>تاريخ النشر:</strong> <span id="modalPublishDate"></span></p>
                <p><strong>الوصف:</strong> <span id="modalDescription"></span></p>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>