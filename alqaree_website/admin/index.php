<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// إحصائيات سريعة
require_once '../includes/db_connect.php';
require_once '../includes/activity_logger.php';

$stats = [];

// عدد التلاوات
$result = $conn->query("SELECT COUNT(*) as count FROM tilawat");
$stats['tilawat_count'] = $result->fetch_assoc()['count'];

// عدد المواعظ
$result = $conn->query("SELECT COUNT(*) as count FROM hekum");
$stats['hekum_count'] = $result->fetch_assoc()['count'];

// عدد المقالات
$result = $conn->query("SELECT COUNT(*) as count FROM articles");
$stats['articles_count'] = $result->fetch_assoc()['count'];

// إجمالي المحتوى
$stats['total'] = $stats['tilawat_count'] + $stats['hekum_count'] + $stats['articles_count'];

// النشاطات الأخيرة
$activity_logger = new ActivityLogger($conn);
$recent_activities = $activity_logger->getRecentActivities(10);

$page_title = 'لوحة الإدارة - ' . ($_SESSION['admin_username'] ?? 'المستخدم');
$current_page = 'dashboard';

// التحقق من اكتمال بيانات الجلسة وتحديثها إذا لزم الأمر
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // إذا كان admin_id موجود لكن admin_role غير موجود، نحتاج لتحديث الجلسة
    if (isset($_SESSION['admin_id']) && (!isset($_SESSION['admin_role']) || !isset($_SESSION['admin_full_name']))) {
        require_once '../includes/db_connect.php';

        $stmt = $conn->prepare("SELECT username, full_name, role FROM admin_accounts WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_full_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['admin_role'] = $user['role'];
        }

        $stmt->close();
    }

    // تعيين قيم افتراضية إذا لم تكن موجودة
    if (!isset($_SESSION['admin_role'])) {
        $_SESSION['admin_role'] = 'admin'; // قيمة افتراضية
    }
    if (!isset($_SESSION['admin_full_name'])) {
        $_SESSION['admin_full_name'] = $_SESSION['admin_username'] ?? 'admin';
    }
}

