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
            'rows'  => ['description', 'email', 'name', 'slug'],
            'class' => 'fa fa-sort-alpha',
        ],
        'amount'  => [
            'rows'  => ['amount', 'price'],
            'class' => 'fa fa-sort-amount',
        ],
        'numeric' => [
            'rows'  => ['id', 'level',  'phone_number', 'created_at', 'updated_at'],
            'class' => 'fa fa-sort-numeric',
        ],
    ],

    /*
    | Whether icons should be enabled
    */
    'enable_icons' => true,

    /*
    | Defines an icon set to use when sorted data is the none above (alpha nor amount nor numeric)
    */
    'default_icon_set' => 'fa fa-sort',

    /*
    | Defines an icon type to use another class icon based-on-type (alpha or amount or numeric)
    */
    'default_icon_type' => 'default',

    /*
    | Icon that shows when generating a sortable link while column is not sorted
    */
    'sortable_icon' => 'fa fa-sort',

    /*
    | Generated icon is clickable non-clickable (default)
    */
    'clickable_icon' => false,

    /*
    | Icon and text separator (any string)
    | in case of 'clickable_icon' => true; separator creates possibility to style icon and anchor-text properly
    */
    'icon_text_separator' => '',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_default_suffix' => '-asc',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_default_suffix' => '-desc',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_alpha_suffix' => '-asc',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_alpha_suffix' => '-desc',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_amount_suffix' => '-asc',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_amount_suffix' => '-desc',

    /*
    | Suffix class that is appended when ascending order is applied
    */
    'asc_numeric_suffix' => '-asc',

    /*
    | Suffix class that is appended when descending order is applied
    */
    'desc_numeric_suffix' => '-desc',

    /*
    | Default anchor class, if value is null, none is added
    */
    'anchor_class' => null,

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
    'default_order' => 'asc',

    /*
    | Default order for non-sorted columns
    */
    'default_order_unsorted' => 'asc',

    /*
    | Use the first defined sortable column (ModelName::$sortable) as default
    */
    'default_first_column' => 'id',
];

