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

$current_page = 'articles';

// معالجة رسائل النجاح من التوجيه
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['id'])) {
    $last_id = (int)$_GET['id'];
    $message = "✅ تم إضافة المقالة بنجاح! (رقم المقالة: {$last_id})";
    $messageType = 'success';
} elseif (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'تم تحديث المقالة بنجاح!';
    $messageType = 'success';
} elseif (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $message = 'تم حذف المقالة بنجاح!';
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_article'])) {
        // إضافة مقالة جديدة
        $title = $conn->real_escape_string(trim($_POST['title']));
        $author_name = $conn->real_escape_string(trim($_POST['author_name']));
        $category = $conn->real_escape_string(trim($_POST['category']));
        $content = $conn->real_escape_string(trim($_POST['content']));
        $publish_date = $conn->real_escape_string(trim($_POST['publish_date']));

        // تحقق مفصل من الحقول المطلوبة
        $errors = [];
        if (empty($title)) $errors[] = 'عنوان المقالة';
        if (empty($author_name)) $errors[] = 'اسم الكاتب';
        if (empty($content)) $errors[] = 'محتوى المقالة';

        if (!empty($errors)) {
            $message = 'يرجى ملء الحقول التالية: ' . implode(', ', $errors);
            $messageType = 'error';
        } else {
            $sql = "INSERT INTO articles (title, author_name, category, content, publish_date)
                    VALUES ('$title', '$author_name', '$category', '$content', '$publish_date')";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;

                // تسجيل النشاط
                logArticleActivity('article_create', $last_id, $title, "تم إضافة مقالة جديدة: '{$title}' من الكاتب '{$author_name}'");

                // إعادة توجيه للصفحة نفسها مع رسالة نجاح لتجنب إعادة إرسال النموذج
                header('Location: manage-articles.php?success=1&id=' . $last_id);
                exit();
            } else {
                $message = '❌ خطأ في إضافة المقالة: ' . $conn->error;
                $messageType = 'error';
            }
        }
    } elseif (isset($_POST['update_article'])) {
        // تحديث مقالة
        $id = (int)$_POST['article_id'];
        $title = $conn->real_escape_string(trim($_POST['title']));
        $author_name = $conn->real_escape_string(trim($_POST['author_name']));
        $category = $conn->real_escape_string(trim($_POST['category']));
        $content = $conn->real_escape_string(trim($_POST['content']));
        $publish_date = $conn->real_escape_string(trim($_POST['publish_date']));

        $sql = "UPDATE articles SET
                title='$title',
                author_name='$author_name',
                category='$category',
                content='$content',
                publish_date='$publish_date'
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            // تسجيل النشاط
            logArticleActivity('article_update', $id, $title, "تم تحديث المقالة: '{$title}'");

            header('Location: manage-articles.php?updated=1');
            exit();
        } else {
            $message = 'خطأ في تحديث المقالة: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// معالجة الحذف
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // الحصول على بيانات المقالة قبل الحذف للتسجيل
    $article_data = $conn->query("SELECT title FROM articles WHERE id=$id")->fetch_assoc();

    $sql = "DELETE FROM articles WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        // تسجيل النشاط
        $title = $article_data['title'] ?? 'غير معروف';
        logArticleActivity('article_delete', $id, $title, "تم حذف المقالة: '{$title}'");

        header('Location: manage-articles.php?deleted=1');
        exit();
    } else {
        $message = 'خطأ في حذف المقالة: ' . $conn->error;
        $messageType = 'error';
    }
}

// الحصول على المقالات
$articles_items = [];
$result = $conn->query("SELECT * FROM articles ORDER BY publish_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $articles_items[] = $row;
    }
} else {
    $message = 'خطأ في جلب المقالات: ' . $conn->error;
    $messageType = 'error';
}

