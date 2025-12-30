<?php
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
    // جلب مقال عشوائي واحد من قاعدة البيانات
    $sql = "SELECT * FROM articles ORDER BY RAND() LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $article = $result->fetch_assoc();

        // تنسيق البيانات للاستجابة
        $response = [
            'success' => true,
            'article' => [
                'id' => $article['id'],
                'title' => $article['title'],
                'author_name' => $article['author_name'],
                'category' => $article['category'] ?: 'عام',
                'content' => $article['content'],
                'publish_date' => $article['publish_date'] ?: 'غير محدد',
                'excerpt' => mb_substr(strip_tags($article['content']), 0, 150, 'UTF-8') .
                            (mb_strlen(strip_tags($article['content']), 'UTF-8') > 150 ? '...' : '')
            ]
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // في حالة عدم وجود مقالات
        echo json_encode([
            'success' => false,
            'error' => 'لم يتم العثور على مقالات في قاعدة البيانات'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    // في حالة حدوث خطأ
    echo json_encode([
        'success' => false,
        'error' => 'حدث خطأ في استرجاع المقال: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>
