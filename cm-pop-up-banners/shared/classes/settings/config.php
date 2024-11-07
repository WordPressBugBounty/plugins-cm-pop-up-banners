<?php
$cmpopfly_custom_post_type_support = get_option( 'cmpopfly_custom_post_type_support', array() );
$arrayCPT = array();
$args = array(
	'public' => true
);
$post_types = get_post_types( $args, 'objects', 'and' );
foreach ( $post_types as $post_type ) {
	$arrayCPT[ $post_type->name ] = $post_type->labels->singular_name . ' (' . $post_type->name . ')';
}

$before_content = '<div class="settings-tab">
	
	<div class="cminds_settings_toggle_tabs cminds_settings_toggle-opened" style="opacity:.5; pointer-events:none;">Toggle All</div>
	
	<div class="block" id="post-types">
		<h3 class="section-title">
			<span>Post Types</span>
			<svg class="tab-arrow" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#6BC07F"><path d="M0 7.33l2.829-2.83 9.175 9.339 9.167-9.339 2.829 2.83-11.996 12.17z"></path></svg>
		</h3>
		<table class="floated-form-table form-table">
			<tr valign="top" class="cmpopfly_custom_post_type_support">
				<th scope="row">
					<div>Custom Post Types</div>
					<div class="cm_field_help" title="(Only in Pro) Select Custom Post Types on which you want to display Campaigns."></div>
				</th>
				<td>
					<div><select name="cmpopfly_custom_post_type_support[]" multiple="multiple" class="select2">';
					foreach($arrayCPT as $cptkey=>$cptval) {
						if (in_array($cptkey, $cmpopfly_custom_post_type_support)) {
							$before_content .= '<option value="'.$cptkey.'" selected>'.$cptval.'</option>';
						} else {
							$before_content .= '<option value="'.$cptkey.'">'.$cptval.'</option>';
						}
					}
$before_content .= '</select></div>
					<div style="margin-top:5px;">Select Custom Post Types on which you want to display Campaigns.</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="block" id="editor" style="opacity:.5; pointer-events:none;">
		<h3 class="section-title">
			<span>Editor</span>
			<svg class="tab-arrow" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#6BC07F"><path d="M0 7.33l2.829-2.83 9.175 9.339 9.167-9.339 2.829 2.83-11.996 12.17z"></path></svg>
		</h3>
		<table class="floated-form-table form-table">
			<tr valign="top" class="cmpopfly_allow_scripts_in_editor wrapper-bool">
				<th scope="row">
					<div>Allow scripts in editor</div>
					<div class="cm_field_help" title="(Only in Pro) If enabled, the invalid HTML tags and tag attributes will not be stripped."></div>
				</th>
				<td class="field-bool">
					<label><input type="radio" name="c_mpopfly_allow_scripts_in_editor" id="cmpopfly_allow_scripts_in_editor_1" value="1" checked="checked"> On</label>&nbsp;&nbsp;<label><input type="radio" name="c_mpopfly_allow_scripts_in_editor" id="cmpopfly_allow_scripts_in_editor_0" value="0"> Off</label>
					<div style="margin-top:5px;"><span class="cm_field_help_pro">(Only in Pro)</span> If enabled, the invalid HTML tags and tag attributes will not be stripped.</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="block" id="appearance" style="opacity:.5; pointer-events:none;">
		<h3 class="section-title">
			<span>Appearance</span>
			<svg class="tab-arrow" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#6BC07F"><path d="M0 7.33l2.829-2.83 9.175 9.339 9.167-9.339 2.829 2.83-11.996 12.17z"></path></svg>
		</h3>
		<table class="floated-form-table form-table">
			<tr valign="top" class="cmpopfly_mobile_max_width wrapper-string">
				<th scope="row">
					<div>Select max width for mobile devices</div>
					<div class="cm_field_help" title="(Only in Pro) Allows to set the max width for mobile devices. Campaign banners can have different dimentions on devices smaller than this value."></div>
				</th>
				<td class="field-string">
					<input type="text" name="c_mpopfly_mobile_max_width" value="400px">
					<div style="margin-top:5px;"><span class="cm_field_help_pro">(Only in Pro)</span> Allows to set the max width for mobile devices. Campaign banners can have different dimentions on devices smaller than this value.</div>
				</td>
			</tr>
		</table>
	</div>
	
	<div class="block" id="statistics" style="opacity:.5; pointer-events:none;">
		<h3 class="section-title">
			<span>Statistics</span>
			<svg class="tab-arrow" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#6BC07F"><path d="M0 7.33l2.829-2.83 9.175 9.339 9.167-9.339 2.829 2.83-11.996 12.17z"></path></svg>
		</h3>
		<table class="floated-form-table form-table">
			<tr valign="top" class="cmpopfly_statistics wrapper-bool">
				<th scope="row">
					<div>Collect Statistics</div>
					<div class="cm_field_help" title="(Only in Pro) Turn the Statistics (views/clicks) ON/OFF."></div>
				</th>
				<td class="field-bool">
					<label><input type="radio" name="c_mpopfly_statistics" id="cmpopfly_statistics_1" value="1"> On</label>&nbsp;&nbsp;<label><input type="radio" name="c_mpopfly_statistics" id="cmpopfly_statistics_0" value="0" checked="checked"> Off</label>
					<div style="margin-top:5px;"><span class="cm_field_help_pro">(Only in Pro)</span> Turn the Statistics (views/clicks) ON/OFF.</div>
				</td>    
			</tr>
		</table>
	</div>
	
</div>
<script>
jQuery(document).ready(function () {
	jQuery(".select2").select2();
	jQuery(".select2.select2-container").css("width", "100%");
});
</script>
';
$config = [
    'abbrev'   => 'cmpopfly',
    'tabs'     => [
        '0'  => 'Installation Guide',
		'1' => [
        	'tab_name' => 'General',
	        'section' => [
	        	0 => 'Post Types',
	        	1 => 'Editor',
	        	2 => 'Appearance',
	        	3 => 'Statistics',
	        ]
        ],
        '99' => 'Upgrade',
    ],
    'default'  => [
    ],
    'settings' => [
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
		'cmpopfly_allow_scripts_in_editor'       => array(
            'onlyin' => 'Pro',
            'type'        => 'string',
            'value'       => '400px',
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Select max width for mobile devices',
            'description' => 'Allows to set the max width for mobile devices. Campaign banners can have different dimentions on devices smaller than this value.',
        ),
		'cmpopfly_statistics'               => array(
            'onlyin' => 'Pro',
            'type'        => 'bool',
            'value'       => true,
            'category'    => 'general',
            'subcategory' => 'general',
            'label'       => 'Collect Statistics',
            'description' => 'Turn the Statistics (views/clicks) ON/OFF',
        ),
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
                    'before'   => $before_content,
                    'settings' => [
                        //'cmpopfly_custom_post_type_support',
                        //'cmpopfly_mobile_max_width',
                        //'cmpopfly_allow_scripts_in_editor',
						//'cmpopfly_statistics',
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