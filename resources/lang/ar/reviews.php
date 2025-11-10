<?php

return [
    // Review Management
    'title' => 'إدارة التقييمات',
    'singular' => 'تقييم',
    'plural' => 'تقييمات',

    // Review Fields
    'fields' => [
        'reviewer' => 'المقيم',
        'booking' => 'الحجز',
        'vehicle' => 'المركبة',
        'rating' => 'التقييم',
        'review_text' => 'نص التقييم',
        'title' => 'عنوان التقييم',
        'recommendation' => 'يوصي بالمركبة',
        'vehicle_condition_rating' => 'تقييم حالة المركبة',
        'cleanliness_rating' => 'تقييم النظافة',
        'service_rating' => 'تقييم خدمة العملاء',
        'status' => 'حالة التقييم',
        'visibility' => 'مستوى الرؤية',
        'reviewed_at' => 'تاريخ التقييم',
        'admin_notes' => 'ملاحظات الإدارة',
        'helpful_votes' => 'الأصوات المفيدة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],

    // Review Sections
    'sections' => [
        'review_overview' => 'نظرة عامة على التقييم',
        'customer_booking_details' => 'تفاصيل العميل والحجز',
        'review_content' => 'محتوى التقييم',
        'detailed_ratings' => 'التقييمات التفصيلية',
        'review_management' => 'إدارة التقييم',
        'system_information' => 'معلومات النظام',
        'review_statistics' => 'إحصائيات التقييم',
        'review_categories' => 'فئات التقييم',
        'review_status_moderation' => 'حالة التقييم والاعتدال',
    ],

    // Rating Options
    'rating_options' => [
        '1' => '1 نجمة (سيء جداً)',
        '2' => '2 نجمة (سيء)',
        '3' => '3 نجوم (متوسط)',
        '4' => '4 نجوم (جيد)',
        '5' => '5 نجوم (ممتاز)',
    ],

    // Recommendation Options
    'recommendation_options' => [
        'yes' => 'نعم، أوصي بها',
        'no' => 'لا، لا أوصي بها',
        'maybe' => 'ربما، بشروط',
    ],

    // Status Options
    'status_options' => [
        'pending' => 'في انتظار المراجعة',
        'approved' => 'موافق عليه ومنشور',
        'rejected' => 'مرفوض',
        'flagged' => 'مبلغ عنه للمراجعة',
    ],

    // Visibility Options
    'visibility_options' => [
        'public' => 'عام (مرئي للجميع)',
        'private' => 'خاص (للإدارة فقط)',
        'hidden' => 'مخفي',
    ],

    // Messages
    'messages' => [
        'review_created' => 'تم إنشاء التقييم بنجاح',
        'review_updated' => 'تم تحديث التقييم بنجاح',
        'review_deleted' => 'تم حذف التقييم بنجاح',
        'review_approved' => 'تم الموافقة على التقييم',
        'review_rejected' => 'تم رفض التقييم',
        'review_flagged' => 'تم الإبلاغ عن التقييم',
    ],

    // Placeholders
    'placeholders' => [
        'title' => 'ملخص موجز للتقييم',
        'review_text' => 'ما رأيك في تجربة تأجير المركبة؟',
        'admin_notes' => 'ملاحظات داخلية حول هذا التقييم...',
    ],

    // Helper Text
    'helper_text' => [
        'rating' => 'تقييم من 1 (سيء) إلى 5 (ممتاز) نجوم',
        'review_text' => 'الحد الأقصى 1000 حرف',
        'title' => 'عنوان اختياري للتقييم',
        'admin_notes' => 'ملاحظات خاصة مرئية للإداريين فقط',
    ],
];
