<?php
use com\cminds\popupfly\CMPopUpBannersBackend;
?>
<div class="my_meta_control cm-help-items-metacontrol">
    <p class="onlyinpro">
        <?php _e('(Only in Pro) Add new Banner Items by using the "Add Banner Item" button.  Rearrange the order by dragging and dropping.', ''); ?>
    </p>
    <?php
    wp_print_styles('editor-buttons');
    $tinymce_options = array(
        'resize'             => true,
        'add_unload_trigger' => false,
        'relative_urls'      => false,
        'remove_script_host' => false,
        'convert_urls'       => false,
        'wpautop'            => false,
        'indent'             => true,
    );
    $user_id = get_current_user();
    if (!user_can($user_id, 'manage_options')) {
        $tinymce_options['invalid_elements'] = 'script';
    }
    ob_start();
    wp_editor('', 'content-1', array(
        'dfw'           => true,
        'editor_height' => 1,
        'tinymce'       => $tinymce_options,
		)
    );
    $content = ob_get_contents();
    ob_end_clean();
    /*
     * List of the templates
     */
    $templates = CMPopUpBannersBackend::getTemplatesList();
    global $wp_version;
    if (version_compare($wp_version, '4.3', '<')) {
        add_filter('the_editor_content', 'wp_richedit_pre');
    } else {
        add_filter('the_editor_content', 'format_for_editor');
    }
    $switch_class = 'tmce-active';
    ?>
    <?php $itemscount = 1; ?>
    <?php while ($mb->have_fields_and_multi('cm-help-item-group')): ?>
        <?php $mb->the_group_open(); ?>
    <div class="group-wrap <?php echo $mb->get_the_value('toggle_state') ? ' closed' : ''; ?>" >
        <?php $mb->the_field('toggle_state'); ?>
        <?php // @ TODO: toggle should be user specific ?>
        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked(1, $mb->get_the_value()); ?> class="toggle_state hidden" />
        <div class="group-control dodelete" title="<?php _e('Click to remove "Banner Item"', ''); ?>"></div>
        <div class="group-control toggle" title="<?php _e('Click to toggle', ''); ?>"></div>
        <?php $mb->the_field('title'); ?>
        <?php // need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities()  ?>
        <h3 class="handle"><?php echo $mb->get_the_value() ? 'Banner Item - ' . substr(strip_tags(html_entity_decode($mb->get_the_value())), 0, 30) : 'Banner Item'; ?><?php echo ' ' . $itemscount++; ?></h3>
        <?php $mb->the_field('banner-uuid'); ?>
        <input type="hidden" name="<?php $mb->the_name(); ?>" value="<?php echo $mb->get_the_value(); ?>">
        <div class="group-inside">
            <?php $mb->the_field('textarea'); ?>
            <p class="warning update-warning"><?php _e('Sort order has been changed.  Remember to save the post to save these changes.'); ?></p>
            <label>Content</label>
            <div class="customEditor wp-core-ui wp-editor-wrap <?php echo $switch_class; ?>">
                <div class="wp-editor-tools hide-if-no-js">
                    <div class="wp-media-buttons custom_upload_buttons">
                        <?php do_action('media_buttons'); ?>
                    </div>
                    <div class="wp-editor-tabs">
                        <a data-mode="tmce" class="wp-switch-editor switch-tmce"><?php _e('Visual'); ?></a>
                        <a data-mode="html" class="wp-switch-editor switch-html"> <?php _ex('Text', 'Name for the Text editor tab (formerly HTML)'); ?></a>
                    </div>
                </div><!-- .wp-editor-tools -->
                <div class="wp-editor-container">
                    <textarea class="wp-editor-area" rows="10" cols="50" name="<?php $mb->the_name(); ?>" rows="3">
                        <?php echo esc_html(apply_filters('the_editor_content', html_entity_decode($mb->get_the_value(NULL, false, 'content')))); ?>
                    </textarea>
                </div>
                <p><span><?php _e('Enter in the content'); ?></span></p>
                <div class="clear"></div>
            </div>
        </div><!-- .group-inside -->
    </div><!-- .group-wrap -->
    <?php $mb->the_group_close(); ?>
<?php endwhile; ?>
<p><a href="#" class="button adddesigner_trigger" id="adddesigner_trigger"><?php _e('Show AdDesigner', ''); ?></a></p>
<p class="meta-save"><strong>To save the settings use the "Publish/Update" button in the right column.</strong></p>
<iframe src="<?php echo admin_url('admin-ajax.php' . '?action=cm_pub_addesigner'); ?>" id="cmac_addesigner_container">Loading...</iframe>
<?php do_action('cmpopfly_after_help_items') ?>
</div>