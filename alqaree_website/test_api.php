<?php
// اختبار جميع الـ APIs
$apis = [
    'ayah' => 'get_random_ayah.php',
    'articles' => 'get_random_articles.php',
    'tilawat' => 'get_random_tilawat.php',
    'hekum' => 'get_random_hekum.php'
];

$results = [];

foreach ($apis as $name => $endpoint) {
    $url = "http://localhost/alqaree_website/$endpoint";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'timeout' => 10
        ]
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data !== null) {
            $results[$name] = [
                'status' => 'success',
                'success' => $data['success'] ?? false,
                'data' => $data
            ];
        } else {
            $results[$name] = [
                'status' => 'error',
                'error' => 'Invalid JSON response'
            ];
        }
    } else {
        $results[$name] = [
            'status' => 'error',
            'error' => 'Could not connect to API'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار APIs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; direction: rtl; }
        .api-test { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; max-width: 100%; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>اختبار APIs الموقع</h1>

    <?php foreach ($results as $name => $result): ?>
        <div class="api-test">
            <h2><?php echo htmlspecialchars($name); ?> API</h2>
            <?php if ($result['status'] === 'success'): ?>
                <?php if ($result['success']): ?>
                    <div class="success">✅ API يعمل بشكل صحيح</div>
                    <details>
                        <summary>عرض البيانات</summary>
                        <pre><?php echo json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
                    </details>
                <?php else: ?>
                    <div class="warning">⚠️ API يعيد success=false</div>
                    <pre><?php echo json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></pre>
                <?php endif; ?>
            <?php else: ?>
                <div class="error">❌ خطأ في الاتصال: <?php echo htmlspecialchars($result['error']); ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="api-test">
        <h2>معلومات النظام</h2>
        <p><strong>الوقت:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>الخادم:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'غير محدد'; ?></p>
        <p><strong>PHP:</strong> <?php echo PHP_VERSION; ?></p>
    </div>
</body>
</html>
