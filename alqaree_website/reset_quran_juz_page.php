<?php
// ملف إعادة تعيين صفحات الأجزاء القرآنية
$page_title = 'إعادة تعيين صفحات الأجزاء';
include 'includes/db_connect.php';
include 'includes/header.php';

// فحص إذا كان الطلب POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {

    // إعادة تعيين جميع الصفحات والأجزاء للآيات
    $sql = "UPDATE quran_verses SET juz_number = NULL, page_number = NULL";

    if ($conn->query($sql) === TRUE) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "✅ تم إعادة تعيين جميع صفحات الأجزاء بنجاح!";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "❌ خطأ في إعادة التعيين: " . $conn->error;
        echo "</div>";
    }
}

// فحص عدد الآيات التي لها أجزاء وصفحات
$sql_with_data = "SELECT COUNT(*) as count FROM quran_verses WHERE juz_number IS NOT NULL OR page_number IS NOT NULL";
$result_with_data = $conn->query($sql_with_data);
$count_with_data = $result_with_data->fetch_assoc()['count'];

// فحص إجمالي عدد الآيات
$sql_total = "SELECT COUNT(*) as count FROM quran_verses";
$result_total = $conn->query($sql_total);
$count_total = $result_total->fetch_assoc()['count'];
?>

<section class="page-content islamic-decor">
    <h1>إعادة تعيين صفحات الأجزاء القرآنية</h1>

    <div style="background: #fff3cd; color: #856404; padding: 20px; border-radius: 10px; border: 1px solid #ffeaa7; margin: 20px 0;">
        <h3>⚠️ تحذير هام:</h3>
        <p>هذا الإجراء سيقوم بإزالة جميع بيانات الأجزاء والصفحات من آيات القرآن الكريم.</p>
        <p><strong>استخدم هذا فقط إذا كنت تريد إعادة تحميل البيانات من جديد.</strong></p>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3>إحصائيات الحالية:</h3>
        <ul>
            <li><strong>إجمالي عدد الآيات:</strong> <?php echo $count_total; ?></li>
            <li><strong>عدد الآيات التي لها بيانات الأجزاء والصفحات:</strong> <?php echo $count_with_data; ?></li>
            <li><strong>عدد الآيات بدون بيانات:</strong> <?php echo $count_total - $count_with_data; ?></li>
        </ul>
    </div>

    <form method="POST" onsubmit="return confirm('هل أنت متأكد من إعادة تعيين جميع بيانات الأجزاء والصفحات؟')">
        <input type="hidden" name="confirm_reset" value="1">
        <button type="submit" style="background: #dc3545; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">
            🔄 إعادة تعيين جميع البيانات
        </button>
    </form>

</section>

<?php include 'includes/footer.php'; ?>
