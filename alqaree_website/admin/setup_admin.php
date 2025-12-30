<?php
// ملف إعداد النظام الإداري - للاختبار والتطوير
$page_title = 'إعداد النظام الإداري';
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
        .setup-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .setup-title {
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 700;
        }

        .setup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .setup-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .setup-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .setup-card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        .setup-card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .setup-card-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .setup-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .setup-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .setup-button.secondary {
            background: linear-gradient(135deg, #007bff, #6610f2);
        }

        .setup-button.secondary:hover {
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        .setup-button.danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }

        .setup-button.danger:hover {
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 8px;
        }

        .status-ok {
            background: #28a745;
        }

        .status-warning {
            background: #ffc107;
        }

        .status-error {
            background: #dc3545;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            margin: 15px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .setup-summary {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            backdrop-filter: blur(10px);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

<section class="page-content">
    <div class="setup-panel">
        <h1 class="setup-title">🚀 إعداد النظام الإداري</h1>
        <p style="text-align: center; font-size: 1.2rem; margin-bottom: 30px;">
            أداة شاملة لإعداد وتهيئة النظام الإداري للمشروع
        </p>

        <div id="setup-progress" class="progress-bar">
            <div id="progress-fill" class="progress-fill" style="width: 0%"></div>
        </div>

        <div class="setup-grid">
            <!-- فحص النظام -->
            <div class="setup-card">
                <span class="setup-card-icon">🔍</span>
                <h3 class="setup-card-title">فحص النظام</h3>
                <p class="setup-card-description">
                    فحص قاعدة البيانات والجداول والإعدادات الأساسية
                </p>
                <button class="setup-button secondary" onclick="checkSystem()">فحص النظام</button>
            </div>

            <!-- إنشاء المدير الأول -->
            <div class="setup-card">
                <span class="setup-card-icon">👤</span>
                <h3 class="setup-card-title">إنشاء المدير الأول</h3>
                <p class="setup-card-description">
                    إنشاء حساب المدير الأول للنظام (admin/admin123)
                </p>
                <button class="setup-button" onclick="createFirstAdmin()">إنشاء المدير</button>
            </div>

            <!-- إدراج بيانات تجريبية -->
            <div class="setup-card">
                <span class="setup-card-icon">📝</span>
                <h3 class="setup-card-title">البيانات التجريبية</h3>
                <p class="setup-card-description">
                    إدراج مقالات ومحتوى تجريبي للاختبار والعرض
                </p>
                <button class="setup-button" onclick="insertSampleData()">إدراج البيانات</button>
            </div>

            <!-- إعادة تعيين النظام -->
            <div class="setup-card">
                <span class="setup-card-icon">🔄</span>
                <h3 class="setup-card-title">إعادة التعيين</h3>
                <p class="setup-card-description">
                    إعادة تعيين النظام إلى الحالة الأولية (حذف جميع البيانات)
                </p>
                <button class="setup-button danger" onclick="resetSystem()">إعادة التعيين</button>
            </div>

            <!-- النسخ الاحتياطي -->
            <div class="setup-card">
                <span class="setup-card-icon">💾</span>
                <h3 class="setup-card-title">النسخ الاحتياطي</h3>
                <p class="setup-card-description">
                    إنشاء نسخة احتياطية من قاعدة البيانات والملفات
                </p>
                <button class="setup-button secondary" onclick="createBackup()">إنشاء نسخة</button>
            </div>

            <!-- تحسين الأداء -->
            <div class="setup-card">
                <span class="setup-card-icon">⚡</span>
                <h3 class="setup-card-title">تحسين الأداء</h3>
                <p class="setup-card-description">
                    تنظيف قاعدة البيانات وتحسين الأداء العام
                </p>
                <button class="setup-button" onclick="optimizeSystem()">تحسين الأداء</button>
            </div>
        </div>

        <div id="setup-summary" class="setup-summary" style="display: none;">
            <h3 style="color: white; margin-bottom: 15px;">📊 ملخص النظام:</h3>
            <div id="summary-content"></div>
        </div>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px;">
        <h3>🔗 روابط سريعة:</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">
            <a href="login.php" style="background: #007bff; color: white; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center;">تسجيل الدخول</a>
            <a href="debug_login.php" style="background: #28a745; color: white; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center;">Debug تسجيل الدخول</a>
            <a href="fix_login.php" style="background: #ffc107; color: black; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center;">إصلاح تسجيل الدخول</a>
            <a href="reset_password.php" style="background: #dc3545; color: white; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center;">إعادة تعيين كلمة المرور</a>
            <a href="../test.php" style="background: #6f42c1; color: white; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center;">صفحة الاختبار</a>
            <a href="../index.php" style="background: #17a2b8; color: white; padding: 10px; border-radius: 5px; text-decoration: none; text-align: center;">الصفحة الرئيسية</a>
        </div>
    </div>

</section>

<script>
let setupProgress = 0;

function updateProgress(percent) {
    setupProgress = percent;
    document.getElementById('progress-fill').style.width = percent + '%';
}

function showSummary(data) {
    const summaryDiv = document.getElementById('setup-summary');
    const contentDiv = document.getElementById('summary-content');

    let html = '';
    for (const [key, value] of Object.entries(data)) {
        html += `<div class="summary-item">
            <span>${key}</span>
            <span>${value}</span>
        </div>`;
    }

    contentDiv.innerHTML = html;
    summaryDiv.style.display = 'block';
}

function checkSystem() {
    updateProgress(20);
    fetch('system_check.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100);
            showSummary(data);
            alert('تم فحص النظام بنجاح!');
        })
        .catch(error => {
            updateProgress(0);
            alert('حدث خطأ في فحص النظام: ' + error.message);
        });
}

function createFirstAdmin() {
    if (confirm('هل تريد إنشاء حساب المدير الأول (admin/admin123)؟')) {
        updateProgress(50);
        fetch('create_admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'username=admin&password=admin123&full_name=المدير الأول&role=admin&auto_setup=1'
        })
        .then(response => response.json())
        .then(data => {
            updateProgress(100);
            if (data.success) {
                alert('تم إنشاء المدير الأول بنجاح!');
                showSummary(data);
            } else {
                alert('فشل في إنشاء المدير: ' + data.message);
            }
        })
        .catch(error => {
            updateProgress(0);
            alert('حدث خطأ: ' + error.message);
        });
    }
}

