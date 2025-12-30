<?php
// ملف تهيئة قاعدة البيانات - للاختبار والتطوير
$page_title = 'تهيئة قاعدة البيانات';
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
        .init-container {
            max-width: 800px;
            margin: 0 auto;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .init-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .database-icon {
            font-size: 4rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .init-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        .section-icon {
            font-size: 1.5rem;
            margin-left: 10px;
            width: 30px;
            text-align: center;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }

        .init-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .init-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .init-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .card-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-desc {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .init-button {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }

        .init-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .init-button.danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }

        .init-button.danger:hover {
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.3);
        }

        .init-button.secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .init-button.secondary:hover {
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
        }

        .progress-container {
            margin: 20px 0;
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .progress-text {
            text-align: center;
            font-weight: 600;
            color: #333;
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

        .alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 8px;
        }

        .status-good {
            background: #28a745;
        }

        .status-warning {
            background: #ffc107;
        }

        .status-error {
            background: #dc3545;
        }

        .table-container {
            overflow-x: auto;
            margin: 20px 0;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .status-table th,
        .status-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #e9ecef;
        }

        .status-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        .status-table tr:hover {
            background: #f8f9fa;
        }

        .status-icon {
            font-size: 1.2rem;
        }

        .backup-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .backup-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .backup-stat {
            text-align: center;
        }

        .backup-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }

        .backup-label {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>

<section class="page-content">
    <div class="init-container">
        <div class="init-header">
            <div class="database-icon">🗄️</div>
            <h1>تهيئة قاعدة البيانات</h1>
            <p>أدوات شاملة لإدارة وتهيئة قاعدة البيانات</p>
        </div>

        <div class="progress-container" id="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div class="progress-text" id="progress-text">جاري التحضير...</div>
        </div>

        <!-- معلومات قاعدة البيانات -->
        <div class="init-section">
            <div class="section-header">
                <span class="section-icon">📊</span>
                <h2 class="section-title">حالة قاعدة البيانات</h2>
            </div>

            <?php
            // معلومات قاعدة البيانات
            $db_stats = [
                'المقالات' => $conn->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'],
                'التلاوات' => $conn->query("SELECT COUNT(*) as count FROM tilawat")->fetch_assoc()['count'],
                'المواعظ' => $conn->query("SELECT COUNT(*) as count FROM hekum")->fetch_assoc()['count'],
                'المديرين' => $conn->query("SELECT COUNT(*) as count FROM admin_accounts")->fetch_assoc()['count'],
                'السجلات' => $conn->query("SELECT COUNT(*) as count FROM activity_logs")->fetch_assoc()['count'],
                'الآيات' => $conn->query("SELECT COUNT(*) as count FROM quran_verses")->fetch_assoc()['count']
            ];
            ?>

            <div class="init-grid">
                <?php foreach ($db_stats as $label => $count): ?>
                    <div class="init-card">
                        <span class="card-icon">
                            <?php
                            $icons = [
                                'المقالات' => '📝',
                                'التلاوات' => '🎵',
                                'المواعظ' => '🎤',
                                'المديرين' => '👥',
                                'السجلات' => '📋',
                                'الآيات' => '📖'
                            ];
                            echo $icons[$label] ?? '📊';
                            ?>
                        </span>
                        <div class="card-title"><?php echo $label; ?></div>
                        <div class="card-value"><?php echo number_format($count); ?></div>
                        <div class="card-desc">سجل</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- أدوات التهيئة -->
        <div class="init-section">
            <div class="section-header">
                <span class="section-icon">🔧</span>
                <h2 class="section-title">أدوات التهيئة</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <button class="init-button" onclick="runDiagnostics()">تشخيص شامل</button>
                <button class="init-button secondary" onclick="optimizeDatabase()">تحسين الأداء</button>
                <button class="init-button danger" onclick="clearOldLogs()">مسح السجلات القديمة</button>
                <button class="init-button" onclick="createBackup()">إنشاء نسخة احتياطية</button>
            </div>

            <div id="diagnostics-result" style="margin-top: 20px;"></div>
        </div>

        <!-- إدارة البيانات -->
        <div class="init-section">
            <div class="section-header">
                <span class="section-icon">🗂️</span>
                <h2 class="section-title">إدارة البيانات</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <button class="init-button secondary" onclick="importSampleData()">استيراد بيانات تجريبية</button>
                <button class="init-button danger" onclick="truncateTables()">تفريغ الجداول</button>
                <button class="init-button" onclick="resetAutoIncrement()">إعادة تعيين العدادات</button>
                <button class="init-button secondary" onclick="checkDataIntegrity()">فحص سلامة البيانات</button>
            </div>
        </div>

        <!-- معلومات النظام -->
        <div class="init-section">
            <div class="section-header">
                <span class="section-icon">💻</span>
                <h2 class="section-title">معلومات النظام</h2>
            </div>

            <div class="table-container">
                <table class="status-table">
                    <tr>
                        <th>الخاصية</th>
                        <th>الحالة</th>
                        <th>القيمة</th>
                    </tr>
                    <tr>
                        <td>اتصال قاعدة البيانات</td>
                        <td>
                            <?php if ($conn->connect_error): ?>
                                <span class="status-error">❌</span> خطأ
                            <?php else: ?>
                                <span class="status-good">✅</span> متصل
                            <?php endif; ?>
                        </td>
                        <td><?php echo $conn->server_info ?? 'غير متوفر'; ?></td>
                    </tr>
                    <tr>
                        <td>إصدار MySQL</td>
                        <td><span class="status-good">ℹ️</span> معلومات</td>
                        <td><?php echo $conn->server_version ?? 'غير محدد'; ?></td>
                    </tr>
                    <tr>
                        <td>إصدار PHP</td>
                        <td><span class="status-good">ℹ️</span> معلومات</td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td>ذاكرة PHP المستخدمة</td>
                        <td><span class="status-good">ℹ️</span> معلومات</td>
                        <td><?php echo round(memory_get_peak_usage() / 1024 / 1024, 2); ?> MB</td>
                    </tr>
                    <tr>
                        <td>المنطقة الزمنية</td>
                        <td><span class="status-good">ℹ️</span> معلومات</td>
                        <td><?php echo date_default_timezone_get(); ?></td>
                    </tr>
                    <tr>
                        <td>وقت الخادم</td>
                        <td><span class="status-good">ℹ️</span> معلومات</td>
                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- نسخ احتياطي -->
        <div class="backup-section">
            <h3 style="margin-top: 0; color: #495057;">📦 النسخ الاحتياطية</h3>

            <div class="backup-info">
                <div class="backup-stat">
                    <div class="backup-number" id="backup-count">0</div>
                    <div class="backup-label">نسخة متاحة</div>
                </div>
                <div class="backup-stat">
                    <div class="backup-number" id="backup-size">0 MB</div>
                    <div class="backup-label">الحجم الإجمالي</div>
                </div>
                <div class="backup-stat">
                    <div class="backup-number" id="last-backup">-</div>
                    <div class="backup-label">آخر نسخة</div>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <button class="init-button secondary" onclick="listBackups()">عرض النسخ الاحتياطية</button>
                <button class="init-button danger" onclick="cleanupOldBackups()">مسح النسخ القديمة</button>
            </div>

            <div id="backup-list" style="margin-top: 15px; display: none;"></div>
        </div>
    </div>

</section>

<script>
let currentProgress = 0;

function updateProgress(percent, text = '') {
    currentProgress = percent;
    document.getElementById('progress-fill').style.width = percent + '%';
    if (text) {
        document.getElementById('progress-text').textContent = text;
    }
    document.getElementById('progress-container').style.display = 'block';
}

function hideProgress() {
    document.getElementById('progress-container').style.display = 'none';
    currentProgress = 0;
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert ' + type;
    alertDiv.innerHTML = message;
    alertDiv.style.marginTop = '15px';

    // إزالة التنبيهات السابقة
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // إضافة التنبيه الجديد
    document.querySelector('.init-container').appendChild(alertDiv);

    // إزالة التنبيه تلقائياً بعد 5 ثوان
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function runDiagnostics() {
    updateProgress(10, 'جاري تشغيل التشخيص...');

    fetch('run_diagnostics.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم الانتهاء من التشخيص');

            setTimeout(() => {
                hideProgress();

                let html = '<h4>نتائج التشخيص:</h4>';

                if (data.tables) {
                    html += '<h5>الجداول:</h5><ul>';
                    data.tables.forEach(table => {
                        const status = table.exists ? '✅ موجود' : '❌ ناقص';
                        html += `<li>${table.name}: ${status}</li>`;
                    });
                    html += '</ul>';
                }

                if (data.issues && data.issues.length > 0) {
                    html += '<h5 style="color: #dc3545;">المشاكل المكتشفة:</h5><ul>';
                    data.issues.forEach(issue => {
                        html += `<li style="color: #dc3545;">${issue}</li>`;
                    });
                    html += '</ul>';
                } else {
                    html += '<p style="color: #28a745;">✅ لم يتم العثور على مشاكل</p>';
                }

                document.getElementById('diagnostics-result').innerHTML = html;
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في تشغيل التشخيص: ' + error.message, 'error');
        });
}

function optimizeDatabase() {
    updateProgress(25, 'جاري تحسين قاعدة البيانات...');

    fetch('optimize_database.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم تحسين قاعدة البيانات');

            setTimeout(() => {
                hideProgress();
                if (data.success) {
                    showAlert('تم تحسين قاعدة البيانات بنجاح', 'success');
                } else {
                    showAlert('فشل في تحسين قاعدة البيانات: ' + data.message, 'error');
                }
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في تحسين قاعدة البيانات: ' + error.message, 'error');
        });
}

function clearOldLogs() {
    if (!confirm('هل تريد مسح جميع السجلات القديمة (أقدم من 30 يوم)؟')) {
        return;
    }

    updateProgress(50, 'جاري مسح السجلات القديمة...');

    fetch('clear_old_logs.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم مسح السجلات القديمة');

            setTimeout(() => {
                hideProgress();
                if (data.success) {
                    showAlert(`تم مسح ${data.deleted_count} سجل قديم`, 'success');
                } else {
                    showAlert('فشل في مسح السجلات: ' + data.message, 'error');
                }
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في مسح السجلات: ' + error.message, 'error');
        });
}

function createBackup() {
    updateProgress(30, 'جاري إنشاء النسخة الاحتياطية...');

    fetch('create_backup.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم إنشاء النسخة الاحتياطية');

            setTimeout(() => {
                hideProgress();
                if (data.success) {
                    showAlert(`تم إنشاء النسخة الاحتياطية: ${data.filename}`, 'success');
                    loadBackupStats();
                } else {
                    showAlert('فشل في إنشاء النسخة الاحتياطية: ' + data.message, 'error');
                }
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في إنشاء النسخة الاحتياطية: ' + error.message, 'error');
        });
}

function importSampleData() {
    if (!confirm('هل تريد استيراد بيانات تجريبية للمشروع؟')) {
        return;
    }

    updateProgress(20, 'جاري استيراد البيانات التجريبية...');

    fetch('../insert_articles.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'auto_setup=1'
    })
    .then(response => response.text())
    .then(result => {
        updateProgress(100, 'تم استيراد البيانات التجريبية');

        setTimeout(() => {
            hideProgress();
            if (result.includes('تم إدراج')) {
                showAlert('تم استيراد البيانات التجريبية بنجاح', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('تم استيراد البيانات التجريبية', 'success');
            }
        }, 500);
    })
    .catch(error => {
        hideProgress();
        showAlert('فشل في استيراد البيانات: ' + error.message, 'error');
    });
}

function truncateTables() {
    if (!confirm('تحذير: سيتم حذف جميع البيانات! هل تريد المتابعة؟')) {
        return;
    }

    if (!confirm('هل أنت متأكد تماماً؟ هذا الإجراء لا رجعة فيه!')) {
        return;
    }

    updateProgress(80, 'جاري تفريغ الجداول...');

    fetch('truncate_tables.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم تفريغ الجداول');

            setTimeout(() => {
                hideProgress();
                if (data.success) {
                    showAlert('تم تفريغ جميع الجداول بنجاح', 'warning');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('فشل في تفريغ الجداول: ' + data.message, 'error');
                }
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في تفريغ الجداول: ' + error.message, 'error');
        });
}

function resetAutoIncrement() {
    updateProgress(60, 'جاري إعادة تعيين العدادات...');

    fetch('reset_auto_increment.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم إعادة تعيين العدادات');

            setTimeout(() => {
                hideProgress();
                if (data.success) {
                    showAlert('تم إعادة تعيين العدادات بنجاح', 'success');
                } else {
                    showAlert('فشل في إعادة تعيين العدادات: ' + data.message, 'error');
                }
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في إعادة تعيين العدادات: ' + error.message, 'error');
        });
}

function checkDataIntegrity() {
    updateProgress(40, 'جاري فحص سلامة البيانات...');

    fetch('check_data_integrity.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم فحص سلامة البيانات');

            setTimeout(() => {
                hideProgress();

                let message = 'تم فحص سلامة البيانات بنجاح';
                let type = 'success';

                if (data.issues && data.issues.length > 0) {
                    message = `تم العثور على ${data.issues.length} مشكلة في البيانات`;
                    type = 'warning';
                }

                showAlert(message, type);
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في فحص سلامة البيانات: ' + error.message, 'error');
        });
}

function loadBackupStats() {
    fetch('get_backup_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('backup-count').textContent = data.count;
                document.getElementById('backup-size').textContent = data.total_size + ' MB';
                document.getElementById('last-backup').textContent = data.last_backup || '-';
            }
        })
        .catch(error => {
            console.log('فشل في تحميل إحصائيات النسخ الاحتياطية:', error);
        });
}

function listBackups() {
    const backupList = document.getElementById('backup-list');

    fetch('list_backups.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.backups.length > 0) {
                let html = '<h4>النسخ الاحتياطية المتاحة:</h4><ul>';
                data.backups.forEach(backup => {
                    html += `<li>${backup.filename} - ${backup.size} - ${backup.date}</li>`;
                });
                html += '</ul>';
                backupList.innerHTML = html;
                backupList.style.display = 'block';
            } else {
                backupList.innerHTML = '<p>لا توجد نسخ احتياطية</p>';
                backupList.style.display = 'block';
            }
        })
        .catch(error => {
            backupList.innerHTML = '<p style="color: #dc3545;">فشل في تحميل قائمة النسخ الاحتياطية</p>';
            backupList.style.display = 'block';
        });
}

function cleanupOldBackups() {
    if (!confirm('هل تريد حذف النسخ الاحتياطية القديمة (أقدم من 30 يوم)؟')) {
        return;
    }

    updateProgress(70, 'جاري مسح النسخ القديمة...');

    fetch('cleanup_backups.php')
        .then(response => response.json())
        .then(data => {
            updateProgress(100, 'تم مسح النسخ القديمة');

            setTimeout(() => {
                hideProgress();
                if (data.success) {
                    showAlert(`تم حذف ${data.deleted_count} نسخة قديمة`, 'success');
                    loadBackupStats();
                } else {
                    showAlert('فشل في مسح النسخ القديمة: ' + data.message, 'error');
                }
            }, 500);
        })
        .catch(error => {
            hideProgress();
            showAlert('فشل في مسح النسخ القديمة: ' + error.message, 'error');
        });
}

// تحميل إحصائيات النسخ الاحتياطية عند تحميل الصفحة
loadBackupStats();
</script>

</body>
</html>
