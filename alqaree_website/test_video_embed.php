<?php
include 'includes/db_connect.php';

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
.test-section { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.video-container { margin: 20px 0; }
iframe { width: 100%; height: 300px; border-radius: 8px; }
</style>";

echo "<h1>اختبار تضمين الفيديوهات</h1>";

// اختبار التلاوات
echo "<div class='test-section'>";
echo "<h2>اختبار التلاوات</h2>";

$sql = "SELECT id, title, youtube_embed_code FROM tilawat ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $tilawat = $result->fetch_assoc();
    echo "<h3>" . htmlspecialchars($tilawat['title']) . "</h3>";
    echo "<p><strong>البيانات الأصلية:</strong> " . htmlspecialchars(substr($tilawat['youtube_embed_code'], 0, 100)) . "...</p>";

    // استخراج video ID
    $embed_code = $tilawat['youtube_embed_code'];
    $video_id = null;

    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $embed_code)) {
        $video_id = $embed_code;
    }

    if ($video_id) {
        echo "<p class='success'>✅ تم استخراج Video ID: " . $video_id . "</p>";
        $embed_url = "https://www.youtube-nocookie.com/embed/" . $video_id . "?rel=0&modestbranding=1";
        echo "<div class='video-container'>";
        echo "<iframe src='$embed_url' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' allowfullscreen loading='lazy'></iframe>";
        echo "</div>";
    } else {
        echo "<p class='error'>❌ فشل في استخراج Video ID</p>";
        if (strpos($embed_code, '<iframe') !== false) {
            echo "<p class='warning'>⚠️ البيانات تحتوي على iframe كامل</p>";
            echo "<div class='video-container'>" . $embed_code . "</div>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ لا توجد تلاوات في قاعدة البيانات</p>";
}

echo "</div>";

// اختبار المواعظ
echo "<div class='test-section'>";
echo "<h2>اختبار المواعظ</h2>";

$sql = "SELECT id, title, youtube_embed_code FROM hekum ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $hekum = $result->fetch_assoc();
    echo "<h3>" . htmlspecialchars($hekum['title']) . "</h3>";
    echo "<p><strong>البيانات الأصلية:</strong> " . htmlspecialchars(substr($hekum['youtube_embed_code'], 0, 100)) . "...</p>";

    // استخراج video ID
    $embed_code = $hekum['youtube_embed_code'];
    $video_id = null;

    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
        $video_id = $matches[1];
    } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $embed_code)) {
        $video_id = $embed_code;
    }

    if ($video_id) {
        echo "<p class='success'>✅ تم استخراج Video ID: " . $video_id . "</p>";
        $embed_url = "https://www.youtube-nocookie.com/embed/" . $video_id . "?rel=0&modestbranding=1";
        echo "<div class='video-container'>";
        echo "<iframe src='$embed_url' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' allowfullscreen loading='lazy'></iframe>";
        echo "</div>";
    } else {
        echo "<p class='error'>❌ فشل في استخراج Video ID</p>";
        if (strpos($embed_code, '<iframe') !== false) {
            echo "<p class='warning'>⚠️ البيانات تحتوي على iframe كامل</p>";
            echo "<div class='video-container'>" . $embed_code . "</div>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ لا توجد مواعظ في قاعدة البيانات</p>";
}

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>معلومات إضافية</h2>";
echo "<p><strong>الوقت:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>الخادم:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// جلب عدد السجلات
$tilawat_count = $conn->query("SELECT COUNT(*) as count FROM tilawat")->fetch_assoc()['count'];
$hekum_count = $conn->query("SELECT COUNT(*) as count FROM hekum")->fetch_assoc()['count'];

echo "<p><strong>عدد التلاوات:</strong> $tilawat_count</p>";
echo "<p><strong>عدد المواعظ:</strong> $hekum_count</p>";
echo "</div>";

$conn->close();
?>
