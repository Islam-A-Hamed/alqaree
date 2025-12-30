<?php
session_start();

// إذا كان المستخدم مسجل دخول بالفعل، توجيهه للصفحة الرئيسية
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

// التحقق من وجود المستخدم الافتراضي وإنشاؤه تلقائياً إذا لم يكن موجوداً
require_once '../includes/db_connect.php';
require_once '../includes/activity_logger.php';
$check_admin = $conn->query("SELECT id FROM admin_accounts WHERE username = 'admin'");
if ($check_admin->num_rows === 0) {
    // إنشاء المستخدم الافتراضي تلقائياً
    $default_password = password_hash('alqaree2024', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin_accounts (username, password_hash, email, full_name, role) VALUES ('admin', '$default_password', 'admin@alqaree.com', 'مدير النظام', 'admin')");
}
// لا نغلق الاتصال هنا لأنه سيتم استخدامه لاحقاً

// معالجة تسجيل الدخول
$login_error = '';
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // التحقق من صحة البيانات
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'يرجى إدخال اسم المستخدم وكلمة المرور';
        header('Location: login.php');
        exit();
    } else {
        // البحث عن المستخدم في قاعدة البيانات
        $stmt = $conn->prepare("SELECT id, username, password_hash, full_name, role, is_active FROM admin_accounts WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // التحقق من أن الحساب نشط
            if ($user['is_active'] == 1) {
                // التحقق من كلمة المرور
                if (password_verify($password, $user['password_hash'])) {
                    // تسجيل الدخول ناجح
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_full_name'] = $user['full_name'] ?? $user['username'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['login_time'] = date('Y-m-d H:i:s');

                    // تحديث وقت آخر دخول
                    $update_stmt = $conn->prepare("UPDATE admin_accounts SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // تسجيل النشاط
                    logUserActivity('user_login', "تم تسجيل الدخول إلى النظام");

                    $stmt->close();
                    $conn->close();

                    header('Location: index.php');
                    exit();
                } else {
                    $_SESSION['login_error'] = 'كلمة المرور غير صحيحة. <a href="reset_password.php" style="color: #e74c3c;">اضغط هنا لإعادة تعيين كلمة المرور</a>';
                    header('Location: login.php');
                    exit();
                }
            } else {
                $_SESSION['login_error'] = 'الحساب غير نشط، يرجى الاتصال بالدعم الفني';
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['login_error'] = 'اسم المستخدم غير موجود. <a href="fix_login.php" style="color: #e74c3c;">اضغط هنا لإصلاح المشكلة</a>';
            header('Location: login.php');
            exit();
        }

        $stmt->close();
        $conn->close();
    }
    // ملاحظة: لن يتم الوصول إلى هذا الكود أبداً بسبب إعادة التوجيه
}

// إغلاق اتصال قاعدة البيانات في نهاية معالجة تسجيل الدخول
if (isset($conn) && $conn instanceof mysqli && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="تسجيل دخول لوحة إدارة موقع القارئ">
    <title>تسجيل الدخول - لوحة الإدارة</title>
    <link rel="stylesheet" href="../css/admin-style.css">
    <link rel="icon" type="image/svg+xml" href="../icon-192x192.svg">
    <style>
        .login-wrapper {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-dark) 50%, var(--admin-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--admin-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--admin-secondary), var(--admin-success), var(--admin-warning));
        }

        .login-logo {
            margin-bottom: 40px;
        }

        .login-logo h1 {
            color: var(--admin-primary);
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .login-logo p {
            color: var(--admin-gray-500);
            font-size: 16px;
            font-weight: 500;
        }

        .login-form {
            text-align: right;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--admin-gray-700);
            font-weight: 600;
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--admin-gray-300);
            border-radius: var(--radius-lg);
            font-size: 16px;
            transition: var(--transition-normal);
            background: var(--admin-white);
            color: var(--admin-gray-700);
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--admin-secondary);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.15);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: var(--admin-gray-400);
            font-size: 15px;
        }


        /* Password toggle button */
        .password-input-container {
            position: relative;
        }

        .password-toggle-btn {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--admin-gray-400);
            cursor: pointer;
            padding: 8px;
            border-radius: var(--radius-md);
            transition: var(--transition-fast);
            font-size: 16px;
            line-height: 1;
        }

        .password-toggle-btn:hover {
            color: var(--admin-secondary);
            background: var(--admin-gray-100);
        }

        .form-control.with-toggle {
            padding-left: 50px;
        }

        .btn-login {
            width: 100%;
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--admin-secondary) 0%, var(--admin-primary) 100%);
            color: var(--admin-white);
            border: none;
            border-radius: var(--radius-lg);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-normal);
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(52, 152, 219, 0.4);
        }

        .alert {
            background: rgba(231, 76, 60, 0.1);
            color: var(--admin-accent);
            border: 1px solid rgba(231, 76, 60, 0.2);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-icon {
            margin-left: 10px;
            font-size: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--admin-gray-500);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition-fast);
            padding: 10px 16px;
            border-radius: var(--radius-md);
        }

        .back-link:hover {
            color: var(--admin-secondary);
            background: var(--admin-gray-50);
        }

        .back-link::before {
            content: '←';
            margin-left: 8px;
            font-size: 16px;
        }

        .login-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--admin-gray-200);
            color: var(--admin-gray-400);
            font-size: 12px;
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            position: relative;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: var(--admin-white);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 35px 25px;
                margin: 15px;
                max-width: none;
            }

            .login-logo h1 {
                font-size: 2.4rem;
            }

            .form-control {
                padding: 14px 18px;
                font-size: 16px;
            }

            .btn-login {
                padding: 14px 18px;
                font-size: 15px;
            }
        }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .login-card {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <h1>القــارئ</h1>
                <p>لوحة الإدارة المتقدمة</p>
            </div>

            <?php if ($login_error): ?>
                <div class="alert">
                    <span class="alert-icon">⚠️</span>
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           placeholder="أدخل اسم المستخدم" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" class="form-control with-toggle"
                               placeholder="أدخل كلمة المرور" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()" title="إظهار كلمة المرور">
                            🔓
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span>دخول لوحة الإدارة</span>
                </button>
            </form>

            <a href="../index.php" class="back-link">
                العودة للموقع الرئيسي
            </a>

            <div class="login-footer">
                © 2024 موقع القارئ - جميع الحقوق محفوظة
                <br><small>
                    <a href="reset_password.php" style="color: #e74c3c; text-decoration: underline; font-weight: bold;">🔑 إعادة تعيين كلمة المرور</a> |
                    <a href="fix_login.php" style="color: #666; text-decoration: underline;">🔧 إصلاح تسجيل الدخول</a> |
                    <a href="init_database.php" style="color: #666; text-decoration: underline;">إعادة تهيئة قاعدة البيانات</a> |
                    <a href="create_admin.php" style="color: #666; text-decoration: underline;">إنشاء المستخدم الافتراضي</a> |
                    <a href="test_db.php" style="color: #666; text-decoration: underline;">اختبار قاعدة البيانات</a>
                </small>
            </div>
        </div>
    </div>

    <script>
        // Loading state for form submission
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const span = btn.querySelector('span');
            span.textContent = 'جاري تسجيل الدخول...';
            btn.classList.add('loading');
        });

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Enter key navigation
        document.getElementById('username').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle-btn');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.innerHTML = '🔒';
                toggleBtn.title = 'إخفاء كلمة المرور';
            } else {
                passwordInput.type = 'password';
                toggleBtn.innerHTML = '🔓';
                toggleBtn.title = 'إظهار كلمة المرور';
            }
        }
    </script>
</body>
</html>
