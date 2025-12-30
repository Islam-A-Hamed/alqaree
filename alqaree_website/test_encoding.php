<?php
// Force UTF-8 encoding
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار الترميز - موقع القارئ</title>
</head>
<body>
    <h1>اختبار الترميز العربي</h1>
    <p>إذا كنت ترى هذا النص باللغة العربية بشكل صحيح، فإن الترميز يعمل!</p>
    <p>القرآن الكريم: بسم الله الرحمن الرحيم</p>
    <p>التاريخ الحالي: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p>إصدار PHP: <?php echo PHP_VERSION; ?></p>
    <p>ترميز النظام: <?php echo ini_get('default_charset'); ?></p>
</body>
</html>
