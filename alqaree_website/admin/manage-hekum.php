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

$current_page = 'hekum';

// معالجة رسائل النجاح من التوجيه
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['id'])) {
    $last_id = (int)$_GET['id'];
    $message = "✅ تم إضافة الموعظة بنجاح! (رقم الموعظة: {$last_id})";
    $messageType = 'success';
} elseif (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'تم تحديث الموعظة بنجاح!';
    $messageType = 'success';
} elseif (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $message = 'تم حذف الموعظة بنجاح!';
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_sermon'])) {
        // إضافة موعظة جديدة
        $title = $conn->real_escape_string(trim($_POST['title']));
        $speaker_name = $conn->real_escape_string(trim($_POST['speaker_name']));
        $video_duration = $conn->real_escape_string(trim($_POST['video_duration']));
        $publish_date = $conn->real_escape_string(trim($_POST['publish_date']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $youtube_embed_code = $conn->real_escape_string(trim($_POST['youtube_embed_code']));

        // تحقق مفصل من الحقول المطلوبة
        $errors = [];
        if (empty($title)) $errors[] = 'عنوان الموعظة';
        if (empty($speaker_name)) $errors[] = 'اسم المتحدث';
        if (empty($video_duration)) $errors[] = 'مدة الفيديو';
        if (empty($youtube_embed_code)) $errors[] = 'رابط الفيديو';

        if (!empty($errors)) {
            $message = 'يرجى ملء الحقول التالية: ' . implode(', ', $errors);
            $messageType = 'error';
        } else {
            $sql = "INSERT INTO hekum (title, speaker_name, video_duration, publish_date, description, youtube_embed_code)
                    VALUES ('$title', '$speaker_name', '$video_duration', '$publish_date', '$description', '$youtube_embed_code')";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;

                // تسجيل النشاط
                logHekumActivity('hekum_create', $last_id, $title, "تم إضافة موعظة جديدة: '{$title}' من المتحدث '{$speaker_name}'");

                // إعادة توجيه للصفحة نفسها مع رسالة نجاح لتجنب إعادة إرسال النموذج
                header('Location: manage-hekum.php?success=1&id=' . $last_id);
                exit();
            } else {
                $message = '❌ خطأ في إضافة الموعظة: ' . $conn->error;
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['update_sermon'])) {
        // تحديث موعظة
        $id = (int)$_POST['sermon_id'];
        $title = $conn->real_escape_string(trim($_POST['title']));
        $speaker_name = $conn->real_escape_string(trim($_POST['speaker_name']));
        $video_duration = $conn->real_escape_string(trim($_POST['video_duration']));
        $publish_date = $conn->real_escape_string(trim($_POST['publish_date']));
        $description = $conn->real_escape_string(trim($_POST['description']));
        $youtube_embed_code = $conn->real_escape_string(trim($_POST['youtube_embed_code']));

        $sql = "UPDATE hekum SET
                title='$title',
                speaker_name='$speaker_name',
                video_duration='$video_duration',
                publish_date='$publish_date',
                description='$description',
                youtube_embed_code='$youtube_embed_code'
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            // تسجيل النشاط
            logHekumActivity('hekum_update', $id, $title, "تم تحديث الموعظة: '{$title}'");

            header('Location: manage-hekum.php?updated=1');
            exit();
        } else {
            $message = 'خطأ في تحديث الموعظة: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// معالجة الحذف
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // الحصول على بيانات الموعظة قبل الحذف للتسجيل
    $hekum_data = $conn->query("SELECT title FROM hekum WHERE id=$id")->fetch_assoc();

    $sql = "DELETE FROM hekum WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        // تسجيل النشاط
        $title = $hekum_data['title'] ?? 'غير معروف';
        logHekumActivity('hekum_delete', $id, $title, "تم حذف الموعظة: '{$title}'");

        header('Location: manage-hekum.php?deleted=1');
        exit();
    } else {
        $message = 'خطأ في حذف الموعظة: ' . $conn->error;
        $messageType = 'error';
    }
}


// الحصول على المواعظ
$hekum_items = [];
$result = $conn->query("SELECT * FROM hekum ORDER BY publish_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hekum_items[] = $row;
    }
} else {
    $message = 'خطأ في جلب المواعظ: ' . $conn->error;
    $messageType = 'error';
}

