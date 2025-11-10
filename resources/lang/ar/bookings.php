<?php

return [
    // Booking Management
    'title' => 'إدارة الحجوزات',
    'singular' => 'حجز',
    'plural' => 'حجوزات',

    // Booking Fields
    'fields' => [
        'booking_number' => 'رقم الحجز',
        'renter' => 'المستأجر',
        'vehicle' => 'المركبة',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'pickup_location' => 'موقع الاستلام',
        'return_location' => 'موقع الإرجاع',
        'total_amount' => 'المبلغ الإجمالي',
        'deposit_amount' => 'مبلغ العربون',
        'status' => 'حالة الحجز',
        'payment_status' => 'حالة الدفع',
        'special_requests' => 'طلبات خاصة',
        'terms_accepted' => 'موافقة على الشروط',
        'created_at' => 'تاريخ الحجز',
        'updated_at' => 'تاريخ التحديث',
        'duration_days' => 'مدة الحجز (أيام)',
        'daily_rate' => 'السعر اليومي',
        'additional_fees' => 'رسوم إضافية',
        'discount_amount' => 'مبلغ الخصم',
        'insurance_fee' => 'رسوم التأمين',
        'pickup_time' => 'وقت الاستلام',
        'return_time' => 'وقت الإرجاع',
        'driver_age' => 'عمر السائق',
        'additional_drivers' => 'سائقون إضافيون',
    ],

    // Booking Sections
    'sections' => [
        'booking_overview' => 'نظرة عامة على الحجز',
        'rental_details' => 'تفاصيل التأجير',
        'customer_information' => 'معلومات العميل',
        'vehicle_information' => 'معلومات المركبة',
        'payment_details' => 'تفاصيل الدفع',
        'additional_information' => 'معلومات إضافية',
    ],

    // Booking Status
    'status_options' => [
        'pending' => 'في الانتظار',
        'confirmed' => 'مؤكد',
        'ongoing' => 'جاري',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
        'expired' => 'منتهي الصلاحية',
    ],

    // Payment Status
    'payment_status_options' => [
        'pending' => 'في الانتظار',
        'partially_paid' => 'مدفوع جزئياً',
        'paid' => 'مدفوع',
        'refunded' => 'مسترد',
        'failed' => 'فشل',
    ],

    // Messages
    'messages' => [
        'booking_created' => 'تم إنشاء الحجز بنجاح',
        'booking_updated' => 'تم تحديث الحجز بنجاح',
        'booking_cancelled' => 'تم إلغاء الحجز بنجاح',
        'booking_confirmed' => 'تم تأكيد الحجز بنجاح',
        'payment_processed' => 'تم معالجة الدفع بنجاح',
        'refund_processed' => 'تم معالجة الاسترداد بنجاح',
        'vehicle_not_available' => 'المركبة غير متاحة في هذه الفترة',
    ],
];
