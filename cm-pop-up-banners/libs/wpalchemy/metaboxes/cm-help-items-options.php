<?php
use com\cminds\popupfly\CMPopUpBanners;
use com\cminds\popupfly\CMPopUpBannersShared;
use com\cminds\popupfly\CMPOPFLY_Settings;
?>
<div class="my_meta_control cm-help-items-options">
    <?php
    wp_print_styles('editor-buttons');
    ob_start();
    wp_editor('', 'content', array(
        'dfw'           => true,
        'editor_height' => 1,
        'tinymce'       => array(
            'resize'             => true,
            'add_unload_trigger' => false,
            'relative_urls'      => false,
            'remove_script_host' => false,
            'convert_urls'       => false
        ),
    ));
    $content = ob_get_contents();
    ob_end_clean();

    $args = array(
        'post_type'         => 'page',
        'show_option_none'  => CMPopUpBanners::__('None'),
        'option_none_value' => '',
    );

    global $wp_version;
    if (version_compare($wp_version, '4.3', '<')) {
        add_filter('the_editor_content', 'wp_richedit_pre');
    } else {
        add_filter('the_editor_content', 'format_for_editor');
    }
    $switch_class = 'tmce-active';

    $defaultWidgetType = CMPOPFLY_Settings::get('cmpopfly_default_widget_type');
    $widgetType = CMPOPFLY_Settings::getConfig('cmpopfly_default_widget_type');
    $displayMethod = CMPOPFLY_Settings::getConfig('cmpopfly_default_display_method');
    $widgetDisplayMethod = CMPOPFLY_Settings::getConfig('cmpopfly_default_display_method');
    $widgetShape = CMPOPFLY_Settings::getConfig('cm-campaign-widget-shape');
    $widgetShowEffect = CMPOPFLY_Settings::getConfig('cm-campaign-widget-show-effect');
    $widgetInterval = CMPOPFLY_Settings::getConfig('cm-campaign-widget-interval');
    $underlayType = CMPOPFLY_Settings::getConfig('cm-campaign-widget-underlay-type');
    $clicksCountMethod = CMPOPFLY_Settings::getConfig('cm-campaign-widget-clicks-count-method');
    $soundEffectMethod = CMPOPFLY_Settings::getConfig('cm-campaign-sound-effect-type');
    $fireMethod = CMPOPFLY_Settings::getConfig('cm-campaign-widget-fire-method');
    $usersType = CMPOPFLY_Settings::getConfig('cm-campaign-widget-user-type');

    if (isset($_GET['post'])) {
        $activityDates = get_post_meta($_GET['post'], CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY);
    }
    if (!empty($activityDates)) {
        $activityDates = maybe_unserialize($activityDates[0]);
    } else {
        $activityDates = false;
    }
    if (isset($_GET['post'])) {
        $activityDays = get_post_meta($_GET['post'], CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DAYS_META_KEY);
    }
    if (!empty($activityDays)) {
        $activityDays = maybe_unserialize($activityDays[0]);
    } else {
        $activityDays = false;
    }
    ?>

    <div id="cmpopfly-options-group-accortion">

        <div id="cmpopfly-options-group-tabs">
            <ul>
                <li><a href="#cmpopfly-tab-basic-visual">Basic Visual</a></li>
                <li><a href="#cmpopfly-tab-advanced-visual">Advanced Visual</a></li>
                <li><a href="#cmpopfly-tab-sound">Sound</a></li>
                <li><a href="#cmpopfly-tab-activity">Activity</a></li>
            </ul>

            <div id="cmpopfly-tab-basic-visual" class="options-tab hidden">
                <label>Type</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-type'); ?>
                    <select name="<?php $mb->the_name(); ?>" id="cm-campaign-widget-type">
                        <?php
                        $fieldValue = $mb->get_the_value();