$page_title = 'إدارة المواعظ المتقدمة';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إدارة المواعظ القرآنية - نظام متقدم">
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
                    <div class="header-title">إدارة المواعظ القرآنية</div>
                    <nav class="header-breadcrumb">
                        <span class="breadcrumb-item">لوحة التحكم</span>
                        <span class="breadcrumb-item active">المواعظ</span>
                    </nav>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <span style="margin-left: 5px;">➕</span>
                        إضافة موعظة جديدة
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="admin-content">
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
                        <span class="alert-icon"><?php echo $messageType === 'success' ? '✅' : '❌'; ?></span>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add Sermon Form -->
                <div class="admin-card" id="addForm" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">إضافة موعظة جديدة</h3>
                        <p class="card-subtitle">أدخل جميع بيانات الموعظة المطلوبة</p>
                    </div>
                    <form class="admin-form" method="POST" id="sermonForm">
                        <div class="card-body">
                            <!-- تنبيه -->
                            <div class="alert alert-info" style="margin-bottom: 25px;">
                                <span class="alert-icon">ℹ️</span>
                                النظام يعمل حالياً بدون YouTube API. يرجى ملء جميع الحقول يدوياً.
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title" class="form-label">عنوان الموعظة *</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           placeholder="مثال: خطبة الجمعة - أهمية الصلاة" required>
                                </div>
                                <div class="form-group">
                                    <label for="speaker_name" class="form-label">اسم المتحدث *</label>
                                    <input type="text" id="speaker_name" name="speaker_name" class="form-control"
                                           placeholder="مثال: الشيخ عبدالله بن عبدالرحمن" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="video_duration" class="form-label">مدة الفيديو *</label>
                                    <input type="text" id="video_duration" name="video_duration" class="form-control"
                                           placeholder="مثال: 15:30" required>
                                </div>
                                <div class="form-group">
                                    <label for="publish_date" class="form-label">تاريخ النشر</label>
                                    <input type="date" id="publish_date" name="publish_date" class="form-control"
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group"></div>
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
                                          placeholder="وصف تفصيلي للموعظة..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_sermon" class="btn btn-success">
                                <span style="margin-left: 5px;">💾</span>
                                حفظ الموعظة
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="hideAddForm()">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sermons List -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">المواعظ الحالية</h3>
                        <p class="card-subtitle">إدارة جميع المواعظ المسجلة في النظام</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($hekum_items)): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>العنوان</th>
                                            <th>المتحدث</th>
                                            <th>المدة</th>
                                            <th>تاريخ النشر</th>
                                            <th>المشاهدات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hekum_items as $hekum_item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($hekum_item['title']); ?></td>
                                                <td><?php echo htmlspecialchars($hekum_item['speaker_name']); ?></td>
                                                <td><?php echo htmlspecialchars($hekum_item['video_duration']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($hekum_item['publish_date'])); ?></td>
                                                <td><?php echo number_format($hekum_item['views_count']); ?></td>
                                                <td class="action-buttons">
                                                    <button class="action-btn btn-warning edit-btn"
                                                            data-id="<?php echo $hekum_item['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($hekum_item['title']); ?>"
                                                            data-speaker="<?php echo htmlspecialchars($hekum_item['speaker_name']); ?>"
                                                            data-duration="<?php echo htmlspecialchars($hekum_item['video_duration']); ?>"
                                                            data-date="<?php echo $hekum_item['publish_date']; ?>"
                                                            data-description="<?php echo htmlspecialchars($hekum_item['description']); ?>"
                                                            data-youtube="<?php echo htmlspecialchars($hekum_item['youtube_embed_code']); ?>">
                                                        <span>✏️</span> تعديل
                                                    </button>
                                                    <button class="action-btn btn-danger"
                                                            onclick="deleteHekumItem(<?php echo $hekum_item['id']; ?>, '<?php echo htmlspecialchars($hekum_item['title']); ?>')">
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
                                <div class="empty-icon">📖</div>
                                <div class="empty-title">لا توجد مواعظ</div>
                                <div class="empty-text">لم يتم إضافة أي مواعظ بعد. اضغط على "إضافة موعظة جديدة" لبدء إضافة المحتوى.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Sermon Modal -->
                <div class="modal" id="editModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">تعديل الموعظة</h3>
                        </div>
                        <form class="admin-form" method="POST">
                            <div class="modal-body">
                                <input type="hidden" id="edit_sermon_id" name="sermon_id">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_title" class="form-label">عنوان الموعظة *</label>
                                        <input type="text" id="edit_title" name="title" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_speaker_name" class="form-label">اسم المتحدث *</label>
                                        <input type="text" id="edit_speaker_name" name="speaker_name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_video_duration" class="form-label">مدة الفيديو *</label>
                                        <input type="text" id="edit_video_duration" name="video_duration" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_publish_date" class="form-label">تاريخ النشر</label>
                                        <input type="date" id="edit_publish_date" name="publish_date" class="form-control">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group"></div>
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
                                <button type="submit" name="update_sermon" class="btn btn-success">
                                    <span style="margin-left: 5px;">💾</span>
                                    تحديث الموعظة
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
        function showEditModal(id, title, speaker, duration, date, description, youtube) {
            document.getElementById('edit_sermon_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_speaker_name').value = speaker;
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

        // Delete sermon with confirmation
        function deleteHekumItem(id, title) {
            if (confirm(`هل أنت متأكد من حذف الموعظة "${title}"؟\n\nملاحظة: هذا الإجراء لا يمكن التراجع عنه.`)) {
                window.location.href = `manage-hekum.php?delete=${id}`;
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
                    const speaker = button.dataset.speaker;
                    const duration = button.dataset.duration;
                    const date = button.dataset.date;
                    const description = button.dataset.description;
                    const youtube = button.dataset.youtube;
                    showEditModal(id, title, speaker, duration, date, description, youtube);
                });
            });

            // Close modal when clicking outside
            document.getElementById('editModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('editModal')) {
                    closeEditModal();
                }
            });

            // Set current date for new hekum items
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('publish_date').value = today;

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
        });
    </script>
</body>
</html>
