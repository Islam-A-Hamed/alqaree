<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../includes/db_connect.php';
include '../includes/activity_logger.php';

// معالجة العمليات
$message = '';
$messageType = '';

$current_page = 'tilawat';

// معالجة رسائل النجاح من التوجيه
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['id'])) {
    $last_id = (int)$_GET['id'];
    $message = "تم إضافة التلاوة بنجاح";
    $messageType = 'success';
    $messageDetails = "تم حفظ التلاوة برقم {$last_id} في قاعدة البيانات. يمكنك الآن عرضها أو تعديلها.";
} elseif (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = "تم تحديث التلاوة بنجاح";
    $messageType = 'success';
    $messageDetails = "تم حفظ جميع التغييرات على التلاوة المحددة.";
} elseif (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $message = "تم حذف التلاوة بنجاح";
    $messageType = 'success';
    $messageDetails = "تم حذف التلاوة من قاعدة البيانات نهائياً.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_recitation'])) {
        // إضافة تلاوة جديدة
        $title = $conn->real_escape_string(trim($_POST['title']));
        $surah_name = $conn->real_escape_string(trim($_POST['surah_name']));
        $reciter_name = $conn->real_escape_string(trim($_POST['reciter_name']));
        $video_duration = $conn->real_escape_string(trim($_POST['video_duration']));
        $publish_date = $conn->real_escape_string(trim($_POST['publish_date']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $youtube_embed_code = $conn->real_escape_string(trim($_POST['youtube_embed_code']));

        // تحقق مفصل من الحقول المطلوبة
        $errors = [];
        if (empty($title)) $errors[] = 'عنوان التلاوة';
        if (empty($surah_name)) $errors[] = 'اسم السورة';
        if (empty($reciter_name)) $errors[] = 'اسم القارئ';
        if (empty($video_duration)) $errors[] = 'مدة الفيديو';
        if (empty($youtube_embed_code)) $errors[] = 'رابط الفيديو';

        if (!empty($errors)) {
            $message = "الحقول المطلوبة غير مكتملة";
            $messageType = 'error';
            $messageDetails = "يرجى ملء الحقول التالية قبل الحفظ: " . implode(', ', $errors);
        } else {
            $sql = "INSERT INTO tilawat (title, surah_name, reciter_name, video_duration, publish_date, description, youtube_embed_code)
                    VALUES ('$title', '$surah_name', '$reciter_name', '$video_duration', '$publish_date', '$description', '$youtube_embed_code')";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;

                // تسجيل النشاط
                logTilawatActivity('tilawat_create', $last_id, $title, "تم إضافة تلاوة جديدة: '{$title}' للسورة '{$surah_name}' من القارئ '{$reciter_name}'");

                // إعادة توجيه للصفحة نفسها مع رسالة نجاح لتجنب إعادة إرسال النموذج
                header('Location: manage-tilawat.php?success=1&id=' . $last_id);
                exit();
            } else {
                $message = "فشل في حفظ التلاوة";
                $messageType = 'error';
                $messageDetails = "حدث خطأ في قاعدة البيانات أثناء حفظ التلاوة. يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني.";
            }
        }
    } elseif (isset($_POST['update_recitation'])) {
        // تحديث تلاوة
        $id = (int)$_POST['recitation_id'];
        $title = $conn->real_escape_string(trim($_POST['title']));
        $surah_name = $conn->real_escape_string(trim($_POST['surah_name']));
        $reciter_name = $conn->real_escape_string(trim($_POST['reciter_name']));
        $video_duration = $conn->real_escape_string(trim($_POST['video_duration']));
        $publish_date = $conn->real_escape_string(trim($_POST['publish_date']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $youtube_embed_code = $conn->real_escape_string(trim($_POST['youtube_embed_code']));

        $sql = "UPDATE tilawat SET
                title='$title',
                surah_name='$surah_name',
                reciter_name='$reciter_name',
                video_duration='$video_duration',
                publish_date='$publish_date',
                description='$description',
                youtube_embed_code='$youtube_embed_code'
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            // تسجيل النشاط
            logTilawatActivity('tilawat_update', $id, $title, "تم تحديث التلاوة: '{$title}'");

            header('Location: manage-tilawat.php?updated=1');
            exit();
        } else {
            $message = "فشل في تحديث التلاوة";
            $messageType = 'error';
            $messageDetails = "حدث خطأ في قاعدة البيانات أثناء تحديث التلاوة. يرجى التحقق من صحة البيانات والمحاولة مرة أخرى.";
        }
    }
}

// معالجة الحذف
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // الحصول على بيانات التلاوة قبل الحذف للتسجيل
    $tilawat_data = $conn->query("SELECT title FROM tilawat WHERE id=$id")->fetch_assoc();

    $sql = "DELETE FROM tilawat WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        // تسجيل النشاط
        $title = $tilawat_data['title'] ?? 'غير معروف';
        logTilawatActivity('tilawat_delete', $id, $title, "تم حذف التلاوة: '{$title}'");

        header('Location: manage-tilawat.php?deleted=1');
        exit();
    } else {
        $message = "فشل في حذف التلاوة";
        $messageType = 'error';
        $messageDetails = "حدث خطأ في قاعدة البيانات أثناء حذف التلاوة. يرجى المحاولة مرة أخرى.";
    }
}


