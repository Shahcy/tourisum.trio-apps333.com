<?php

return [

    'label' => 'تنقل الصفحات',

    'overview' => '{1} عرض نتيجة واحدة|[2,*] عرض :first إلى :last من أصل :total نتيجة',

    'fields' => [

        'records_per_page' => [

            'label' => 'عدد النتائج في كل صفحة',

            'options' => [
                'all' => 'الكل',
            ],

        ],

    ],

    'actions' => [

        'first' => [
            'label' => 'الأول',
        ],

        'go_to_page' => [
            'label' => 'الذهاب إلى الصفحة :page',
        ],

        'last' => [
            'label' => 'الأخير',
        ],

        'next' => [
            'label' => 'التالي',
        ],

        'previous' => [
            'label' => 'السابق',
        ],

    ],

];