$page_title = 'إدارة المقالات الدينية المتقدمة';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="إدارة المقالات الدينية - نظام متقدم">
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

        /* Category badges */
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            background: var(--admin-gray-200);
            color: var(--admin-gray-700);
        }

        .category-العبادات {
            background: rgba(52, 152, 219, 0.1);
            color: var(--admin-primary);
        }

        .category-العقيدة {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }

        .category-الأخلاق {
            background: rgba(230, 126, 34, 0.1);
            color: #e67e22;
        }

        .category-السيرة {
            background: rgba(26, 188, 156, 0.1);
            color: #1abc9c;
        }

        .category-الفقه {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .category-التفسير {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }

        /* Enhanced form styling */
        #articleForm textarea {
            resize: vertical;
            min-height: 200px;
            font-family: 'Courier New', monospace;
            line-height: 1.5;
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

            #articleForm textarea {
                min-height: 150px;
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
                    <div class="header-title">إدارة المقالات الدينية</div>
                    <nav class="header-breadcrumb">
                        <span class="breadcrumb-item">لوحة التحكم</span>
                        <span class="breadcrumb-item active">المقالات</span>
                    </nav>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <span style="margin-left: 5px;">➕</span>
                        إضافة مقالة جديدة
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

                <!-- Add Article Form -->
                <div class="admin-card" id="addForm" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">إضافة مقالة جديدة</h3>
                        <p class="card-subtitle">أدخل جميع بيانات المقالة المطلوبة</p>
                    </div>
                    <form class="admin-form" method="POST" id="articleForm">
                        <div class="card-body">
                            <!-- تنبيه -->
                            <div class="alert alert-info" style="margin-bottom: 25px;">
                                <span class="alert-icon">ℹ️</span>
                                النظام يدعم المقالات الدينية بتنسيق نصي كامل مع إمكانية التنسيق الأساسي.
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title" class="form-label">عنوان المقالة *</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           placeholder="مثال: فضائل صيام رمضان" required>
                                </div>
                                <div class="form-group">
                                    <label for="author_name" class="form-label">اسم الكاتب *</label>
                                    <input type="text" id="author_name" name="author_name" class="form-control"
                                           placeholder="مثال: الشيخ محمد بن صالح العثيمين" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category" class="form-label">التصنيف</label>
                                    <select id="category" name="category" class="form-control">
                                        <option value="عام">عام</option>
                                        <option value="العبادات">العبادات</option>
                                        <option value="العقيدة">العقيدة</option>
                                        <option value="الأخلاق">الأخلاق</option>
                                        <option value="السيرة">السيرة النبوية</option>
                                        <option value="الفقه">الفقه</option>
                                        <option value="التفسير">التفسير</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="publish_date" class="form-label">تاريخ النشر</label>
                                    <input type="date" id="publish_date" name="publish_date" class="form-control"
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="content" class="form-label">محتوى المقالة *</label>
                                <textarea id="content" name="content" class="form-control" rows="12"
                                          placeholder="اكتب محتوى المقالة هنا... يمكنك استخدام التنسيق الأساسي مثل الأسطر الجديدة." required></textarea>
                                <small class="form-text">
                                    يدعم النظام الأسطر الجديدة والتنسيق الأساسي. سيتم عرض النص كما هو.
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_article" class="btn btn-success">
                                <span style="margin-left: 5px;">💾</span>
                                حفظ المقالة
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="hideAddForm()">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Articles List -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">المقالات الحالية</h3>
                        <p class="card-subtitle">إدارة جميع المقالات الدينية المسجلة في النظام</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($articles_items)): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>العنوان</th>
                                            <th>الكاتب</th>
                                            <th>التصنيف</th>
                                            <th>تاريخ النشر</th>
                                            <th>المشاهدات</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($articles_items as $article): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($article['title']); ?></td>
                                                <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                                <td>
                                                    <span class="category-badge <?php echo 'category-' . strtolower(str_replace(' ', '-', $article['category'] ?: 'عام')); ?>">
                                                        <?php echo htmlspecialchars($article['category'] ?: 'عام'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($article['publish_date'])); ?></td>
                                                <td><?php echo number_format($article['views_count']); ?></td>
                                                <td class="action-buttons">
                                                    <button class="action-btn btn-warning edit-btn"
                                                            data-id="<?php echo $article['id']; ?>"
                                                            data-title="<?php echo htmlspecialchars($article['title']); ?>"
                                                            data-author="<?php echo htmlspecialchars($article['author_name']); ?>"
                                                            data-category="<?php echo htmlspecialchars($article['category'] ?: 'عام'); ?>"
                                                            data-date="<?php echo $article['publish_date']; ?>"
                                                            data-content="<?php echo htmlspecialchars($article['content']); ?>">
                                                        <span>✏️</span> تعديل
                                                    </button>
                                                    <button class="action-btn btn-danger"
                                                            onclick="deleteArticleItem(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title']); ?>')">
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
                                <div class="empty-icon">📝</div>
                                <div class="empty-title">لا توجد مقالات</div>
                                <div class="empty-text">لم يتم إضافة أي مقالات بعد. اضغط على "إضافة مقالة جديدة" لبدء إضافة المحتوى.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Edit Article Modal -->
                <div class="modal" id="editModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">تعديل المقالة</h3>
                        </div>
                        <form class="admin-form" method="POST">
                            <div class="modal-body">
                                <input type="hidden" id="edit_article_id" name="article_id">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_title" class="form-label">عنوان المقالة *</label>
                                        <input type="text" id="edit_title" name="title" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_author_name" class="form-label">اسم الكاتب *</label>
                                        <input type="text" id="edit_author_name" name="author_name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="edit_category" class="form-label">التصنيف</label>
                                        <select id="edit_category" name="category" class="form-control">
                                            <option value="عام">عام</option>
                                            <option value="العبادات">العبادات</option>
                                            <option value="العقيدة">العقيدة</option>
                                            <option value="الأخلاق">الأخلاق</option>
                                            <option value="السيرة">السيرة النبوية</option>
                                            <option value="الفقه">الفقه</option>
                                            <option value="التفسير">التفسير</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_publish_date" class="form-label">تاريخ النشر</label>
                                        <input type="date" id="edit_publish_date" name="publish_date" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="edit_content" class="form-label">محتوى المقالة *</label>
                                    <textarea id="edit_content" name="content" class="form-control" rows="12" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_article" class="btn btn-success">
                                    <span style="margin-left: 5px;">💾</span>
                                    تحديث المقالة
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
        function showEditModal(id, title, author, category, date, content) {
            document.getElementById('edit_article_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_author_name').value = author;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_publish_date').value = date;
            document.getElementById('edit_content').value = content;
            document.getElementById('editModal').classList.add('show');
        }

        // Close edit modal
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }

        // Delete article with confirmation
        function deleteArticleItem(id, title) {
            if (confirm(`هل أنت متأكد من حذف المقالة "${title}"؟\n\nملاحظة: هذا الإجراء لا يمكن التراجع عنه.`)) {
                window.location.href = `manage-articles.php?delete=${id}`;
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
                    const author = button.dataset.author;
                    const category = button.dataset.category;
                    const date = button.dataset.date;
                    const content = button.dataset.content;
                    showEditModal(id, title, author, category, date, content);
                });
            });

            // Close modal when clicking outside
            document.getElementById('editModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('editModal')) {
                    closeEditModal();
                }
            });

            // Set current date for new articles
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
