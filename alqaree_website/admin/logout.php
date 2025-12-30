<?php
session_start();

// تسجيل النشاط قبل تسجيل الخروج
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    include '../includes/db_connect.php';
    include '../includes/activity_logger.php';
    logUserActivity('user_logout', "تم تسجيل الخروج من النظام");
}

// مسح جميع بيانات الجلسة
$_SESSION = array();

// مسح cookie الجلسة إذا كان موجود
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// تدمير الجلسة
session_destroy();

// توجيه المستخدم لصفحة تسجيل الدخول
header('Location: login.php?logout=success');
exit();
?>
