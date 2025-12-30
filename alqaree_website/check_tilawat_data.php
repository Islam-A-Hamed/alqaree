<?php
include 'includes/db_connect.php';

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
th { background-color: #f2f2f2; }
.code { font-family: monospace; font-size: 12px; max-width: 400px; word-break: break-all; }
.analysis { background: #f9f9f9; padding: 10px; margin: 10px 0; border-radius: 5px; }
</style>";

echo "<h1>تحليل بيانات التلاوات</h1>";

// جلب عينة من بيانات التلاوات
$sql = "SELECT id, title, youtube_embed_code FROM tilawat LIMIT 10";
$result = $conn->query($sql);

echo "<table>";
echo "<tr><th>ID</th><th>Title</th><th>YouTube Embed Code</th><th>Length</th><th>Type</th></tr>";

$embed_codes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $code = $row['youtube_embed_code'];
        $embed_codes[] = $code;

        // تحليل نوع الكود
        $type = "غير محدد";
        if (strpos($code, 'youtube.com/embed/') !== false) {
            $type = "Embed URL";
        } elseif (strpos($code, 'youtu.be/') !== false) {
            $type = "Short URL";
        } elseif (strpos($code, 'youtube.com/watch?v=') !== false) {
            $type = "Watch URL";
        } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $code)) {
            $type = "Video ID";
        } elseif (strpos($code, '<iframe') !== false) {
            $type = "Full iframe";
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td class='code'>" . htmlspecialchars($code) . "</td>";
        echo "<td>" . strlen($code) . "</td>";
        echo "<td>" . $type . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>لا توجد بيانات تلاوات</td></tr>";
}

echo "</table>";

// تحليل البيانات
echo "<div class='analysis'>";
echo "<h2>تحليل البيانات:</h2>";

if (!empty($embed_codes)) {
    $sample = $embed_codes[0];
    echo "<p><strong>عينة من البيانات:</strong> " . htmlspecialchars(substr($sample, 0, 100)) . "...</p>";

    // استخراج video ID من أنواع مختلفة
    $video_id = null;
    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $sample, $matches)) {
        $video_id = $matches[1];
        echo "<p><strong>✅ تم العثور على Video ID من embed URL:</strong> " . $video_id . "</p>";
    } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $sample, $matches)) {
        $video_id = $matches[1];
        echo "<p><strong>✅ تم العثور على Video ID من short URL:</strong> " . $video_id . "</p>";
    } elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $sample, $matches)) {
        $video_id = $matches[1];
        echo "<p><strong>✅ تم العثور على Video ID من watch URL:</strong> " . $video_id . "</p>";
    } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $sample)) {
        $video_id = $sample;
        echo "<p><strong>✅ البيانات تحتوي على Video ID مباشر:</strong> " . $video_id . "</p>";
    } else {
        echo "<p><strong>❌ لم يتم التعرف على تنسيق البيانات</strong></p>";
    }

    if ($video_id) {
        $embed_url = "https://www.youtube.com/embed/" . $video_id;
        echo "<p><strong>الـ embed URL الصحيح:</strong> " . $embed_url . "</p>";
        echo "<p><strong>اختبار الرابط:</strong> <a href='$embed_url' target='_blank'>$embed_url</a></p>";
    }
} else {
    echo "<p>لا توجد بيانات للتحليل</p>";
}

echo "</div>";

// جلب عدد التلاوات
$sql_count = "SELECT COUNT(*) as count FROM tilawat";
$result_count = $conn->query($sql_count);
if ($result_count) {
    $count = $result_count->fetch_assoc();
    echo "<p><strong>إجمالي التلاوات:</strong> " . $count['count'] . "</p>";
}

$conn->close();
?>
