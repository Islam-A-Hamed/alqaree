<?php
// ملف إنشاء مدير جديد - للاختبار والتطوير
$page_title = 'إنشاء مدير جديد';
include '../includes/db_connect.php';

// فحص إذا كان الطلب POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'moderator';
    $auto_setup = isset($_POST['auto_setup']);

    $errors = [];
    $success = false;

    // التحقق من البيانات
    if (empty($username)) {
        $errors[] = 'اسم المستخدم مطلوب';
    } elseif (strlen($username) < 3) {
        $errors[] = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
    }

    if (empty($password)) {
        $errors[] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 6) {
        $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }

    if (empty($full_name)) {
        $errors[] = 'الاسم الكامل مطلوب';
    }

    if (!in_array($role, ['admin', 'moderator'])) {
        $errors[] = 'الدور غير صحيح';
    }

    // فحص إذا كان اسم المستخدم موجود مسبقاً
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'اسم المستخدم موجود مسبقاً';
        }
    }

    // إنشاء الحساب إذا لم توجد أخطاء
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admin_accounts (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $role);

        if ($stmt->execute()) {
            $success = true;
            $new_admin_id = $conn->insert_id;

            // تسجيل النشاط
            if (function_exists('logActivity')) {
                $current_user = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'system';
                logActivity($new_admin_id, $username, 'account_created', 'admin_accounts', $new_admin_id, 'إنشاء حساب مدير جديد');
            }
        } else {
            $errors[] = 'فشل في إنشاء الحساب: ' . $conn->error;
        }
    }

    // إرجاع النتيجة كـ JSON إذا كان الطلب auto_setup
    if ($auto_setup) {
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'تم إنشاء المدير بنجاح',
                'admin_id' => $new_admin_id,
                'username' => $username,
                'role' => $role
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - لوحة التحكم</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .create-admin-container {
            max-width: 600px;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .create-admin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-icon {
            font-size: 4rem;
            color: #007bff;
            margin-bottom: 20px;
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

        .password-requirements {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
            line-height: 1.4;
        }

        .requirement-met {
            color: #28a745;
        }

        .requirement-not-met {
            color: #dc3545;
        }

        .role-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
        }

        .role-info.admin {
            background: #f8d7da;
            border-color: #f5c6cb;
        }

        .create-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .create-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .create-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .existing-admins {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .existing-admins h3 {
            margin-top: 0;
            color: #495057;
        }

        .admin-list {
            display: grid;
            gap: 10px;
        }

        .admin-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .admin-details h4 {
            margin: 0;
            color: #333;
        }

        .admin-role {
            font-size: 14px;
            color: #666;
            margin: 2px 0 0;
        }

        .admin-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .quick-create {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .quick-create h3 {
            margin-top: 0;
            color: #856404;
        }

        .quick-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .quick-btn {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .quick-btn:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>

<section class="page-content">
    <div class="create-admin-container">
        <div class="create-admin-header">
            <div class="admin-icon">👤</div>
            <h1>إنشاء مدير جديد</h1>
            <p>أضف حساب مدير جديد للنظام</p>
        </div>

        <div class="quick-create">
            <h3>إنشاء سريع:</h3>
            <div class="quick-buttons">
                <button class="quick-btn" onclick="quickCreate('admin', 'admin123', 'المدير الأول')">مدير أول</button>
                <button class="quick-btn" onclick="quickCreate('moderator', 'mod123', 'مشرف المحتوى')">مشرف محتوى</button>
                <button class="quick-btn" onclick="quickCreate('moderator', 'support123', 'دعم فني')">دعم فني</button>
            </div>
        </div>

        <?php
        // عرض الرسائل
        if (isset($success) && $success) {
            echo "<div class='alert success'>";
            echo "✅ تم إنشاء حساب المدير بنجاح!<br>";
            echo "<strong>اسم المستخدم:</strong> " . htmlspecialchars($username) . "<br>";
            echo "<strong>الدور:</strong> " . htmlspecialchars($role) . "<br>";
            echo "<small>تأكد من مشاركة كلمة المرور مع المستخدم الجديد</small>";
            echo "</div>";
        }

        if (!empty($errors)) {
            echo "<div class='alert error'>";
            echo "<ul style='margin: 0; padding-right: 20px;'>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul></div>";
        }
        ?>

        <form method="POST" id="create-admin-form">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required
                       placeholder="أدخل اسم مستخدم فريد">
            </div>

            <div class="form-group">
                <label for="full_name">الاسم الكامل:</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required
                       placeholder="أدخل الاسم الكامل">
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required
                       placeholder="أدخل كلمة مرور قوية">
                <div class="password-requirements">
                    <div id="length-check" class="requirement-not-met">✓ 6 أحرف على الأقل</div>
                    <div id="letter-check" class="requirement-not-met">✓ حرف واحد على الأقل</div>
                    <div id="number-check" class="requirement-not-met">✓ رقم واحد على الأقل</div>
                </div>
            </div>

            <div class="form-group">
                <label for="role">الدور:</label>
                <select id="role" name="role" required onchange="updateRoleInfo()">
                    <option value="moderator" <?php echo (($_POST['role'] ?? '') === 'moderator') ? 'selected' : ''; ?>>مشرف</option>
                    <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>مدير</option>
                </select>

                <div id="role-info" class="role-info">
                    <strong>المشرف:</strong> يمكنه إدارة المحتوى فقط (مقالات، تلاوات، مواعظ)
                </div>
            </div>

            <button type="submit" class="create-button" id="submit-btn">إنشاء حساب المدير</button>
        </form>

        <div class="existing-admins">
            <h3>المديرون الحاليون:</h3>
            <div class="admin-list">
                <?php
                $admins_result = $conn->query("SELECT username, full_name, role, is_active FROM admin_accounts ORDER BY username");

                if ($admins_result && $admins_result->num_rows > 0) {
                    while ($admin = $admins_result->fetch_assoc()) {
                        $initials = strtoupper(substr($admin['username'], 0, 2));
                        $status_class = $admin['is_active'] ? 'status-active' : 'status-inactive';
                        $status_text = $admin['is_active'] ? 'نشط' : 'معطل';

                        echo '<div class="admin-item">';
                        echo '<div class="admin-info">';
                        echo '<div class="admin-avatar">' . $initials . '</div>';
                        echo '<div class="admin-details">';
                        echo '<h4>' . htmlspecialchars($admin['username']) . '</h4>';
                        echo '<div class="admin-role">' . htmlspecialchars($admin['full_name'] ?: 'بدون اسم') . ' • ' . htmlspecialchars($admin['role']) . '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<span class="admin-status ' . $status_class . '">' . $status_text . '</span>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="text-align: center; padding: 20px; color: #666;">لا توجد حسابات إدارية</div>';
                }
                ?>
            </div>
        </div>
    </div>

</section>

<script>
function updateRoleInfo() {
    const role = document.getElementById('role').value;
    const roleInfo = document.getElementById('role-info');

    if (role === 'admin') {
        roleInfo.className = 'role-info admin';
        roleInfo.innerHTML = '<strong>المدير:</strong> يمكنه الوصول الكامل لجميع ميزات النظام بما في ذلك إدارة المستخدمين والإعدادات';
    } else {
        roleInfo.className = 'role-info';
        roleInfo.innerHTML = '<strong>المشرف:</strong> يمكنه إدارة المحتوى فقط (مقالات، تلاوات، مواعظ)';
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const submitBtn = document.getElementById('submit-btn');

    const lengthCheck = document.getElementById('length-check');
    const letterCheck = document.getElementById('letter-check');
    const numberCheck = document.getElementById('number-check');

    // فحص الطول
    if (password.length >= 6) {
        lengthCheck.className = 'requirement-met';
    } else {
        lengthCheck.className = 'requirement-not-met';
    }

    // فحص الحروف
    if (/[a-zA-Z]/.test(password)) {
        letterCheck.className = 'requirement-met';
    } else {
        letterCheck.className = 'requirement-not-met';
    }

    // فحص الأرقام
    if (/\d/.test(password)) {
        numberCheck.className = 'requirement-met';
    } else {
        numberCheck.className = 'requirement-not-met';
    }

    // تفعيل/تعطيل زر الإرسال
    const allMet = password.length >= 6 && /[a-zA-Z]/.test(password) && /\d/.test(password);
    submitBtn.disabled = !allMet;
}

// مراقبة كلمة المرور
document.getElementById('password').addEventListener('input', checkPasswordStrength);

// فحص كلمة المرور عند تحميل الصفحة
checkPasswordStrength();

function quickCreate(username, password, fullName) {
    // تعبئة النموذج
    document.getElementById('username').value = username;
    document.getElementById('password').value = password;
    document.getElementById('full_name').value = fullName;

    // فحص كلمة المرور
    checkPasswordStrength();

    // التركيز على حقل اسم المستخدم للتعديل إذا لزم الأمر
    document.getElementById('username').focus();
    document.getElementById('username').select();
}

// التحقق من اسم المستخدم المتاح
document.getElementById('username').addEventListener('blur', function() {
    const username = this.value.trim();

    if (username.length >= 3) {
        fetch('check_username.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'username=' + encodeURIComponent(username)
        })
        .then(response => response.json())
        .then(data => {
            if (data.available === false) {
                this.style.borderColor = '#dc3545';
                alert('اسم المستخدم موجود مسبقاً');
            } else {
                this.style.borderColor = '#28a745';
            }
        })
        .catch(error => {
            console.log('فشل في فحص اسم المستخدم:', error);
        });
    }
});

// منع الإرسال عند الضغط على Enter في حقل اسم المستخدم
document.getElementById('username').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('full_name').focus();
    }
});
</script>

</body>
</html>
