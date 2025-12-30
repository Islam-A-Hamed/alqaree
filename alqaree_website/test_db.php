<?php
include 'includes/db_connect.php';

// اختبار الجداول المتاحة
$tables = ['quran_verses', 'articles', 'tilawat', 'hekum'];
$results = [];

foreach ($tables as $table) {
    $sql = "SELECT COUNT(*) as count FROM `$table`";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $results[$table] = $row['count'];
    } else {
        $results[$table] = "خطأ: " . $conn->error;
    }
}

// اختبار جلب آية عشوائية
$sql = "SELECT id, surah_number, ayah_number, ayah_text FROM quran_verses ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);
$sample_ayah = null;
if ($result && $result->num_rows > 0) {
    $sample_ayah = $result->fetch_assoc();
}

// اختبار جلب مقالة عشوائية
$sql = "SELECT id, title, author_name FROM articles ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);
$sample_article = null;
if ($result && $result->num_rows > 0) {
    $sample_article = $result->fetch_assoc();
}

// اختبار جلب تلاوة عشوائية
$sql = "SELECT id, title, reciter_name FROM tilawat ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);
$sample_tilawat = null;
if ($result && $result->num_rows > 0) {
    $sample_tilawat = $result->fetch_assoc();
}

// اختبار جلب موعظة عشوائية
$sql = "SELECT id, title, speaker_name FROM hekum ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);
$sample_hekum = null;
if ($result && $result->num_rows > 0) {
    $sample_hekum = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار قاعدة البيانات</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; direction: rtl; }
        .test-section { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; max-width: 100%; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>اختبار قاعدة البيانات</h1>

    <div class="test-section">
        <h2>عدد السجلات في الجداول</h2>
        <?php foreach ($results as $table => $count): ?>
            <div class="<?php echo is_numeric($count) ? (intval($count) > 0 ? 'success' : 'warning') : 'error'; ?>">
                <?php echo $table; ?>: <?php echo $count; ?> سجل
            </div>
        <?php endforeach; ?>
    </div>

    <div class="test-section">
        <h2>عينة من الآيات القرآنية</h2>
        <?php if ($sample_ayah): ?>
            <div class="success">✅ تم العثور على آية</div>
            <pre><?php echo json_encode($sample_ayah, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
        <?php else: ?>
            <div class="error">❌ لا توجد آيات في قاعدة البيانات</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>عينة من المقالات</h2>
        <?php if ($sample_article): ?>
            <div class="success">✅ تم العثور على مقالة</div>
            <pre><?php echo json_encode($sample_article, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
        <?php else: ?>
            <div class="warning">⚠️ لا توجد مقالات في قاعدة البيانات</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>عينة من التلاوات</h2>
        <?php if ($sample_tilawat): ?>
            <div class="success">✅ تم العثور على تلاوة</div>
            <pre><?php echo json_encode($sample_tilawat, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
        <?php else: ?>
            <div class="warning">⚠️ لا توجد تلاوات في قاعدة البيانات</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>عينة من المواعظ</h2>
        <?php if ($sample_hekum): ?>
            <div class="success">✅ تم العثور على موعظة</div>
            <pre><?php echo json_encode($sample_hekum, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
        <?php else: ?>
            <div class="warning">⚠️ لا توجد مواعظ في قاعدة البيانات</div>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>معلومات إضافية</h2>
        <p><strong>وقت التنفيذ:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>إصدار PHP:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>ترميز قاعدة البيانات:</strong> utf8mb4</p>
    </div>
</body>
</html>
