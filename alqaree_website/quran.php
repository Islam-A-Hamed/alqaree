<?php
$page_title = 'القرآن الكريم';
$page_description = 'المصحف الشريف الكريم - اقرأ واستمع لكلام الله تعالى مع التفسير والشرح';
include 'includes/header.php';
include 'includes/db_connect.php';

// Initialize search variables
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'surah_name';
$search_results = [];

if (!empty($search_query) && $conn) {
    $sql = "";
    $params = [];
    $types = "";

    switch ($search_type) {
        case 'surah_name':
            $sql = "SELECT qv.ayah_number, qv.ayah_text, qs.surah_name_arabic, qs.surah_number FROM quran_verses qv JOIN quran_surahs qs ON qv.surah_number = qs.surah_number WHERE qs.surah_name_arabic LIKE ? ORDER BY qv.surah_number, qv.ayah_number";
            $params = ['%' . $search_query . '%'];
            $types = "s";
            break;
        case 'surah_number':
            // Ensure surah number is an integer for search
            if (is_numeric($search_query)) {
                $sql = "SELECT qv.ayah_number, qv.ayah_text, qs.surah_name_arabic, qs.surah_number FROM quran_verses qv JOIN quran_surahs qs ON qv.surah_number = qs.surah_number WHERE qs.surah_number = ? ORDER BY qv.surah_number, qv.ayah_number";
                $params = [(int)$search_query];
                $types = "i";
            }
            break;
        case 'ayah_number':
            // For ayah number search, we need a surah context usually.
            // For simplicity, let's search for ayah number across all surahs for now.
            // A more advanced implementation might require the user to specify surah as well.
            if (is_numeric($search_query)) {
                $sql = "SELECT qv.ayah_number, qv.ayah_text, qs.surah_name_arabic, qs.surah_number FROM quran_verses qv JOIN quran_surahs qs ON qv.surah_number = qs.surah_number WHERE qv.ayah_number = ? ORDER BY qv.surah_number, qv.ayah_number";
                $params = [(int)$search_query];
                $types = "i";
            }
            break;
        case 'surah_ayah_combined':
            $parts = explode(',', $search_query);
            if (count($parts) == 2) {
                $surah_name_part = trim($parts[0]);
                $ayah_number_part = trim($parts[1]);

                if (!empty($surah_name_part) && is_numeric($ayah_number_part)) {
                    $sql = "SELECT qv.ayah_number, qv.ayah_text, qs.surah_name_arabic, qs.surah_number FROM quran_verses qv JOIN quran_surahs qs ON qv.surah_number = qs.surah_number WHERE qs.surah_name_arabic LIKE ? AND qv.ayah_number = ? ORDER BY qv.surah_number, qv.ayah_number";
                    $params = ['%' . $surah_name_part . '%', (int)$ayah_number_part];
                    $types = "si";
                }
            }
            break;
    }

    if (!empty($sql)) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($types) && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $search_results[] = $row;
            }
            $stmt->close();
        } else {
            // Handle prepare error, e.g., log it
            error_log("Failed to prepare statement: " . $conn->error);
        }
    }
}

// Handle filters
$revelation_filter = isset($_GET['revelation_filter']) ? $_GET['revelation_filter'] : '';
$ayah_count_filter = isset($_GET['ayah_count_filter']) ? $_GET['ayah_count_filter'] : '';

// Get current surah from URL parameter (only if no search query is active and no filters are applied)
// Get current surah from URL parameter
$current_surah = isset($_GET['surah']) ? (int)$_GET['surah'] : 1;
$current_surah = max(1, min(114, $current_surah)); // Ensure valid surah number

// Fetch surah information from database
$surah_info = null;
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM quran_surahs WHERE surah_number = ?");
    $stmt->bind_param("i", $current_surah);
    $stmt->execute();
    $result = $stmt->get_result();
    $surah_info = $result->fetch_assoc();
    $stmt->close();
}