function insertSampleData() {
    if (confirm('هل تريد إدراج بيانات تجريبية في النظام؟')) {
        updateProgress(30);
        fetch('../insert_articles.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'auto_setup=1'
        })
        .then(response => response.text())
        .then(result => {
            updateProgress(100);
            if (result.includes('تم إدراج')) {
                alert('تم إدراج البيانات التجريبية بنجاح!');
            } else {
                alert('تم إدراج البيانات التجريبية');
            }
        })
        .catch(error => {
            updateProgress(0);
            alert('حدث خطأ في إدراج البيانات: ' + error.message);
        });
    }
}

function resetSystem() {
    if (confirm('تحذير: سيتم حذف جميع البيانات! هل تريد المتابعة؟')) {
        if (confirm('هل أنت متأكد تماماً؟ هذا الإجراء لا رجعة فيه!')) {
            updateProgress(10);
            fetch('reset_database.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'confirm_reset=1&auto_setup=1'
            })
            .then(response => response.text())
            .then(result => {
                updateProgress(100);
                alert('تم إعادة تعيين النظام بنجاح!');
                location.reload();
            })
            .catch(error => {
                updateProgress(0);
                alert('حدث خطأ في إعادة التعيين: ' + error.message);
            });
        }
    }
}

function createBackup() {
    updateProgress(70);
    fetch('create_backup.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100);
            if (data.success) {
                alert('تم إنشاء النسخة الاحتياطية بنجاح!\nالملف: ' + data.filename);
                showSummary(data);
            } else {
                alert('فشل في إنشاء النسخة الاحتياطية: ' + data.message);
            }
        })
        .catch(error => {
            updateProgress(0);
            alert('حدث خطأ: ' + error.message);
        });
}

function optimizeSystem() {
    updateProgress(40);
    fetch('optimize_database.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100);
            alert('تم تحسين النظام بنجاح!');
            showSummary(data);
        })
        .catch(error => {
            updateProgress(0);
            alert('حدث خطأ في التحسين: ' + error.message);
        });
}

// فحص النظام عند تحميل الصفحة
window.addEventListener('load', function() {
    // فحص سريع للنظام
    fetch('quick_check.php')
        .then(response => response.json())
        .then(data => {
            showSummary(data);
        })
        .catch(error => {
            console.log('فحص سريع غير متوفر');
        });
});
</script>

</body>
</html>
