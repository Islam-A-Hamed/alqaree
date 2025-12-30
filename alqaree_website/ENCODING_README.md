# حل مشكلة الترميز العربي في الموقع

## المشكلة
إذا كنت ترى رموزاً غريبة مثل `Ø§Ù„ØµÙØ­Ø©` بدلاً من النصوص العربية، فهذا يعني مشكلة في ترميز المحتوى.

## الحل

### 1. إعادة تشغيل XAMPP
1. افتح XAMPP Control Panel
2. اضغط **Stop** لخدمتي Apache و MySQL
3. انتظر 5 ثواني
4. اضغط **Start** لخدمتي Apache و MySQL

### 2. مسح ذاكرة المتصفح
**الطريقة السريعة:** اضغط `Ctrl+F5` في المتصفح

**أو اتبع هذه الخطوات:**
- **Chrome:** `Ctrl+Shift+Delete` → اختر "Cached images and files" → Clear data
- **Firefox:** `Ctrl+Shift+Delete` → اختر "Cache" → Clear Now
- **Edge:** `Ctrl+Shift+Delete` → اختر "Cached data and files" → Clear

### 3. فتح الموقع
اذهب إلى: `http://localhost/alqaree_website/`

### 4. اختبار الترميز
افتح أيضاً: `http://localhost/alqaree_website/test_encoding_simple.html`

## الملفات التي تم إصلاحها
- `index.php` - الصفحة الرئيسية
- `js/ayah-manager.js` - إدارة الآيات
- `js/articles-manager.js` - إدارة المقالات
- `js/tilawat-manager.js` - إدارة التلاوات
- `js/hekum-manager.js` - إدارة المواعظ
- `css/index-page.css` - تنسيقات الصفحة الرئيسية
- `.htaccess` - إعدادات الخادم

## الترميز المستخدم
- **UTF-8 مع BOM** لجميع الملفات
- **Content-Type: text/html; charset=UTF-8** في كل صفحة
- **AddDefaultCharset UTF-8** في إعدادات Apache

## في حالة استمرار المشكلة
1. تأكد من أن XAMPP يعمل بشكل صحيح
2. جرب متصفح آخر
3. تأكد من أن الملفات محفوظة بترميز UTF-8
4. تحقق من إعدادات المتصفح (يجب أن يكون الترميز تلقائياً)

## ملاحظات مهمة
- تم تعطيل Cache المؤقت للتطوير في ملف `.htaccess`
- في بيئة الإنتاج، يجب إعادة تفعيل Cache
- جميع الملفات تستخدم UTF-8 مع BOM لضمان التوافق
