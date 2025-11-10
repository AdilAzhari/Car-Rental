<?php

return [
    // Activity Log Management
    'title' => 'إدارة سجل الأنشطة',
    'singular' => 'نشاط',
    'plural' => 'سجل الأنشطة',

    // Activity Log Fields
    'fields' => [
        'id' => 'معرف السجل',
        'log_name' => 'اسم السجل',
        'description' => 'الوصف',
        'subject_type' => 'نوع الموضوع',
        'subject_id' => 'معرف الموضوع',
        'causer_id' => 'معرف المستخدم',
        'causer_type' => 'نوع المستخدم',
        'event' => 'نوع الحدث',
        'properties' => 'الخصائص',
        'created_at' => 'الطابع الزمني',
        'user' => 'المستخدم',
        'role' => 'الدور',
    ],

    // Activity Sections
    'sections' => [
        'activity_overview' => 'نظرة عامة على النشاط',
        'subject_information' => 'معلومات الموضوع',
        'actor_information' => 'معلومات الفاعل',
        'activity_properties' => 'خصائص النشاط',
        'activity_information' => 'معلومات النشاط',
    ],

    // Event Types
    'events' => [
        'created' => 'إنشاء',
        'updated' => 'تحديث',
        'deleted' => 'حذف',
        'viewed' => 'عرض',
        'logged_in' => 'تسجيل دخول',
        'logged_out' => 'تسجيل خروج',
    ],

    // Subject Types
    'subject_types' => [
        'User' => 'مستخدم',
        'Vehicle' => 'مركبة',
        'Booking' => 'حجز',
        'Review' => 'تقييم',
        'Payment' => 'دفعة',
        'System' => 'النظام',
    ],

    // Filters
    'filters' => [
        'log_type' => 'نوع السجل',
        'event_type' => 'نوع الحدث',
        'subject_type' => 'نوع الموضوع',
        'user' => 'المستخدم',
        'today_activities' => 'أنشطة اليوم',
        'this_week' => 'هذا الأسبوع',
        'created_from' => 'من',
        'created_until' => 'إلى',
    ],

    // Messages
    'messages' => [
        'activity_deleted' => 'تم حذف النشاط بنجاح',
        'activities_archived' => 'تم أرشفة الأنشطة المحددة',
        'no_properties' => 'لا توجد خصائص',
        'items_count' => ':count عنصر',
    ],

    // Activity Descriptions
    'descriptions' => [
        'vehicle_added' => 'تمت إضافة المركبة إلى النظام',
        'vehicle_updated' => 'تم تحديث تفاصيل المركبة',
        'vehicle_deleted' => 'تمت إزالة المركبة من النظام',
        'user_registered' => 'تم تسجيل مستخدم جديد',
        'user_verified' => 'تم التحقق من المستخدم',
        'booking_created' => 'تم إنشاء حجز جديد',
        'booking_confirmed' => 'تم تأكيد الحجز',
        'payment_processed' => 'تمت معالجة الدفعة',
        'review_submitted' => 'تم تقديم تقييم جديد',
    ],
];