// Fetch verses for current surah
$verses = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT ayah_number, ayah_text FROM quran_verses WHERE surah_number = ? ORDER BY ayah_number");
    $stmt->bind_param("i", $current_surah);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $verses[] = $row;
    }
    $stmt->close();
}
?>

    <section class="page-content islamic-decor">
        <div class="basmala-text" style="font-size: 5rem;">﷽</div>
        <h1 class="sacred-text">القرآن الكريم</h1>
        <p style="font-size: 1.3rem; color: var(--text-color); margin-bottom: 40px; text-align: center;">
            المصحف الشريف الكريم - اقرأ واستمع لكلام الله تعالى مع التفسير والشرح
        </p>

        <!-- Surah Navigation -->
        <div class="islamic-border" style="margin: 40px 0; padding: 30px;">
            <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 20px; font-family: var(--heading-font);">
                اختر السورة
            </h2>
            <form action="" method="GET" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center;">
                <select name="revelation_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">نوع الوحي (الكل)</option>
                    <option value="meccan" <?php echo (isset($_GET['revelation_filter']) && $_GET['revelation_filter'] == 'meccan') ? 'selected' : ''; ?>>مكية</option>
                    <option value="medinan" <?php echo (isset($_GET['revelation_filter']) && $_GET['revelation_filter'] == 'medinan') ? 'selected' : ''; ?>>مدنية</option>
                </select>

                <select name="ayah_count_filter" style="padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    <option value="">عدد الآيات (الكل)</option>
                    <option value="1-20" <?php echo (isset($_GET['ayah_count_filter']) && $_GET['ayah_count_filter'] == '1-20') ? 'selected' : ''; ?>>1-20 آية</option>
                    <option value="21-50" <?php echo (isset($_GET['ayah_count_filter']) && $_GET['ayah_count_filter'] == '21-50') ? 'selected' : ''; ?>>21-50 آية</option>
                    <option value="51-100" <?php echo (isset($_GET['ayah_count_filter']) && $_GET['ayah_count_filter'] == '51-100') ? 'selected' : ''; ?>>51-100 آية</option>
                    <option value="101+" <?php echo (isset($_GET['ayah_count_filter']) && $_GET['ayah_count_filter'] == '101+') ? 'selected' : ''; ?>>أكثر من 100 آية</option>
                </select>
                
                <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 0.9rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                    تطبيق الفلتر
                </button>
                <a href="quran.php" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 0.9rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; text-decoration: none;">
                    إزالة الفلاتر
                </a>
            </form>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; max-height: 300px; overflow-y: auto;">
                <?php
                // Fetch all surahs for navigation with filters
                $all_surahs = [];
                if ($conn) {
                    $sql_all_surahs = "SELECT surah_number, surah_name_arabic, revelation_type, total_ayahs FROM quran_surahs";
                    $where_clauses = [];
                    $params_all_surahs = [];
                    $types_all_surahs = "";

                    if (!empty($revelation_filter)) {
                        $where_clauses[] = "revelation_type = ?";
                        $params_all_surahs[] = $revelation_filter;
                        $types_all_surahs .= "s";
                    }

                    if (!empty($ayah_count_filter)) {
                        switch ($ayah_count_filter) {
                            case '1-20':
                                $where_clauses[] = "total_ayahs BETWEEN 1 AND 20";
                                break;
                            case '21-50':
                                $where_clauses[] = "total_ayahs BETWEEN 21 AND 50";
                                break;
                            case '51-100':
                                $where_clauses[] = "total_ayahs BETWEEN 51 AND 100";
                                break;
                            case '101+':
                                $where_clauses[] = "total_ayahs >= 101";
                                break;
                        }
                    }

                    if (!empty($where_clauses)) {
                        $sql_all_surahs .= " WHERE " . implode(" AND ", $where_clauses);
                    }

                    $sql_all_surahs .= " ORDER BY surah_number";

                    $stmt_all_surahs = $conn->prepare($sql_all_surahs);
                    if ($stmt_all_surahs) {
                        if (!empty($types_all_surahs) && !empty($params_all_surahs)) {
                            $stmt_all_surahs->bind_param($types_all_surahs, ...$params_all_surahs);
                        }
                        $stmt_all_surahs->execute();
                        $result_all_surahs = $stmt_all_surahs->get_result();
                        while ($row = $result_all_surahs->fetch_assoc()) {
                            $all_surahs[] = $row;
                        }
                        $stmt_all_surahs->close();
                    } else {
                        error_log("Failed to prepare statement for all surahs: " . $conn->error);
                    }
                }

                foreach ($all_surahs as $surah):
                    $is_active = ($surah['surah_number'] == $current_surah);
                ?>
                    <a href="?surah=<?php echo $surah['surah_number']; ?>&revelation_filter=<?php echo htmlspecialchars($revelation_filter); ?>&ayah_count_filter=<?php echo htmlspecialchars($ayah_count_filter); ?>"
                       class="surah-link <?php echo $is_active ? 'active' : ''; ?>"
                       style="display: block; padding: 12px; text-decoration: none; border: 1px solid var(--border-color); border-radius: 8px; text-align: center; transition: all 0.3s ease; background: <?php echo $is_active ? 'var(--accent-color)' : 'transparent'; ?>; color: <?php echo $is_active ? 'white' : 'var(--text-color)'; ?>;">
                        <div style="font-size: 1.1em; font-weight: bold; margin-bottom: 4px;">
                            <?php echo $surah['surah_number']; ?>. <?php echo $surah['surah_name_arabic']; ?>
                        </div>
                        <span class="revelation-badge <?php echo $surah['revelation_type']; ?>" style="background: <?php echo $surah['revelation_type'] == 'meccan' ? 'rgba(39, 174, 96, 0.2)' : 'rgba(231, 76, 60, 0.2)'; ?>; color: <?php echo $surah['revelation_type'] == 'meccan' ? '#27ae60' : '#e74c3c'; ?>;">
                            <?php echo $surah['revelation_type'] == 'meccan' ? 'مكية' : 'مدنية'; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Search Form -->
        <div class="islamic-border" style="margin: 40px 0; padding: 30px;">
            <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 20px; font-family: var(--heading-font);">
                بحث في القرآن الكريم
            </h2>
            <form action="" method="GET" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" name="search_query" placeholder="اكتب كلمة البحث (مثال: الله، الفاتحة, 1)"
                       value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>"
                       style="padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 1rem; text-align: right; direction: rtl;">
                
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="search_type" value="surah_name" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'surah_name') ? 'checked' : ''; ?>>
                        اسم السورة
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="search_type" value="surah_number" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'surah_number') ? 'checked' : ''; ?>>
                        رقم السورة
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="search_type" value="ayah_number" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'ayah_number') ? 'checked' : ''; ?>>
                        رقم الآية
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="search_type" value="surah_ayah_combined" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'surah_ayah_combined') ? 'checked' : ''; ?>>
                        السورة ورقم الآية
                    </label>
                </div>
                
                <button type="submit" style="background: var(--accent-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; align-self: center;">
                    بحث 🔍
                </button>
            </form>
        </div>

        <!-- Current Surah Display -->
        <div class="islamic-border" style="margin: 40px 0; padding: 40px;">
            <?php if (!empty($search_results)): ?>
                <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 30px; font-family: var(--heading-font);">
                    نتائج البحث عن "<?php echo htmlspecialchars($search_query); ?>"
                </h2>
                <div class="quran-verses">
                    <?php if (count($search_results) > 0): ?>
                        <?php foreach ($search_results as $verse): ?>
                            <div class="verse-container" style="margin: 30px 0; padding: 20px; border-right: 3px solid var(--accent-color); background: rgba(26, 95, 122, 0.05); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <span class="info-badge" style="background: var(--primary-color); color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.85rem;">
                                        سورة <?php echo $verse['surah_name_arabic']; ?> (<?php echo $verse['surah_number']; ?>)
                                    </span>
                                    <div class="verse-number" style="display: inline-block; background: var(--accent-color); color: white; border-radius: 50%; width: 30px; height: 30px; text-align: center; line-height: 30px; font-weight: bold; font-size: 0.9rem;">
                                        <?php echo $verse['ayah_number']; ?>
                                    </div>
                                </div>
                                <div class="verse-text" style="font-family: 'Amiri', serif; font-size: 1.8rem; line-height: 2.5; color: var(--primary-color); text-align: right; direction: rtl;">
                                    <?php echo $verse['ayah_text']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; font-size: 1.2rem; color: var(--text-color);">لا توجد نتائج مطابقة لبحثك.</p>
                    <?php endif; ?>
                </div>
            <?php elseif (!empty($surah_info)): ?>
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="font-family: var(--heading-font); color: var(--primary-color); font-size: 2.5rem; margin-bottom: 10px;">
                        سورة <?php echo $surah_info['surah_name_arabic']; ?>
                    </h2>
                    <div style="font-size: 1.1rem; color: var(--text-color); margin-bottom: 15px;">
                        <?php echo $surah_info['surah_name_english']; ?>
                    </div>
                    <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                        <div class="info-badge" style="background: var(--accent-color); color: white; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem;">
                            السورة رقم <?php echo $surah_info['surah_number']; ?>
                        </div>
                        <div class="info-badge" style="background: <?php echo $surah_info['revelation_type'] == 'meccan' ? '#27ae60' : '#e74c3c'; ?>; color: white; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem;">
                            <?php echo $surah_info['revelation_type'] == 'meccan' ? 'مكية' : 'مدنية'; ?>
                        </div>
                        <div class="info-badge" style="background: var(--primary-color); color: white; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem;">
                            <?php echo $surah_info['total_ayahs']; ?> آية
                        </div>
                        <div class="info-badge" style="background: #9b59b6; color: white; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem;">
                            نزلت <?php echo $surah_info['revelation_order']; ?> في الترتيب
                        </div>
                    </div>
                </div>

                <!-- Basmala based on database info -->
                <?php if ($surah_info['bismillah_pre'] == 1): ?>
                    <div class="basmala-text" style="text-align: center; font-size: 2.5rem; margin: 30px 0; color: var(--primary-color); direction: rtl; padding: 20px; background: rgba(26, 95, 122, 0.05); border-radius: 15px; border: 2px solid var(--accent-color);">
                        بِسمِ اللَّهِ الرَّحمـٰنِ الرَّحيمِ
                    </div>
                <?php endif; ?>

                <!-- Verses Display -->
                <div class="quran-verses">
                    <?php
                    $verses_count = count($verses);
                    $initial_display = 7; // عرض أول 7 آيات

                    for ($i = 0; $i < $verses_count; $i++):
                        $verse = $verses[$i];
                        $is_hidden = ($i >= $initial_display) ? 'hidden-verse' : '';
                    ?>
                        <div class="verse-container <?php echo $is_hidden; ?>" style="margin: 30px 0; padding: 20px; border-right: 3px solid var(--accent-color); background: rgba(26, 95, 122, 0.05); border-radius: 8px;">
                            <div class="verse-number" style="display: inline-block; background: var(--accent-color); color: white; border-radius: 50%; width: 30px; height: 30px; text-align: center; line-height: 30px; font-weight: bold; margin-left: 10px; font-size: 0.9rem;">
                                <?php echo $verse['ayah_number']; ?>
                            </div>
                            <div class="verse-text" style="font-family: 'Amiri', serif; font-size: 1.8rem; line-height: 2.5; color: var(--primary-color); text-align: right; direction: rtl;">
                                <?php echo $verse['ayah_text']; ?>
                            </div>
                        </div>

                        <?php if ($i == $initial_display - 1 && $verses_count > $initial_display): ?>
                            <div class="show-more-container" style="text-align: center; margin: 30px 0;">
                                <button id="show-more-btn" class="show-more-btn" style="background: var(--primary-color); color: white; border: none; padding: 15px 30px; border-radius: 25px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                    عرض المزيد من الآيات 📖
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <!-- Navigation between Surahs -->
                <div style="display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <?php if ($current_surah > 1):
                        // Get previous surah name
                        $prev_surah_name = '';
                        $prev_surah_num = $current_surah - 1;
                        if ($conn) {
                            $stmt = $conn->prepare("SELECT surah_name_arabic FROM quran_surahs WHERE surah_number = ?");
                            $stmt->bind_param("i", $prev_surah_num);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $prev_surah_name = $row['surah_name_arabic'];
                            }
                            $stmt->close();
                        }
                    ?>
                        <a href="?surah=<?php echo $current_surah - 1; ?>" class="nav-button" style="padding: 12px 24px; background: var(--primary-color); color: white; text-decoration: none; border-radius: 8px; transition: all 0.3s ease;">
                            ← سورة <?php echo $prev_surah_name; ?>
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <?php if ($current_surah < 114):
                        // Get next surah name
                        $next_surah_name = '';
                        $next_surah_num = $current_surah + 1;
                        if ($conn) {
                            $stmt = $conn->prepare("SELECT surah_name_arabic FROM quran_surahs WHERE surah_number = ?");
                            $stmt->bind_param("i", $next_surah_num);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $next_surah_name = $row['surah_name_arabic'];
                            }
                            $stmt->close();
                        }
                    ?>
                        <a href="?surah=<?php echo $current_surah + 1; ?>" class="nav-button" style="padding: 12px 24px; background: var(--primary-color); color: white; text-decoration: none; border-radius: 8px; transition: all 0.3s ease;">
                            سورة <?php echo $next_surah_name; ?> →
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; font-size: 1.2rem; color: var(--text-color);">
                    <?php if (!empty($search_query) && empty($search_results)): ?>
                        لا توجد نتائج مطابقة لبحثك عن "<?php echo htmlspecialchars($search_query); ?>".
                    <?php else: ?>
                        الرجاء تحديد سورة من القائمة أعلاه أو استخدام حقل البحث.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

    </section>

    <style>
        .surah-link:hover {
            background: var(--accent-color) !important;
            color: white !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .surah-link.active {
            box-shadow: 0 4px 12px rgba(26, 95, 122, 0.3);
        }

        .verse-container:hover {
            background: rgba(26, 95, 122, 0.1);
        }

        .nav-button:hover {
            background: var(--accent-color) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .hidden-verse {
            display: none;
        }

        .show-more-btn:hover {
            background: var(--accent-color) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3) !important;
        }

        .show-more-btn:active {
            transform: translateY(0);
        }

        .info-badge {
            display: inline-block;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .info-badge:hover {
            transform: translateY(-2px);
        }

        .surah-link .revelation-badge {
            display: inline-block;
            font-size: 0.75em;
            padding: 2px 6px;
            border-radius: 10px;
            margin-top: 4px;
            font-weight: bold;
        }

        .surah-link .meccan {
            background: rgba(39, 174, 96, 0.2);
            color: #27ae60;
        }

        .surah-link .medinan {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }

        .quran-verses {
            direction: rtl;
        }

        @media (max-width: 768px) {
            .verse-text {
                font-size: 1.5rem !important;
                line-height: 2.2 !important;
            }

            .surah-link {
                font-size: 0.9rem;
                padding: 8px !important;
            }
        }
    </style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showMoreBtn = document.getElementById('show-more-btn');
    const hiddenVerses = document.querySelectorAll('.hidden-verse');

    if (showMoreBtn && hiddenVerses.length > 0) {
        showMoreBtn.addEventListener('click', function() {
            // إظهار جميع الآيات المخفية
            hiddenVerses.forEach(function(verse) {
                verse.style.display = 'block';
                // إضافة أنيميشن للظهور التدريجي
                verse.style.opacity = '0';
                verse.style.transform = 'translateY(20px)';
                verse.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

                setTimeout(function() {
                    verse.style.opacity = '1';
                    verse.style.transform = 'translateY(0)';
                }, 100);
            });

            // إخفاء زر "عرض المزيد"
            showMoreBtn.style.display = 'none';
        });
    }
});
</script>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
include 'includes/footer.php';
?>
