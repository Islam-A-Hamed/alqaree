# تشخيص مشاكل تحميل المحتوى

## المشكلة
الأقسام في الصفحة الرئيسية لا تعرض المحتوى وتعمل ريلود فقط.

## خطوات التشخيص

### 1. اختبار قاعدة البيانات
```
http://localhost/alqaree_website/test_db.php
```
- يتحقق من وجود البيانات في الجداول
- يعرض عدد السجلات في كل جدول

### 2. اختبار APIs
```
http://localhost/alqaree_website/test_api.php
```
- يختبر جميع endpoints (آيات، مقالات، تلاوات، مواعظ)
- يعرض حالة كل API والبيانات المرجعة

### 3. اختبار JavaScript
```
http://localhost/alqaree_website/debug.html
```
- يختبر جميع وظائف JavaScript
- يعرض console logs لتتبع الأخطاء

### 4. فحص Console المتصفح
1. افتح الموقع: `http://localhost/alqaree_website/`
2. اضغط F12 لفتح Developer Tools
3. اذهب إلى تبويب Console
4. ابحث عن رسائل مثل:
   - `Ayah manager loaded`
   - `Articles manager loaded`
   - إلخ

## الأسباب المحتملة والحلول

### 1. عدم وجود بيانات في قاعدة البيانات
**التحقق:** `test_db.php`
**الحل:** استيراد البيانات أو إضافة بيانات تجريبية

### 2. APIs لا تعمل
**التحقق:** `test_api.php`
**الحل:** إصلاح ملفات PHP API

### 3. JavaScript لا يُحمَّل
**التحقق:** Console في المتصفح
**الحل:** التأكد من أن ملفات JS موجودة وصحيحة

### 4. مشاكل في DOM
**التحقق:** Console في المتصفح
**الحل:** التأكد من وجود العناصر HTML المطلوبة

### 5. مشاكل CORS
**التحقق:** Console في المتصفح (Network tab)
**الحل:** إعداد headers CORS في ملفات PHP

## ملفات التشخيص المتاحة

- `test_db.php` - اختبار قاعدة البيانات
- `test_api.php` - اختبار APIs
- `debug.html` - اختبار JavaScript
- `test_encoding_simple.html` - اختبار الترميز

## Console Logs المُتوقعة

إذا كان JavaScript يعمل بشكل صحيح، ستظهر هذه الرسائل:
```
Ayah manager loaded
Ayah manager DOMContentLoaded
Articles manager loaded
Articles manager DOMContentLoaded
Tilawat manager loaded
Tilawat manager DOMContentLoaded
Hekum manager loaded
Hekum manager DOMContentLoaded
```

## الخطوات التالية

1. شغل جميع ملفات الاختبار أعلاه
2. انسخ أي أخطاء تظهر
3. أخبرني بالنتائج لأساعدك في الحل
