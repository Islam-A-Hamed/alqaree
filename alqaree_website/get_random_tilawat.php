<?php
// API قديم لجلب تلاوات عشوائية (مُستبدل)
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// السماح بالوصول من نفس النطاق فقط (CORS)
$allowed_origins = ['localhost', '127.0.0.1', 'alqaree_website'];
$host = $_SERVER['HTTP_HOST'] ?? '';

if (!empty($host) && !preg_match('/^(localhost|127\.0\.0\.1|alqaree_website)/i', $host)) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Access denied'], JSON_UNESCAPED_UNICODE));
}

// تضمين ملف الاتصال بقاعدة البيانات
require_once 'includes/db_connect.php';

try {
    // جلب تلاوة عشوائية من قاعدة البيانات
    $sql = "SELECT * FROM tilawat ORDER BY RAND() LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $tilawat = $result->fetch_assoc();

        // استخراج video ID من youtube_embed_code
        $embed_code = $tilawat['youtube_embed_code'];
        $video_id = null;

        // التحقق من أنواع مختلفة من الروابط
        if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
            $video_id = $matches[1];
        } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
            $video_id = $matches[1];
        } elseif (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $embed_code, $matches)) {
            $video_id = $matches[1];
        } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $embed_code)) {
            // إذا كان embed_code هو video ID مباشرة
            $video_id = $embed_code;
        }

        // إنشاء iframe إذا تم العثور على video ID
        $iframe_html = '';
        if ($video_id) {
            $iframe_html = '<iframe src="https://www.youtube-nocookie.com/embed/' . htmlspecialchars($video_id) . '?rel=0&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width: 100%; height: 500px; border-radius: 12px;"></iframe>';
        } else {
            // إذا كان embed_code هو iframe جاهز، استخدمه كما هو
            if (strpos($embed_code, '<iframe') !== false) {
                $iframe_html = $embed_code;
            } else {
                $iframe_html = '<div class="error-message">خطأ في رابط الفيديو</div>';
            }
        }

        // تنسيق البيانات للاستجابة
        $response = [
            'success' => true,
            'tilawat' => [
                'id' => $tilawat['id'],
                'title' => $tilawat['title'],
                'surah_name' => $tilawat['surah_name'],
                'reciter_name' => $tilawat['reciter_name'],
                'video_duration' => $tilawat['video_duration'] ?: 'غير محدد',
                'description' => $tilawat['description'] ?: '',
                'youtube_embed_code' => $iframe_html,
                'video_id' => $video_id, // إضافة video_id للتشخيص
                'views_count' => $tilawat['views_count'],
                'publish_date' => $tilawat['publish_date'] ?: 'غير محدد'
            ]
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // في حالة عدم وجود تلاوات
        echo json_encode([
            'success' => false,
            'error' => 'لم يتم العثور على تلاوات في قاعدة البيانات'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    // في حالة حدوث خطأ
    echo json_encode([
        'success' => false,
        'error' => 'حدث خطأ في استرجاع التلاوة: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>
