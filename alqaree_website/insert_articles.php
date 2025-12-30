<?php
// ملف إدراج مقالات تجريبية - للاختبار والتطوير
$page_title = 'إدراج مقالات تجريبية';
include 'includes/db_connect.php';
include 'includes/header.php';

// فحص إذا كان الطلب POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // مقالات تجريبية للإدراج
    $sample_articles = [
        [
            'title' => 'أهمية الصبر في حياة المسلم',
            'author_name' => 'الشيخ عبدالله بن عبدالرحمن',
            'category' => 'الإيمان',
            'content' => 'الصبر هو أحد أهم الصفات التي يجب أن يتحلى بها المسلم في حياته اليومية...',
            'publish_date' => '2024-01-15'
        ],
        [
            'title' => 'فضائل تلاوة القرآن الكريم',
            'author_name' => 'الشيخ عبدالرحمن السعدي',
            'category' => 'القرآن',
            'content' => 'تلاوة القرآن الكريم لها فضائل عظيمة وأجر كبير عند الله...',
            'publish_date' => '2024-01-10'
        ],
        [
            'title' => 'آداب الوضوء والصلاة',
            'author_name' => 'الشيخ محمد بن صالح العثيمين',
            'category' => 'الصلاة',
            'content' => 'الوضوء هو أول خطوات الصلاة وله آداب وسنن يجب مراعاتها...',
            'publish_date' => '2024-01-05'
        ]
    ];

    $inserted = 0;
    foreach ($sample_articles as $article) {
        $sql = "INSERT INTO articles (title, author_name, category, content, publish_date)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE content = VALUES(content)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss",
            $article['title'],
            $article['author_name'],
            $article['category'],
            $article['content'],
            $article['publish_date']
        );

        if ($stmt->execute()) {
            $inserted++;
        }
    }

    echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin: 20px 0; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "تم إدراج $inserted مقال تجريبي بنجاح!";
    echo "</div>";
}
?>

<section class="page-content islamic-decor">
    <h1>إدراج مقالات تجريبية</h1>

    <p>اضغط على الزر أدناه لإدراج 3 مقالات تجريبية في قاعدة البيانات.</p>

    <form method="POST">
        <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 16px;">
            إدراج المقالات التجريبية
        </button>
    </form>

</section>

<?php include 'includes/footer.php'; ?>
