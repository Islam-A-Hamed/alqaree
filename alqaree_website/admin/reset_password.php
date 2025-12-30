<?php
// ملف إعادة تعيين كلمة المرور - للاختبار والتطوير
$page_title = 'إعادة تعيين كلمة المرور';
include '../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - لوحة التحكم</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .reset-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            max-width: 600px;
        }

        .reset-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .reset-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .reset-button:active {
            transform: translateY(0);
        }

        .reset-button:disabled {
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

        .alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }

        .user-list {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin: 20px 0;
        }

        .user-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-list th,
        .user-list td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
        }

        .user-list th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .user-list tr:hover {
            background: #f8f9fa;
        }

        .quick-reset {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .quick-reset-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .quick-reset-btn {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .quick-reset-btn:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>

<section class="page-content">
    <h1>🔑 إعادة تعيين كلمة المرور</h1>

    <div class="reset-panel">
        <p>استخدم هذه الأداة لإعادة تعيين كلمات مرور حسابات المديرين.</p>

        <div class="quick-reset">
            <h3>⚡ إعادة تعيين سريعة:</h3>
            <div class="quick-reset-buttons">
                <button class="quick-reset-btn" onclick="quickReset('admin', 'admin123')">مدير: admin → admin123</button>
                <button class="quick-reset-btn" onclick="quickReset('moderator', 'mod123')">مشرف: moderator → mod123</button>
                <button class="quick-reset-btn" onclick="resetAllPasswords()">جميع الحسابات → pass123</button>
            </div>
        </div>

        <div class="reset-form">
            <h2>إعادة تعيين مخصص:</h2>

            <?php
            $message = '';
            $message_type = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = trim($_POST['username'] ?? '');
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                if (empty($username)) {
                    $message = 'اسم المستخدم مطلوب';
                    $message_type = 'error';
                } elseif (empty($new_password)) {
                    $message = 'كلمة المرور الجديدة مطلوبة';
                    $message_type = 'error';
                } elseif (strlen($new_password) < 6) {
                    $message = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                    $message_type = 'error';
                } elseif ($new_password !== $confirm_password) {
                    $message = 'كلمات المرور غير متطابقة';
                    $message_type = 'error';
                } else {
                    // فحص وجود المستخدم
                    $stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 0) {
                        $message = 'اسم المستخدم غير موجود';
                        $message_type = 'error';
                    } else {
                        // إعادة تعيين كلمة المرور
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE admin_accounts SET password_hash = ? WHERE username = ?");

                        if ($update_stmt->execute([$hashed_password, $username])) {
                            $message = 'تم إعادة تعيين كلمة المرور بنجاح';
                            $message_type = 'success';

                            // تسجيل النشاط
                            $user_id = $result->fetch_assoc()['id'];
                            if (function_exists('logActivity')) {
                                logActivity($user_id, $username, 'password_reset', 'admin_accounts', $user_id, 'إعادة تعيين كلمة المرور');
                            }
                        } else {
                            $message = 'فشل في إعادة تعيين كلمة المرور';
                            $message_type = 'error';
                        }
                    }
                }
            }

            if (!empty($message)) {
                echo "<div class='alert $message_type'>$message</div>";
            }
            ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">اسم المستخدم:</label>
                    <input type="text" id="username" name="username" required
                           placeholder="أدخل اسم المستخدم">
                </div>

                <div class="form-group">
                    <label for="new_password">كلمة المرور الجديدة:</label>
                    <input type="password" id="new_password" name="new_password" required
                           placeholder="أدخل كلمة مرور قوية">
                </div>

                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="أعد إدخال كلمة المرور">
                </div>

                <button type="submit" class="reset-button">إعادة تعيين كلمة المرور</button>
            </form>
        </div>

        <div class="user-list">
            <h3>👥 قائمة المستخدمين:</h3>
            <?php
            $result = $conn->query("SELECT username, full_name, role, last_login FROM admin_accounts ORDER BY username");

            if ($result && $result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الدور</th><th>آخر دخول</th></tr>';

                while ($user = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['full_name'] ?: 'غير محدد') . '</td>';
                    echo '<td>' . htmlspecialchars($user['role']) . '</td>';
                    echo '<td>' . ($user['last_login'] ?: 'لم يسجل دخول') . '</td>';
                    echo '</tr>';
                }

                echo '</table>';
            } else {
                echo '<p style="text-align: center; padding: 20px; color: #6c757d;">لا توجد حسابات إدارية</p>';
            }
            ?>
        </div>
    </div>

    <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin-top: 20px;">
        <h3>🔒 نصائح الأمان:</h3>
        <ul>
            <li>استخدم كلمات مرور قوية تحتوي على أحرف كبيرة وصغيرة وأرقام ورموز</li>
            <li>غير كلمة المرور بانتظام</li>
            <li>لا تشارك كلمة المرور مع أي شخص</li>
            <li>في الإنتاج، احذف هذه الصفحة أو احمها</li>
        </ul>
    </div>

</section>

<script>
function quickReset(username, password) {
    if (confirm(`هل تريد إعادة تعيين كلمة مرور "${username}" إلى "${password}"؟`)) {
        document.getElementById('username').value = username;
        document.getElementById('new_password').value = password;
        document.getElementById('confirm_password').value = password;

        // تمكين الزر وإرسال النموذج
        const button = document.querySelector('.reset-button');
        button.disabled = false;
        button.click();
    }
}

function resetAllPasswords() {
    if (confirm('هل تريد إعادة تعيين كلمات مرور جميع الحسابات إلى "pass123"؟')) {
        fetch('bulk_reset_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ new_password: 'pass123' })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('تم إعادة تعيين كلمات مرور جميع الحسابات بنجاح');
                location.reload();
            } else {
                alert('فشل في إعادة التعيين: ' + result.message);
            }
        })
        .catch(error => {
            alert('حدث خطأ: ' + error.message);
        });
    }
}

// التحقق من تطابق كلمات المرور
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const button = document.querySelector('.reset-button');

    if (newPassword !== confirmPassword) {
        this.style.borderColor = '#dc3545';
        button.disabled = true;
    } else {
        this.style.borderColor = '#28a745';
        button.disabled = false;
    }
});
</script>

</body>
</html>
