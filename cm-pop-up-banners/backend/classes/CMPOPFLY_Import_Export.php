<?php
namespace com\cminds\popupfly;

use com\cminds\popupfly\CMPopUpBannersBackend;
use com\cminds\popupfly\CMPopUpBannersShared;

class CMPOPFLY_Import_Export {

    public static $calledClassName;

    public static function init() {
        if (empty(self::$calledClassName)) {
            self::$calledClassName = __CLASS__;
        }
        add_action('current_screen', array(self::$calledClassName, 'handleExport'));
        add_action('current_screen', array(self::$calledClassName, 'handleImport'));
    }

    public static function getCurrentPage() {
        $page = filter_input(INPUT_GET, 'page');
        return $page;
    }

    public static function prepareHelpItemData($postId, $fillJsonStruct = true) {
        $postMeta = array();
        $postData = get_post($postId, ARRAY_A);
        $fields = self::takeAllMetaSlugs();
        if (!empty($postData)) {
            $postData = array_intersect_key($postData, array('ID' => '', 'post_title' => ''));

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $postMeta[$field] = get_post_meta($postId, $field);
                }
            }

            $postData = array_merge($postData, $postMeta);
        }

        return $postData;
    }

    public static function takeAllMetaSlugs() {

        $fields = [];

        if (!empty(CMPopUpBannersBackend::$customMetaboxes)) {
            foreach (CMPopUpBannersBackend::$customMetaboxes as $metabox) {
                $fields[] = $metabox->id;
            }
        }
        $fields[] = CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DAYS_META_KEY;
        $fields[] = CMPopUpBannersShared::CMPOPFLY_CUSTOM_USER_TYPES_META_KEY;
        $fields[] = CMPopUpBannersShared::CM_STAR_RATING;

        return $fields;
    }

    /**
     * Function exports the items with given postIds
     * @param type $postIds
     */
    public static function exportHelpItems($postIds) {
        $helpItems = array();
        if (!is_array($postIds)) {
            $postIds = array($postIds);
        }

        foreach ($postIds as $postId) {
            if (is_numeric($postId)) {
                $helpItem = self::prepareHelpItemData($postId, true);

                /*
                 * Add the helpItem if it's not empty
                 */
                if (!empty($helpItem)) {
                    $helpItems[] = $helpItem;
                }
            }
        }

        $helpItemContent = json_encode($helpItems);
        $filename = 'cm_ad_items_' . md5(implode(',', $postIds)) . '_' . date('Ymd_His', current_time('timestamp'));

        /*
         *  Prepare File
         */
        $file = @tempnam("tmp", "zip");

        if ($file) {
            $zip = new \ZipArchive();
            $zip->open($file, \ZipArchive::OVERWRITE);

            /*
             *  Stuff with content
             */
            $zip->addFromString($filename . '.json', $helpItemContent);

            /*
             * Close and send to users
             */
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($file));
            header('Content-Disposition: attachment; filename="' . $filename . '.zip' . '"');
            readfile($file);
            unlink($file);
            exit;
        }
    }

    /**
     * Exports the Help Items
     */
    public static function handleExport() {
        $page = self::getCurrentPage();

        if (!empty($_POST['cmr_doExport']) && $page == 'cm-popupflyin-import_export') {
            $args = array(
                'post_type'      => CMPopUpBannersShared::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => - 1
            );

            $posts = get_posts($args);

            if ($posts) {
                foreach ($posts as $post) {
                    $postIds[] = $post->ID;
                }
                self::exportHelpItems($postIds);
            }
        }
    }

    public static function handleImport() {
        $page = self::getCurrentPage();
        if (!empty($_POST['cmr_doImport']) && !empty($_FILES['importJSON']) && is_uploaded_file($_FILES['importJSON']['tmp_name']) && $page == 'cm-popupflyin-import_export') {
            self::importJson($_FILES['importJSON']);
        }
    }

    public static function importJson($file) {

        $json_data = json_decode(file_get_contents($file['tmp_name']), true);
        $error = '';
        $fields = self::takeAllMetaSlugs();

        foreach ($json_data as $data) {
            $post_data = array(
                'post_title'  => sanitize_text_field($data['post_title']),
                'post_type'   => CMPopUpBannersShared::POST_TYPE,
                'post_status' => 'publish'
            );

            $post_id = wp_insert_post($post_data);

            if (!empty($fields)) {
                foreach ($fields as $field) {
                    update_post_meta($post_id, $field, $data[$field][0]);
                }
            }
        }

        $queryArgs = array('msg' => 'imported', 'error' => $error);
        wp_safe_redirect(add_query_arg($queryArgs, $_SERVER['REQUEST_URI']), 303);
        exit;
    }

}