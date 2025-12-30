<?php
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
.test-section { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.link-test { padding: 10px; margin: 5px 0; background: #f9f9f9; border-radius: 4px; }
</style>";

echo "<h1>اختبار جميع الروابط في الموقع</h1>";

// قائمة الروابط للاختبار
$links_to_test = [
    'الصفحة الرئيسية' => 'index.php',
    'القرآن الكريم' => 'quran.php',
    'التلاوات' => 'tilawat.php',
    'المواعظ' => 'hekum.php',
    'المقالات' => 'articles.php',
    'خريطة المشروع' => 'project-links.php',
    'API الآيات' => 'get_random_ayah.php',
    'API التلاوات' => 'get_random_tilawat.php',
    'API المواعظ' => 'get_random_hekum.php',
    'API المقالات' => 'get_random_articles.php',
    'اختبار قاعدة البيانات' => 'test_db.php',
    'اختبار APIs' => 'test_api.php',
    'اختبار الترميز' => 'test_encoding_simple.html',
    'إدراج بيانات تجريبية' => 'insert_sample_videos.php',
];

echo "<div class='test-section'>";
echo "<h2>نتائج اختبار الروابط</h2>";

foreach ($links_to_test as $name => $link) {
    $full_url = "http://localhost/alqaree_website/$link";

    // محاولة الوصول للرابط
    $headers = @get_headers($full_url);
    $status = false;

    if ($headers) {
        $status_code = substr($headers[0], 9, 3);
        $status = ($status_code >= 200 && $status_code < 400);
    }

    echo "<div class='link-test'>";
    if ($status) {
        echo "<span class='success'>✅ $name</span>";
        echo " - <a href='$link' target='_blank'>$link</a>";
    } else {
        echo "<span class='error'>❌ $name</span>";
        echo " - $link (غير متاح)";
    }
    echo "</div>";
}

echo "</div>";

// إحصائيات سريعة
echo "<div class='test-section'>";
echo "<h2>إحصائيات الموقع</h2>";

// عد ملفات PHP
$php_files = glob("*.php");
$js_files = glob("js/*.js");
$css_files = glob("css/*.css");

echo "<p><strong>ملفات PHP:</strong> " . count($php_files) . "</p>";
echo "<p><strong>ملفات JavaScript:</strong> " . count($js_files) . "</p>";
echo "<p><strong>ملفات CSS:</strong> " . count($css_files) . "</p>";
echo "<p><strong>وقت الإنشاء:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

// تحقق من وجود المشاكل الشائعة
echo "<div class='test-section'>";
echo "<h2>فحص المشاكل الشائعة</h2>";

$issues = [];

// فحص الاتصال بقاعدة البيانات
try {
    include 'includes/db_connect.php';
    $result = $conn->query("SELECT 1");
    if (!$result) {
        $issues[] = "مشكلة في الاتصال بقاعدة البيانات";
    }
    $conn->close();
} catch (Exception $e) {
    $issues[] = "خطأ في قاعدة البيانات: " . $e->getMessage();
}

// فحص وجود الملفات الأساسية
$required_files = ['index.php', 'includes/header.php', 'includes/footer.php', 'css/style.css'];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $issues[] = "ملف مفقود: $file";
    }
}

if (empty($issues)) {
    echo "<p class='success'>✅ لا توجد مشاكل شائعة مكتشفة</p>";
} else {
    echo "<p class='warning'>⚠️ تم العثور على " . count($issues) . " مشاكل:</p>";
    foreach ($issues as $issue) {
        echo "<p class='error'>• $issue</p>";
    }
}

echo "</div>";
?>