// التحقق من وجود المستخدم الافتراضي في قاعدة البيانات
require_once '../includes/db_connect.php';
$check_admin = $conn->query("SELECT id FROM admin_accounts WHERE username = 'admin'");
if ($check_admin->num_rows === 0) {
    // إنشاء المستخدم الافتراضي إذا لم يكن موجوداً
    $default_password = password_hash('alqaree2024', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin_accounts (username, password_hash, email, full_name, role) VALUES ('admin', '$default_password', 'admin@alqaree.com', 'مدير النظام', 'admin')");
}
// لا نغلق الاتصال هنا لأنه سيتم استخدامه لاحقاً

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="لوحة الإدارة الرئيسية - موقع القارئ">
    <title><?php echo $page_title; ?> - موقع القارئ</title>
    <link rel="stylesheet" href="../css/admin-style.css">
    <link rel="icon" type="image/svg+xml" href="../icon-192x192.svg">
    <style>
        .sidebar-user-info {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-user-info .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user-info .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--admin-secondary), var(--admin-primary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--admin-white);
            font-weight: 700;
            font-size: 18px;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-user-info .user-info div:last-child {
            flex: 1;
        }

        .sidebar-user-info .user-info div:last-child div:first-child {
            font-weight: 700;
            font-size: 16px;
            color: var(--admin-white);
            margin-bottom: 2px;
        }

        .sidebar-user-info .user-info div:last-child div:last-child {
            font-size: 12px;
            opacity: 0.8;
            color: var(--admin-gray-300);
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .sidebar-footer .logout-link {
            display: block;
            text-align: center;
            padding: 12px 20px;
            background: rgba(231, 76, 60, 0.1);
            color: #ff6b6b;
            text-decoration: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            transition: var(--transition-normal);
        }

        .sidebar-footer .logout-link:hover {
            background: rgba(231, 76, 60, 0.2);
            color: #ff5252;
            transform: translateY(-2px);
        }

        /* Sidebar Navigation Links */
        .sidebar-nav-item {
            margin-bottom: 8px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--admin-gray-300);
            text-decoration: none;
            border-radius: var(--radius-lg);
            transition: var(--transition-normal);
            font-weight: 500;
            font-size: 14px;
        }

        .sidebar-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--admin-white);
            transform: translateX(4px);
        }

        .sidebar-nav-link.active {
            background: var(--admin-secondary);
            color: var(--admin-white);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .sidebar-nav-link.active:hover {
            background: var(--admin-primary);
            transform: translateX(4px);
        }

        .sidebar-nav-icon {
            font-size: 18px;
            min-width: 20px;
            text-align: center;
        }

        /* Ensure sidebar layout */
        .admin-sidebar {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .sidebar-nav {
            flex: 1;
        }

        .welcome-hero {
            background: linear-gradient(135deg, var(--admin-secondary) 0%, var(--admin-primary) 100%);
            color: var(--admin-white);
            border-radius: var(--radius-xl);
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .welcome-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .welcome-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .welcome-meta {
            background: rgba(255,255,255,0.1);
            padding: 15px 20px;
            border-radius: var(--radius-lg);
            display: inline-block;
            position: relative;
            z-index: 1;
        }

        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card-large {
            background: var(--admin-white);
            border-radius: var(--radius-xl);
            padding: 30px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--admin-gray-200);
            transition: var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .stat-card-large::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--admin-secondary) 0%, var(--admin-primary) 100%);
        }

        .stat-card-large:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-icon-large {
            font-size: 3.5rem;
            opacity: 0.8;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 600;
            color: var(--admin-success);
        }

        .stat-trend.up {
            color: var(--admin-success);
        }

        .stat-trend.down {
            color: var(--admin-accent);
        }

        .stat-number-large {
            font-size: 3rem;
            font-weight: 800;
            color: var(--admin-primary);
            margin-bottom: 8px;
        }

        .stat-label-large {
            color: var(--admin-gray-600);
            font-size: 16px;
            font-weight: 600;
        }

        .quick-actions-section {
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--admin-gray-800);
            margin: 0;
        }

        .section-subtitle {
            color: var(--admin-gray-500);
            font-size: 14px;
            margin: 0;
        }

        .activity-section .admin-card {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--admin-gray-500);
        }

        .activity-empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--admin-gray-600);
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
        }

        .mobile-menu-btn:hover {
            background: var(--admin-gray-100);
        }

        /* User Settings Section */
        .user-settings-section {
            margin-bottom: 30px;
        }


        .settings-form {
            max-width: 600px;
        }

        .settings-form .form-group {
            margin-bottom: 25px;
        }

        .settings-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--admin-gray-700);
            font-weight: 600;
            font-size: 15px;
        }

        .settings-form .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--admin-gray-300);
            border-radius: var(--radius-lg);
            font-size: 15px;
            transition: var(--transition-normal);
            background: var(--admin-white);
            color: var(--admin-gray-700);
            box-sizing: border-box;
        }

        .settings-form .form-control:focus {
            outline: none;
            border-color: var(--admin-secondary);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.15);
        }

        .settings-form .form-control::placeholder {
            color: var(--admin-gray-400);
        }

        .password-input-container {
            position: relative;
        }

        .password-toggle-btn {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--admin-gray-500);
            cursor: pointer;
            padding: 8px;
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
            font-size: 16px;
        }

        .password-toggle-btn:hover {
            color: var(--admin-secondary);
            background: var(--admin-gray-100);
        }

        .settings-form .form-control.with-toggle {
            padding-left: 50px;
        }

        .settings-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .settings-actions .btn {
            padding: 12px 24px;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition-normal);
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--admin-secondary) 0%, var(--admin-primary) 100%);
            color: var(--admin-white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: var(--admin-gray-200);
            color: var(--admin-gray-700);
        }

        .btn-secondary:hover {
            background: var(--admin-gray-300);
        }

        .alert-message {
            padding: 15px 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--admin-success);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--admin-accent);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .alert-info {
            background: rgba(52, 152, 219, 0.1);
            color: var(--admin-primary);
            border: 1px solid rgba(52, 152, 219, 0.2);
        }

        .alert-icon {
            margin-left: 10px;
            font-size: 18px;
        }

        .current-user-info {
            background: rgba(52, 152, 219, 0.05);
            padding: 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 25px;
            border: 1px solid rgba(52, 152, 219, 0.1);
        }

        .current-user-info h4 {
            color: var(--admin-primary);
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 700;
        }

        .user-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .user-info-label {
            color: var(--admin-gray-600);
            font-weight: 600;
        }

        .user-info-value {
            color: var(--admin-gray-800);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .welcome-hero {
                padding: 30px 20px;
            }

            .welcome-hero h1 {
                font-size: 2rem;
            }

            .stats-overview {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 15px;
            }

            .stat-card-large {
                padding: 20px;
            }

            .stat-number-large {
                font-size: 2.5rem;
            }

            .settings-card {
                padding: 20px;
            }

            /* Sidebar Responsive */
            .sidebar-nav-link {
                padding: 14px 16px;
                font-size: 13px;
                gap: 10px;
            }

            .sidebar-nav-icon {
                font-size: 16px;
                min-width: 18px;
            }

            .sidebar-user-info {
                padding: 16px;
            }

            .sidebar-user-info .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .sidebar-user-info .user-info div:last-child div:first-child {
                font-size: 14px;
            }

            .settings-actions {
                flex-direction: column;
            }

            .settings-actions .btn {
                width: 100%;
            }
        }

        /* Recent Activities Section */
        .recent-activities-section {
            margin-bottom: 40px;
        }

        .view-all-link {
            color: var(--admin-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: var(--transition-fast);
        }

        .view-all-link:hover {
            color: var(--admin-primary);
            text-decoration: underline;
        }

        .activities-container {
            background: var(--admin-white);
            border-radius: var(--radius-xl);
            padding: 0;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(52, 152, 219, 0.1);
            overflow: hidden;
            max-height: 500px;
            overflow-y: auto;
        }

        .activities-container::-webkit-scrollbar {
            width: 8px;
        }

        .activities-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .activities-container::-webkit-scrollbar-thumb {
            background: rgba(52, 152, 219, 0.3);
            border-radius: 4px;
        }

        .activities-container::-webkit-scrollbar-thumb:hover {
            background: rgba(52, 152, 219, 0.5);
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition-fast);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: rgba(52, 152, 219, 0.02);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--admin-secondary) 0%, var(--admin-primary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--admin-white);
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-text {
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 4px;
        }

        .activity-user {
            font-weight: 700;
            color: var(--admin-gray-800);
        }

        .activity-action {
            color: var(--admin-secondary);
            font-weight: 600;
        }

        .activity-entity {
            color: var(--admin-gray-700);
            font-style: italic;
        }

        .activity-time {
            font-size: 12px;
            color: var(--admin-gray-500);
            font-weight: 500;
        }

        .activity-description {
            font-size: 13px;
            color: var(--admin-gray-600);
            margin-top: 6px;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.02);
            border-radius: var(--radius-md);
            border-left: 3px solid var(--admin-secondary);
        }

        .no-activities {
            text-align: center;
            padding: 60px 20px;
        }

        .no-activities-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .no-activities-text {
            font-size: 18px;
            font-weight: 600;
            color: var(--admin-gray-600);
            margin-bottom: 8px;
        }

        .no-activities-subtext {
            font-size: 14px;
            color: var(--admin-gray-500);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .activity-item {
                padding: 16px;
                gap: 12px;
            }

            .activity-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .activity-text {
                font-size: 14px;
            }

            .activity-description {
                font-size: 12px;
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1 class="sidebar-logo">القارئ</h1>
                <p class="sidebar-subtitle">لوحة الإدارة</p>
            </div>

            <div class="sidebar-user-info">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo mb_substr($_SESSION['admin_username'], 0, 1, 'UTF-8'); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">
                            <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                        </div>
                        <div style="font-size: 12px; opacity: 0.8;">مدير النظام</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-nav-item">
                    <a href="index.php" class="sidebar-nav-link <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>">
                        <span class="sidebar-nav-icon">📊</span>
                        لوحة التحكم
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="manage-tilawat.php" class="sidebar-nav-link <?php echo ($current_page === 'tilawat') ? 'active' : ''; ?>">
                        <span class="sidebar-nav-icon">🎵</span>
                        إدارة التلاوات
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="manage-hekum.php" class="sidebar-nav-link <?php echo ($current_page === 'hekum') ? 'active' : ''; ?>">
                        <span class="sidebar-nav-icon">📖</span>
                        إدارة المواعظ
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="manage-articles.php" class="sidebar-nav-link <?php echo ($current_page === 'articles') ? 'active' : ''; ?>">
                        <span class="sidebar-nav-icon">📝</span>
                        إدارة المقالات
                    </a>
                </div>
                <div class="sidebar-nav-item">
                    <a href="user_settings.php" class="sidebar-nav-link <?php echo ($current_page === 'settings') ? 'active' : ''; ?>">
                        <span class="sidebar-nav-icon">⚙️</span>
                        إعدادات المستخدم
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-link">تسجيل الخروج</a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
                    <div class="header-title">لوحة الإدارة</div>
                </div>
                <div class="header-actions">
                    <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <span style="margin-left: 5px;">🌐</span>
                        عرض الموقع
                    </a>
                </div>
            </header>

            <!-- Content -->
            <main class="admin-content">
                <!-- Welcome Hero -->
                <div class="welcome-hero">
                    <h1>مرحباً <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'المستخدم'); ?> في لوحة الإدارة</h1>
                    <p>يمكنك إدارة جميع محتويات الموقع من هنا بكفاءة واحترافية</p>
                    <div class="welcome-meta">
                        <strong>آخر دخول:</strong> <?php echo date('d/m/Y H:i', strtotime($_SESSION['login_time'])); ?>
                    </div>
                </div>

                <!-- Statistics Overview -->
                <section class="stats-overview">
                    <div class="stat-card-large">
                        <div class="stat-header">
                            <div class="stat-icon-large">🎵</div>
                            <div class="stat-trend up">
                                <span>+<?php echo rand(5, 15); ?>%</span>
                            </div>
                        </div>
                        <div class="stat-number-large"><?php echo $stats['tilawat_count']; ?></div>
                        <div class="stat-label-large">التلاوات المسجلة</div>
                    </div>

                    <div class="stat-card-large">
                        <div class="stat-header">
                            <div class="stat-icon-large">📖</div>
                            <div class="stat-trend up">
                                <span>+<?php echo rand(3, 10); ?>%</span>
                            </div>
                        </div>
                        <div class="stat-number-large"><?php echo $stats['hekum_count']; ?></div>
                        <div class="stat-label-large">المواعظ المتاحة</div>
                    </div>

                    <div class="stat-card-large" style="--card-accent: #27ae60;">
                        <div class="stat-header">
                            <div class="stat-icon-large">📝</div>
                            <div class="stat-trend up" style="background: rgba(39, 174, 96, 0.1); color: #27ae60;">
                                <span>+<?php echo rand(4, 12); ?>%</span>
                            </div>
                        </div>
                        <div class="stat-number-large" style="color: #27ae60;"><?php echo $stats['articles_count']; ?></div>
                        <div class="stat-label-large">المقالات الدينية</div>
                    </div>

                    <div class="stat-card-large">
                        <div class="stat-header">
                            <div class="stat-icon-large">📊</div>
                            <div class="stat-trend up">
                                <span>+<?php echo rand(8, 20); ?>%</span>
                            </div>
                        </div>
                        <div class="stat-number-large"><?php echo $stats['total']; ?></div>
                        <div class="stat-label-large">إجمالي المحتوى</div>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="quick-actions-section">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">الإجراءات السريعة</h2>
                            <p class="section-subtitle">اختصر الوقت واستخدم الروابط المباشرة</p>
                        </div>
                    </div>

                    <div class="quick-actions">
                        <a href="manage-tilawat.php" class="action-card">
                            <span class="action-icon">🎵</span>
                            <div class="action-title">إدارة التلاوات</div>
                            <div class="action-desc">إضافة التلاوات مع إمكانية جلب البيانات تلقائياً</div>
                        </a>

                        <a href="manage-hekum.php" class="action-card">
                            <span class="action-icon">📖</span>
                            <div class="action-title">إدارة المواعظ</div>
                            <div class="action-desc">إضافة المواعظ مع إمكانية جلب البيانات تلقائياً</div>
                        </a>

                        <a href="manage-articles.php" class="action-card" style="--card-gradient: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                            <span class="action-icon">📝</span>
                            <div class="action-title">إدارة المقالات</div>
                            <div class="action-desc">إضافة المقالات الدينية مع التصنيف والتنسيق الكامل</div>
                        </a>

                        <a href="../index.php" class="action-card" target="_blank">
                            <span class="action-icon">🌐</span>
                            <div class="action-title">عرض الموقع</div>
                            <div class="action-desc">زيارة الموقع الأمامي لرؤية التغييرات</div>
                        </a>
                    </div>
                </section>

                <!-- Recent Activities -->
                <section class="recent-activities-section">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">النشاطات الأخيرة</h2>
                            <p class="section-subtitle">تتبع جميع العمليات في النظام</p>
                        </div>
                        <a href="#" class="view-all-link" onclick="loadAllActivities()">عرض الكل</a>
                    </div>

                    <div class="activities-container">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php
                                        $icon = '📝';
                                        switch ($activity['entity_type']) {
                                            case 'user': $icon = '👤'; break;
                                            case 'tilawat': $icon = '🎵'; break;
                                            case 'hekum': $icon = '📖'; break;
                                            case 'article': $icon = '📝'; break;
                                        }
                                        echo $icon;
                                        ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <span class="activity-user"><?php echo htmlspecialchars($activity['username']); ?></span>
                                            <span class="activity-action">
                                                <?php
                                                switch ($activity['action_type']) {
                                                    case 'user_login': echo 'سجل دخول'; break;
                                                    case 'user_logout': echo 'سجل خروج'; break;
                                                    case 'user_settings_update': echo 'حدث إعداداته'; break;
                                                    case 'tilawat_create': echo 'أضاف تلاوة'; break;
                                                    case 'tilawat_update': echo 'حدث تلاوة'; break;
                                                    case 'tilawat_delete': echo 'حذف تلاوة'; break;
                                                    case 'hekum_create': echo 'أضاف موعظة'; break;
                                                    case 'hekum_update': echo 'حدث موعظة'; break;
                                                    case 'hekum_delete': echo 'حذف موعظة'; break;
                                                    case 'article_create': echo 'أضاف مقالة'; break;
                                                    case 'article_update': echo 'حدث مقالة'; break;
                                                    case 'article_delete': echo 'حذف مقالة'; break;
                                                    default: echo htmlspecialchars($activity['action_type']);
                                                }
                                                ?>
                                            </span>
                                            <?php if (!empty($activity['entity_title'])): ?>
                                                <span class="activity-entity">"<?php echo htmlspecialchars($activity['entity_title']); ?>"</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo $activity_logger->formatTimeAgo($activity['created_at']); ?>
                                        </div>
                                        <?php if (!empty($activity['description'])): ?>
                                            <div class="activity-description">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-activities">
                                <div class="no-activities-icon">📋</div>
                                <div class="no-activities-text">لا توجد نشاطات حديثة</div>
                                <div class="no-activities-subtext">ستظهر هنا جميع العمليات التي تتم في النظام</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !menuBtn.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card-large, .action-card, .admin-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });

        // Password toggle functionality
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;

            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '🙈';
                button.title = 'إخفاء كلمة المرور';
            } else {
                input.type = 'password';
                button.innerHTML = '👁️';
                button.title = 'إظهار كلمة المرور';
            }
        }

        // Reset form function
        function resetForm() {
            const form = document.getElementById('settingsForm');
            form.reset();

            // Reset password toggle buttons
            const passwordInputs = form.querySelectorAll('input[type="text"]');
            passwordInputs.forEach(input => {
                if (input.classList.contains('with-toggle')) {
                    input.type = 'password';
                    const button = input.nextElementSibling;
                    if (button) {
                        button.innerHTML = '👁️';
                        button.title = 'إظهار كلمة المرور';
                    }
                }
            });
        }

        // Form validation
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== '' && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('كلمة المرور الجديدة وتأكيدها غير متطابقين');
                return false;
            }

            if (newPassword !== '' && newPassword.length < 6) {
                e.preventDefault();
                alert('كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل');
                return false;
            }

            return true;
        });

        // Load all activities
        function loadAllActivities() {
            // يمكن إضافة منطق لتحميل المزيد من النشاطات هنا
            alert('سيتم عرض جميع النشاطات في الإصدارات القادمة');
        }
    </script>
</body>
</html>
<?php
// إغلاق اتصال قاعدة البيانات في نهاية الملف
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
