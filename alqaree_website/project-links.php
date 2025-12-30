<?php
// ملف روابط المشروع - للاختبار والتطوير
$page_title = 'روابط المشروع والمصادر';
include 'includes/header.php';
?>

<section class="page-content islamic-decor">
    <h1>روابط المشروع والمصادر</h1>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>🔗 روابط سريعة للمشروع:</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin: 10px 0;">
                <strong>🏠 الصفحة الرئيسية:</strong>
                <a href="index.php" target="_blank">index.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📝 المقالات:</strong>
                <a href="articles.php" target="_blank">articles.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎵 التلاوات:</strong>
                <a href="tilawat.php" target="_blank">tilawat.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎤 المواعظ:</strong>
                <a href="hekum.php" target="_blank">hekum.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📖 القرآن:</strong>
                <a href="quran.php" target="_blank">quran.php</a>
            </li>
        </ul>
    </div>

    <div style="background: #e9ecef; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>🔧 روابط الإدارة:</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin: 10px 0;">
                <strong>👨‍💼 لوحة التحكم:</strong>
                <a href="admin/index.php" target="_blank">admin/index.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📝 إدارة المقالات:</strong>
                <a href="admin/manage-articles.php" target="_blank">admin/manage-articles.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎵 إدارة التلاوات:</strong>
                <a href="admin/manage-tilawat.php" target="_blank">admin/manage-tilawat.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎤 إدارة المواعظ:</strong>
                <a href="admin/manage-hekum.php" target="_blank">admin/manage-hekum.php</a>
            </li>
        </ul>
    </div>

    <div style="background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>🧪 روابط الاختبار:</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin: 10px 0;">
                <strong>🧪 صفحة الاختبار:</strong>
                <a href="test.php" target="_blank">test.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📊 فحص المقالات:</strong>
                <a href="check_articles.php" target="_blank">check_articles.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>➕ إدراج مقالات:</strong>
                <a href="insert_articles.php" target="_blank">insert_articles.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🔄 إعادة تعيين البيانات:</strong>
                <a href="reset_quran_juz_page.php" target="_blank">reset_quran_juz_page.php</a>
            </li>
        </ul>
    </div>

    <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>🌐 APIs المتاحة:</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin: 10px 0;">
                <strong>📖 آية عشوائية:</strong>
                <a href="get_random_ayah.php" target="_blank">get_random_ayah.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📝 مقال عشوائي:</strong>
                <a href="get_random_article.php" target="_blank">get_random_article.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📝 مقالات متعددة:</strong>
                <a href="get_random_articles.php" target="_blank">get_random_articles.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎤 موعظة عشوائية:</strong>
                <a href="get_random_hekum.php" target="_blank">get_random_hekum.php</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎵 تلاوة عشوائية:</strong>
                <a href="get_random_tilawat.php" target="_blank">get_random_tilawat.php</a>
            </li>
        </ul>
    </div>

    <div style="background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>📋 ملفات المشروع:</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>📁 الجذر</strong><br>
                <small>index.php, *.php</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>👨‍💼 الإدارة</strong><br>
                <small>admin/*.php</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>🎨 التصميم</strong><br>
                <small>css/*.css</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>⚙️ JavaScript</strong><br>
                <small>js/*.js</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>🔧 المساعدات</strong><br>
                <small>includes/*.php</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>🗃️ البيانات</strong><br>
                <small>sql/*.sql</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>📊 السجلات</strong><br>
                <small>logs/*.log</small>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <strong>🖼️ الصور</strong><br>
                <small>images/*</small>
            </div>
        </div>
    </div>

    <div style="background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>📖 ملفات التوثيق:</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin: 10px 0;">
                <strong>👨‍💼 دليل الإدارة:</strong>
                <a href="ADMIN_SYSTEM_README.md" target="_blank">ADMIN_SYSTEM_README.md</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>🎬 دليل الفيديو:</strong>
                <a href="VIDEO_SYSTEM_README.md" target="_blank">VIDEO_SYSTEM_README.md</a>
            </li>
            <li style="margin: 10px 0;">
                <strong>📋 README الرئيسي:</strong>
                <a href="README.md" target="_blank">README.md</a>
            </li>
        </ul>
    </div>

</section>

<?php include 'includes/footer.php'; ?>
