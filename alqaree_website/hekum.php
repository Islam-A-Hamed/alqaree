<?php
$page_title = 'الحكم والمواعظ';
$page_description = 'مجموعة من الحكم والمواعظ القيمة والدروس الإسلامية من السنة النبوية والسيرة العطرة';

include 'includes/db_connect.php';
include 'includes/header.php';

/* ========= YouTube Embed Function ========= */
function enhanceYouTubeEmbed($embedCode) {
    $videoId = null;

    if (preg_match('/<iframe[^>]*src="([^"]*)"/i', $embedCode, $matches)) {
        $url = $matches[1];
    } else {
        $url = $embedCode;
    }

    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
        '/youtube\.com\/v\/([^&\n?#]+)/'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $videoId = $matches[1];
            break;
        }
    }

    if (!$videoId) {
        return $embedCode;
    }

    $embedUrl = "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0&modestbranding=1";

    return '<iframe src="' . $embedUrl . '" loading="lazy" allowfullscreen></iframe>';
}
?>

<section class="page-content islamic-decor">

    <div class="main-details" style="width:100%; display:flex; flex-direction:column; align-items:center;">
        <div class="basmala-text sacred-float" style="font-size:4rem;">﷽</div>
        <h1 class="sacred-text quran-reveal">الحكم والمواعظ</h1>
        <p style="font-size:1.3rem; margin-bottom:40px;">
            استمع إلى حكم ومواعظ قيمة من السنة النبوية والسيرة العطرة
        </p>
    </div>

    <!-- Advanced Search and Filter Form -->
    <div class="islamic-border" style="margin: 40px auto; padding: 30px; max-width: 1200px; width: 90%;">
        <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 25px; font-family: var(--heading-font);">
            بحث متقدم وفلترة المواعظ
        </h2>
        <form action="" method="GET" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="text" name="search_query" placeholder="ابحث في المواعظ (العنوان, المتحدث, الوصف)"
                   value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>"
                   style="padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; text-align: right; direction: rtl;">

            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                <!-- Speaker Filter -->
                <select name="speaker_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">المتحدث (الكل)</option>
                    <?php
                    // Fetch speakers dynamically
                    $speakers = [];
                    if ($conn) {
                        $speaker_result = $conn->query("SELECT DISTINCT speaker_name FROM hekum WHERE speaker_name IS NOT NULL AND speaker_name != ''");
                        while ($speaker_row = $speaker_result->fetch_assoc()) {
                            $speakers[] = $speaker_row['speaker_name'];
                        }
                    }
                    foreach ($speakers as $speaker):
                        $selected = (isset($_GET['speaker_filter']) && $_GET['speaker_filter'] == $speaker) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($speaker); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($speaker); ?></option>
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
                <a href="hekum.php" style="background: #dc3545; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    إزالة الفلاتر
                </a>
            </div>
        </form>
    </div>

<?php
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$speaker_filter = isset($_GET['speaker_filter']) ? $_GET['speaker_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$duration_filter = isset($_GET['duration_filter']) ? $_GET['duration_filter'] : '';

$sql = "SELECT * FROM hekum";
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE ? OR description LIKE ? OR speaker_name LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= "sss";
}

if (!empty($speaker_filter)) {
    $where_clauses[] = "speaker_name = ?";
    $params[] = $speaker_filter;
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

        /* ===== Flex Wrapper (مرة واحدة فقط) ===== */
        echo '<div class="sermons-flex">';

        while ($row = $result->fetch_assoc()) {

            $description = nl2br($row['description'] ?? '');
            $short_description = mb_strlen(strip_tags($description), 'UTF-8') > 100
                ? mb_substr(strip_tags($description), 0, 100, 'UTF-8') . '...'
                : $description;

            $modal_data_attributes =
                'data-id="' . $row['id'] . '" ' .
                'data-title="' . htmlspecialchars($row['title']) . '" ' .
                'data-speaker_name="' . htmlspecialchars($row['speaker_name']) . '" ' .
                'data-video_duration="' . htmlspecialchars($row['video_duration'] ?: 'غير محدد') . '" ' .
                'data-publish_date="' . htmlspecialchars($row['publish_date'] ?: 'غير محدد') . '" ' .
                'data-description="' . htmlspecialchars($description) . '" ' .
                'data-youtube_embed_code="' . htmlspecialchars($row['youtube_embed_code']) . '"';

            echo '<div class="sermon-item card open-modal-card" ' . $modal_data_attributes . '>';

            echo '  <div class="video-content-wrapper">';
            echo '      <div class="video-container">';
            echo            enhanceYouTubeEmbed($row['youtube_embed_code']);
            echo '      </div>';

            echo '      <div class="video-info">';
            echo '          <h3>' . htmlspecialchars($row['title']) . '</h3>';
            echo '          <div class="video-meta">';
            echo '              <p><strong>المتحدث:</strong> ' . htmlspecialchars($row['speaker_name']) . '</p>';
            echo '              <p><strong>المدة:</strong> ' . ($row['video_duration'] ?: 'غير محدد') . '</p>';
            echo '              <p><strong>تاريخ النشر:</strong> ' . ($row['publish_date'] ?: 'غير محدد') . '</p>';
            echo '          </div>';

            if (!empty($description)) {
                echo '<p class="description-text"><strong>الوصف:</strong> ' . $short_description . '</p>';
            }

            echo '      </div>';
            echo '  </div>';
            echo '</div>';
        }

        echo '</div>'; // end sermons-flex

    } else {
        // No hekum found after search/filter
        echo '<p>لا توجد مواعظ مطابقة لبحثك أو فلاترك.</p>';
    }
    $stmt->close(); // Close statement here
} else {
    // Handle prepare error, e.g., log it and display a user-friendly message
    error_log("Failed to prepare statement: " . $conn->error);
    echo '<p>حدث خطأ أثناء إعداد استعلام البحث. الرجاء المحاولة مرة أخرى لاحقًا.</p>';
}
?>

</section>

<!-- ========= Modal ========= -->
<div id="sermonModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalSermonTitle"></h2>
            <span class="close-btn">&times;</span>
        </div>
        <div class="modal-body">
            <div class="video-container" id="modalSermonVideoContainer">
                <button class="fullscreen-btn" id="modalSermonFullscreenBtn" title="ملء الشاشة">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" fill="currentColor"/>
                    </svg>
                </button>
            </div>
            <p><strong>المتحدث:</strong> <span id="modalSpeakerName"></span></p>
            <p><strong>المدة:</strong> <span id="modalSermonVideoDuration"></span></p>
            <p><strong>تاريخ النشر:</strong> <span id="modalSermonPublishDate"></span></p>
            <p><strong>الوصف:</strong> <span id="modalSermonDescription"></span></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>