//            echo '<option value="0" ' . selected('0', $fieldValue, false) . '>' . CMPopUpBanners::__('Default') . ' (' . $widgetType['options'][$defaultWidgetType] . ') </option>';
                        foreach ($widgetType['options'] as $key => $value) {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select><br />
                    <span class='field-info'>You can choose the different type for the current campaign.</span>
                </p>

                <label>Disable campaign
                    <p>
                        <?php
                        $mb->the_field('cm-campaign-widget-disable');
                        $value = $mb->get_the_value();
                        $checked = is_string($value) ? $value : '0';
                        ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $checked); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign will not be displayed</span>
                    </p>
                </label>

                <label>Display method</label>
                <p class="onlyinpro">
                    <span class="floatLeft">
                        <?php
                        $mb->the_field('cm-campaign-display-method');
                        $fieldValue = $mb->get_the_value();
                        if (empty($fieldValue)) {
                            $fieldValue = $widgetDisplayMethod['value'];
                        }
                        foreach ($widgetDisplayMethod['options'] as $key => $value) {
                            echo '<input disabled="disabled" name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . ' class="campaign-display-method">' . $value . "<br />";
                        }
                        ?>
                    </span>
                <div class="clear"></div>
                <br />
                <span class='field-info'>(Only in Pro) You can choose the different display method for the current campaign.</span>
                </p>
                <label class="onlyinpro">Close time</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-close-time'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>(Only in Pro) Close pop-up after the specified seconds</span>
                </p>
                <label>Close on clicking anywhere
                    <p>
                        <?php
                        $mb->the_field('cm-campaign-widget-close-on-underlay-click');
                        ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign's banner will it will close on clicking anywhere in the underlay area.
                            Otherwise it will close only when clicked on the button in it's corner.</span>
                    </p>
                </label>
                <label>Width</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-width'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign width. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
                <label>Height</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-height'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign height. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
                <label>Mobile Width</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-mobile-width'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign width on mobiles. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
                <label>Mobile Height</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-mobile-height'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign height on mobiles. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
                <label class="onlyinpro">Padding</label>
                <p>
                    <?php $mb->the_field('cm-campaign-padding'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="10px" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>(Only in Pro) Campaign padding. If blank defaults to 10px. Please input value in pixels.</span>
                </p>
                <label class="onlyinpro">Z-index</label>
                <p>
                    <?php $mb->the_field('cm-campaign-zindex'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="100" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>(Only in Pro) The 'z-index' of the banner. If you find that the banner is under other elements, increase this value. If blank defaults to 100. Please input an integer value (0-10000000000).</span>
                </p>
                <label>Background color</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-background-color'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="#ffffff" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>Campaign background color. Please enter it in hexadecimal color format (eg. #abc123) or "transparent". If blank defaults to #f0f1f2.</span>
                </p>
                <label class="onlyinpro">Background image</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-background-image'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="http://example.com/image.png" value="<?php echo $metabox->get_the_value(); ?>" class="long-text" /><br />
                    <span class='field-info'>(Only in Pro) Campaign background image. Please the url of the image you'd like to use for all of the banners in the campaign.</span>
                </p>
                <label class="onlyinpro">Background link</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-background-url'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="http://example.com" value="<?php echo $metabox->get_the_value(); ?>" class="long-text" /><br />
                    <span class='field-info'>(Only in Pro) Campaign background url. Please the url all of the backgrounds in the campaign will be linked to.</span>
                </p>
                <label>Shape</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-shape'); ?>
                    <select name="<?php $mb->the_name(); ?>">
                        <?php
                        $fieldValue = $mb->get_the_value();
                        if (empty($fieldValue)) {
                            $fieldValue = isset($widgetShape['default'])?$widgetShape['default']:'';
                        }
                        foreach ($widgetShape['options'] as $key => $value) {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <br />
                    <span class='field-info'>You can choose the different shape for the current campaign.</span>
                </p>
                <label>Center content vertically
                    <p>
                        <?php
                        $mb->the_field('cm-campaign-widget-center-vertically');
                        ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign's banner content will be centered verically</span>
                    </p>
                </label>
                <label>Center content horizontally
                    <p>
                        <?php
                        $mb->the_field('cm-campaign-widget-center-horizontally');
                        ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign's banner content will be centered horizontally</span>
                    </p>
                </label>
            </div>

            <div id="cmpopfly-tab-advanced-visual" class="options-tab hidden">

                <label class="onlyinpro">Show effect</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-show-effect'); ?>
                    <select disabled="disabled" name="<?php $mb->the_name(); ?>">
                        <?php
                        $fieldValue = $mb->get_the_value();
                        if (empty($fieldValue)) {
                            $fieldValue = isset($widgetShowEffect['default'])?$widgetShowEffect['default']:'';
                        }
                        foreach ($widgetShowEffect['options'] as $key => $value) {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <br />
                    <span class='field-info'>(Only in Pro) You can choose the different show effect for the current campaign.</span>
                </p>

                <label class="onlyinpro">Delay to show</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-delay-to-show'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>(Only in Pro) Campaign time between page loads and appearing. If blank defaults to 0s. Please input value in seconds.</span>
                </p>

                <label class="onlyinpro">Show interval</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-interval'); ?>
                    <select disabled="disabled" name="<?php $mb->the_name(); ?>" id="user_show_method-flying-bottom">
                        <?php
                        $fieldValue = $mb->get_the_value();
                        if (empty($fieldValue)) {
                            $fieldValue = isset($widgetInterval['default'])?$widgetInterval['default']:'';
                        }
                        foreach ($widgetInterval['options'] as $key => $value) {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <br />
                    <span class='field-info'>(Only in Pro) You can choose the different show interval for the current campaign.</span>
                </p>
                <span id="resetFloatingBottomBannerHowManyTimes" style="display: none;">
                    <label class="onlyinpro">Fixed number of times</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-widget-interval_fixed_number_show_times'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $metabox->get_the_value(); ?>"/>
                        <span class='field-info'>(Only in Pro) How many times campaign should be shown. Resets after "Interval reset time" number of days.</span>
                    </p>
                </span>
                <span id="resetFloatingBottomBannerCookieContainer" style="display: none;">
                    <label class="onlyinpro">Interval reset time</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-widget-interval_reset_time'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $metabox->get_the_value(); ?>"/>
                        <span class='field-info'>(Only in Pro) After how many days after first impression campaign should appear again. If blank defaults to 7 days.</span>
                    </p>
                </span>
                <span id="underlayTypeContainer" style="display: none;">
                    <label>Underlay type</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-widget-underlay-type'); ?>
                        <select name="<?php $mb->the_name(); ?>">
                            <?php
                            $fieldValue = $mb->get_the_value();
                            if (empty($fieldValue)) {
                                $fieldValue = isset($underlayType['default'])?$underlayType['default']:'';
                            }
                            foreach ($underlayType['options'] as $key => $value) {
                                echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                            }
                            ?>
                        </select>
                        <br />
                        <span class='field-info'>You can choose the different underlay type for current campaign.</span>
                    </p>
                </span>
                <label class="onlyinpro">Statistics clicks counting method (Only in Pro)</label>
                <p>
                    <?php
                    $mb->the_field('cm-campaign-clicks-counting-method');
                    $fieldValue = $mb->get_the_value();
                    if (empty($fieldValue)) {
                        $fieldValue = $clicksCountMethod['value'];
                    }
                    foreach ($clicksCountMethod['options'] as $key => $value) {
                        echo '<input disabled="disabled" name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . '>' . $value . "<br />";
                    }
                    ?>
                </p>
            </div>

            <div id="cmpopfly-tab-sound" class="options-tab hidden">
                <label class="onlyinpro">Sound effect when popup shows (Only in Pro)</label>
                <p>
                    <?php
                    $mb->the_field('cm-campaign-sound-effect-type');
                    $fieldValue = $mb->get_the_value();
                    if (empty($fieldValue)) {
                        $fieldValue = $soundEffectMethod['value'];
                    }
                    foreach ($soundEffectMethod['options'] as $key => $value) {
                        echo '<input disabled="disabled" name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . ' class="cm-campaign-sound-effect-type">' . $value . "<br />";
                    }
                    ?>
                </p>
            </div>

            <div id="cmpopfly-tab-activity" class="options-tab hidden">
                <div class="cmpopfly-options-group">
                    <label class="onlyinpro">Users type for pop-up</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-user-type'); ?>
                        <select disabled="disabled" name="<?php $mb->the_name(); ?>">
                            <?php
                            $fieldValue = $mb->get_the_value();
                            foreach ($usersType['options'] as $key => $value) {
                                echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                            }
                            ?>
                        </select><br />
                        <span class='field-info'>
                            (Only in Pro) For which groups of users the popup will be displayed.
                        </span>
                    </p>
                    <label class="onlyinpro">Show pop-up after users registration </label>
                    <p>
                        <?php
                        $mb->the_field('cm-campaign-thank');
                        $value = $mb->get_the_value();
                        $checked = is_string($value) ? $value : '0';
                        ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input disabled="disabled" type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $checked); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>(Only in Pro) Use with plugin <strong>CM Registration</strong></span>
                    </p>
                    <label>Show on every page</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-show-allpages'); ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $metabox->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign will be displayed on each post and page of your website</span>
                    </p>

                    <label>Show on homepage</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-show-homepage'); ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $metabox->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign will be displayed on homepage (main url of the page).</span>
                    </p>

                    <label class="onlyinpro">When fire the popup?</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-fire-method'); ?>
                        <select disabled="disabled" name="<?php $mb->the_name(); ?>" id="cm-campaign-widget-when-fire-the-popup">
                            <?php
                            $fieldValue = $mb->get_the_value();
                            foreach ($fireMethod['options'] as $key => $value) {
                                echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                            }
                            ?>
                        </select><br />
                        <span class='field-info'>
                            (Only in Pro) To fire the popup on hover or click action for certain element, please add the <strong>'cm-pop-up-banners-trigger'</strong> class attribute to it. eg.
                            <pre>&lt;div class="cm-pop-up-banners-trigger"&gt;&lt;/div&gt;</pre>
                        </span>
                    </p>

                    <label class="onlyinpro">Minimum device width</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-min-device-width'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" value="<?php echo $mb->get_the_value(); ?>" placeholder="enter value eg. 700"  class="<?php $mb->the_name(); ?>" /><br />
                        <span class='field-info'>(Only in Pro) Select the minimum width of the device (in pixels) where the banner should be displayed. "700" will hide it on most smartphones, but not tablets. Shows on all devices if blank.</span>
                    </p>

                    <label class="onlyinpro">Maximum device width</label>
                    <p>
                        <?php $mb->the_field('cm-campaign-max-device-width'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" value="<?php echo $mb->get_the_value(); ?>" placeholder="enter value eg. 320"  class="<?php $mb->the_name(); ?>" /><br />
                        <span class='field-info'>(Only in Pro) Select the maximum width of the device (in pixels) where the banner should be displayed. "320" will show it on iPhone 5, but not on iPhone 6. Shows on all devices if blank.</span>
                    </p>

                    <label class="onlyinpro">Show on URLs matching pattern</label>
                    <p>
                        <?php $mb->the_field('cm-help-item-show-wildcard'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="/help/" value="<?php echo $metabox->get_the_value(); ?>"/><br />
                        <span class='field-info'>(Only in Pro) If this field is filled campaign will be displayed on pages with matching url. Permalinks must be enabled for this function to work.</span>
                    </p>

                    <label>Show on selected posts/pages</label>
                    <?php while ($mb->have_fields_and_multi('cm-help-item-options')): ?>

                        <?php $mb->the_group_open(); ?>

                        <div class="group-wrap <?php echo $mb->get_the_value('toggle_state') ? ' closed' : ''; ?>" >

                            <?php $mb->the_field('toggle_state'); ?>
                            <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="toggle_state hidden" />

                            <div class="group-control dodelete" title="<?php _e('Click to remove "Page"', ''); ?>"></div>
                            <div class="group-control toggle" title="<?php _e('Click to toggle', ''); ?>"></div>

                            <?php $mb->the_field('title'); ?>
                            <?php // need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities()    ?>
                            <h3 class="handle">Page/Post</h3>

                            <div class="group-inside">
                                <?php
                                try {
                                    $mb->the_field('cm-help-item-url');

                                    $args['name'] = $mb->get_the_name();
                                    $args['selected'] = $metabox->get_the_value();
                                    $args['custom_post_types'] = 1;
                                    cmpopfly_cminds_dropdown($args);
                                } catch (\Throwable $e) {
                                    error_log($e);
                                }
                                ?>

                            </div><!-- .group-inside -->

                        </div><!-- .group-wrap -->

                        <?php $mb->the_group_close(); ?>
                    <?php endwhile; ?>

                    <span class='field-info'>Choose the pages on which current campaign should be displayed</span>
                    <p><a href="#" class="docopy-cm-help-item-options button"><span class="icon add"></span>Add Page</a></p>

                    <label class="onlyinpro">Activity dates</label>
                    <p>
                    <span class='field-info'>(Only in Pro) You can choose the activity dates for current campaign.</span>
                    </p>
                    <label class="onlyinpro">Activity days</label>
                    <p>
                    <span class='field-info'>(Only in Pro) You can choose the activity dates for current campaign.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <p class="meta-save">
        <strong>To save the settings use the "Publish/Update" button in the right column.</strong>
    </p>

</div>