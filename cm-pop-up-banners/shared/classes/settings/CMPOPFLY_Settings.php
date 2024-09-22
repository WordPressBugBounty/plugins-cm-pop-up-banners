<?php

namespace com\cminds\popupfly;

class CMPOPFLY_Settings extends Settings {
    
    protected static $abbrev = 'cmpopfly';
    protected static $dir = __DIR__;

    public static function init() {
        self::load_config();

        add_action(self::abbrev('_save_options_after'), [__CLASS__, 'beforeSaveSettings'], 10, 2);
        add_action(self::abbrev('_save_options_after'), [__CLASS__, 'afterSaveSettings'], 100, 2);
        add_filter(self::abbrev('_before_saving_option'), [__CLASS__, 'beforeSaveOption'], 10, 2);
        add_filter(self::abbrev('_before_sanitizing_option'), [__CLASS__, 'beforeSanitizingOption'], 10, 2);
        add_filter(self::abbrev('-custom-settings-tab-content-50'), array(__CLASS__, 'outputLabelsSettings'));
    }

    public static function outputLabelsSettings() {
        $view = CMPOPFLY_PLUGIN_DIR . '/views/backend/settings_labels.phtml';
        ob_start();
        include $view;
        $content = ob_get_clean();

        return $content;
    }

    public static function beforeSanitizingOption($option_value, $option_name) {
        if (in_array($option_name, array('cmpopfly_glossaryPermalink'))) {
            $option_value = sanitize_title($option_value);
        }
        return $option_value;
    }

    public static function beforeSaveOption($option_value, $option_name) {
        if ($option_name == 'cmpopfly_index_letters') {
            $option_value = array_map('mb_strtolower', explode(',', $option_value));
        }
        return $option_value;
    }

    public static function beforeSaveSettings($post, $messages) {

        if (isset($post['cmpopfly_removeAllOptions'])) {
            self::_cleanupOptions();
            $messages = 'CM Tooltip Glossary data options have been removed from the database.';
        }

        if (isset($post['cmpopfly_removeAllItems'])) {
            self::_cleanupItems();
            $messages = 'CM Tooltip Glossary data terms have been removed from the database.';
        }
    }

    public static function afterSaveSettings($post, $messages) {
        $enqueeFlushRules = false;
        /*
         * Update the page options
         */
        //\CMPOPFLY_Glossary_Index::tryGenerateGlossaryIndexPage();
        if (isset($post["cmpopfly_glossaryPermalink"]) && $post["cmpopfly_glossaryPermalink"] !== \CM\CMPOPFLY_Settings::get('cmpopfly_glossaryPermalink')) {
            /*
             * Update glossary post permalink
             */
            $glossaryPost = array(
                'ID'        => $post["cmpopfly_glossaryID"],
                'post_name' => $post["cmpopfly_glossaryPermalink"]
            );
            wp_update_post($glossaryPost);
            $enqueeFlushRules = true;
        }

        if (empty($post["cmpopfly_glossaryPermalink"])) {
            $post["cmpopfly_glossaryPermalink"] = 'glossary';
        }
        update_option('cmpopfly_glossaryPermalink', $post["cmpopfly_glossaryPermalink"]);

        if (apply_filters('cmpopfly_enqueueFlushRules', $enqueeFlushRules, $post)) {
            self::_flush_rewrite_rules();
        }

        unset($post['cmpopfly_glossaryID'], $post['cmpopfly_glossaryPermalink'], $post['cmpopfly_glossarySave']);
    }

    /**
     * Function cleans up the plugin, removing the terms, resetting the options etc.
     *
     * @return string
     */
    protected static function _cleanupOptions($force = true) {
        /*
         * Remove the data from the other tables
         */
        do_action('cmpopfly_do_cleanup');

//        $glossaryIndexPageId = CMPOPFLY_Glossary_Index::getGlossaryIndexPageId();
//        if (!empty($glossaryIndexPageId)) {
//            wp_delete_post($glossaryIndexPageId);
//        }

        /*
         * Remove the options
         */
        $optionNames = wp_load_alloptions();

        $options_names = array_filter(array_keys($optionNames), function ($k) {
            return strpos($k, 'cmpopfly_') === 0;
        });
        foreach ($options_names as $optionName) {
            delete_option($optionName);
        }
    }

    /**
     * Function cleans up the plugin, removing the terms, resetting the options etc.
     *
     * @return string
     */
    protected static function _cleanupItems($force = true) {

        do_action('cmpopfly_do_cleanup_items_before');

        $glossary_index = self::getGlossaryItems();

        /*
         * Remove the glossary terms
         */
        foreach ($glossary_index as $post) {
            wp_delete_post($post->ID, $force);
        }

        $tags = get_terms(array(
            'taxonomy'   => 'glossary-tags',
            'hide_empty' => false,
        ));

        foreach ($tags as $tag) {
            wp_delete_term($tag->term_id, 'glossary-tags');
        }

        $categories = get_terms(array(
            'taxonomy'   => 'glossary-categories',
            'hide_empty' => false,
        ));

        foreach ($categories as $category) {
            wp_delete_term($category->term_id, 'glossary-categories');
        }

        /*
         * Invalidate the list of all glossary items stored in cache
         */
        do_action('cmpopfly_do_cleanup_items_after');
    }

}