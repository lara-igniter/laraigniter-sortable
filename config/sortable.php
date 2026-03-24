<?php

/*
|--------------------------------------------------------------------------
| Laraigniter Sortable – default configuration
|--------------------------------------------------------------------------
|
| These are the package defaults. Publish this file to config/sortable.php
| (via "php artisan vendor:publish --tag=sortable") to override any value
| at the application level. Published values always take precedence over
| the defaults declared here.
|
*/

$config = [
    'columns' => [
        'alpha'   => [
            'rows'  => [
                'name',
                'description',
                'etiology',
                'note',
                'last_name',
                'father_name',
                'mother_name',
                'member_name',
                'supplier_name',
                'speciality_name',
                'payment_method',
            ],
            'class' => 'fal fa-sort-alpha',
        ],
        'amount'  => [
            'rows'  => [
                'amount',
                'debt_amount',
                'paid_amount',
                'unpaid_amount',
                'price',
            ],
            'class' => 'fal fa-sort-amount',
        ],
        'numeric' => [
            'rows'  => [
                'id',
                'receipt_number',
                'payment_number',
                'registration_number',
                'license_number',
                'expiration_license_date',
                'expense_date',
                'receipt_date',
                'payment_date',
                'created_at',
                'updated_at',
            ],
            'class' => 'fal fa-sort-numeric',
        ],
    ],

    /*
    | Whether icons should be enabled
    */
    'enable_icons' => true,

    /*
    | Defines an icon set to use when sorted data is the none above (alpha nor amount nor numeric)
    */
    'default_icon_set' => 'fal fa-sort',

    /*
    | Defines an icon type to use another class icon based-on-type (alpha or amount or numeric)
    */
    'default_icon_type' => 'default',

    /*
    | Icon that shows when generating a sortable link while column is not sorted
    */
    'sortable_icon' => 'fal fa-sort fa-lg',

    /*
    | Generated icon is clickable non-clickable (default)
    */
    'clickable_icon' => false,

    /*
    | Icon and text separator (any string)
    | in case of 'clickable_icon' => true; separator creates possibility to style icon and anchor-text properly
    */
    'icon_text_separator' => '<span class="ml-2"></span>',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_default_suffix' => '-down fa-lg',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_default_suffix' => '-up fa-lg',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_alpha_suffix' => '-down fa-lg',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_alpha_suffix' => '-up-alt fa-lg',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_amount_suffix' => '-down-alt fa-lg',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_amount_suffix' => '-up fa-lg',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_numeric_suffix' => '-down fa-lg',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_numeric_suffix' => '-up-alt fa-lg',

    /*
    | Default anchor class, if value is null, none is added
    */
    'anchor_class' => 'd-flex text-white',

    /*
    | Default active anchor class, if value is null none is added
    */
    'active_anchor_class' => null,

    /*
    | Default sort order anchor class, if value is null none is added
    */
    'order_anchor_class_prefix' => null,

    /*
    | Formatting function applied to name of column, use null to turn formatting off
    */
    'formatting_function' => null,

    /*
    | Apply title inside anchor
    */
    'title_inside_anchor' => false,

    /*
    | Apply formatting function to custom titles as well as column names
    */
    'format_custom_titles' => true,

    /*
    | Inject title parameter in query strings, use null to turn injection off
    | example: 'inject_title' => 't' will result in. Admin/users/?t="formatted title of sorted column"
    */
    'inject_title_as' => null,

    /*
    | default order for: $this->user->sortable('id')->paginate(10) usage
    */
    'default_order' => 'desc',

    /*
    | Default order for non-sorted columns
    */
    'default_order_unsorted' => 'asc',

    /*
    | Use the first defined sortable column (ModelName::$sortable) as default
    */
    'default_first_column' => 'id',
];

