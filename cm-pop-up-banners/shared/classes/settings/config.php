<?php

$config = [
    'abbrev'   => 'cmpopfly',
    'tabs'     => [
        '0'  => 'Installation Instructions',
        '1'  => 'Pro Features',
        '99' => 'Upgrade',
    ],
    'default'  => [
    ],
    'settings' => [
        'cmpopfly_statistics'               => array(
            'onlyin' => 'Pro',
            'type'        => 'bool',
            'value'       => true,
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Collect Statistics',
            'description' => 'Turn the Statistics (views/clicks) ON/OFF',
        ),
        /*
		'cmpopfly_geolocation_api_key'      => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '',
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Geolocation API Key',
            'description' => 'Geolocation API Key. To receive API register at http://ipinfodb.com/register.php',
        ),
		*/
        'cmpopfly_custom_post_type_support' => array(
            'onlyin' => 'Pro',
            'type'        => 'multicheckbox',
            'value'       => '',
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Custom Post Types',
            'description' => 'Select Custom Post Types on which you want to display Campaigns.',
            'options'     => (function () {
                $arrayCPT = array();
                $args = array(
                    'public' => true,
                        // '_builtin' => false
                );
                $post_types = get_post_types($args, 'objects', 'and');
                foreach ($post_types as $post_type) {
                    $arrayCPT[$post_type->name] = $post_type->labels->singular_name . ' (' . $post_type->name . ')';
                }

                return $arrayCPT;
            }),
        ),
        'cmpopfly_mobile_max_width'              => array(
            'onlyin' => 'Pro',
            'type'        => 'bool',
            'value'       => false,
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Allow scripts in editor',
            'description' => 'If enabled, the invalid HTML tags and tag attributes will not be stripped.',
        ),
        // General - Widget
        'cmpopfly_allow_scripts_in_editor'       => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '400px',
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Select max width for mobile devices',
            'description' => 'Allows to set the max width for mobile devices. Campaign banners can have different dimentions on devices smaller than this value.',
        ),
        // General - Widget
        'cmpopfly_default_widget_side'           => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'right',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the side the widget appears on',
            'description' => 'Allows to select the side of the screen the widget should appear on',
            'options'     => array('right' => 'Right', 'left' => 'Left'),
        ),
        // General - Widget
        'cmpopfly_default_widget_type'           => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'popup',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget type',
            'description' => 'Allows to select the type of the widget',
            'options'     => array('popup' => 'Pop-Up', 'flyin' => 'Fly-In Bottom (Only in Pro)', 'full' => 'Full Screen (Only in Pro)'),
        ),
        // General - Widget
        'cmpopfly_default_widget_theme'          => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'black',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget theme',
            'description' => 'Allows to select the theme of the widget',
            'options'     => array('black' => 'Black', 'white' => 'White'),
        ),
        // General - Widget
        'cmpopfly_default_widget_width'          => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '250px',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the width of the widget',
            'description' => 'Allows to select the width of the widgets container',
        ),
        // General - Widget
        'cmpopfly_default_widget_height'         => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '305px',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the height of the widget',
            'description' => 'Allows to select the height of the widgets container',
        ),
        // General - Widget
        'cmpopfly_widget_showsearch'             => array(
            'onlyin' => 'Pro',
            'type'        => 'bool',
            'value'       => TRUE,
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Show "Search" in the widget',
            'description' => 'Allows to decide if the Search input should appear within the widget',
        ),
        // General - Widget
        'cmpopfly_widget_showtitle'              => array(
            'onlyin' => 'Pro',
            'type'        => 'bool',
            'value'       => FALSE,
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Show Help Item\'s title in the widget',
            'description' => 'Allows to decide if the Help Item title should appear on the top of the widget',
        ),
        // General - Widget
        'cmpopfly_default_display_method'        => array(
            'onlyin' => 'Pro',
            'type'        => 'radio',
            'value'       => 'random',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget display method',
            'description' => 'Allows to select the display method of the widget',
            'options'     => array('selected' => 'Selected', 'random' => 'Random'),
        ),
        // General - Widget
        'cmpopfly_custom_widget_width'           => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '250px',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the width of the widget',
            'description' => 'Allows to select the width of the widget',
        ),
        // General - Widget
        'cmpopfly_custom_widget_height'          => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '300px',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the height of the widget',
            'description' => 'Allows to select the height of the widget',
        ),
        // General - Widget
        'cmpopfly_custom_background_color'       => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '#ffffff',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select background color of the widghe',
            'description' => 'Allows to select the background color of the widget',
        ),
        // General - Widget
        'cmpopfly_custom_delay_to_show'          => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '0',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Set the time between page loads and appearing of the widget',
            'description' => 'Allows to set the time between page loads and appearing of the widget',
        ),
        // General - Widget
        'cm-campaign-widget-shape'               => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'rounded',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget shape',
            'description' => 'Allows to select the shape of the widget',
            'options'     => array('rounded' => 'Rounded Edges', 'sharp' => 'Sharp Edges'),
        ),
        // General - Widget
        'cm-campaign-widget-show-effect'         => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'popin',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget show effect',
            'description' => 'Allows to select widget show effect',
            'options'     => array('popin'    => 'Pop-In',
                'bounce'   => 'Bounce',
                'shake'    => 'Shake',
                'flash'    => 'Flash',
                'tada'     => 'Tada',
                'swing'    => 'Swing',
                'rotateIn' => 'Rotate In'
            ),
        ),
        // General - Widget
        'cm-campaign-widget-interval'            => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'always',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget showing interval',
            'description' => 'Allows to select the showing interval of the widget',
            'options'     => array('always' => 'Every Time Page Loads', 'once' => 'Only First Time Page Loads', 'only_once' => 'Only Once On Any Page', 'fixed_times' => 'Fixed number of times till reset'),
        ),
        // General - Widget
        'cm-campaign-widget-interval-reset-time' => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '7',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Enter first time page loads interval option reset period',
            'description' => 'Allows to set first time page loads interval option reset period',
        ),
        // General - Widget
        'cm-campaign-widget-underlay-type'       => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => 'dark',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget underlay type',
            'description' => 'Allows to select widget underlay type',
            'options'     => array('dark'  => 'Dark Underlay',
                'light' => 'Light Underlay',
                'no'    => 'No Underlay'
            ),
        ),
        // General - Widget
        'cm-campaign-widget-selected-banner'     => array(
            'onlyin' => 'Pro',
            'type'        => 'select',
            'value'       => '',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget banner',
            'description' => 'Allows to select widget baner',
            'options'     => array(),
        ),
        // General - Widget
        'cm-campaign-widget-clicks-count-method' => array(
            'onlyin' => 'Pro',
            'type'        => 'radio',
            'value'       => 'all',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select the widget clicks count method',
            'description' => 'Allows to select the clicks count method of the widget',
            'options'     => array('one' => 'Only one click per banner show', 'all' => 'All clicks until close button click'),
        ),
        // General - Widget
        'cm-campaign-widget-fire-method'         => array(
            'onlyin' => 'Pro',
            'type'        => 'radio',
            'value'       => 'all',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'When fire the popup?',
            'description' => 'When fire the popup?',
            'options'     => array('pageload'   => 'On Pageload',
                'click'      => 'Element Click',
                'hover'      => 'Element Hover',
                'inactive'   => 'After inactive for X seconds',
                'leave'      => 'On page leave intent',
                'pageBottom' => 'Reaching the bottom of page')
        ),
        'cm-campaign-widget-user-type'           => array(
            'onlyin' => 'Pro',
            'type'        => 'radio',
            'value'       => 'all',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Who must see popup?',
            'description' => 'For which groups of users the popup will be displayed.',
            'options'     => array(
                'all'    => 'For all users',
                'login'  => 'Logged in users',
                'logout' => 'Non Logged in users'
            )
        ),
        'cm-campaign-widget-thank'               => array(
            'onlyin' => 'Pro',
            'type'        => 'bool',
            'value'       => 'all',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Show thank pop-up after registration',
            'description' => 'Use with plugin CM Registration',
        ),
        'cm-campaign-sound-effect-type'          => array(
            'onlyin' => 'Pro',
            'type'        => 'radio',
            'value'       => 'none',
            'category'    => 'custom',
            'subcategory' => 'widget',
            'label'       => 'Select sound effect when popup shows',
            'description' => 'Allows to select widget sound effect when popup shows',
            'options'     => array(
                'none'   => 'None',
                'value'  => 'Default',
                'custom' => 'Custom from media library'
            ),
        )
    ],
    'presets' => [
        'default' => [
            '0'  => [
                'labels' => [
                    'label'    => '',
                    'before'   => '[cminds_free_guide id="cmpopfly"]',
                    'settings' => []
                ],
            ],
            '1'  => [
                'generic' => [
                    'label'    => '',
                    'before'   => '',
                    'settings' => [
                        'cmpopfly_statistics',
                        //'cmpopfly_geolocation_api_key',
                        'cmpopfly_custom_post_type_support',
                        'cmpopfly_mobile_max_width',
                        'cmpopfly_allow_scripts_in_editor',
                        'cmpopfly_default_widget_side',
                        'cmpopfly_default_widget_type',
                        'cmpopfly_default_widget_theme',
                        'cmpopfly_default_widget_width',
                        'cmpopfly_default_widget_height',
                        'cmpopfly_widget_showsearch',
                        'cmpopfly_widget_showtitle',
                        'cmpopfly_default_display_method',
                        'cmpopfly_custom_widget_width',
                        'cmpopfly_custom_widget_height',
                        'cmpopfly_custom_background_color',
                        'cmpopfly_custom_delay_to_show',
                        'cm-campaign-widget-shape',
                        'cm-campaign-widget-show-effect',
                        'cm-campaign-widget-interval',
                        'cm-campaign-widget-interval-reset-time',
                        'cm-campaign-widget-underlay-type',
                        'cm-campaign-widget-selected-banner',
                        'cm-campaign-widget-clicks-count-method',
                        'cm-campaign-widget-fire-method',
                        'cm-campaign-widget-user-type',
                        'cm-campaign-widget-thank',
                        'cm-campaign-sound-effect-type'
                    ]
                ],
            ],
            '99' => [
                'labels' => [
                    'label'  => '',
                    'before' => '[cminds_upgrade_box id="cmpopfly"]'
                ]
            ]
        ]
    ]
];
