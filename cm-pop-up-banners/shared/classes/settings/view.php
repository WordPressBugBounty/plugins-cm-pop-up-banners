<?php namespace com\cminds\popupfly; ?>
<?php if (!empty($messages)): ?>
    <div class="updated" style="clear:both"><p><?php echo $messages; ?></p></div>
<?php endif; ?>

<?php echo do_shortcode('[cminds_free_activation id="cmpopfly"]'); ?>

<div id="cminds_settings_container">

    <br/>
    <div class="clear"></div>

    <form method="post">
        <?php wp_nonce_field('update-options'); ?>
        <input type="hidden" name="action" value="update" />


        <div id="cm_settings_tabs" class="glossarySettingsTabs">

            <?php
            CMPOPFLY_Settings::renderSettingsTabsControls();
            CMPOPFLY_Settings::renderSettingsTabs();
            ?>

            <?php
            $additionalTooltipTabContent = apply_filters('cmpopfly_settings_tooltip_tab_content_after', '');
            echo $additionalTooltipTabContent;
            ?>
        </div>

        <p class="submit" style="clear:left">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'cm-tooltip-glossary') ?>" name="cmpopfly_glossarySave" />
        </p>
    </form>
</div>