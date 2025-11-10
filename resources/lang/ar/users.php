<?php

return [
    // User Management
    'title' => 'إدارة المستخدمين',
    'singular' => 'مستخدم',
    'plural' => 'مستخدمون',

    // User Fields
    'fields' => [
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'role' => 'الدور',
        'status' => 'الحالة',
        'date_of_birth' => 'تاريخ الميلاد',
        'address' => 'العنوان',
        'city' => 'المدينة',
        'state' => 'المحافظة',
        'postal_code' => 'الرمز البريدي',
        'country' => 'الدولة',
        'license_number' => 'رقم الرخصة',
        'license_expiry_date' => 'تاريخ انتهاء الرخصة',
        'is_verified' => 'محقق',
        'is_active' => 'نشط',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'preferred_language' => 'اللغة المفضلة',
        'notification_preferences' => 'تفضيلات الإشعارات',
    ],

    // User Sections
    'sections' => [
        'personal_information' => 'المعلومات الشخصية',
        'account_details' => 'تفاصيل الحساب',
        'location_information' => 'معلومات الموقع',
        'driver_information' => 'معلومات السائق',
    ],

    // User Status
    'status_options' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'suspended' => 'معلق',
        'pending' => 'في الانتظار',
    ],

    // User Roles
    'role_options' => [
        'admin' => 'مدير النظام',
        'owner' => 'مالك مركبة',
        'renter' => 'مستأجر',
    ],

    // Messages
    'messages' => [
        'user_created' => 'تم إنشاء المستخدم بنجاح',
        'user_updated' => 'تم تحديث المستخدم بنجاح',
        'user_deleted' => 'تم حذف المستخدم بنجاح',
        'verification_sent' => 'تم إرسال رابط التحقق',
        'account_verified' => 'تم تحقق الحساب بنجاح',
    ],
];
