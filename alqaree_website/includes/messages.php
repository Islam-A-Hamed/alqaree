<?php
/**
 * نظام الرسائل المحسنة للوحة الإدارة
 * يوفر رسائل موحدة ومحترفة لجميع العمليات
 */

class AdminMessages {
    private static $messages = [
        // رسائل النجاح
        'success' => [
            'add_recitation' => [
                'title' => 'تم إضافة التلاوة بنجاح',
                'message' => 'تم حفظ التلاوة في قاعدة البيانات. يمكنك الآن عرضها أو تعديلها.'
            ],
            'update_recitation' => [
                'title' => 'تم تحديث التلاوة بنجاح',
                'message' => 'تم حفظ جميع التغييرات على التلاوة المحددة.'
            ],
            'delete_recitation' => [
                'title' => 'تم حذف التلاوة بنجاح',
                'message' => 'تم حذف التلاوة من قاعدة البيانات نهائياً.'
            ],
            'add_sermon' => [
                'title' => 'تم إضافة الموعظة بنجاح',
                'message' => 'تم حفظ الموعظة في قاعدة البيانات. يمكنك الآن عرضها أو تعديلها.'
            ],
            'update_sermon' => [
                'title' => 'تم تحديث الموعظة بنجاح',
                'message' => 'تم حفظ جميع التغييرات على الموعظة المحددة.'
            ],
            'delete_sermon' => [
                'title' => 'تم حذف الموعظة بنجاح',
                'message' => 'تم حذف الموعظة من قاعدة البيانات نهائياً.'
            ],
        ],

        // رسائل الخطأ
        'error' => [
            'validation_required' => [
                'title' => 'الحقول المطلوبة غير مكتملة',
                'message' => 'يرجى ملء الحقول التالية قبل الحفظ: {fields}'
            ],
            'database_save' => [
                'title' => 'فشل في حفظ البيانات',
                'message' => 'حدث خطأ في قاعدة البيانات أثناء حفظ البيانات. يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني.'
            ],
            'database_update' => [
                'title' => 'فشل في تحديث البيانات',
                'message' => 'حدث خطأ في قاعدة البيانات أثناء التحديث. يرجى التحقق من صحة البيانات والمحاولة مرة أخرى.'
            ],
            'database_delete' => [
                'title' => 'فشل في حذف البيانات',
                'message' => 'حدث خطأ في قاعدة البيانات أثناء الحذف. يرجى المحاولة مرة أخرى.'
            ],
            'database_load' => [
                'title' => 'مشكلة في تحميل البيانات',
                'message' => 'حدث خطأ في الاتصال بقاعدة البيانات. يرجى إعادة تحميل الصفحة أو الاتصال بالدعم الفني.'
            ],
            'invalid_url' => [
                'title' => 'رابط غير صحيح',
                'message' => 'الرابط المدخل غير صحيح. يرجى التأكد من صحة الرابط.'
            ]
        ],

        // رسائل التحذير
        'warning' => [
            'unsaved_changes' => [
                'title' => 'تغييرات غير محفوظة',
                'message' => 'لديك تغييرات غير محفوظة. هل أنت متأكد من مغادرة الصفحة؟'
            ],
            'delete_confirm' => [
                'title' => 'تأكيد الحذف',
                'message' => 'هل أنت متأكد من حذف "{item}"؟ هذا الإجراء لا يمكن التراجع عنه.'
            ]
        ],

        // رسائل المعلومات
        'info' => [
            'system_offline' => [
                'title' => 'النظام غير متصل',
                'message' => 'بعض المميزات غير متوفرة حالياً. يرجى المحاولة لاحقاً.'
            ],
            'maintenance' => [
                'title' => 'صيانة النظام',
                'message' => 'النظام قيد الصيانة. قد تكون بعض المميزات غير متوفرة.'
            ]
        ]
    ];

    /**
     * الحصول على رسالة محددة
     */
    public static function get($type, $key, $params = []) {
        if (!isset(self::$messages[$type][$key])) {
            return [
                'title' => 'رسالة غير معروفة',
                'message' => 'حدث خطأ في النظام.'
            ];
        }

        $message = self::$messages[$type][$key];

        // استبدال المعلمات في الرسالة
        foreach ($params as $param => $value) {
            $message['message'] = str_replace('{' . $param . '}', $value, $message['message']);
        }

        return $message;
    }

    /**
     * عرض رسالة في الصفحة
     */
    public static function display($type, $key, $params = []) {
        $message = self::get($type, $key, $params);

        $icon = '';
        switch($type) {
            case 'success': $icon = '✅'; break;
            case 'error': $icon = '❌'; break;
            case 'warning': $icon = '⚠️'; break;
            case 'info': $icon = 'ℹ️'; break;
            default: $icon = '📢';
        }

        echo '<div class="alert alert-' . $type . '" id="messageAlert">';
        echo '<span class="alert-icon">' . $icon . '</span>';
        echo '<div class="alert-content">';
        echo '<span class="alert-title">' . htmlspecialchars($message['title']) . '</span>';
        echo '<p class="alert-message">' . htmlspecialchars($message['message']) . '</p>';
        echo '<div class="alert-actions">';
        echo '<button class="alert-dismiss" onclick="dismissAlert()" title="إغلاق الرسالة">✕</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * إنشاء رسالة خطأ للتحقق من النماذج
     */
    public static function validationError($fields) {
        $fieldNames = [
            'title' => 'عنوان التلاوة',
            'surah_name' => 'اسم السورة',
            'reciter_name' => 'اسم القارئ',
            'speaker_name' => 'اسم المتحدث',
            'video_duration' => 'مدة الفيديو',
            'youtube_embed_code' => 'رابط الفيديو'
        ];

        $fieldList = [];
        foreach ($fields as $field) {
            $fieldList[] = $fieldNames[$field] ?? $field;
        }

        return self::get('error', 'validation_required', ['fields' => implode(', ', $fieldList)]);
    }
}

/**
 * وظائف مساعدة للرسائل
 */
function showSuccessMessage($key, $params = []) {
    AdminMessages::display('success', $key, $params);
}

function showErrorMessage($key, $params = []) {
    AdminMessages::display('error', $key, $params);
}

function showWarningMessage($key, $params = []) {
    AdminMessages::display('warning', $key, $params);
}

function showInfoMessage($key, $params = []) {
    AdminMessages::display('info', $key, $params);
}

function showValidationError($fields) {
    $message = AdminMessages::validationError($fields);

    $icon = '❌';

    echo '<div class="alert alert-error" id="messageAlert">';
    echo '<span class="alert-icon">' . $icon . '</span>';
    echo '<div class="alert-content">';
    echo '<span class="alert-title">' . htmlspecialchars($message['title']) . '</span>';
    echo '<p class="alert-message">' . htmlspecialchars($message['message']) . '</p>';
    echo '<div class="alert-actions">';
    echo '<button class="alert-dismiss" onclick="dismissAlert()" title="إغلاق الرسالة">✕</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
