<?php

return [
    // Vehicle Management
    'title' => 'إدارة المركبات',
    'singular' => 'مركبة',
    'plural' => 'مركبات',
    'none' => 'لا يوجد مخالفات حاليه',
    // Vehicle Fields
    'fields' => [
        'make' => 'الشركة المصنعة',
        'model' => 'الموديل',
        'year' => 'سنة الصنع',
        'plate_number' => 'رقم اللوحة',
        'vin' => 'رقم الهيكل (VIN)',
        'fuel_type' => 'نوع الوقود',
        'transmission' => 'ناقل الحركة',
        'daily_rate' => 'السعر اليومي',
        'oil_type' => 'نوع الزيت',
        'last_oil_change' => 'آخر تغيير زيت',
        'policy' => 'سياسة التأجير',
        'status' => 'حالة المركبة',
        'owner' => 'المالك',
        'created_at' => 'تاريخ الإضافة',
        'updated_at' => 'تاريخ التحديث',
        'featured_image' => 'الصورة الرئيسية',
        'gallery_images' => 'معرض الصور',
        'category' => 'الفئة',
        'seating_capacity' => 'عدد المقاعد',
        'doors' => 'عدد الأبواب',
        'engine_size' => 'حجم المحرك (لتر)',
        'mileage' => 'المسافة المقطوعة (كم)',
        'location' => 'الموقع الحالي',
        'pickup_location' => 'موقع الاستلام',
        'is_available' => 'متاح للتأجير',
        'insurance_included' => 'التأمين مشمول',
        'features' => 'مواصفات المركبة',
        'description' => 'الوصف',
    ],

    // Vehicle Sections
    'sections' => [
        'vehicle_gallery' => 'معرض صور المركبة',
        'basic_information' => 'المعلومات الأساسية',
        'categories_specifications' => 'الفئات والمواصفات',
        'ownership_status' => 'الملكية والحالة',
        'location_information' => 'معلومات الموقع',
        'features_specifications' => 'المواصفات والميزات',
    ],

    // Fuel Types
    'fuel_types' => [
        'petrol' => 'بنزين',
        'diesel' => 'ديزل',
        'electric' => 'كهربائي',
        'hybrid' => 'هجين',
        'cng' => 'غاز طبيعي مضغوط',
    ],

    // Transmission Types
    'transmission_types' => [
        'manual' => 'يدوي',
        'automatic' => 'أوتوماتيكي',
        'cvt' => 'CVT',
        'semi_automatic' => 'شبه أوتوماتيكي',
    ],

    // Vehicle Status
    'status_options' => [
        'pending' => 'في الانتظار',
        'approved' => 'موافق عليه',
        'published' => 'منشور',
        'suspended' => 'معلق',
        'maintenance' => 'في الصيانة',
    ],

    // Categories
    'categories' => [
        'economy' => 'اقتصادية',
        'compact' => 'صغيرة',
        'midsize' => 'متوسطة',
        'fullsize' => 'كبيرة',
        'luxury' => 'فاخرة',
        'suv' => 'دفع رباعي',
        'minivan' => 'حافلة صغيرة',
        'pickup' => 'بيك أب',
        'sports' => 'رياضية',
        'convertible' => 'قابلة للتحويل',
    ],

    // Messages
    'messages' => [
        'vehicle_created' => 'تم إضافة المركبة بنجاح',
        'vehicle_updated' => 'تم تحديث المركبة بنجاح',
        'vehicle_deleted' => 'تم حذف المركبة بنجاح',
        'vehicle_published' => 'تم نشر المركبة بنجاح',
        'vehicle_suspended' => 'تم تعليق المركبة بنجاح',
        'images_uploaded' => 'تم رفع الصور بنجاح',
    ],
];