// الحصول على التلاوات
$tilawat_items = [];
$result = $conn->query("SELECT * FROM tilawat ORDER BY publish_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tilawat_items[] = $row;
    }
} else {
    $message = "مشكلة في تحميل التلاوات";
    $messageType = 'error';
    $messageDetails = "حدث خطأ في الاتصال بقاعدة البيانات. يرجى إعادة تحميل الصفحة أو الاتصال بالدعم الفني.";
}

$page_title = 'إدارة التلاوات المتقدمة';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إدارة التلاوات القرآنية - نظام متقدم">
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

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

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
                    <div class="header-title">إدارة التلاوات القرآنية</div>
                    <nav class="header-breadcrumb">
                        <span class="breadcrumb-item">لوحة التحكم</span>
                        <span class="breadcrumb-item active">التلاوات</span>
                    </nav>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <span style="margin-left: 5px;">➕</span>
                        إضافة تلاوة جديدة
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="admin-content">
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" id="messageAlert">
                        <span class="alert-icon">
                            <?php
                            switch($messageType) {
                                case 'success': echo '✅'; break;
                                case 'error': echo '❌'; break;
                                case 'warning': echo '⚠️'; break;
                                case 'info': echo 'ℹ️'; break;
                                default: echo '📢';
                            }
                            ?>
                        </span>
                        <div class="alert-content">
                            <span class="alert-title"><?php echo htmlspecialchars($message); ?></span>
                            <?php if (isset($messageDetails)): ?>
                                <p class="alert-message"><?php echo htmlspecialchars($messageDetails); ?></p>
                            <?php endif; ?>
                            <div class="alert-actions">
                                <button class="alert-dismiss" onclick="dismissAlert()" title="إغلاق الرسالة">✕</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add Recitation Form -->
                <div class="admin-card" id="addForm" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">إضافة تلاوة جديدة</h3>
                        <p class="card-subtitle">أدخل جميع بيانات التلاوة المطلوبة</p>
                    </div>
                    <form class="admin-form" method="POST" id="recitationForm">
                        <div class="card-body">
                            <!-- تنبيه -->
                            <div class="alert alert-info" style="margin-bottom: 25px;">
                                <span class="alert-icon">ℹ️</span>
                                النظام يعمل حالياً بدون YouTube API. يرجى ملء جميع الحقول يدوياً.
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title" class="form-label">عنوان التلاوة *</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           placeholder="مثال: تلاوة سورة الفاتحة" required>
                                    <span id="title-status" class="validation-message" style="display: none;"></span>
                                </div>
                                <div class="form-group">
                                    <label for="surah_name" class="form-label">اسم السورة *</label>
                                    <input type="text" id="surah_name" name="surah_name" class="form-control"
                                           placeholder="مثال: الفاتحة" required>
                                    <span id="surah-status" class="validation-message" style="display: none;"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reciter_name" class="form-label">اسم القارئ *</label>
                                    <input type="text" id="reciter_name" name="reciter_name" class="form-control"
                                           placeholder="مثال: الشيخ عبدالرحمن السديس" required>
                                </div>
                                <div class="form-group">
                                    <label for="video_duration" class="form-label">مدة الفيديو *</label>
                                    <input type="text" id="video_duration" name="video_duration" class="form-control"
                                           placeholder="مثال: 5:30" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="publish_date" class="form-label">تاريخ النشر</label>
                                    <input type="date" id="publish_date" name="publish_date" class="form-control"
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="youtube_embed_code" class="form-label">رابط الفيديو (YouTube) *</label>
                                <input type="url" id="youtube_embed_code" name="youtube_embed_code" class="form-control"
                                       placeholder="https://youtu.be/VIDEO_ID أو https://www.youtube.com/watch?v=VIDEO_ID" required>
                                <small class="form-text">
                                    يمكنك إدخال رابط يوتيوب الكامل أو معرف الفيديو فقط
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea id="description" name="description" class="form-control" rows="4"
                                          placeholder="وصف تفصيلي للتلاوة..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_recitation" class="btn btn-success">
                                <span style="margin-left: 5px;">💾</span>
                                حفظ التلاوة
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="hideAddForm()">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recitations List -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">التلاوات الحالية</h3>
                        <p class="card-subtitle">إدارة جميع التلاوات المسجلة في النظام</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tilawat_items)): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>العنوان</th>
                                            <th>السورة</th>
                                            <th>القارئ</th>
                                            <th>المدة</th>
                                            <th>تاريخ النشر</th>
                                            <th>المشاهدات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tilawat_items as $tilawat_item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($tilawat_item['title']); ?></td>
                                                <td><?php echo htmlspecialchars($tilawat_item['surah_name']); ?></td>
                                                <td><?php echo htmlspecialchars($tilawat_item['reciter_name']); ?></td>
                                                <td><?php echo htmlspecialchars($tilawat_item['video_duration']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($tilawat_item['publish_date'])); ?></td>
                                                <td><?php echo number_format($tilawat_item['views_count']); ?></td>
                                                <td class="action-buttons">
                                                    <button class="action-btn btn-warning edit-btn"
                                                            data-id="<?php echo $tilawat_item['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($tilawat_item['title']); ?>"
                                                            data-surah="<?php echo htmlspecialchars($tilawat_item['surah_name']); ?>"
                                                            data-reciter="<?php echo htmlspecialchars($tilawat_item['reciter_name']); ?>"
                                                            data-duration="<?php echo htmlspecialchars($tilawat_item['video_duration']); ?>"
                                                            data-date="<?php echo $tilawat_item['publish_date']; ?>"
                                                            data-description="<?php echo htmlspecialchars($tilawat_item['description']); ?>"
                                                            data-youtube="<?php echo htmlspecialchars($tilawat_item['youtube_embed_code']); ?>">
                                                        <span>✏️</span> تعديل
                                                    </button>
                                                    <button class="action-btn btn-danger"
                                                            onclick="deleteTilawatItem(<?php echo $tilawat_item['id']; ?>, '<?php echo htmlspecialchars($tilawat_item['title']); ?>')">
                                                        <span>🗑️</span> حذف
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">🎵</div>
                                <div class="empty-title">لا توجد تلاوات</div>
                                <div class="empty-text">لم يتم إضافة أي تلاوات بعد. اضغط على "إضافة تلاوة جديدة" لبدء إضافة المحتوى.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Recitation Modal -->
                <div class="modal" id="editModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">تعديل التلاوة</h3>
                        </div>
                        <form class="admin-form" method="POST">
                            <div class="modal-body">
                                <input type="hidden" id="edit_recitation_id" name="recitation_id">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_title" class="form-label">عنوان التلاوة *</label>
                                        <input type="text" id="edit_title" name="title" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_surah_name" class="form-label">اسم السورة *</label>
                                        <input type="text" id="edit_surah_name" name="surah_name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_reciter_name" class="form-label">اسم القارئ *</label>
                                        <input type="text" id="edit_reciter_name" name="reciter_name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_video_duration" class="form-label">مدة الفيديو *</label>
                                        <input type="text" id="edit_video_duration" name="video_duration" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_publish_date" class="form-label">تاريخ النشر</label>
                                        <input type="date" id="edit_publish_date" name="publish_date" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="edit_youtube_embed_code" class="form-label">رابط الفيديو *</label>
                                    <input type="url" id="edit_youtube_embed_code" name="youtube_embed_code" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit_description" class="form-label">الوصف</label>
                                    <textarea id="edit_description" name="description" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_recitation" class="btn btn-success">
                                    <span style="margin-left: 5px;">💾</span>
                                    تحديث التلاوة
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="closeEditModal()">
                                    إلغاء
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Show add form
        function showAddForm() {
            document.getElementById('addForm').style.display = 'block';
            document.getElementById('addForm').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('title').focus();
        }

        // Hide add form
        function hideAddForm() {
            document.getElementById('addForm').style.display = 'none';
        }

        // Show edit modal
        function showEditModal(id, title, surah, reciter, duration, date, description, youtube) {
            document.getElementById('edit_recitation_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_surah_name').value = surah;
            document.getElementById('edit_reciter_name').value = reciter;
            document.getElementById('edit_video_duration').value = duration;
            document.getElementById('edit_publish_date').value = date;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_youtube_embed_code').value = youtube;
            document.getElementById('editModal').classList.add('show');
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        // Delete recitation with confirmation
        function deleteTilawatItem(id, title) {
            if (confirm(`تأكيد حذف التلاوة\n\nهل أنت متأكد من حذف التلاوة "${title}"؟\n\n⚠️ تحذير: هذا الإجراء لا يمكن التراجع عنه وسيتم حذف التلاوة نهائياً من قاعدة البيانات.`)) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '<span>جاري الحذف...</span>';
                button.disabled = true;

                // Redirect after short delay to show loading
                setTimeout(() => {
                    window.location.href = `manage-tilawat.php?delete=${id}`;
                }, 500);
            }
        }

        // Show success message
        function showSuccessMessage(title, message) {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            toastContainer.innerHTML = `
                <div class="toast toast-success show">
                    <span class="alert-icon">✅</span>
                    <div class="alert-content">
                        <span class="alert-title">${title}</span>
                        <p class="alert-message">${message}</p>
                    </div>
                </div>
            `;
            document.body.appendChild(toastContainer);

            // Auto remove after 3 seconds
            setTimeout(() => {
                const toast = toastContainer.querySelector('.toast');
                toast.classList.add('toast-hide');
                setTimeout(() => {
                    document.body.removeChild(toastContainer);
                }, 300);
            }, 3000);
        }

        // Dismiss alert function
        function dismissAlert() {
            const alert = document.getElementById('messageAlert');
            if (alert) {
                alert.style.animation = 'slideOutRight 0.3s ease-in forwards';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }
        }

        // Auto dismiss success messages after 5 seconds
        function autoDismissAlerts() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.id === 'messageAlert') {
                        dismissAlert();
                    }
                }, 5000); // 5 seconds
            });
        }

        // Form validation functions
        function validateField(fieldId, minLength = 1) {
            const field = document.getElementById(fieldId);
            const status = document.getElementById(fieldId + '-status');
            const value = field.value.trim();

            if (value.length < minLength) {
                field.style.borderColor = 'var(--admin-accent)';
                if (status) {
                    status.textContent = `هذا الحقل مطلوب (${minLength} أحرف على الأقل)`;
                    status.className = 'validation-message validation-error';
                    status.style.display = 'block';
                }
                return false;
            } else {
                field.style.borderColor = 'var(--admin-success)';
                if (status) {
                    status.textContent = '✓ صحيح';
                    status.className = 'validation-message validation-success';
                    status.style.display = 'block';
                }
                return true;
            }
        }

        function validateUrlField(fieldId) {
            const field = document.getElementById(fieldId);
            const status = document.getElementById(fieldId + '-status');
            const value = field.value.trim();

            if (!value) {
                field.style.borderColor = 'var(--admin-accent)';
                if (status) {
                    status.textContent = 'رابط الفيديو مطلوب';
                    status.className = 'validation-message validation-error';
                    status.style.display = 'block';
                }
                return false;
            }

            try {
                new URL(value);
                if (value.includes('youtube.com') || value.includes('youtu.be')) {
                    field.style.borderColor = 'var(--admin-success)';
                    if (status) {
                        status.textContent = '✓ رابط يوتيوب صحيح';
                        status.className = 'validation-message validation-success';
                        status.style.display = 'block';
                    }
                    return true;
                } else {
                    field.style.borderColor = 'var(--admin-warning)';
                    if (status) {
                        status.textContent = '⚠️ يُفضل استخدام رابط يوتيوب';
                        status.className = 'validation-message validation-warning';
                        status.style.display = 'block';
                    }
                    return true;
                }
            } catch {
                field.style.borderColor = 'var(--admin-accent)';
                if (status) {
                    status.textContent = 'الرابط غير صحيح';
                    status.className = 'validation-message validation-error';
                    status.style.display = 'block';
                }
                return false;
            }
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', () => {
            // Edit button listeners
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const id = button.dataset.id;
                    const title = button.dataset.title;
                    const surah = button.dataset.surah;
                    const reciter = button.dataset.reciter;
                    const duration = button.dataset.duration;
                    const date = button.dataset.date;
                    const description = button.dataset.description;
                    const youtube = button.dataset.youtube;
                    showEditModal(id, title, surah, reciter, duration, date, description, youtube);
                });
            });

            // Close modal when clicking outside
            const editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('click', (e) => {
                    if (e.target === editModal) {
                        closeEditModal();
                    }
                });
            }

            // Real-time form validation
            const requiredFields = ['title', 'surah_name', 'reciter_name', 'video_duration'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('blur', () => validateField(fieldId));
                    field.addEventListener('input', () => validateField(fieldId));
                }
            });

            // URL validation
            const urlField = document.getElementById('youtube_embed_code');
            if (urlField) {
                urlField.addEventListener('blur', () => validateUrlField('youtube_embed_code'));
                urlField.addEventListener('input', () => validateUrlField('youtube_embed_code'));
            }

            // Set current date for new tilawat items
            const today = new Date().toISOString().split('T')[0];
            const dateField = document.getElementById('publish_date');
            if (dateField) {
                dateField.value = today;
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

            // Auto dismiss alerts
            autoDismissAlerts();
        });
    </script>
</body>
</html>
