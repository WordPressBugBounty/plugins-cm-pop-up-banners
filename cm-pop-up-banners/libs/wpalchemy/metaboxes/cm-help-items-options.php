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
    
	//$widgetType = CMPOPFLY_Settings::getConfig('cmpopfly_default_widget_type');
    $widgetType = array('options' => array( 'popup' => 'Pop-Up' ));
    
	$displayMethod = CMPOPFLY_Settings::getConfig('cmpopfly_default_display_method');
    
	//$widgetDisplayMethod = CMPOPFLY_Settings::getConfig('cmpopfly_default_display_method');
    $widgetDisplayMethod = array('options' => array( 'selected' => 'Selected', 'random' => 'Random' ));
    
	//$widgetShape = CMPOPFLY_Settings::getConfig('cm-campaign-widget-shape');
    $widgetShape = array('options' => array( 'rounded' => 'Rounded Edges' ));
    
	//$widgetShowEffect = CMPOPFLY_Settings::getConfig('cm-campaign-widget-show-effect');
    $widgetShowEffect = array('options' => array( 'popin' => 'Pop-In' ));
    
	//$widgetInterval = CMPOPFLY_Settings::getConfig('cm-campaign-widget-interval');
    $widgetInterval = array('options' => array( 'always' => 'Every Time Page Loads' ));
    
	//$underlayType = CMPOPFLY_Settings::getConfig('cm-campaign-widget-underlay-type');
    $underlayType = array('options' => array( 'dark' => 'Dark Underlay' ));
    
	//$clicksCountMethod = CMPOPFLY_Settings::getConfig('cm-campaign-widget-clicks-count-method');
    $clicksCountMethod = array('options' => array( 'one' => 'Only one click per banner show', 'all' => 'All clicks until close button click' ));
    
	//$soundEffectMethod = CMPOPFLY_Settings::getConfig('cm-campaign-sound-effect-type');
    $soundEffectMethod = array('options' => array( 'none' => 'None', 'default' => 'Default', 'custom' => 'Custom from media library' ));
    
	//$fireMethod = CMPOPFLY_Settings::getConfig('cm-campaign-widget-fire-method');
    $fireMethod = array('options' => array( 'all' => 'For all users' ));
    
	//$usersType = CMPOPFLY_Settings::getConfig('cm-campaign-widget-user-type');
    $usersType = array('options' => array( 'pageload' => 'On Pageload' ));

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
                <li><a href="#cmpopfly-tab-sticky-button">Sticky Button</a></li>
                <li><a href="#cmpopfly-tab-sound">Sound</a></li>
                <li><a href="#cmpopfly-tab-activity">Activity</a></li>
                <a href="javascript:void(0);" class="button cmpop_post_show_hide_pro_options">Hide Pro options</a>
            </ul>

            <div id="cmpopfly-tab-basic-visual" class="options-tab hidden">
                
				<p>
					<label>Disable campaign</label>
					<?php
					$mb->the_field('cm-campaign-widget-disable');
					$value = $mb->get_the_value();
					$checked = is_string($value) ? $value : '0';
					?>
					<input type="hidden" name="<?php $mb->the_name(); ?>" value="0" />
					<input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $checked); ?> class="<?php $mb->the_name(); ?>" />
					<span class='field-info'>If this checkbox is selected then this campaign will not be displayed</span>
				</p>
                
                <p class="onlyinpro">
					<label>Type</label>
                    <?php $mb->the_field('cm-campaign-widget-type'); ?>
                    <select name="<?php $mb->the_name(); ?>" id="cm-campaign-widget-type" disabled="disabled">
                        <?php
                        $fieldValue = $mb->get_the_value();
						//echo '<option value="0" ' . selected('0', $fieldValue, false) . '>' . CMPopUpBanners::__('Default') . ' (' . $widgetType['options'][$defaultWidgetType] . ') </option>';
                        foreach ($widgetType['options'] as $key => $value) {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select><br />
                    <span class='field-info'><span>(Only in Pro)</span> You can choose the different type for the current campaign.</span>
                </p>
				
                <p class="onlyinpro">
					<label>Fly-In Bottom (Position)</label>
					<select name="<?php $mb->the_name(); ?>" id="cm-campaign-widget-position" disabled="disabled">
                        <option value="left">Left</option>
						<option value="center">Center</option>
						<option value="right">Right</option>
					</select><br />
					<span class='field-info'><span>(Only in Pro)</span> You can choose the Fly-In Bottom position.</span>
				</p>
				
                <p class="onlyinpro">
					<label>Advertisement items rotating time in Fly-In Bottom</label>
					<input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="" class="small-text" disabled="disabled"><br />
					<span class='field-info'><span>(Only in Pro)</span> Advertisement items random rotate after the specified seconds, If blank default to <strong>auto</strong> means no rotate.</span>
				</p>
				
                <p class="onlyinpro">
					<label>Display method</label>
                    <span class="floatLeft">
                        <?php
                        $mb->the_field('cm-campaign-display-method');
                        //$fieldValue = $mb->get_the_value();
                        $fieldValue = 'selected';
                        if (empty($fieldValue)) {
							if(!empty($widgetDisplayMethod['value'])) {
								$fieldValue = $widgetDisplayMethod['value'];
							}
                        }
						if(!empty($widgetDisplayMethod['options'])) {
							foreach ($widgetDisplayMethod['options'] as $key => $value) {
								echo '<input disabled="disabled" name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . ' class="campaign-display-method">' . $value . "<br />";
							}
						}
                        ?>
                    </span><br />
					<span class='field-info'><span>(Only in Pro)</span> You can choose the different display method for the current campaign.</span>
                </p>
				
                <p class="onlyinpro">
					<label>Close time</label>
                    <?php $mb->the_field('cm-campaign-widget-close-time'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'><span>(Only in Pro)</span> Close pop-up after the specified seconds</span>
                </p>
				
				<p>
					<label>Close on clicking anywhere</label>
					<?php
					$mb->the_field('cm-campaign-widget-close-on-underlay-click');
					?>
					<input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
					<input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
					<span class='field-info'>If this checkbox is selected then this campaign's banner will it will close on clicking anywhere in the underlay area. Otherwise it will close only when clicked on the button in it's corner.</span>
				</p>
				
                <p>
					<label>Width</label>
                    <?php $mb->the_field('cm-campaign-widget-width'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign width. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
				
                <p>
					<label>Height</label>
                    <?php $mb->the_field('cm-campaign-widget-height'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign height. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
				
                <p>
					<label>Mobile Width</label>
                    <?php $mb->the_field('cm-campaign-widget-mobile-width'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign width on mobiles. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
				
                <p>
					<label>Mobile Height</label>
                    <?php $mb->the_field('cm-campaign-widget-mobile-height'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="auto" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>campaign height on mobiles. If blank defaults to <strong>auto</strong>. Numeric values will be treated as pixels eg. 500 = 500px. It also accepts percentage values (eg 85%).</span>
                </p>
				
                <p class="onlyinpro">
					<label>Padding</label>
                    <?php $mb->the_field('cm-campaign-padding'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="10px" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'><span>(Only in Pro)</span> Campaign padding. If blank defaults to 10px. Please input value in pixels.</span>
                </p>
				
                <p class="onlyinpro">
					<label>Z-index</label>
                    <?php $mb->the_field('cm-campaign-zindex'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="100001" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'><span>(Only in Pro)</span> The 'z-index' of the banner. If you find that the banner is under other elements, increase this value.<br>If blank defaults to 100001. Please input an integer value e.g. 100001, 100002, 100003 etc.</span>
                </p>
				
                <label>Background color</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-background-color'); ?>
                    <input type="text" name="<?php $mb->the_name(); ?>" placeholder="#ffffff" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
                    <span class='field-info'>Campaign background color. Please enter it in hexadecimal color format (eg. #abc123) or "transparent". If blank defaults to #f0f1f2.</span>
                </p>
				
                <p class="onlyinpro">
					<label>Background image</label>
                    <?php $mb->the_field('cm-campaign-widget-background-image'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="http://example.com/image.png" value="<?php echo $metabox->get_the_value(); ?>" class="long-text" /><br />
                    <span class='field-info'><span>(Only in Pro)</span> Campaign background image. Please the url of the image you'd like to use for all of the banners in the campaign.</span>
                </p>
				
                <p class="onlyinpro">
					<label>Background link</label>
                    <?php $mb->the_field('cm-campaign-widget-background-url'); ?>
                    <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="http://example.com" value="<?php echo $metabox->get_the_value(); ?>" class="long-text" /><br />
                    <span class='field-info'><span>(Only in Pro)</span> Campaign background url. Please the url all of the backgrounds in the campaign will be linked to.</span>
                </p>
				
                <p class="onlyinpro">
					<label>Shape</label>
                    <?php $mb->the_field('cm-campaign-widget-shape'); ?>
                    <select name="<?php $mb->the_name(); ?>" disabled="disabled">
                        <?php
                        $fieldValue = $mb->get_the_value();
                        if (empty($fieldValue)) {
                            $fieldValue = isset($widgetShape['default'])?$widgetShape['default']:'';
                        }
                        foreach ($widgetShape['options'] as $key => $value) {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select><br />
                    <span class='field-info'><span>(Only in Pro)</span> You can choose the different shape for the current campaign.</span>
                </p>
				
				<p>
					<label>Center content vertically</label>
					<?php
					$mb->the_field('cm-campaign-widget-center-vertically');
					?>
					<input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
					<input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
					<span class='field-info'>If this checkbox is selected then this campaign's banner content will be centered verically</span>
				</p>
				
				<p>
					<label>Center content horizontally</label>
					<?php
					$mb->the_field('cm-campaign-widget-center-horizontally');
					?>
					<input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
					<input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
					<span class='field-info'>If this checkbox is selected then this campaign's banner content will be centered horizontally</span>
				</p>
				
				<div class="onlyinpro_empty show" style="display:none;"><p style="margin-top:20px;">Pro options are hidden. Click the button <span style="font-weight:bold;color:#00cd00;">"Show Pro options"</span> to see them.</p></div>
                
            </div>

            <div id="cmpopfly-tab-advanced-visual" class="options-tab hidden">
				
				<?php
				$cmpop_show_overlay = 'yes-color';
				$cmpop_overlay_blur = '2';
				$cmpop_overlay_color = '#000000';
				$cmpop_overlay_color_tra = '0.5';
				$cmpop_border_type = 'none';
				$cmpop_border_color = '#000000';
				$cmpop_border_width = '3';
				$cmpop_border_radius = '0';
				$cmpop_border_margin = '0';
				$cmpop_shadow_color = '#cccccc';
				$cmpop_shadow_type = 'none';
				$cmpop_shadow_x_offset = '0';
				$cmpop_shadow_y_offset = '0';
				$cmpop_shadow_blur = '0';
				$cmpop_shadow_spread = '0';
				$cmpop_close_color = '#666666';
				$cmpop_close_hcolor = '#000000';
				$cmpop_close_shadow = 'on';
				$cmpop_close_scolor = '#000000';
				$cmpop_close_size = '30';
				$cmpop_close_position = 'top_right';
				$cmpop_close_x_offset = '10';
				$cmpop_close_y_offset = '10';
				$cmpop_custom_css = '';
				?>
				
				<div id="appearance_accordion" class="onlyinpro">
					<h3>Behavior</h3>
					<div>
						<div class="appearance_accordion_row">
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
								</select><br />
								<span class='field-info'><span>(Only in Pro)</span> You can choose the different show effect for the current campaign.</span>
							</p>

							<label class="onlyinpro">Delay to show</label>
							<p>
								<?php $mb->the_field('cm-campaign-widget-delay-to-show'); ?>
								<input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $metabox->get_the_value(); ?>" class="small-text" /><br />
								<span class='field-info'><span>(Only in Pro)</span> Campaign time between page loads and appearing. If blank defaults to 0s. Please input value in seconds.</span>
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
								</select><br />
								<span class='field-info'><span>(Only in Pro)</span> You can choose the different show interval for the current campaign.</span>
							</p>
							
							<span id="resetFloatingBottomBannerHowManyTimes" style="display: none;">
								<label class="onlyinpro">Fixed number of times</label>
								<p>
									<?php $mb->the_field('cm-campaign-widget-interval_fixed_number_show_times'); ?>
									<input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $metabox->get_the_value(); ?>"/>
									<span class='field-info'><span>(Only in Pro)</span> How many times campaign should be shown. Resets after "Interval reset time" number of days.</span>
								</p>
							</span>
							
							<span id="resetFloatingBottomBannerCookieContainer" style="display: none;">
								<label class="onlyinpro">Interval reset time</label>
								<p>
									<?php $mb->the_field('cm-campaign-widget-interval_reset_time'); ?>
									<input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $metabox->get_the_value(); ?>"/>
									<span class='field-info'><span>(Only in Pro)</span> After how many days after first impression campaign should appear again. If blank defaults to 7 days.</span>
								</p>
							</span>
							
						</div>
					</div>
					
					<h3>Popup Overlay</h3>
					<div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Show popup overlay?</label>
							<select disabled name="cmpop_show_overlay" class="cmpop_show_overlay">
								<option value="no" <?php if($cmpop_show_overlay == 'no') { echo 'selected'; } ?>>No</option>
								<option value="yes-blur" <?php if($cmpop_show_overlay == 'yes-blur') { echo 'selected'; } ?>>Yes (Blur overlay)</option>
								<option value="yes-color" <?php if($cmpop_show_overlay == 'yes-color') { echo 'selected'; } ?>>Yes (Color overlay)</option>
							</select>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row cmpop_overlay_blur_con" style="<?php if($cmpop_show_overlay == 'yes-blur') { echo 'display:block;'; } ?>">
							<label class="onlyinpro">Overlay Blur</label>
							<input disabled type="number" min="0" step="1" name="cmpop_overlay_blur" class="cmpop_overlay_blur" value="<?php echo $cmpop_overlay_blur; ?>" />px
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>	
						<div class="appearance_accordion_row cmpop_overlay_color_con" style="<?php if($cmpop_show_overlay == 'yes-color') { echo 'display:block;'; } ?>">
							<label class="onlyinpro">Overlay Color</label>
							<input disabled type="color" name="cmpop_overlay_color" class="cmpop_overlay_color" value="<?php echo $cmpop_overlay_color; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row cmpop_overlay_color_tra_con" style="<?php if($cmpop_show_overlay == 'yes-color') { echo 'display:block;'; } ?>">
							<label class="onlyinpro">Transparency</label>
							<input disabled type="number" min="0" step="0.1" max="1" name="cmpop_overlay_color_tra" class="cmpop_overlay_color_tra" value="<?php echo $cmpop_overlay_color_tra; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
					</div>
					<h3>Inner Border</h3>
					<div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Type</label>
							<select disabled name="cmpop_border_type" class="cmpop_border_type">
								<option value="none" <?php if($cmpop_border_type == 'none') { echo 'selected'; } ?>>None</option>
								<option value="dashed" <?php if($cmpop_border_type == 'dashed') { echo 'selected'; } ?>>Dashed</option>
								<option value="dotted" <?php if($cmpop_border_type == 'dotted') { echo 'selected'; } ?>>Dotted</option>
								<option value="double" <?php if($cmpop_border_type == 'double') { echo 'selected'; } ?>>Double</option>
								<option value="groove" <?php if($cmpop_border_type == 'groove') { echo 'selected'; } ?>>Groove</option>
								<option value="inset" <?php if($cmpop_border_type == 'inset') { echo 'selected'; } ?>>Inset</option>
								<option value="outset" <?php if($cmpop_border_type == 'outset') { echo 'selected'; } ?>>Outset</option>
								<option value="ridge" <?php if($cmpop_border_type == 'ridge') { echo 'selected'; } ?>>Ridge</option>
								<option value="solid" <?php if($cmpop_border_type == 'solid') { echo 'selected'; } ?>>Solid</option>
							</select>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Color</label>
							<input disabled type="color" name="cmpop_border_color" class="cmpop_border_color" value="<?php echo $cmpop_border_color; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Width</label>
							<input disabled type="number" name="cmpop_border_width" class="cmpop_border_width" value="<?php echo $cmpop_border_width; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Radius</label>
							<input disabled type="number" name="cmpop_border_radius" class="cmpop_border_radius" value="<?php echo $cmpop_border_radius; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Margin</label>
							<input disabled type="number" name="cmpop_border_margin" class="cmpop_border_margin" value="<?php echo $cmpop_border_margin; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
					</div>
					<h3>Popup Shadow</h3>
					<div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Color</label>
							<input disabled type="color" name="cmpop_shadow_color" class="cmpop_shadow_color" value="<?php echo $cmpop_shadow_color; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Type</label>
							<select disabled name="cmpop_shadow_type" class="cmpop_shadow_type">
								<option value="none" <?php if($cmpop_shadow_type == 'none') { echo 'selected'; } ?>>None</option>
								<option value="inset" <?php if($cmpop_shadow_type == 'inset') { echo 'selected'; } ?>>Inset</option>
								<option value="outset" <?php if($cmpop_shadow_type == 'outset') { echo 'selected'; } ?>>Outset</option>
							</select>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">X Offset</label>
							<input disabled type="number" name="cmpop_shadow_x_offset" class="cmpop_shadow_x_offset" value="<?php echo $cmpop_shadow_x_offset; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Y Offset</label>
							<input disabled type="number" name="cmpop_shadow_y_offset" class="cmpop_shadow_y_offset" value="<?php echo $cmpop_shadow_y_offset; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Blur</label>
							<input disabled type="number" name="cmpop_shadow_blur" class="cmpop_shadow_blur" value="<?php echo $cmpop_shadow_blur; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Spread</label>
							<input disabled type="number" name="cmpop_shadow_spread" class="cmpop_shadow_spread" value="<?php echo $cmpop_shadow_spread; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
					</div>
					<h3>Close Button</h3>
					<div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Color</label>
							<input disabled type="color" name="cmpop_close_color" class="cmpop_close_color" value="<?php echo $cmpop_close_color; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Hover Color</label>
							<input disabled type="color" name="cmpop_close_hcolor" class="cmpop_close_hcolor" value="<?php echo $cmpop_close_hcolor; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Shadow</label>
							<select disabled name="cmpop_close_shadow" class="cmpop_close_shadow">
								<option value="on" <?php if($cmpop_close_shadow == 'on') { echo 'selected'; } ?>>On</option>
								<option value="off" <?php if($cmpop_close_shadow == 'off') { echo 'selected'; } ?>>Off</option>
							</select>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Shadow Color</label>
							<input disabled type="color" name="cmpop_close_scolor" class="cmpop_close_scolor" value="<?php echo $cmpop_close_scolor; ?>" />
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Size</label>
							<input disabled type="number" min="0" step="1" name="cmpop_close_size" class="cmpop_close_size" value="<?php echo $cmpop_close_size; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Position</label>
							<select disabled name="cmpop_close_position" class="cmpop_close_position">
								<option value="bottom_left" <?php if($cmpop_close_position == 'bottom_left') { echo 'selected'; } ?>>Bottom Left</option>
								<option value="bottom_right" <?php if($cmpop_close_position == 'bottom_right') { echo 'selected'; } ?>>Bottom Right</option>
								<option value="top_left" <?php if($cmpop_close_position == 'top_left') { echo 'selected'; } ?>>Top Left</option>
								<option value="top_right" <?php if($cmpop_close_position == 'top_right') { echo 'selected'; } ?>>Top Right</option>
							</select>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">X Offset</label>
							<input type="number" disabled name="cmpop_close_x_offset" class="cmpop_close_x_offset" value="<?php echo $cmpop_close_x_offset; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">Y Offset</label>
							<input type="number" disabled name="cmpop_close_y_offset" class="cmpop_close_y_offset" value="<?php echo $cmpop_close_y_offset; ?>" /><span class="onlyinpro">px</span>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
					</div>
					<h3>Custom CSS</h3>
					<div>
						<div class="appearance_accordion_row">
							<label class="onlyinpro">CSS</label>
							<textarea name="cmpop_custom_css" class="cmpop_custom_css" disabled><?php echo $cmpop_custom_css; ?></textarea>
							<br>
							<p style="margin-top:0"><span class='field-info'><span>(Only in Pro)</span></span></p>
						</div>
					</div>
				</div>
					
				<h3 class="onlyinpro" style="padding:0;">Preview</h3>
				<div class="appearance_preview onlyinpro">
					<?php
					$appearance_preview_bg_style = '';
					if($cmpop_show_overlay == 'yes-color') {
						$appearance_preview_bg_style .= 'background-image:none; background-color:rgba(0, 0, 0, 0.5);';
					} else if($cmpop_show_overlay == 'yes-blur') {
						$appearance_preview_bg_style .= 'background-color:transparent; filter:blur('.$cmpop_overlay_blur.'px);';
					} else if($cmpop_show_overlay == 'none') {
						$appearance_preview_bg_style .= 'display:none;';
					}
					?>
					<div class="appearance_preview_bg" style="<?php echo $appearance_preview_bg_style; ?>"></div>
					<?php
					$appearance_preview_container_style = '';
					$appearance_preview_container_style .= 'padding-top:'.$cmpop_border_margin.'px;';
					$appearance_preview_container_style .= 'padding-bottom:'.$cmpop_border_margin.'px;';
					if($cmpop_shadow_type == 'none') {
						$appearance_preview_container_style .= 'box-shadow:none;';
					} else if($cmpop_shadow_type == 'inset') {
						$appearance_preview_container_style .= 'box-shadow:'.$cmpop_shadow_color.' '.$cmpop_shadow_x_offset.'px '.$cmpop_shadow_y_offset.'px '.$cmpop_shadow_blur.'px '.$cmpop_shadow_spread.'px inset;';
					} else if($cmpop_shadow_type == 'outset') {
						$appearance_preview_container_style .= 'box-shadow:'.$cmpop_shadow_color.' '.$cmpop_shadow_x_offset.'px '.$cmpop_shadow_y_offset.'px '.$cmpop_shadow_blur.'px '.$cmpop_shadow_spread.'px;';
					}
					?>
					<div class="appearance_preview_container" style="<?php echo $appearance_preview_container_style; ?>">
						<?php
						$appearance_preview_inner_style = '';
						$appearance_preview_inner_style .= 'border-style:'.$cmpop_border_type.';';
						$appearance_preview_inner_style .= 'border-color:'.$cmpop_border_color.';';
						$appearance_preview_inner_style .= 'border-width:'.$cmpop_border_width.'px;';
						$appearance_preview_inner_style .= 'border-radius:'.$cmpop_border_radius.'px;';
						$appearance_preview_inner_style .= 'margin-left:'.$cmpop_border_margin.'px;';
						$appearance_preview_inner_style .= 'margin-right:'.$cmpop_border_margin.'px;';
						?>
						<div class="appearance_preview_inner" style="<?php echo $appearance_preview_inner_style; ?>">
							<div class="appearance_preview_content">
								<p>Preview of "CM Pop-Up Banners Pro" plugin</p>
							</div>
							<?php
							$appearance_preview_close_style = '';
							$appearance_preview_close_style .= 'color:'.$cmpop_close_color.';';
							if($cmpop_close_shadow == 'on') {
								$appearance_preview_close_style .= 'text-shadow:0 1px 0 '.$cmpop_close_scolor.';';
							} else {
								$appearance_preview_close_style .= 'text-shadow:none;';
							}
							$appearance_preview_close_style .= 'font-size:'.$cmpop_close_size.'px;';
							if($cmpop_close_position == 'top_left') {
								$appearance_preview_close_style .= 'left:'.$cmpop_close_x_offset.'px;';
								$appearance_preview_close_style .= 'top:'.$cmpop_close_y_offset.'px;';
							} else if($cmpop_close_position == 'bottom_left') {
								$appearance_preview_close_style .= 'left:'.$cmpop_close_x_offset.'px;';
								$appearance_preview_close_style .= 'bottom:'.$cmpop_close_y_offset.'px;';
							} else if($cmpop_close_position == 'bottom_right') {
								$appearance_preview_close_style .= 'right:'.$cmpop_close_x_offset.'px;';
								$appearance_preview_close_style .= 'bottom:'.$cmpop_close_y_offset.'px;';
							} else {
								$appearance_preview_close_style .= 'right:'.$cmpop_close_x_offset.'px;';
								$appearance_preview_close_style .= 'top:'.$cmpop_close_y_offset.'px;';
							}
							?>
							<a href="javascript:void(0);" class="appearance_preview_close" style="<?php echo $appearance_preview_close_style; ?>">×</a>
						</div>
					</div>
				</div>
				<style class="appearance_preview_style">
				.appearance_preview_close:hover { color:<?php echo $cmpop_close_hcolor; ?> !important; }
				</style>
				
				<div class="onlyinpro_empty show" style="display:none;"><p style="margin-top:20px;">Pro options are hidden. Click the button <span style="font-weight:bold;color:#00cd00;">"Show Pro options"</span> to see them.</p></div>
				
			</div>
			
			<div id="cmpopfly-tab-sticky-button" class="options-tab hidden">
			
                <p class="onlyinpro">
					<label>Sticky button</label>
					<input name="cmpop_sticky_button_show" type="radio" value="enable" class="cmpop_sticky_button_show" disabled>Enable
					<br>
					<input name="cmpop_sticky_button_show" type="radio" value="disable" class="cmpop_sticky_button_show" checked="" disabled>Disable
					<br>
					<span class="field-info"><span>(Only in Pro)</span> Popup banner will be shown only when a user clicks on a sticky button.</span>
                </p>
				
				<p class="onlyinpro">
					<label>Type</label>
					<select name="cmpop_sticky_button_type" class="cmpop_sticky_button_type" disabled>
						<option value="basic" selected="">Basic</option>
						<option value="corner">Corner</option>
					</select>
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Placement</label>
					<select name="cmpop_sticky_button_placement" class="cmpop_sticky_button_placement" disabled>
						<option value="top_left">Top Left</option>
						<option value="top_center" class="basic" style="display: block;">Top Center</option>
						<option value="top_right" selected="">Top Right</option>
						<option value="left_center" class="basic" style="display: block;">Left Center</option>
						<option value="right_center" class="basic" style="display: block;">Right Center</option>
						<option value="bottom_left">Bottom Left</option>
						<option value="bottom_center" class="basic" style="display: block;">Bottom Center</option>
						<option value="bottom_right">Bottom Right</option>
					</select>
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Position Top</label>
					<input type="number" min="0" max="100" step="1" name="cmpop_sticky_button_top" class="cmpop_sticky_button_top" value="40" disabled>%
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">				
					<label>Position Right</label>
					<input type="number" min="0" max="100" step="1" name="cmpop_sticky_button_right" class="cmpop_sticky_button_right" value="48" disabled>%
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Border Size</label>
					<input type="number" name="cmpop_sticky_button_border_size" class="cmpop_sticky_button_border_size" value="0" disabled>px
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Border Radius</label>
					<input type="number" name="cmpop_sticky_button_border_radius" class="cmpop_sticky_button_border_radius" value="0" disabled>px
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
					
				<p class="onlyinpro">
					<label>Border Color</label>
					<input type="color" name="cmpop_sticky_button_border_color" class="cmpop_sticky_button_border_color" value="#ff0000" disabled>
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Background Color</label>
					<input type="color" name="cmpop_sticky_button_bg_color" class="cmpop_sticky_button_bg_color" value="#ff0000" disabled>
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Font Size</label>
					<input type="number" name="cmpop_sticky_button_text_size" class="cmpop_sticky_button_text_size" value="16" disabled>px
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Text Color</label>
					<input type="color" name="cmpop_sticky_button_text_color" class="cmpop_sticky_button_text_color" value="#ffffff" disabled>
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<p class="onlyinpro">
					<label>Text</label>
					<input type="text" name="cmpop_sticky_button_text" class="cmpop_sticky_button_text" value="Press!" disabled>
					<br>
					<span class="field-info"><span>(Only in Pro)</span></span>
				</p>
				
				<div class="onlyinpro_empty show" style="display:none;"><p style="margin-top:20px;">Pro options are hidden. Click the button <span style="font-weight:bold;color:#00cd00;">"Show Pro options"</span> to see them.</p></div>
			
			</div>

            <div id="cmpopfly-tab-sound" class="options-tab hidden">
                
                <p class="onlyinpro">
					<label>Sound effect when popup shows</label>
                    <?php
                    $mb->the_field('cm-campaign-sound-effect-type');
                    //$fieldValue = $mb->get_the_value();
                    $fieldValue = 'none';
                    if (empty($fieldValue)) {
						if(!empty($soundEffectMethod['value'])) {
							$fieldValue = $soundEffectMethod['value'];
						}
                    }
					if(!empty($soundEffectMethod['options'])) {
						foreach ($soundEffectMethod['options'] as $key => $value) {
							echo '<input disabled="disabled" name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . ' class="cm-campaign-sound-effect-type">' . $value . "<br />";
						}
					}
                    ?>
					<span class='field-info'><span>(Only in Pro)</span></span>
                </p>
				
				<div class="onlyinpro_empty show" style="display:none;"><p style="margin-top:20px;">Pro options are hidden. Click the button <span style="font-weight:bold;color:#00cd00;">"Show Pro options"</span> to see them.</p></div>
				
            </div>

            <div id="cmpopfly-tab-activity" class="options-tab hidden">
                
				<div class="cmpopfly-options-group">
                    
                    <p class="onlyinpro">
						<label>Users type for pop-up</label>
                        <?php $mb->the_field('cm-campaign-user-type'); ?>
                        <select disabled="disabled" name="<?php $mb->the_name(); ?>">
                            <?php
                            $fieldValue = $mb->get_the_value();
                            foreach ($usersType['options'] as $key => $value) {
                                echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                            }
                            ?>
                        </select><br />
                        <span class='field-info'><span>(Only in Pro)</span> For which groups of users the popup will be displayed.</span>
                    </p>
					
                    <div class="onlyinpro">
						<label>When fire the popup?</label>
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
                            <span>(Only in Pro)</span> To fire the popup on hover or click action for certain element, please add the <strong>'cm-pop-up-banners-trigger'</strong> class attribute to it. eg.
                            <pre>&lt;div class="cm-pop-up-banners-trigger"&gt;&lt;/div&gt;</pre>
                        </span>
                    </div>
					
                    <p class="onlyinpro">
						<label>Show pop-up after users registration </label>
                        <?php
                        $mb->the_field('cm-campaign-thank');
                        $value = $mb->get_the_value();
                        $checked = is_string($value) ? $value : '0';
                        ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input disabled="disabled" type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $checked); ?> class="<?php $mb->the_name(); ?>" />
                        <span class='field-info'><span>(Only in Pro)</span> Use with plugin <strong>CM Registration</strong></span>
                    </p>
					
                    <p>
						<label>Show on every page</label>
                        <?php $mb->the_field('cm-campaign-show-allpages'); ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $metabox->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign will be displayed on each post and page of your website</span>
                    </p>

                    <p>
						<label>Show on homepage</label>
                        <?php $mb->the_field('cm-campaign-show-homepage'); ?>
                        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $metabox->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                        <span class='field-info'>If this checkbox is selected then this campaign will be displayed on homepage (main url of the page).</span>
                    </p>

                    <p class="onlyinpro">
						<label>Show on URLs matching pattern</label>
                        <?php $mb->the_field('cm-help-item-show-wildcard'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" placeholder="/help/" value="<?php echo $metabox->get_the_value(); ?>"/><br />
                        <span class='field-info'><span>(Only in Pro)</span> If this field is filled campaign will be displayed on pages with matching url. Permalinks must be enabled for this function to work.</span>
                    </p>
					
					<div>
						<label>Show on selected posts/pages</label>
						<?php while ($mb->have_fields_and_multi('cm-help-item-options')): ?>
							<?php $mb->the_group_open(); ?>
							<div class="group-wrap <?php echo $mb->get_the_value('toggle_state') ? ' closed' : ''; ?>" >
								<?php $mb->the_field('toggle_state'); ?>
								<input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $mb->get_the_value()); ?> class="toggle_state hidden" />
								<div class="group-control dodelete" title="<?php _e('Click to remove "Page"', ''); ?>"></div>
								<div class="group-control toggle" title="<?php _e('Click to toggle', ''); ?>"></div>
								<?php $mb->the_field('title'); ?>
								<?php
								//need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities()
								?>
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
					</div>
					
                    <p>
						<a href="#" class="docopy-cm-help-item-options button">
							<span class="icon add"></span>
							Add New
						</a>
					</p>

                    <p class="onlyinpro">
						<label>Activity dates</label>
						<span class='field-info'><span>(Only in Pro)</span> You can choose the activity dates for current campaign.</span>
                    </p>
					
                    <p class="onlyinpro">
						<label>Activity days</label>
						<span class='field-info'><span>(Only in Pro)</span> You can choose the activity dates for current campaign.</span>
                    </p>
					
                    <p class="onlyinpro">
						<label>Minimum device width</label>
                        <?php $mb->the_field('cm-campaign-min-device-width'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" value="<?php echo $mb->get_the_value(); ?>" placeholder="enter value eg. 700"  class="<?php $mb->the_name(); ?>" /><br />
                        <span class='field-info'><span>(Only in Pro)</span> Select the minimum width of the device (in pixels) where the banner should be displayed. "700" will hide it on most smartphones, but not tablets. Shows on all devices if blank.</span>
                    </p>

                    <p class="onlyinpro">
						<label>Maximum device width</label>
                        <?php $mb->the_field('cm-campaign-max-device-width'); ?>
                        <input disabled="disabled" type="text" name="<?php $mb->the_name(); ?>" value="<?php echo $mb->get_the_value(); ?>" placeholder="enter value eg. 320"  class="<?php $mb->the_name(); ?>" /><br />
                        <span class='field-info'><span>(Only in Pro)</span> Select the maximum width of the device (in pixels) where the banner should be displayed. "320" will show it on iPhone 5, but not on iPhone 6. Shows on all devices if blank.</span>
                    </p>
					
					<p class="onlyinpro">
						<label>Statistics clicks counting method</label>
						<?php
						$mb->the_field('cm-campaign-clicks-counting-method');
						$fieldValue = $mb->get_the_value();
						if (empty($fieldValue)) {
							if(!empty($clicksCountMethod['value'])) {
								$fieldValue = $clicksCountMethod['value'];
							}
						}
						if(!empty($clicksCountMethod['options'])) {
							foreach ($clicksCountMethod['options'] as $key => $value) {
								echo '<input disabled="disabled" name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . '>' . $value . "<br />";
							}
						}
						?>
						<span class='field-info'><span>(Only in Pro)</span></span>
					</p>
					
                </div>
				
				<div class="onlyinpro_empty show" style="display:none;"><p style="margin-top:20px;">Pro options are hidden. Click the button <span style="font-weight:bold;color:#00cd00;">"Show Pro options"</span> to see them.</p></div>
				
            </div>
			
        </div>
		
    </div>

    <p class="meta-save">
        <strong>To save the settings use the "Publish/Update" button in the right column.</strong>
    </p>

</div>
<style>
.cmpop_post_show_hide_pro_options { display:inline-block !important; float:none !important; background:lightgreen !important; margin:16px !important; cursor:pointer !important; font-weight:normal !important; }
</style>
<script>
jQuery(document).ready(function() {
	jQuery(".cmpop_post_show_hide_pro_options").click(function() {
		var that = jQuery(this);
		if(that.text() == 'Hide Pro options') {
			that.text('Show Pro options');
			jQuery('.onlyinpro').addClass('hide');
			jQuery('.onlyinpro_empty').addClass('show');
		} else {
			that.text('Hide Pro options');
			jQuery('.onlyinpro').removeClass('hide');
			jQuery('.onlyinpro_empty').removeClass('show');
		}
	});
	jQuery(".cmpop_post_show_hide_pro_options").trigger('click');
});
</script>