<?php
/**
 * نظام تسجيل النشاطات للوحة الإدارة
 */

class ActivityLogger {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * تسجيل نشاط جديد
     */
    public function logActivity($action_type, $entity_type, $entity_id = null, $entity_title = null, $description = null, $old_values = null, $new_values = null) {
        // الحصول على بيانات المستخدم الحالي
        $user_id = $_SESSION['admin_id'] ?? null;
        $username = $_SESSION['admin_username'] ?? 'غير معروف';

        if (!$user_id) {
            return false; // لا يمكن تسجيل النشاط بدون مستخدم
        }

        // الحصول على IP والمتصفح
        $ip_address = $this->getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // تحويل المصفوفات إلى JSON إذا لزم الأمر
        $old_values_json = $old_values ? json_encode($old_values, JSON_UNESCAPED_UNICODE) : null;
        $new_values_json = $new_values ? json_encode($new_values, JSON_UNESCAPED_UNICODE) : null;

        // إعداد الاستعلام
        $stmt = $this->conn->prepare("
            INSERT INTO activity_logs
            (user_id, username, action_type, entity_type, entity_id, entity_title, description, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssissssss",
            $user_id,
            $username,
            $action_type,
            $entity_type,
            $entity_id,
            $entity_title,
            $description,
            $old_values_json,
            $new_values_json,
            $ip_address,
            $user_agent
        );

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * الحصول على النشاطات الأخيرة
     */
    public function getRecentActivities($limit = 50, $user_id = null, $entity_type = null) {
        $where_clauses = [];
        $params = [];
        $types = '';

        if ($user_id) {
            $where_clauses[] = "user_id = ?";
            $params[] = $user_id;
            $types .= 'i';
        }

        if ($entity_type) {
            $where_clauses[] = "entity_type = ?";
            $params[] = $entity_type;
            $types .= 's';
        }

        $where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

        $query = "
            SELECT * FROM activity_logs
            {$where_sql}
            ORDER BY created_at DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $params[] = $limit;
            $types .= 'i';
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("i", $limit);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $activities = [];
        while ($row = $result->fetch_assoc()) {
            // تحويل JSON إلى مصفوفات
            if ($row['old_values']) {
                $row['old_values'] = json_decode($row['old_values'], true);
            }
            if ($row['new_values']) {
                $row['new_values'] = json_decode($row['new_values'], true);
            }
            $activities[] = $row;
        }

        $stmt->close();
        return $activities;
    }

    /**
     * الحصول على إحصائيات النشاطات
     */
    public function getActivityStats($days = 30) {
        $query = "
            SELECT
                COUNT(*) as total_activities,
                COUNT(DISTINCT user_id) as active_users,
                COUNT(CASE WHEN action_type LIKE '%create%' THEN 1 END) as create_actions,
                COUNT(CASE WHEN action_type LIKE '%update%' THEN 1 END) as update_actions,
                COUNT(CASE WHEN action_type LIKE '%delete%' THEN 1 END) as delete_actions,
                entity_type,
                COUNT(*) as entity_count
            FROM activity_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY entity_type
            ORDER BY entity_count DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $days);
        $stmt->execute();
        $result = $stmt->get_result();

        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }

        $stmt->close();
        return $stats;
    }

    /**
     * الحصول على عنوان IP للعميل
     */
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * تنظيف السجلات القديمة
     */
    public function cleanupOldLogs($days_to_keep = 90) {
        $stmt = $this->conn->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->bind_param("i", $days_to_keep);
        $result = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $affected_rows;
    }

    /**
     * تنسيق الوقت (منذ كم وقت)
     */
    public function formatTimeAgo($datetime) {
        $now = new DateTime();
        $activity_time = new DateTime($datetime);
        $diff = $now->diff($activity_time);

        if ($diff->y > 0) {
            return "منذ " . $diff->y . " سنة";
        } elseif ($diff->m > 0) {
            return "منذ " . $diff->m . " شهر";
        } elseif ($diff->d > 0) {
            return "منذ " . $diff->d . " يوم";
        } elseif ($diff->h > 0) {
            return "منذ " . $diff->h . " ساعة";
        } elseif ($diff->i > 0) {
            return "منذ " . $diff->i . " دقيقة";
        } else {
            return "الآن";
        }
    }
}

/**
 * دوال مساعدة للتسجيل السريع
 */
function logActivity($action_type, $entity_type, $entity_id = null, $entity_title = null, $description = null, $old_values = null, $new_values = null) {
    static $logger = null;

    if ($logger === null) {
        require_once 'db_connect.php';
        global $conn;
        $logger = new ActivityLogger($conn);
    }

    return $logger->logActivity($action_type, $entity_type, $entity_id, $entity_title, $description, $old_values, $new_values);
}

/**
 * دوال محددة لأنواع النشاطات الشائعة
 */
function logUserActivity($action, $description = null) {
    return logActivity($action, 'user', $_SESSION['admin_id'] ?? null, $_SESSION['admin_username'] ?? null, $description);
}

function logTilawatActivity($action, $tilawat_id, $tilawat_title, $description = null, $old_values = null, $new_values = null) {
    return logActivity($action, 'tilawat', $tilawat_id, $tilawat_title, $description, $old_values, $new_values);
}

function logHekumActivity($action, $hekum_id, $hekum_title, $description = null, $old_values = null, $new_values = null) {
    return logActivity($action, 'hekum', $hekum_id, $hekum_title, $description, $old_values, $new_values);
}

function logArticleActivity($action, $article_id, $article_title, $description = null, $old_values = null, $new_values = null) {
    return logActivity($action, 'article', $article_id, $article_title, $description, $old_values, $new_values);
}
?>
