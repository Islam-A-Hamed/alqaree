<?php
// معالجة طلبات OPTIONS للـ CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
    // جلب آية عشوائية من القرآن الكريم مع معلومات السورة
    $sql = "SELECT
                qv.id,
                qv.surah_number,
                qv.ayah_number,
                qv.ayah_text,
                qv.juz_number,
                qv.page_number,
                qs.surah_name_arabic,
                qs.surah_name_english,
                qs.revelation_type,
                qs.total_ayahs,
                qs.revelation_order
            FROM quran_verses qv
            INNER JOIN quran_surahs qs ON qv.surah_number = qs.surah_number
            ORDER BY RAND()
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $ayah = $result->fetch_assoc();

        // تنسيق البيانات للاستجابة
        $response = [
            'success' => true,
            'ayah' => [
                'id' => $ayah['id'],
                'surah_number' => $ayah['surah_number'],
                'surah_name_arabic' => $ayah['surah_name_arabic'],
                'surah_name_english' => $ayah['surah_name_english'],
                'ayah_number' => $ayah['ayah_number'],
                'ayah_text' => $ayah['ayah_text'],
                'juz_number' => $ayah['juz_number'],
                'page_number' => $ayah['page_number'],
                'revelation_type' => $ayah['revelation_type'],
                'total_ayahs' => $ayah['total_ayahs'],
                'revelation_order' => $ayah['revelation_order'],
                'reference' => "سورة {$ayah['surah_name_arabic']} - الآية {$ayah['ayah_number']}",
                'revelation_type_arabic' => $ayah['revelation_type'] === 'meccan' ? 'مكية' : 'مدنية'
            ]
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // في حالة عدم وجود نتائج
        echo json_encode([
            'success' => false,
            'error' => 'لم يتم العثور على آيات في قاعدة البيانات'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    // في حالة حدوث خطأ
    echo json_encode([
        'success' => false,
        'error' => 'حدث خطأ في استرجاع الآية: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>
