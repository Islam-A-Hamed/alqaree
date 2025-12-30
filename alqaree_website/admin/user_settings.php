<?php
// ملف إعدادات المستخدم - للاختبار والتطوير
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$page_title = 'إعدادات المستخدم';
$current_page = 'settings';
include '../includes/db_connect.php';
include '../includes/activity_logger.php';

// الحصول على بيانات المستخدم الحالي
$current_user_id = $_SESSION['admin_id'] ?? null;
$user_data = null;

if ($current_user_id) {
    $stmt = $conn->prepare("SELECT * FROM admin_accounts WHERE id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
}

// معالجة النماذج - يجب أن تكون قبل أي output
$profile_message = '';
$profile_message_type = '';
$security_message = '';
$security_message_type = '';

// معالجة رسائل النجاح من التوجيه - الملف الشخصي
if (isset($_GET['profile_success']) && $_GET['profile_success'] == '1') {
    $profile_message = 'تم تحديث الملف الشخصي بنجاح';
    $profile_message_type = 'success';
} elseif (isset($_GET['profile_error']) && $_GET['profile_error'] == '1') {
    $profile_message = 'فشل في تحديث الملف الشخصي';
    $profile_message_type = 'error';
} elseif (isset($_GET['profile_duplicate']) && $_GET['profile_duplicate'] == '1') {
    $profile_message = 'اسم المستخدم موجود بالفعل، يرجى اختيار اسم آخر';
    $profile_message_type = 'error';
}

// معالجة رسائل النجاح من التوجيه - الأمان
if (isset($_GET['password_success']) && $_GET['password_success'] == '1') {
    $security_message = 'تم تغيير كلمة المرور بنجاح';
    $security_message_type = 'success';
} elseif (isset($_GET['password_error']) && $_GET['password_error'] == '1') {
    $security_message = 'فشل في تغيير كلمة المرور';
    $security_message_type = 'error';
} elseif (isset($_GET['password_current_wrong']) && $_GET['password_current_wrong'] == '1') {
    $security_message = 'كلمة المرور الحالية غير صحيحة';
    $security_message_type = 'error';
} elseif (isset($_GET['password_required']) && $_GET['password_required'] == '1') {
    $security_message = 'كلمة المرور الحالية والجديدة مطلوبة';
    $security_message_type = 'error';
} elseif (isset($_GET['password_short']) && $_GET['password_short'] == '1') {
    $security_message = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
    $security_message_type = 'error';
} elseif (isset($_GET['password_mismatch']) && $_GET['password_mismatch'] == '1') {
    $security_message = 'كلمات المرور غير متطابقة';
    $security_message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($username)) {
        header('Location: user_settings.php?tab=profile&profile_error=1');
        exit();
    } elseif (empty($full_name)) {
        header('Location: user_settings.php?tab=profile&profile_error=1');
        exit();
    } elseif ($username !== $user_data['username']) {
        // التحقق من عدم وجود اسم مستخدم مكرر
        $check_stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE username = ? AND id != ?");
        $check_stmt->bind_param("si", $username, $current_user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $check_stmt->close();
            header('Location: user_settings.php?tab=profile&profile_duplicate=1');
            exit();
        }
        $check_stmt->close();

        $update_stmt = $conn->prepare("UPDATE admin_accounts SET username = ?, full_name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $username, $full_name, $email, $current_user_id);

        if ($update_stmt->execute()) {
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_full_name'] = $full_name;

            // تسجيل النشاط
            logUserActivity('user_profile_update', 'تم تحديث الملف الشخصي');

            header('Location: user_settings.php?tab=profile&profile_success=1');
            exit();
        } else {
            header('Location: user_settings.php?tab=profile&profile_error=1');
            exit();
        }
    } else {
        $update_stmt = $conn->prepare("UPDATE admin_accounts SET full_name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $full_name, $email, $current_user_id);

        if ($update_stmt->execute()) {
            $_SESSION['admin_full_name'] = $full_name;

            // تسجيل النشاط
            logUserActivity('user_profile_update', 'تم تحديث الملف الشخصي');

            header('Location: user_settings.php?tab=profile&profile_success=1');
            exit();
        } else {
            header('Location: user_settings.php?tab=profile&profile_error=1');
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password)) {
        header('Location: user_settings.php?tab=security&password_required=1');
        exit();
    } elseif (!password_verify($current_password, $user_data['password_hash'])) {
        header('Location: user_settings.php?tab=security&password_current_wrong=1');
        exit();
    } elseif (strlen($new_password) < 6) {
        header('Location: user_settings.php?tab=security&password_short=1');
        exit();
    } elseif ($new_password !== $confirm_password) {
        header('Location: user_settings.php?tab=security&password_mismatch=1');
        exit();
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE admin_accounts SET password_hash = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_hash, $current_user_id);

        if ($update_stmt->execute()) {
            // تسجيل النشاط
            logUserActivity('user_password_change', 'تم تغيير كلمة المرور');

            header('Location: user_settings.php?tab=security&password_success=1');
            exit();
        } else {
            header('Location: user_settings.php?tab=security&password_error=1');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - موقع القارئ</title>
    <link rel="stylesheet" href="../css/admin-style.css">
    <link rel="icon" type="image/svg+xml" href="../icon-192x192.svg">
    <style>
        /* Sidebar Styles - Same as admin pages */
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

        .settings-container {
            max-width: 900px;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .settings-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .settings-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin: 0 auto 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .settings-tabs {
            display: flex;
            margin-bottom: 30px;
            background: white;
            border-radius: 8px;
            padding: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .tab-button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #666;
        }

        .tab-button.active {
            background: #007bff;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .settings-section {
            display: none;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .settings-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .settings-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .settings-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .settings-button.danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }

        .settings-button.danger:hover {
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .alert.warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }

        .current-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #007bff;
        }

        .password-strength {
            margin-top: 8px;
            font-size: 14px;
        }

        .strength-weak {
            color: #dc3545;
        }

        .strength-medium {
            color: #ffc107;
        }

        .strength-strong {
            color: #28a745;
        }

        .activity-log {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-top: 15px;
        }

        .activity-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-action {
            font-weight: 600;
            color: #495057;
        }

        .activity-time {
            color: #6c757d;
            font-size: 14px;
        }

        /* Password toggle functionality */
        .password-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle-btn {
            position: absolute;
            left: 12px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-size: 16px;
            line-height: 1;
        }

        .password-toggle-btn:hover {
            color: #007bff;
            background: rgba(0, 123, 255, 0.1);
        }

        .password-input-container input {
            padding-left: 50px !important;
        }

        .password-input-container input:focus + .password-toggle-btn,
        .password-toggle-btn:focus {
            color: #007bff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-container {
                margin: 15px;
                padding: 20px;
            }

            .settings-header {
                text-align: center;
                margin-bottom: 20px;
            }

            .settings-avatar {
                width: 80px;
                height: 80px;
                font-size: 24px;
                margin: 0 auto 15px;
            }

            .settings-tabs {
                flex-direction: column;
                gap: 10px;
            }

            .settings-tab-button {
                width: 100%;
                padding: 12px;
            }

            .settings-section {
                padding: 15px;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .settings-actions {
                flex-direction: column;
                gap: 10px;
            }

            .settings-button {
                width: 100%;
                padding: 12px;
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
                    <div class="header-title">إعدادات المستخدم</div>
                    <nav class="header-breadcrumb">
                        <span class="breadcrumb-item">لوحة التحكم</span>
                        <span class="breadcrumb-item active">الإعدادات</span>
                    </nav>
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
                <div class="settings-container">
        <div class="settings-header">
            <div class="settings-avatar">
                <?php echo mb_substr($_SESSION['admin_username'], 0, 1, 'UTF-8'); ?>
            </div>
            <h1><?php echo htmlspecialchars($_SESSION['admin_full_name'] ?? $_SESSION['admin_username']); ?></h1>
            <p style="color: #666; margin: 5px 0 0;">إعدادات الحساب الشخصي</p>
        </div>

        <div class="settings-tabs">
            <button class="tab-button active" onclick="showTab('profile')">الملف الشخصي</button>
            <button class="tab-button" onclick="showTab('security')">الأمان</button>
            <button class="tab-button" onclick="showTab('activity')">النشاط</button>
        </div>

        <!-- الملف الشخصي -->
        <div id="profile-tab" class="settings-section active">
            <h2>الملف الشخصي</h2>

            <div class="current-info">
                <div class="info-item">
                    <span class="info-label">اسم المستخدم:</span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">الدور:</span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_role']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">آخر دخول:</span>
                    <span class="info-value"><?php echo $user_data['last_login'] ?? 'غير محدد'; ?></span>
                </div>
            </div>

            <?php if (!empty($profile_message)): ?>
                <div class="alert alert-<?php echo $profile_message_type; ?>">
                    <?php echo htmlspecialchars($profile_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">اسم المستخدم:</label>
                    <input type="text" id="username" name="username"
                           value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="full_name">الاسم الكامل:</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني (اختياري):</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                </div>

                <button type="submit" name="update_profile" class="settings-button">تحديث الملف الشخصي</button>
            </form>
        </div>

        <!-- الأمان -->
        <div id="security-tab" class="settings-section">
            <h2>إعدادات الأمان</h2>

            <?php if (!empty($security_message)): ?>
                <div class="alert alert-<?php echo $security_message_type; ?>">
                    <?php echo htmlspecialchars($security_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="current_password">كلمة المرور الحالية:</label>
                    <div class="password-input-container">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('current_password')" title="إظهار كلمة المرور">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">كلمة المرور الجديدة:</label>
                    <div class="password-input-container">
                        <input type="password" id="new_password" name="new_password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('new_password')" title="إظهار كلمة المرور">
                            👁️
                        </button>
                    </div>
                    <div id="password-strength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور الجديدة:</label>
                    <div class="password-input-container">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')" title="إظهار كلمة المرور">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" name="change_password" class="settings-button">تغيير كلمة المرور</button>
            </form>
        </div>

        <!-- النشاط -->
        <div id="activity-tab" class="settings-section">
            <h2>سجل النشاط</h2>

            <div class="activity-log">
                <?php
                $activity_stmt = $conn->prepare("SELECT action_type, entity_type, description, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
                $activity_stmt->bind_param("i", $current_user_id);
                $activity_stmt->execute();
                $activity_result = $activity_stmt->get_result();

                if ($activity_result->num_rows > 0) {
                    while ($activity = $activity_result->fetch_assoc()) {
                        echo '<div class="activity-item">';
                        echo '<div class="activity-action">' . htmlspecialchars($activity['action_type']) . ' - ' . htmlspecialchars($activity['entity_type']) . '</div>';
                        echo '<div class="activity-time">' . date('Y-m-d H:i', strtotime($activity['created_at'])) . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="activity-item" style="justify-content: center; color: #666;">لا توجد أنشطة مسجلة</div>';
                }
                ?>
            </div>

                </div>
            </main>
        </div>
    </div>

<script>
function showTab(tabName) {
    // إزالة الكلاس active من جميع الأزرار والأقسام
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.settings-section').forEach(section => section.classList.remove('active'));

    // إضافة الكلاس active للزر والقسم المحدد
    document.querySelector(`[onclick="showTab('${tabName}')"]`).classList.add('active');
    document.getElementById(`${tabName}-tab`).classList.add('active');
}

// التحقق من التبويب المطلوب من URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        showTab(tab);
    }
});

function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('password-strength');

    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }

    let strength = 0;
    let feedback = [];

    if (password.length >= 8) strength++;
    else feedback.push('8 أحرف على الأقل');

    if (/[a-z]/.test(password)) strength++;
    else feedback.push('حرف صغير');

    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('حرف كبير');

    if (/[0-9]/.test(password)) strength++;
    else feedback.push('رقم');

    if (/[^A-Za-z0-9]/.test(password)) strength++;
    else feedback.push('رمز خاص');

    let strengthText = '';
    let strengthClass = '';

    if (strength < 3) {
        strengthText = 'ضعيفة: ' + feedback.join(', ');
        strengthClass = 'strength-weak';
    } else if (strength < 4) {
        strengthText = 'متوسطة - أضف ' + feedback.slice(0, 2).join(', ');
        strengthClass = 'strength-medium';
    } else {
        strengthText = 'قوية ✓';
        strengthClass = 'strength-strong';
    }

    strengthDiv.innerHTML = strengthText;
    strengthDiv.className = 'password-strength ' + strengthClass;
}

// فحص قوة كلمة المرور عند الكتابة
document.getElementById('new_password').addEventListener('input', function() {
    checkPasswordStrength(this.value);
});

// التحقق من تطابق كلمات المرور
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;

    if (newPassword !== confirmPassword) {
        this.style.borderColor = '#dc3545';
    } else {
        this.style.borderColor = '#28a745';
    }
});

function clearActivityLog() {
    if (confirm('هل تريد مسح جميع سجلات النشاط الخاصة بك؟')) {
        fetch('clear_user_activity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'confirm_clear=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('تم مسح سجل النشاط بنجاح');
                location.reload();
            } else {
                alert('فشل في مسح سجل النشاط: ' + data.message);
            }
        })
        .catch(error => {
            alert('حدث خطأ: ' + error.message);
        });
    }
}

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

// Password toggle functionality with animation
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;

    if (input.type === 'password') {
        input.type = 'text';
        button.innerHTML = '🙈';
        button.title = 'إخفاء كلمة المرور';
        button.style.transform = 'scale(1.1)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    } else {
        input.type = 'password';
        button.innerHTML = '👁️';
        button.title = 'إظهار كلمة المرور';
        button.style.transform = 'scale(0.9)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    }
}
</script>

</body>
</html>
