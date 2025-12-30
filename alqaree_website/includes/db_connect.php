<?php

include 'config.php'; // Include error reporting and other configurations

$servername = "localhost"; // غالباً يكون "localhost"
$username = "root";       // اسم مستخدم قاعدة البيانات الخاصة بك (غالباً "root" في XAMPP)
$password = "";           // كلمة مرور قاعدة البيانات (غالباً فارغة في XAMPP)
$dbname = "alqaree_website"; // اسم قاعدة البيانات التي أنشأتها

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تعيين ترميز الأحرف UTF-8 لدعم اللغة العربية
$conn->set_charset("utf8mb4");

?>
