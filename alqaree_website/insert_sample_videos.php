<?php
include 'includes/db_connect.php';

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
</style>";

echo "<h1>إدراج بيانات فيديوهات تجريبية</h1>";

// فيديوهات تلاوات تجريبية (معروفة وتعمل)
$sample_tilawat = [
    [
        'title' => 'سورة البقرة - القارئ عبدالرحمن السديس',
        'surah_name' => 'سورة البقرة',
        'reciter_name' => 'عبدالرحمن السديس',
        'video_duration' => '15:30',
        'youtube_embed_code' => 'dQw4w9WgXcQ', // rickroll - video ID فقط
        'description' => 'تلاوة مباركة لسورة البقرة بصوت القارئ عبدالرحمن السديس',
        'views_count' => 1250,
        'publish_date' => '2024-01-15'
    ],
    [
        'title' => 'سورة الفاتحة - القارئ ماهر المعيقلي',
        'surah_name' => 'سورة الفاتحة',
        'reciter_name' => 'ماهر المعيقلي',
        'video_duration' => '02:45',
        'youtube_embed_code' => 'dQw4w9WgXcQ', // نفس الفيديو للاختبار
        'description' => 'تلاوة جميلة لسورة الفاتحة',
        'views_count' => 890,
        'publish_date' => '2024-01-10'
    ]
];

// فيديوهات مواعظ تجريبية
$sample_hekum = [
    [
        'title' => 'فضائل صيام رمضان',
        'speaker_name' => 'الشيخ سعود الشريم',
        'video_duration' => '25:15',
        'youtube_embed_code' => 'dQw4w9WgXcQ', // rickroll video ID
        'description' => 'موعظة قيمة عن فضائل شهر رمضان المبارك',
        'views_count' => 2100,
        'publish_date' => '2024-01-20'
    ],
    [
        'title' => 'أهمية الصلاة في وقتها',
        'speaker_name' => 'الشيخ عبدالعزيز آل الشيخ',
        'video_duration' => '18:30',
        'youtube_embed_code' => 'dQw4w9WgXcQ', // نفس الفيديو للاختبار
        'description' => 'موعظة عن أهمية المحافظة على الصلاة في أوقاتها المحددة',
        'views_count' => 1650,
        'publish_date' => '2024-01-18'
    ]
];

// إدراج التلاوات
echo "<h2>إدراج التلاوات التجريبية</h2>";
foreach ($sample_tilawat as $tilawat) {
    $sql = "INSERT INTO tilawat (title, surah_name, reciter_name, video_duration, youtube_embed_code, description, views_count, publish_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis",
        $tilawat['title'],
        $tilawat['surah_name'],
        $tilawat['reciter_name'],
        $tilawat['video_duration'],
        $tilawat['youtube_embed_code'],
        $tilawat['description'],
        $tilawat['views_count'],
        $tilawat['publish_date']
    );

    if ($stmt->execute()) {
        echo "<p class='success'>✅ تم إدراج التلاوة: " . htmlspecialchars($tilawat['title']) . "</p>";
    } else {
        echo "<p class='error'>❌ خطأ في إدراج التلاوة: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// إدراج المواعظ
echo "<h2>إدراج المواعظ التجريبية</h2>";
foreach ($sample_hekum as $hekum) {
    $sql = "INSERT INTO hekum (title, speaker_name, video_duration, youtube_embed_code, description, views_count, publish_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE title = VALUES(title)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis",
        $hekum['title'],
        $hekum['speaker_name'],
        $hekum['video_duration'],
        $hekum['youtube_embed_code'],
        $hekum['description'],
        $hekum['views_count'],
        $hekum['publish_date']
    );

    if ($stmt->execute()) {
        echo "<p class='success'>✅ تم إدراج الموعظة: " . htmlspecialchars($hekum['title']) . "</p>";
    } else {
        echo "<p class='error'>❌ خطأ في إدراج الموعظة: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// عرض الإحصائيات النهائية
echo "<h2>الإحصائيات النهائية</h2>";
$tilawat_count = $conn->query("SELECT COUNT(*) as count FROM tilawat")->fetch_assoc()['count'];
$hekum_count = $conn->query("SELECT COUNT(*) as count FROM hekum")->fetch_assoc()['count'];

echo "<p><strong>عدد التلاوات:</strong> $tilawat_count</p>";
echo "<p><strong>عدد المواعظ:</strong> $hekum_count</p>";

if ($tilawat_count > 0 && $hekum_count > 0) {
    echo "<p class='success'>✅ تم إدراج البيانات التجريبية بنجاح!</p>";
    echo "<p><a href='index.php' target='_blank'>اذهب للصفحة الرئيسية للاختبار</a></p>";
} else {
    echo "<p class='warning'>⚠️ قد تحتاج لإضافة بيانات إضافية</p>";
}

$conn->close();
?>
