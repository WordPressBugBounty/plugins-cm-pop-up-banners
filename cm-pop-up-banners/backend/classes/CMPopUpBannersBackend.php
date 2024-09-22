<?php
namespace com\cminds\popupfly;

use \WPAlchemy_MetaBox;
use com\cminds\popupfly\CMPopUpBanners;
use com\cminds\popupfly\CMPopUpBannersShared;
use com\cminds\popupfly\ViewHelper;
use com\cminds\popupfly\CMPOPFLY_Labels;
use com\cminds\popupfly\CMPOPFLY_Settings;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main backend class file/controller.
 * What it does:
 * - shows/adds/edits plugin settings
 * - adding metaboxes to admin area
 * - adding admin scripts
 * - other admin area only things
 *
 * How it works:
 * - everything is hooked up in the constructor
 */
class CMPopUpBannersBackend {

    public static $calledClassName;
    protected static $instance = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;
    protected static $statisticsPageSlug = NULL;
    public static $settingsPageSlug = NULL;
    public static $importExportPageSlug = NULL;
    public static $aboutPageSlug = NULL;
    public static $exportPageSlug = NULL;
    public static $exportNonceAction = 'cm-popupflyin-export';
    public static $exportNonceField = 'cm-popupflyin-export-nonce';
    public static $customMetaboxes = array();
    protected static $bannersDataArray = array();
    public static $isPreview = false;

    /**
     * Main Instance
     *
     * Insures that only one instance of class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0
     * @static
     * @staticvar array $instance
     * @return The one true CMPopUpBanners
     */
    public static function instance() {
        $class = __CLASS__;
        if (!isset(self::$instance) && !( self::$instance instanceof $class )) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct() {
        if (empty(self::$calledClassName)) {
            self::$calledClassName = __CLASS__;
        }
        self::$cssPath = CMPOPFLY_PLUGIN_URL . 'backend/assets/css/';
        self::$jsPath = CMPOPFLY_PLUGIN_URL . 'backend/assets/js/';
        self::$viewsPath = CMPOPFLY_PLUGIN_DIR . 'backend/views/';

        self::$statisticsPageSlug = CMPOPFLY_SLUG_NAME . '-statistics';
        self::$settingsPageSlug = CMPOPFLY_SLUG_NAME . '-settings';
        self::$importExportPageSlug = CMPOPFLY_SLUG_NAME . '-import_export';
        self::$aboutPageSlug = CMPOPFLY_SLUG_NAME . '-about';

        /*
         * Metabox SECTION
         */

        include_once CMPOPFLY_PLUGIN_DIR . 'libs/wpalchemy/wpalchemy.php';
        include_once CMPOPFLY_PLUGIN_DIR . 'libs/ip2locationlite.php';

        self::$customMetaboxes[] = new WPAlchemy_MetaBox(array
            (
            'id'          => '_cm_advertisement_items',
            'title'       => 'Advertisement Items',
            'template'    => CMPOPFLY_PLUGIN_DIR . 'libs/wpalchemy/metaboxes/cm-help-items.php',
            'types'       => array(CMPopUpBannersShared::POST_TYPE),
            'init_action' => array(self::$calledClassName, 'metaInit'),
            'save_filter' => array(self::$calledClassName, 'metaRepeatingSaveFilter'),
        ));

        self::$customMetaboxes[] = new WPAlchemy_MetaBox(array
            (
            'id'       => '_cm_advertisement_items_custom_fields',
            'title'    => 'Campaign - Options',
            'template' => CMPOPFLY_PLUGIN_DIR . 'libs/wpalchemy/metaboxes/cm-help-items-options.php',
            'types'    => array(CMPopUpBannersShared::POST_TYPE)
        ));

        add_filter('query_vars', array(self::$calledClassName, 'addQueryVars'));
        add_action('parse_query', array(self::$calledClassName, 'processQueryArg'));

        /*
         * Recreate the default filters on the_content
         * this will make it much easier to output the meta content with proper/expected formatting
         */
        add_filter('meta_content', 'wptexturize');
        add_filter('meta_content', 'convert_smilies');
        add_filter('meta_content', 'convert_chars');
        add_filter('meta_content', 'wpautop');
        add_filter('meta_content', 'shortcode_unautop');
        add_filter('meta_content', 'prepend_attachment');
        add_filter('meta_content', 'do_shortcode');

        /*
         * Metabox SECTION END
         */

        add_filter('mce_css', array(self::$calledClassName, 'plugin_mce_css'));

        add_action('init', array(self::$calledClassName, 'createPostType'));
        add_action('admin_init', array(self::$calledClassName, 'pluginUpgrade'));
        add_action('current_screen', array(self::$calledClassName, 'handlePost'));

        add_action('save_post', array(self::$calledClassName, 'updateLinkedTemplates'));

        add_action('admin_menu', array(self::$calledClassName, 'addMenu'));
		//add_filter('post_row_actions',array(self::$calledClassName, 'addRowAction'), 10, 2);
        add_filter('page_row_actions', array(self::$calledClassName, 'addRowAction'), 10, 2);

        add_filter('manage_edit-' . CMPopUpBannersShared::POST_TYPE . '_columns', array(self::$calledClassName, 'editScreenColumns'));
        add_filter('manage_' . CMPopUpBannersShared::POST_TYPE . '_posts_custom_column', array(self::$calledClassName, 'editScreenColumnsContent'), 10, 2);
        /*
         * Preview
         */
        add_action('wp_ajax_cm_popupflyin_preview', array(self::$calledClassName, 'outputPreview'));

        add_filter('post_type_link', array(self::$calledClassName, 'replacePostLink'), 999, 4);
        add_filter('plugins_loaded', array(self::$calledClassName, 'stop_ckeditor'));

        /*
         * Metaboxes
         */
        add_action('add_meta_boxes', array(self::$calledClassName, 'registerBoxes'));
        add_action('save_post', array(self::$calledClassName, 'savePostdata'), 999, 1);
        add_action('update_post', array(self::$calledClassName, 'savePostdata'), 999, 1);

        /*
         * Notice
         */
        add_action('admin_notices', array(self::$calledClassName, 'showMessage'));
        /*
         * Ajax handlers
         */
        add_action('wp_ajax_cm_pub_addesigner', array(self::$calledClassName, 'getAddesigner'));

        add_action('wp_ajax_cm_popupflyin_register_click', array(self::$calledClassName, 'registerClick'));
        add_action('wp_ajax_nopriv_cm_popupflyin_register_click', array(self::$calledClassName, 'registerClick'));

        add_action('wp_ajax_cm_popupflyin_prepare_statistics_data', array(self::$calledClassName, 'getStatistics'));

        add_action('wp_trash_post', array(self::$calledClassName, 'clearPinnedPostsOnCampaignDelete'));

        add_action('post_type_labels_' . CMPopUpBannersShared::POST_TYPE, array(self::$calledClassName, 'customListLabels'));

        add_action('admin_bar_menu', array(self::$calledClassName, 'addEditCampaignLinkToAdminBar'), 999);
    }

    public static function addEditCampaignLinkToAdminBar() {
        global $post;
        if ($post && !self::is_edit_page()) {
            $activeCampaign = CMPopUpBannersBackend::getWidgetForPage($post->ID, 'campaign');
            if (!empty($activeCampaign)) {
                global $wp_admin_bar;
                $wp_admin_bar->add_menu(array(
                    'id'     => 'cm-pop-up-banners-for-wordpress-pro-post-edit-campaign-link',
                    'title'  => 'Edit PopUp Campaign',
                    'parent' => 'root-default',
                    'href'   => admin_url('post.php?post=' . $activeCampaign->ID . '&action=edit')
                ));
            }
        }
    }

    public static function is_edit_page($new_edit = null) {
        global $pagenow;
        //make sure we are on the backend
        if (!is_admin())
            return false;

        if ($new_edit == "edit")
            return in_array($pagenow, array('post.php', 'edit.php'));
        elseif ($new_edit == "new") //check for new post page
            return in_array($pagenow, array('post-new.php'));
        else //check for either new or edit
            return in_array($pagenow, array('post.php', 'post-new.php', 'edit.php'));
    }

    public static function editScreenColumns($columns) {
        $baseColumns = $columns;
        $columns = array(
            'cb'     => '<input type="checkbox" />',
            'title'  => __('Item name'),
            'global' => __('Global'),
            'date'   => __('Date'),
        );

        return $columns;
    }

    /*
	public static function editScreenColumnsContent($column, $post_id) {
        switch ($column) {
            case 'global' :
                $helpItemMeta = [];
                $status = CMPopUpBanners::__('No');
                if (isset($helpItemMeta['cm-campaign-show-allpages']) && $helpItemMeta['cm-campaign-show-allpages']) {
                    $status = CMPopUpBanners::__('Yes');
                }
                echo $status;
                break;
        }
    }
	*/

	public static function editScreenColumnsContent( $column, $post_id ) {
        switch ( $column ) {
            case 'global' :
                $helpItemMeta = CMPOPFLY_Import_Export::prepareHelpItemData( $post_id, FALSE );
                $status       = CMPopUpBanners::__( 'No' );
				//if ( isset( $helpItemMeta[ 'cm-campaign-show-allpages' ] ) && $helpItemMeta[ 'cm-campaign-show-allpages' ] ) {
                if ( isset( $helpItemMeta[ '_cm_advertisement_items_custom_fields' ][0][ 'cm-campaign-show-allpages' ]) && $helpItemMeta[ '_cm_advertisement_items_custom_fields' ][0][ 'cm-campaign-show-allpages' ]) {
                    $status = CMPopUpBanners::__( 'Yes' );
                }
                echo $status;
                break;
        }
    }

    public static function stop_ckeditor($plugins) {
        $get = $_GET;
        if (!empty($get['post_type']) || !empty($get['post'])) {
            $postType = null;

            if (!empty($get['post_type'])) {
                $postType = $get['post_type'];
            } elseif (!empty($get['post'])) {
                $postType = get_post_type($get['post']);
            }

            if ($postType && $postType == 'cm-help-item') {
                remove_action('init', 'ckeditor_init');
            }
        }
        return $plugins;
    }

    public static function replacePostLink($post_link, $post, $leavename, $sample) {
        if ($post->post_type == CMPopUpBannersShared::POST_TYPE) {
            return admin_url('admin-ajax.php?action=cm_popupflyin_preview&campaign_id=' . $post->ID);
        }
        return $post_link;
    }

    public static function addQueryVars($vars) {
        $vars[] = "post_id";
        $vars[] = "cm-action";
        return $vars;
    }

    /**
     * Plugin compability with previous version on upgrade
     */
    public static function pluginUpgrade() {
        // upgrade to 1.0.3 version, on adding support for CPT in settings
        if (!get_option('cmpopfly_custom_post_type_support', false)) {

            $arrayCPT = array();
            $args = array(
                'public' => true,
                    // '_builtin' => false
            );

            $post_types = get_post_types($args, 'objects', 'and');

            foreach ($post_types as $post_type) {
                $arrayCPT[] = $post_type->name;
            }

            update_option('cmpopfly_custom_post_type_support', $arrayCPT);
        }
    }

    /**
     * Create custom post type
     */
    public static function createPostType() {
        $args = array(
            'label'               => 'Campaign',
            'labels'              => array(
                'add_new_item'  => 'Add New Campaign',
                'add_new'       => 'Add New Campaign',
                'edit_item'     => 'Edit Campaign Item',
                'view_item'     => 'View Campaign Item',
                'singular_name' => 'Advertisement Item',
                'name'          => CMPOPFLY_PLUGIN_NAME,
                'menu_name'     => 'Campaigns'
            ),
            'description'         => 'CM Campaigns',
            'map_meta_cap'        => true,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_admin_bar'   => true,
            'show_in_menu'        => CMPOPFLY_SLUG_NAME,
            '_builtin'            => false,
            'capability_type'     => 'post',
            'hierarchical'        => true,
            'has_archive'         => false,
            'rewrite'             => array('slug' => CMPopUpBannersShared::POST_TYPE, 'with_front' => false, 'feeds' => false, 'feed' => false),
            'query_var'           => true,
            'supports'            => array('title', 'revisions'),
        );

        register_post_type(CMPopUpBannersShared::POST_TYPE, $args);

        $args2 = array(
            'label'               => 'Help Item Template',
            'labels'              => array(
                'add_new_item'  => 'Add New Help Item Template',
                'add_new'       => 'Add Help Item Template',
                'edit_item'     => 'Edit Help Item Template',
                'view_item'     => 'View Help Item Template',
                'singular_name' => 'Help Item Template',
                'name'          => CMPOPFLY_PLUGIN_NAME,
                'menu_name'     => 'Help Item Templates'
            ),
            'description'         => 'CM Help Item Templates',
            'map_meta_cap'        => true,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'public'              => false,
            'show_ui'             => true,
            'show_in_admin_bar'   => true,
            'show_in_menu'        => false,
            '_builtin'            => false,
            'capability_type'     => 'post',
            'hierarchical'        => true,
            'has_archive'         => false,
            'rewrite'             => array('slug' => CMPopUpBannersShared::POST_TYPE_TEMPLATE, 'with_front' => false, 'feeds' => false, 'feed' => false),
            'query_var'           => true,
            'supports'            => array('title', 'editor', 'revisions'),
        );

        register_post_type(CMPopUpBannersShared::POST_TYPE_TEMPLATE, $args2);
    }

    /**
     * Checks for an action during the query parsing
     */
    public static function processQueryArg() {
        global $wp_query;

        if (empty($wp_query)) {
            return;
        }
        $postType = get_query_var('post_type');
        $postId = get_query_var('post_id');
        $action = get_query_var('cm-action');

        if (is_admin() && $postType == CMPopUpBannersShared::POST_TYPE && $postId && $action) {
            switch ($action) {
                case 'export':
                    CMPOPFLY_Import_Export::exportHelpItems($postId);
                    break;

                default:
                    break;
            }

            $redirectUrl = esc_url(add_query_arg(array('post_type' => CMPopUpBannersShared::POST_TYPE), admin_url('edit.php')));
            wp_redirect($redirectUrl);
            exit();
        }
    }

    public static function getWidgetForPage($postId, $type = 'widget') {
        if (is_home() && !is_front_page()) {
            $postId = get_option('page_for_posts');
        }
        if (empty($postId)) {
            return false;
        }
        $campaignId = $urlCampaign = $globalCampaign = false;
        /*
         * checks if page/post config is not blocking the campaign
         */
        if (CMPopUpBannersShared::checkIfNotBlocked($postId)) {
            return false;
        }

        /*
         * if page has campaign assigned
         */
        $Campaign = CMPopUpBannersShared::getPostHelpItem($postId);

        //check if campaign have valid activity dates and valid user type

        if (!self::_checkActivityDates($Campaign) || !self::_checkUserType($Campaign)) {
            $Campaign = false;
        }
        /*
         * if not is campaign matched to url pattern
         */
        $urlCampaign = CMPopUpBannersShared::getHelpItemMatchingUrl(get_permalink());
        //check if campaign have valid activity dates and valid user type
        if (!self::_checkActivityDates($urlCampaign) || !self::_checkUserType($urlCampaign)) {
            $urlCampaign = false;
        }
        /*
         * if not is global campaign set
         */
        $globalCampaign = CMPopUpBannersShared::getGlobalHelpItem();
        //check if campaign have valid activity dates and valid user type
        if (!self::_checkActivityDates($globalCampaign) || !self::_checkUserType($urlCampaign)) {
            $globalCampaign = false;
        }
        //check if current CPT is allowed
        $currentPostType = get_post_type($postId);
        $allowedPostTypes = get_option('cmpopfly_custom_post_type_support', []);
        if(!is_array($allowedPostTypes)){
            $allowedPostTypes = [];
        }
        if (!in_array($currentPostType, $allowedPostTypes)) {
            $globalCampaign = false;
        }
        /*
         * if not is campaign matched to homepage
         */
        $homepageCampaign = CMPopUpBannersShared::getHelpItemForHomepage(is_front_page());
        //check if campaign have valid activity dates and valid user type
        if (!self::_checkActivityDates($homepageCampaign) || !self::_checkUserType($urlCampaign)) {
            $homepageCampaign = false;
        }

        $registerCampaign = CMPopUpBannersShared::getHelpItemThankPopup();

        if (isset($_GET['cminds_debug'])) {
            var_dump(array(
                'postId'           => $postId,
                'postCampaign'     => $Campaign,
                'urlCampaign'      => $urlCampaign,
                'globalCampaign'   => $globalCampaign,
                'homepageCampaign' => $homepageCampaign,
            ));
        }
        if ($registerCampaign) {
            $campaignId = $registerCampaign;
        } elseif ($Campaign === FALSE || $Campaign === '-1' || $Campaign === '' || $Campaign == '0') {
            if (!empty($urlCampaign)) {
                $campaignId = $urlCampaign;
            } else if (!empty($homepageCampaign)) {
                $campaignId = $homepageCampaign;
            } else {
                if (!empty($globalCampaign)) {
                    $campaignId = $globalCampaign;
                } else {
                    /*
                     * No campaign - not defined
                     */
                    $campaignId = FALSE;
                }
            }
        } else {
            $campaignId = $Campaign;
        }

        if ($campaignId === FALSE) {
            /*
             * no campaigns
             */
            return false;
        }
        $post = get_post($campaignId);
        if (!$post || $post->post_type !== CMPopUpBannersShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($campaignId) || wp_is_post_autosave($campaignId)) {
            /*
             * Wrong post type!
             */
            return false;
        }
        $additionalData = array('campaign_id' => $campaignId);
        if ($type == 'widget') {
            $postMeta = get_post_meta($campaignId);
            $postMeta = array_merge($postMeta, $additionalData);

            $serializedPostMeta = get_post_meta($campaignId, '_cm_advertisement_items_custom_fields', true);
			if($serializedPostMeta) {
				if (isset($serializedPostMeta['cm-campaign-widget-disable']) && $serializedPostMeta['cm-campaign-widget-disable'] == '1') {
					$postMeta = array();
				}
			}

            return $postMeta;
        } elseif ($type == 'campaign') {
            return $post;
        }
        return false;
    }

    public static function getWidgetForUrl($url, $type = 'widget') {
        /*
         * if not is campaign matched to url pattern
         */
        $urlCampaign = CMPopUpBannersShared::getHelpItemMatchingUrl($url);
        //check if campaign have valid activity dates and valid user type
        if (!self::_checkActivityDates($urlCampaign) || !self::_checkUserType($urlCampaign)) {
            $urlCampaign = false;
        }

        /*
         * if not is global campaign set
         */
        $globalCampaign = CMPopUpBannersShared::getGlobalHelpItem();
        //check if campaign have valid activity dates and valid user type
        if (!self::_checkActivityDates($globalCampaign) || !self::_checkUserType($globalCampaign)) {
            $globalCampaign = false;
        }
        /*
         * if not is campaign matched to homepage
         */
        $homepageCampaign = CMPopUpBannersShared::getHelpItemForHomepage(is_front_page());
        //check if campaign have valid activity dates and valid user type
        if (!self::_checkActivityDates($homepageCampaign) || !self::_checkUserType($homepageCampaign)) {
            $homepageCampaign = false;
        }

        $registerCampaign = CMPopUpBannersShared::getHelpItemThankPopup();

        if (isset($_GET['cminds_debug'])) {
            var_dump(array(
                'urlCampaign'      => $urlCampaign,
                'globalCampaign'   => $globalCampaign,
                'homepageCampaign' => $homepageCampaign,
            ));
        }

        if ($registerCampaign) {
            $campaignId = $registerCampaign;
        } elseif (!empty($urlCampaign)) {
            $campaignId = $urlCampaign;
        } else if (!empty($homepageCampaign)) {
            $campaignId = $homepageCampaign;
        } else {

            if (!empty($globalCampaign)) {
                $campaignId = $globalCampaign;
            } else {
                /*
                 * No campaign - not defined
                 */
                $campaignId = FALSE;
            }
        }

        if ($campaignId === FALSE) {
            /*
             * no campaigns
             */
            return false;
        }
        $post = get_post($campaignId);
        if (!$post || $post->post_type !== CMPopUpBannersShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($campaignId) || wp_is_post_autosave($campaignId)) {
            /*
             * Wrong post type!
             */
            return false;
        }
        $additionalData = array('campaign_id' => $campaignId);
        if ($type == 'widget') {
            $postMeta = get_post_meta($campaignId);
            $postMeta = array_merge($postMeta, $additionalData);

            $serializedPostMeta = get_post_meta($campaignId, '_cm_advertisement_items_custom_fields', true);
            if ('1' == $serializedPostMeta['cm-campaign-widget-disable']) {
                $postMeta = array();
            }

            return $postMeta;
        } elseif ($type == 'campaign') {
            return $post;
        }
        return false;
    }

    /**
     * Check if camapign id has valid activity dates
     */
    private static function _checkActivityDates($postId) {
        $metaDates = get_post_meta($postId, CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY);
        $metaDays = get_post_meta($postId, CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DAYS_META_KEY);
        $valid = true;
        if (!empty($metaDates)) {
            /*
             * Campaign is disabled until at least one date range is OK
             */
            $valid = false;
            $activityDates = maybe_unserialize($metaDates[0]);
            if (!empty($activityDates) && is_array($activityDates)) {
                foreach ($activityDates AS $oneRange) {
                    if (self::_compareDates($oneRange)) {
                        $valid = true;
                        break;
                    }
                }
            }
        }
        if (!empty($metaDays)) {
            $metaDays = maybe_unserialize($metaDays[0]);
            if (!empty($metaDays) && is_array($metaDays)) {
                foreach ($metaDays as $day => $hours) {
                    if (isset($hours['checked']) && $hours['checked'] == 'Y') {
                        $valid = false;
                        if (self::_compareDays($day, $hours)) {
                            $valid = true;
                            break;
                        }
                    }
                }
            }
        }
        return $valid;
    }

    /**
     * Check if camapign id has valid user type
     */
    private static function _checkUserType($postId) {

        $userType = get_post_meta($postId, CMPopUpBannersShared::CMPOPFLY_CUSTOM_USER_TYPES_META_KEY, true);

        if ($userType == 'login' && !is_user_logged_in()) {
            $valid = false;
        } elseif ($userType == 'logout' && is_user_logged_in()) {
            $valid = false;
        } else {
            $valid = true;
        }

        return $valid;
    }

    private static function _compareDates(array $dates = array()) {
        $now = time();

        $hoursFrom = !empty($dates['hours_from']) ? $dates['hours_from'] : '00';
        $minsFrom = !empty($dates['mins_from']) ? $dates['mins_from'] : '00';

        $hoursTo = !empty($dates['hours_to']) ? $dates['hours_to'] : '00';
        $minsTo = !empty($dates['mins_to']) ? $dates['mins_to'] : '00';

        if (!empty($dates['date_from']) && empty($dates['date_till'])) {
            $dateFrom = new \DateTime($dates['date_from'] . ' ' . $hoursFrom . ":" . $minsFrom);
            if ($dateFrom->getTimestamp() < $now) {
                return true;
            } else {
                return false;
            }
        }
        if (empty($dates['date_from']) && !empty($dates['date_till'])) {
            $dateTill = new \DateTime($dates['date_till'] . ' ' . $hoursTo . ":" . $minsTo);
            if ($dateTill->getTimestamp() > $now) {
                return true;
            } else {
                return false;
            }
        }
        if (!empty($dates['date_from']) && !empty($dates['date_till'])) {
            $dateFrom = new \DateTime($dates['date_from'] . ' ' . $hoursFrom . ":" . $minsFrom);
            $dateTill = new \DateTime($dates['date_till'] . ' ' . $hoursTo . ":" . $minsTo);
            if ($dateFrom->getTimestamp() < $now && $dateTill->getTimestamp() > $now) {
                return true;
            } else {
                return false;
            }
        }
    }

    private static function _compareDays($day, array $hours = array()) {
        $now = time();
        /**
         * check if day of the week matches
         */
        if ($day != date("D", $now)) {
            return false;
        }

        $today = new \DateTime();

        $hoursFrom = !empty($hours['hours_from']) ? $hours['hours_from'] : '00';
        $minsFrom = !empty($hours['mins_from']) ? $hours['mins_from'] : '00';

        $hoursTo = !empty($hours['hours_to']) ? $hours['hours_to'] : '24';
        $minsTo = !empty($hours['mins_to']) ? $hours['mins_to'] : '00';

        if (!empty($hours['hours_from']) && empty($hours['hours_to'])) {
            $dateFrom = new \DateTime(date("Y-m-d") . ' ' . $hoursFrom . ":" . $minsFrom);
            if ($dateFrom->getTimestamp() < $now) {
                return true;
            } else {
                return false;
            }
        }
        if (empty($hours['hours_from']) && !empty($hours['hours_to'])) {
            $dateTill = new \DateTime(date("Y-m-d") . ' ' . $hoursTo . ":" . $minsTo);
            if ($dateTill->getTimestamp() > $now) {
                return true;
            } else {
                return false;
            }
        }
        if (!empty($hours['hours_from']) && !empty($hours['hours_to'])) {
            $dateFrom = new \DateTime(date("Y-m-d") . ' ' . $hoursFrom . ":" . $minsFrom);
            $dateTill = new \DateTime(date("Y-m-d") . ' ' . $hoursTo . ":" . $minsTo);
            if ($dateFrom->getTimestamp() < $now && $dateTill->getTimestamp() > $now) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Outputs the preview
     */
    public static function outputPreview() {

        $helpItemPostId = filter_input(INPUT_GET, 'campaign_id');

        if ($helpItemPostId) {
            $post = get_post($helpItemPostId);
            if (!$post || $post->post_type !== CMPopUpBannersShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($helpItemPostId) || wp_is_post_autosave($helpItemPostId)) {
                echo 'Wrong post type!';
                die();
            }
        } else {
            echo 'No "campaign_id" parameter!';
            die();
        }

        self::$isPreview = true;
        CMPopUpBannersShared::getWidgetOutput();

        $linkPath = CMPOPFLY_PLUGIN_DIR . 'backend/views/preview.phtml';
        $cssPath = self::$cssPath;

        if (file_exists($linkPath)) {
            ob_start();
            require $linkPath;
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
        }
        die();
    }

    /**
     * Returns the name of the view from the $templateId
     */
    public static function getTemplateName($templateId) {
        return $templateId;
    }

    /**
     * Returns the content of the template based on ID
     */
    public static function getTemplate($templateId) {
        $template = '';

        $templatePost = get_post($templateId);

        if (!empty($templatePost)) {
            $template = array(
                'content' => $templatePost->post_content,
                'title'   => $templatePost->post_title
            );
        }

        return $template;
    }

    /**
     * Returns the list of available templates
     */
    public static function getTemplatesList() {
        $templates = array();

        $templatePosts = get_posts(array(
            'post_type'      => CMPopUpBannersShared::POST_TYPE_TEMPLATE,
            'posts_per_page' => -1
        ));

        if (!empty($templatePosts)) {
            foreach ($templatePosts as $templatePost) {
                $templates[$templatePost->ID] = $templatePost->post_title;
            }
        }

        return $templates;
    }

    /**
     *
     * @param $postData
     *
     * @return \stdClass
     *
     * @deprecated This function is not using anymore since 1.5.2v
     *
     */
    public static function fillHelpItemJsonStruct($postData) {
        $itemsData = array();
        $helpItemObj = new \stdClass();

        $helpItemObj->id = $postData['ID'];
        $helpItemObj->title = !empty($postData['post_title']) ? $postData['post_title'] : '';
        $helpItemObj->header = !empty($postData['header']) ? $postData['header'] : '';
        $helpItemObj->footer = !empty($postData['footer']) ? $postData['footer'] : '';
        $helpItemObj->widget_type = !empty($postData['cm-campaign-widget-type']) ? $postData['cm-campaign-widget-type'] : '';

        foreach ($postData[0]['cm-help-item-group'] as $groupKey => $group) {
            $dataRow = array();
			//$dataRow[ 'id' ] = $groupKey;

            foreach ($group as $fieldKey => $fieldValue) {
                switch ($fieldKey) {
                    case 'textarea':
                        $fieldValue = wpautop(do_shortcode(self::replaceImgWithBase64($fieldValue)));
                        $fieldKey = 'textarea';
                        break;

                    default:
                        break;
                }

                $dataRow[$fieldKey] = $fieldValue;
            }

            $itemsData[] = $dataRow;
        }


        foreach ($itemsData as $helpItemItemsArr) {
            $helpItem = new \stdClass();

            foreach ($helpItemItemsArr as $key => $value) {
                $helpItem->$key = $value;
            }

            $helpItemObj->helpItems[] = $helpItem;
        }

        return $helpItemObj;
    }

    /**
     * Function exports the items with given postIds
     * @param type $postIds
     */
    public static function addRowAction($actions, $post) {
        if ($post->post_type == CMPopUpBannersShared::POST_TYPE) {
            $pinOption = get_option('cm_popupflyin_json_api_pinprotect', false);
            $pin = !empty($pinOption) ? '&pin=' . $pinOption : '';
            $actions['cm_json'] = '<a href="' . admin_url('admin-ajax.php?action=cm_popupflyin_json_api&help_id=' . $post->ID . $pin) . '" target="_blank">JSON API</a>';
            $actions['cm_export'] = '<a href="' . admin_url('edit.php?post_type=cm-ad-item&post_id=' . $post->ID . '&cm-action=export') . '">Export</a>';
            $actions['popupflyin_preview'] = '<a href="' . admin_url('admin-ajax.php?action=cm_popupflyin_preview&campaign_id=' . $post->ID) . '" target="_blank">Preview</a>';
            unset($actions['preview']);
            unset($actions['view']);
        }
        return $actions;
    }

    public static function addMenu() {
        global $submenu;

        add_menu_page('Campaign', CMPOPFLY_PLUGIN_NAME, 'edit_posts', CMPOPFLY_SLUG_NAME, 'edit.php?post_type=' . CMPopUpBannersShared::POST_TYPE);
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Add New Campaign', 'Add New Campaign', 'edit_posts', 'post-new.php?post_type=' . CMPopUpBannersShared::POST_TYPE);
        do_action('cm_popflyin_submenu_page');
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Statistics', 'Statistics', 'edit_posts', self::$statisticsPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Settings', 'Settings', 'edit_posts', self::$settingsPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Import/Export', 'Import/Export', 'edit_posts', self::$importExportPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_filter('views_edit-' . CMPopUpBannersShared::POST_TYPE, array(self::$calledClassName, 'filterAdminNav'), 10, 1);
        add_filter('views_edit-' . CMPopUpBannersShared::POST_TYPE_TEMPLATE, array(self::$calledClassName, 'filterAdminNav'), 10, 1);
    }

    /**
     * Filters admin navigation menus to show horizontal link bar
     * @global string $submenu
     * @global type $plugin_page
     * @param type $views
     * @return string
     */
    public static function filterAdminNav($views) {
        global $submenu, $plugin_page;
        $scheme = is_ssl() ? 'https://' : 'http://';
        $adminUrl = str_replace($scheme . $_SERVER['HTTP_HOST'], '', admin_url());
        $currentUri = str_replace($adminUrl, '', $_SERVER['REQUEST_URI']);
        $submenus = array();

        if (isset($submenu[CMPOPFLY_SLUG_NAME])) {
            $thisMenu = $submenu[CMPOPFLY_SLUG_NAME];

            $firstMenuItem = $thisMenu[0];
            unset($thisMenu[0]);

            $secondMenuItem = array('Trash', 'edit_posts', 'edit.php?post_status=trash&post_type=' . CMPopUpBannersShared::POST_TYPE, 'Trash');
            array_unshift($thisMenu, $firstMenuItem, $secondMenuItem);

            foreach ($thisMenu as $item) {
                $slug = $item[2];
                $isCurrent = ($slug == $plugin_page || strpos($item[2], '.php') === strpos($currentUri, '.php'));
                $isCurrent = ($slug == $currentUri);
                $isExternalPage = strpos($item[2], 'http') !== FALSE;
                $isNotSubPage = $isExternalPage || strpos($item[2], '.php') !== FALSE;
                $url = $isNotSubPage ? $slug : get_admin_url(null, 'admin.php?page=' . $slug);
                $target = $isExternalPage ? '_blank' : '';
                $submenus[$item[0]] = '<a href="' . $url . '" target="' . $target . '" class="' . ($isCurrent ? 'current' : '') . '">' . strip_tags($item[0]) . '</a>';
            }
        }
        return $submenus;
    }

    public static function getAdminNav() {
        global $self, $parent_file, $submenu_file, $plugin_page, $typenow, $submenu;
        ob_start();
        $submenus = array();

        $menuItem = CMPOPFLY_SLUG_NAME;
        if (isset($submenu[$menuItem])) {
            $thisMenu = $submenu[$menuItem];

            foreach ($thisMenu as $sub_item) {
                $slug = $sub_item[2];

                // Handle current for post_type=post|page|foo pages, which won't match $self.
                $self_type = !empty($typenow) ? $self . '?post_type=' . $typenow : 'nothing';

                $isCurrent = FALSE;
                $subpageUrl = get_admin_url('', 'admin.php?page=' . $slug);

                if (
                        (!isset($plugin_page) && $self == $slug ) ||
                        ( isset($plugin_page) && $plugin_page == $slug && ( $menuItem == $self_type || $menuItem == $self || file_exists($menuItem) === false ) )
                ) {
                    $isCurrent = TRUE;
                }

                $url = (strpos($slug, '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : $subpageUrl;
                $submenus[] = array(
                    'link'    => $url,
                    'title'   => strip_tags($sub_item[0]),
                    'current' => $isCurrent
                );
            }
            include self::$viewsPath . 'nav.phtml';
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    /*
     * Sanitize the input similar to post_content
     * @param array $meta - all data from metabox
     * @param int $post_id
     * @return array
     */

    public static function kia_single_save_filter($meta, $post_id) {

        if (isset($meta['test_editor'])) {
            $meta['test_editor'] = sanitize_post_field('post_content', $meta['test_editor'], $post_id, 'db');
        }

        return $meta;
    }

    /*
     * Sanitize the input similar to post_content
     * @param array $meta - all data from metabox
     * @param int $post_id
     * @return array
     */

    public static function metaRepeatingSaveFilter($meta, $post_id) {
		
		$allowed_tags = array(
			'a' => array(
				'href' => array(),
				'title' => array(),
				'target' => array(),
			),
			'b' => array(),
			'blockquote' => array(
				'cite' => array(),
			),
			'br' => array(),
			'cite' => array(),
			'code' => array(),
			'del' => array(
				'datetime' => array(),
			),
			'dd' => array(),
			'div' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
			),
			'dl' => array(),
			'dt' => array(),
			'em' => array(),
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array(),
			'i' => array(),
			'img' => array(
				'alt' => array(),
				'src' => array(),
				'height' => array(),
				'width' => array(),
			),
			'li' => array(),
			'ol' => array(
				'start' => array(),
				'type' => array(),
			),
			'p' => array(),
			'q' => array(
				'cite' => array(),
			),
			'span' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
			),
			'strike' => array(),
			'strong' => array(),
			'ul' => array(),
		);
		
        if (!is_array($meta)) {
            $meta = array();
        }
		array_walk($meta, function (&$masterItem, $key, $post_id) {
            foreach ($masterItem as &$item) {
                if (isset($item['cm_load_template']) && !empty($item['template_linked'])) {
                    $template = self::getTemplate($item['cm_load_template']);
					$item['title'] = sanitize_post_field('post_title', $template['title'], $post_id, 'db');
					$item['textarea'] = $template['content'];
                } else {
                    if (isset($item['textarea'])) {	
						$item['textarea'] = $item['textarea'];
                        if (!isset($item['banner-uuid'])) {
                            $item['banner-uuid'] = CMPopUpBannersShared::giveUniqueId();
                        }
						
                    }
                }
            }
        }, $post_id);
		return $meta;
    }
	
    /*
     * Enqueue styles and scripts specific to metaboxs
     */

    public static function enqueueScripts() {
		// I prefer to enqueue the styles only on pages that are using the metaboxes
        wp_enqueue_style('wpalchemy-metabox', CMPOPFLY_PLUGIN_URL . 'libs/wpalchemy/assets/meta.css');

		//make sure we enqueue some scripts just in case ( only needed for repeating metaboxes )
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-mouse');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-spinner');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-tooltip');

        /*
         * enque jQuery UI styles
         */
        wp_enqueue_style('ac_jqueryUIStylesheet', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/jquery-ui/smoothness/jquery-ui-1.12.1.min.css');
        wp_enqueue_script('word-count');

        wp_enqueue_script('editor');

        wp_enqueue_script('quicktags');
        wp_enqueue_style('buttons');

        wp_enqueue_script('wplink');

        wp_enqueue_script('wp-fullscreen');
        wp_enqueue_script('media-upload');

		// special script for dealing with repeating textareas- needs to run AFTER all the tinyMCE init scripts, so make 'editor' a requirement
        wp_enqueue_script('kia-metabox', CMPOPFLY_PLUGIN_URL . 'libs/wpalchemy/assets/kia-metabox.js', array('jquery'), '1.1', true);

        /*
         * Enqueue popupflyin scripts
         */
        wp_enqueue_script('cm_popupflyin_backend', CMPOPFLY_PLUGIN_URL . 'backend/assets/js/cm-popupflyin-backend.js', array('jquery', 'jquery-ui-tabs'), '1.0.0', true);
        wp_localize_script('cm_popupflyin_backend', 'cm_popupflyin_backend', array('ajaxurl' => admin_url('admin-ajax.php'), 'plugin_url' => CMPOPFLY_PLUGIN_URL));

        /*
         * Enqueue popupflyin styles
         */
        wp_enqueue_style('cm_popupflyin_css', CMPOPFLY_PLUGIN_URL . 'backend/assets/css/cm-popupflyin.css');

        // if visual editor disabled in WP user settings, register tinymce
        if (function_exists('wp_register_tinymce_scripts')) {
            $wp_scripts = wp_scripts();
            $wp_scripts->remove('wp-tinymce');
            wp_register_tinymce_scripts($wp_scripts, true);
            wp_enqueue_script('wp-tinymce');
        }
    }

    public static function metaInit() {
        add_action('admin_enqueue_scripts', array(self::$calledClassName, 'enqueueScripts'));
    }

    public static function kia_metabox_scripts() {
        wp_print_scripts('kia-metabox');
    }

    public static function plugin_mce_css($mce_css) {
        if (!empty($mce_css)) {
            $mce_css .= ',';
        }
        $mce_css .= CMPOPFLY_PLUGIN_URL . 'backend/assets/css/cm-popupflyin.css';
        return $mce_css;
    }

    public static function replaceImgWithBase64($content = '') {
        return preg_replace_callback(
                '#<img(.*)src=["\'](.*?)["\'](.*)/>#i', array(__CLASS__, '_replaceImgWithBase64'), $content
        );
    }

    public static function _replaceImgWithBase64($matches) {
        $img = '<img ' . $matches[1] . ' src="' . self::_curlBase64Encode($matches[2]) . '" ' . $matches[3] . '/>';
        return $img;
    }

    /**
     * Function grabs the image from the given url and prepares the Base64 encoded representation of this string
     * Then caches it and returns the base64 representation of the image with the right MIME type
     *
     * @param string $url - url of the image
     * @param int $ttl - time to live of cache
     * @return type
     */
    public static function _curlBase64Encode($url = null, $ttl = 86400) {
        if ($url) {
            $option_name = 'ep_base64_encode_images_' . md5($url);
            $data = get_option($option_name);
            if (isset($data['cached_at']) && (time() - $data['cached_at'] <= $ttl)) {
# serve cache
            } else {
                if (strstr($url, 'http:') === FALSE && strstr($url, 'https:') === FALSE) {
                    $base = get_bloginfo('url');
                    $url = $base . '/admin/' . $url;
                }
                $ch = curl_init();
                $options = array(
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
//                    CURLOPT_SSLVERSION     => 3
                );
                curl_setopt_array($ch, $options);
                $returnData = curl_exec($ch);
                if (!$returnData) {
                    var_dump(curl_error($ch));
                    die;
                }
                $data['chunk'] = base64_encode($returnData);
                $data['mime'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($http_code === 200) {
                    $data['cached_at'] = time();
                    update_option($option_name, $data);
                }
            }
        }

        return 'data:' . $data['mime'] . ';base64,' . $data['chunk'];
    }

    public static function renderAdminPage() {
        global $wpdb;
        $pageId = filter_input(INPUT_GET, 'page');

        $content = '';
        $title = '';

        switch ($pageId) {
            case CMPOPFLY_SLUG_NAME . '-settings': {
                    $title = CMPopUpBanners::__('Settings');
                    wp_enqueue_style('jquery-ui-tabs-css', self::$cssPath . 'jquery-ui-tabs.css');
                    wp_enqueue_script('jquery-ui-tabs');

                    /*
                     * Enqueue colorpicker
                     */
                    wp_enqueue_style('wp-color-picker');
                    wp_enqueue_style('cm-color-picker', self::$cssPath . 'cm-color-picker.css');
                    wp_enqueue_script('cm-color-picker', self::$jsPath . 'cm-color-picker.js', array('wp-color-picker'), false, true);

                    $content = CMPOPFLY_Settings::render();

                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-about': {
                    $title = CMPopUpBanners::__('About');
                    $content = ViewHelper::load('backend/views/about.phtml', $title);
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-pro': {
                    $content = ViewHelper::load('backend/views/pro.phtml');
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-userguide': {
                    wp_redirect('https://www.cminds.com/wordpress-plugins-library/pop-up-banners-plugin-for-wordpress/');
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-export': {
                    $title = CMPopUpBanners::__('Export');
                    $content = ViewHelper::load('backend/views/export.phtml');
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-statistics': {
                    global $availableCampaigns;
                    $availableCampaigns = self::getAvailableCampaigns();
                    $title = CMPopUpBanners::__('Statistics');
                    $scriptData['ajaxUrl'] = admin_url('admin-ajax.php' . '?action=cm_popupflyin_prepare_statistics_data');

                    wp_enqueue_style('ac_jqueryUIStylesheet', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/jquery-ui/smoothness/jquery-ui-1.10.3.custom.min.css');
                    wp_enqueue_style('statistics_custom_css', self::$cssPath . 'statistics-custom.css');
                    wp_enqueue_style('cm-popupflyin-preloader', self::$cssPath . 'statistics-preloader.css');

                    $params = apply_filters('CMPOPFLY_admin_statistics', array());
                    $params['title'] = $title;
                    $content = ViewHelper::load('backend/views/statistics.phtml', $params);
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-import_export': {
                    wp_enqueue_style('ac_jqueryUIStylesheet', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/jquery-ui/smoothness/jquery-ui-1.10.3.custom.min.css');
                    wp_enqueue_style('statistics_custom_css', self::$cssPath . 'statistics-custom.css');
                    wp_enqueue_style('cm-popupflyin-preloader', self::$cssPath . 'statistics-preloader.css');

                    $title = CMPopUpBanners::__('Import/Export');
                    $content = ViewHelper::load('backend/views/import_export.phtml');
                    break;
                }
        }

        self::displayAdminPage($content, $title);
    }

    public static function getAvailableCampaigns() {
        global $wpdb;
        return $wpdb->get_results('SELECT ID, post_title' .
                        ' FROM ' . CMPOPFLY_POST_TABLE .
                        " WHERE post_type = '" . CMPopUpBannersShared::POST_TYPE . "'" .
                        " AND post_status = 'publish'" .
                        " ORDER BY ID DESC"
        );
    }

    public static function displayAdminPage($content, $title) {
        $nav = self::getAdminNav();
        include_once self::$viewsPath . 'template.phtml';
    }

    /**
     * Saves the settings
     */
    public static function handlePost() {
        $page = filter_input(INPUT_GET, 'page');
        $postData = filter_input_array(INPUT_POST);

        if ($page == 'cm-popupflyin-settings' && !empty($postData)) {
            do_action('cm_popupflyin_save_settings', $postData);

            //$params = CMPOPFLY_Settings::processPostRequest();

            // Labels
            /*
			$labels = CMPOPFLY_Labels::getLabels();
            foreach ($labels as $labelKey => $label) {
                if (isset($_POST['label_' . $labelKey])) {
                    CMPOPFLY_Labels::setLabel($labelKey, stripslashes($_POST['label_' . $labelKey]));
                }
            }
			*/

        }
    }

    /**
     * Save post metadata when a post is saved.
     *
     * @param int $post_id The ID of the post.
     */
    public static function updateLinkedTemplates($post_id) {
        /*
         * In production code, $slug should be set only once in the plugin,
         * preferably as a class property, rather than in each function that needs it.
         */
        $slug = CMPopUpBannersShared::POST_TYPE_TEMPLATE;
        $postType = filter_input(INPUT_POST, 'post_type');

		// If this isn't a 'book' post, don't update it.
        if ($slug != $postType) {
            return;
        }
        $args = array(
            'post_type'      => CMPopUpBannersShared::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1);

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $needsSaving = FALSE;
            $metabox = self::$customMetaboxes[0];
            $meta = $metabox->the_meta($post->ID);

            if (!empty($meta['cm-help-item-group'])) {
                foreach ($meta['cm-help-item-group'] as $key => $cmItemGroup) {
                    if (!empty($cmItemGroup['cm_load_template']) && $cmItemGroup['cm_load_template'] == $post_id) {
                        if (!empty($cmItemGroup['template_linked']) && $cmItemGroup['template_linked'] && !empty($cmItemGroup['cm_load_template'])) {
                            $template = self::getTemplate($cmItemGroup['cm_load_template']);
                            $newTemplateContent = sanitize_post_field('post_content', $template['content'], $post->ID, 'db');
                            $newTemplateTitle = sanitize_post_field('post_title', $template['title'], $post_id, 'db');

                            if ($newTemplateContent !== $cmItemGroup['textarea']) {
                                $meta['cm-help-item-group'][$key]['textarea'] = $newTemplateContent;
                                $needsSaving = true;
                            }
                            if ($newTemplateTitle !== $cmItemGroup['title']) {
                                $meta['cm-help-item-group'][$key]['title'] = $newTemplateTitle;
                                $needsSaving = true;
                            }
                        }
                    }
                }
            }

            if ($needsSaving) {
                update_post_meta($post->ID, $metabox->id, $meta);
            }
        }
    }

    /**
     * Returns the list of post types for which the custom settings may be applied
     * @return type
     */
    public static function getApplicablePostTypes() {
        $postTypes = array('post', 'page');
        return apply_filters('cmpopfly-metabox-posttypes', $postTypes);
    }

    /**
     * Register metaboxes
     */
    public static function registerBoxes() {
        foreach (self::getApplicablePostTypes() as $postType) {
            add_meta_box('cmpopfly-metabox', CMPOPFLY_PLUGIN_NAME, array(self::$calledClassName, 'showMetaBox'), $postType, 'side', 'high');
        }
    }

    /**
     * Shows metabox containing selectbox with amazon category ID which should be advertised in the Tooltips on this page
     * @global type $post
     */
    public static function showMetaBox() {
        global $post;
        $disableAds = CMPopUpBanners::meta($post->ID, CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS, '0');
        $activeCampaign = self::getWidgetForPage($post->ID, 'campaign');

        echo '<div>';

        echo '<input type="hidden" value="0" name="' . CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS . '" />';
        echo '<label>';
        echo '<input type="checkbox" value="1" name="' . CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS . '" ' . checked('1', $disableAds, false) . '/>';
        echo CMPopUpBanners::__('Hide banners on this post/page') . ' </label>';
        echo '</div>';

        echo '<div>';

        /*
         * campaigns info with links to edit
         */
        if (!empty($activeCampaign)) {
            echo '<br><label>' . CMPopUpBanners::__('Existing Pinned Campaign:') . ' </label>';
            echo '<div>';
            echo '<a href="' . admin_url('post.php?post=' . $activeCampaign->ID . '&action=edit') . '" />' . $activeCampaign->post_title . '</a>';
            echo '</div>';
        } else {
            echo '<br><label>' . CMPopUpBanners::__('No Campaign Pinned.') . ' </label>';
            echo '<div style="text-align: center;">';
            echo '<a href="' . admin_url('edit.php?post_type=cm-ad-item') . '" />' . CMPopUpBanners::__('Pin Campaign >>') . '</a>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Saves the information form the metabox in the post's meta
     * @param type $post_id
     */
    public static function savePostdata($post_id) {

        $doPreview = filter_input(INPUT_POST, 'wp-preview');
        if ($doPreview == 'dopreview') {
            return;
        }

        //if (!current_user_can('edit_post', $post_id) || wp_is_post_revision($post_id)) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $postType = isset($_POST['post_type']) ? $_POST['post_type'] : '';

        if (in_array($postType, self::getApplicablePostTypes())) {
            $show = ( isset($_POST[CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS])) ? $_POST[CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS] : '0';
            update_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS, $show);

            $customWidgetType = ( isset($_POST[CMPopUpBannersShared::CMPOPFLY_CUSTOM_WIDGET_TYPE])) ? $_POST[CMPopUpBannersShared::CMPOPFLY_CUSTOM_WIDGET_TYPE] : '0';
            update_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_WIDGET_TYPE, $customWidgetType);
        }

        if (in_array($postType, array(CMPopUpBannersShared::POST_TYPE))) {

            delete_option('cm-campaign-show-allpages');

            if (isset($_POST['_cm_advertisement_items_custom_fields']['cm-campaign-show-allpages'])) {

                /*
                 * if selected global, deselect all other global items
                 */
                if ($_POST['_cm_advertisement_items_custom_fields']['cm-campaign-show-allpages'] == 1) {
                    $postsArgs = array(
                        'post_type' => CMPopUpBannersShared::POST_TYPE
                    );
                    $updatePosts = get_posts($postsArgs);
                    if (!empty($updatePosts)) {
                        foreach ($updatePosts as $onePost) {
                            $serializedPostMeta = get_post_meta($onePost->ID, '_cm_advertisement_items_custom_fields');
                            $postMeta = maybe_unserialize($serializedPostMeta);
                            $postMeta[0]['cm-campaign-show-allpages'] = 0;
                            update_post_meta($onePost->ID, '_cm_advertisement_items_custom_fields', $postMeta);
                        }
                    }
                }

                /*
                 * end of only one active campaign
                 */
                $newGlobalHelpItemId = $_POST['_cm_advertisement_items_custom_fields']['cm-campaign-show-allpages'];
                $globalHelpItem = CMPopUpBannersShared::getGlobalHelpItem(true);

                $doPreview = filter_input(INPUT_POST, 'wp-preview');
                /*
                 * Trying to set another Help Item to show on all pages
                 */
                if (!$doPreview && !empty($newGlobalHelpItemId) && $globalHelpItem > 0 && $globalHelpItem !== $post_id) {
                    $url = esc_url(add_query_arg(array('warning' => 1), $_POST['_wp_http_referer']));
                    wp_safe_redirect($url);
                    exit();
                }
            }

            $args = array(
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'post_type'        => 'page',
                'suppress_filters' => true,
                'meta_query'       => array(
                    array(
                        'key'   => CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM,
                        'value' => $post_id,
                    ),
                )
            );
            $query = new \WP_Query($args);
            $pages = $query->get_posts();
            if (!empty($pages)) {
                foreach ($pages as $pageId) {
                    update_post_meta($pageId, CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM, '-1');
                }
            }

            $cmHelpItemOptions = filter_input(INPUT_POST, '_cm_advertisement_items_custom_fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($cmHelpItemOptions['cm-help-item-options'])) {
                foreach ($cmHelpItemOptions['cm-help-item-options'] as $key => $helpItemOption) {
                    $showHelpItemPageId = intval($helpItemOption['cm-help-item-url']);
                    if ($showHelpItemPageId) {
                        $result = update_post_meta($showHelpItemPageId, CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM, $post_id);
                    }
                }
            }

            /*
             * Workaround for saving activity dates.
             * New key in post meta is created because when tried to save
             * array in array by standard meta boxes functionality, data was filered away.
             */
            if (!empty($cmHelpItemOptions['cm-campaign-widget-activity-dates'])) {
                if (is_array($cmHelpItemOptions['cm-campaign-widget-activity-dates'])) {
                    $returnArray = array();
                    foreach ($cmHelpItemOptions['cm-campaign-widget-activity-dates'] AS $key => $value) {
                        foreach ($value AS $oneDateKey => $oneDateValue) {
                            $returnArray[$oneDateKey][$key] = $oneDateValue;
                        }
                    }
                    update_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY, serialize($returnArray));
                }
            } else {
                delete_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY);
            }

            /**
             * Activity days save
             */
            if (!empty($cmHelpItemOptions['cm-campaign-widget-activity-days'])) {
                if (is_array($cmHelpItemOptions['cm-campaign-widget-activity-days'])) {
                    update_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DAYS_META_KEY, serialize($cmHelpItemOptions['cm-campaign-widget-activity-days']));
                }
            } else {
                delete_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_ACTIVITY_DAYS_META_KEY);
            }

            if (!empty($cmHelpItemOptions['cm-campaign-user-type'])) {
                $userType = CMPopUpBannersShared::CMPOPFLY_CUSTOM_USER_TYPES_META_KEY;
                $value = $cmHelpItemOptions['cm-campaign-user-type'];
                update_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_USER_TYPES_META_KEY, $cmHelpItemOptions['cm-campaign-user-type']);
            } else {
                delete_post_meta($post_id, CMPopUpBannersShared::CMPOPFLY_CUSTOM_USER_TYPES_META_KEY);
            }

			if (isset($_POST['_cm_advertisement_items_custom_fields'])) {
				if(is_array($_POST['_cm_advertisement_items_custom_fields'])) {
					if(count($_POST['_cm_advertisement_items_custom_fields']) > 0) {
						$final_array = array();
						foreach($_POST['_cm_advertisement_items_custom_fields'] as $customFieldKey=>$customFieldValue) {
							if(!is_array($customFieldValue)) {
								$final_array[$customFieldKey] = cmpopupbanners_strip_tags($customFieldValue);
							} else {
								$final_inner_array = array();
								$counter = 0;
								foreach($customFieldValue as $customInnerFieldKey=>$customInnerFieldValue) {
									foreach($customInnerFieldValue as $customSecondInnerFieldKey=>$customSecondInnerFieldValue) {
										if($customSecondInnerFieldValue != '') {
											$final_inner_array[$counter][$customSecondInnerFieldKey] = $customSecondInnerFieldValue;
											$counter++;
										}
									}
								}
								$final_array[$customFieldKey] = $final_inner_array;
							}
						}
						update_post_meta($post_id, '_cm_advertisement_items_custom_fields', $final_array);
					}
				}
			}

        }

    }

    /**
     * Show the message
     * @global type $post
     * @return type
     */
    public static function showMessage() {
        global $post;

        if (empty($post)) {
            return;
        }

        $showWarning = filter_input(INPUT_GET, 'warning');
        if (in_array($post->post_type, array(CMPopUpBannersShared::POST_TYPE)) && $showWarning == '1') {
            $globalHelpItemId = CMPopUpBannersShared::getGlobalHelpItem();
            $url = esc_url(add_query_arg(array('post' => $globalHelpItemId, 'action' => 'edit'), admin_url('post.php')));

            cminds_show_message('One of the other <a href="' . $url . '" target="_blank">Help Items (edit)</a> is set to be displayed on every page. You can only have one "global" Help Item.', true);
        }
    }

    /**
     * Ajax action gets the AdDesigner
     */
    public static function getAddesigner() {
        include self::$viewsPath . 'addesigner.phtml';
        exit;
    }

    /**
     * Ajax action to register clicks
     */
    public static function registerClick() {
        $result = self::insertClickEvent(array(
                    'campaign_id' => filter_input(INPUT_POST, 'campaign_id'),
                    'banner_id'   => filter_input(INPUT_POST, 'banner_id'),
                    'amount'      => filter_input(INPUT_POST, 'amount')
        ));
        if (true == $result) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'error' => print_r($result, true)));
        }
        exit;
    }

    /**
     * Ajax action gets the statistics
     */
    public static function getStatistics() {
        $postData = filter_input_array(INPUT_POST);
        switch ($postData['type']) {
            case 'period' :
				//self::generatePeriodStatistics($postData);
                break;
            case 'daily' :
				//self::generateDailyStatistics($postData);
                break;
            case 'access_log' :
				//self::generateAccessLogStatistics($postData);
                break;
            default : break;
        }
        exit;
    }

    public static function generatePeriodStatistics($params) {
        if (empty($params['date_from']) && empty($params['date_to'])) {
            self::renderEmptyPage('Must specify the date range');
        }
        if (!empty($params['date_from'])) {
            $params['date_from'] = $params['date_from'] . ' 00:00:00';
        }
        if (!empty($params['date_to'])) {
            $params['date_to'] = $params['date_to'] . ' 23:59:59';
        }
        $where = '';
        $events = array();
        if (!empty($params['date_from']) && empty($params['date_to'])) {
            if (!self::checkDateFrom($params['date_from'])) {
                self::renderEmptyPage('Date "from" must be in the past');
            }
            $where = "ht.regdate > '" . esc_sql($params['date_from']) . "'";
        } elseif (empty($params['date_from']) && !empty($params['date_to'])) {
            $where = "ht.regdate < '" . esc_sql($params['date_to']) . "'";
        } elseif (!empty($params['date_from']) && !empty($params['date_to'])) {
            if (!self::checkDates($params['date_from'], $params['date_to'])) {
                self::renderEmptyPage('Date "from" must be before date "to"');
            }
            $where = "ht.regdate BETWEEN '" . esc_sql($params['date_from']) . "' AND '" . esc_sql($params['date_to']) . "'";
        }
        global $wpdb;
        $eventsClicks = $wpdb->get_results('SELECT ht.banner_id, sum(ht.amount) as amount_clicks FROM ' . CMPOPFLY_HISTORY_TABLE . ' AS ht' .
                ' WHERE ' . $where . " AND ht.event_type = 'cl'" .
                ' GROUP BY ht.banner_id'
        );
        $eventsImpressions = $wpdb->get_results('SELECT ht.banner_id, ht.campaign_id, sum(ht.amount) as amount_impressions, pt.post_title FROM ' . CMPOPFLY_HISTORY_TABLE . ' AS ht' .
                ' LEFT JOIN ' . CMPOPFLY_POST_TABLE . ' AS pt ON ht.campaign_id=pt.ID' .
                ' WHERE ' . $where . " AND event_type = 'im'" .
                ' GROUP BY ht.banner_id' .
                ' ORDER BY sum(ht.amount) DESC'
        );
        foreach ($eventsImpressions AS $oneImpression) {
            $oneImpression->amount_clicks = 0;
            foreach ($eventsClicks AS $oneClickKey => $oneClick) {
                if ($oneImpression->banner_id == $oneClick->banner_id) {
                    $oneImpression->amount_clicks = $oneClick->amount_clicks;
                    unset($eventsClicks[$oneClickKey]);
                }
            }
            $events[] = $oneImpression;
        }
        self::fillBannersDataArray($events);
        self::renderPeriodStatisticsDataTable($events);
    }

    private static function renderPeriodStatisticsDataTable(array $data = array()) {
        if (!empty($data)) {
            $result = "<table id='period-statistics-data-table'>";
            $result .= "<thead><tr><th>Campaign Name</th><th>Banner Id</th><th>Impressions</th><th>Clicks</th><th>Ratio</th></tr></thead>";
            $result .= "<tbody>";
            foreach ($data as $oneData) {
                $clicks_rate = ((int) $oneData->amount_clicks / (int) $oneData->amount_impressions) * 100;
                $result .= '<tr>'
                        . '<td><a href="' . admin_url('post.php') . '?post=' . $oneData->campaign_id . '&action=edit">' . $oneData->post_title . '</a></td>'
                        . '<td>' . $oneData->banner_id . '</td>'
                        . '<td>' . $oneData->amount_impressions . '</td>'
                        . '<td>' . $oneData->amount_clicks . '</td>'
                        . '<td>' . number_format($clicks_rate, 2, '.', '') . '%</td>'
                        . '</tr>';
            }
            $result .= "</tbody>";
            $result .= "</table>";
            echo json_encode(array('success' => true, 'content' => $result));
        } else {
            self::renderEmptyPage();
        }
    }

    public static function generateDailyStatistics($params) {
        if (empty($params['date_from']) && empty($params['date_to'])) {
            self::renderEmptyPage('Must specify the date range');
        }
        if (!empty($params['date_from'])) {
            $params['date_from'] = $params['date_from'] . ' 00:00:00';
        }
        if (!empty($params['date_to'])) {
            $params['date_to'] = $params['date_to'] . ' 23:59:59';
        }
        $where = '';
        $events = array();
        if (!empty($params['date_from']) && empty($params['date_to'])) {
            if (!self::checkDateFrom($params['date_from'])) {
                self::renderEmptyPage('Date "from" must be in the past');
            }
            $where = "ht.regdate > '" . esc_sql($params['date_from']) . "'";
        } elseif (empty($params['date_from']) && !empty($params['date_to'])) {
            $where = "ht.regdate < '" . esc_sql($params['date_to']) . "'";
        } elseif (!empty($params['date_from']) && !empty($params['date_to'])) {
            if (!self::checkDates($params['date_from'], $params['date_to'])) {
                self::renderEmptyPage('Date "from" must be before date "to"');
            }
            $where = "ht.regdate BETWEEN '" . esc_sql($params['date_from']) . "' AND '" . esc_sql($params['date_to']) . "'";
        }
        global $wpdb;
        $eventsClicks = $wpdb->get_results('SELECT ht.banner_id, sum(ht.amount) as amount_clicks, date(ht.regdate) AS regdate FROM ' . CMPOPFLY_HISTORY_TABLE . ' AS ht' .
                ' WHERE ' . $where . " AND ht.event_type = 'cl'" .
                ' GROUP BY date(ht.regdate), ht.banner_id' .
                ' ORDER BY ht.regdate DESC'
        );
        $eventsImpressions = $wpdb->get_results('SELECT ht.banner_id, ht.campaign_id, sum(ht.amount) as amount_impressions, pt.post_title, date(ht.regdate) AS regdate FROM ' . CMPOPFLY_HISTORY_TABLE . ' AS ht' .
                ' LEFT JOIN ' . CMPOPFLY_POST_TABLE . ' AS pt ON ht.campaign_id=pt.ID' .
                ' WHERE ' . $where . " AND event_type = 'im'" .
                ' GROUP BY date(ht.regdate), ht.banner_id' .
                ' ORDER BY ht.regdate DESC'
        );
        foreach ($eventsImpressions AS $oneImpression) {
            $oneImpression->amount_clicks = 0;
            foreach ($eventsClicks AS $oneClickKey => $oneClick) {
                if ($oneImpression->banner_id == $oneClick->banner_id && $oneImpression->regdate == $oneClick->regdate) {
                    $oneImpression->amount_clicks = $oneClick->amount_clicks;
                    unset($eventsClicks[$oneClickKey]);
                }
            }
            $events[] = $oneImpression;
        }
        self::fillBannersDataArray($events);
        self::renderDailyStatisticsDataTable($events);
    }

    private static function fillBannersDataArray($events) {
        global $wpdb;
        if (empty($events)) {
            return false;
        }
        foreach ($events as $oneEvent) {
            $campaignIds[$oneEvent->campaign_id] = $oneEvent->campaign_id;
        }
        $postIn = implode(', ', $campaignIds);
        $banners = $wpdb->get_results('SELECT post_id, meta_value FROM ' . CMPOPFLY_POST_META_TABLE .
                " WHERE meta_key = '_cm_advertisement_items' AND post_id IN (" . $postIn . ")"
        );
        foreach ($banners AS $oneBanner) {
            $unserializedBanners = maybe_unserialize($oneBanner->meta_value);
            self::$bannersDataArray[$oneBanner->post_id] = $unserializedBanners['cm-help-item-group'];
        }
    }

    public static function getBannerContent($campaignId, $bannerId) {
        if (empty(self::$bannersDataArray) || empty($campaignId) || empty($bannerId)) {
            return false;
        }
        if (isset(self::$bannersDataArray[$campaignId])) {
            foreach (self::$bannersDataArray[$campaignId] AS $oneBanner) {
                if ($oneBanner['banner-uuid'] == $bannerId) {
                    return preg_replace("/\"/", "'", $oneBanner['textarea']);
                }
            }
        } else {
            return false;
        }
    }

    private static function renderDailyStatisticsDataTable(array $data = array()) {
        if (!empty($data)) {
            $result = "<table id='daily-statistics-data-table'>";
            $result .= "<thead><tr><th>Campaign Name</th><th>Banner Id</th><th>Impressions</th><th>Clicks</th><th>Ratio</th><th>Date</th></tr></thead>";
            $result .= "<tbody>";
            foreach ($data as $oneData) {
                $clicks_rate = ((int) $oneData->amount_clicks / (int) $oneData->amount_impressions) * 100;
                $result .= '<tr>'
                        . '<td><a href="' . admin_url('post.php') . '?post=' . $oneData->campaign_id . '&action=edit">' . $oneData->post_title . '</a></td>'
                        . '<td>' . $oneData->banner_id . '</td>'
                        . '<td>' . $oneData->amount_impressions . '</td>'
                        . '<td>' . $oneData->amount_clicks . '</td>'
                        . '<td>' . number_format($clicks_rate, 2, '.', '') . '%</td>'
                        . '<td>' . date("m/d/Y", strtotime($oneData->regdate)) . '</td>'
                        . '</tr>';
            }
            $result .= "</tbody>";
            $result .= "</table>";
            echo json_encode(array('success' => true, 'content' => $result));
        } else {
            self::renderEmptyPage();
        }
    }

    private static function renderEmptyPage($message = '') {
        ob_start();
        include self::$viewsPath . 'statistics/no_data.phtml';
        $content = ob_get_clean();
        echo json_encode(array('success' => true, 'content' => $content));
        exit;
    }

    public static function generateAccessLogStatistics($params) {
        if (empty($params['date_from']) && empty($params['date_to'])) {
            self::renderEmptyPage('Must specify the date range');
        }
        if (!empty($params['date_from'])) {
            $params['date_from'] = $params['date_from'] . ' 00:00:00';
        }
        if (!empty($params['date_to'])) {
            $params['date_to'] = $params['date_to'] . ' 23:59:59';
        }
        $where = '';
        $events = array();
        $total = 0;
        $today = time();
        if (!empty($params['date_from']) && empty($params['date_to'])) {
            if (!self::checkDateFrom($params['date_from'])) {
                self::renderEmptyPage('Date "from" must be in the past');
            }
            $where = "ht.regdate > '" . esc_sql($params['date_from']) . "'";
            $params['date_to'] = date("Y-m-d");
        } elseif (empty($params['date_from']) && !empty($params['date_to'])) {
            $where = "ht.regdate < '" . esc_sql($params['date_to']) . "'";
            /*
             * take max 6 months before
             */
            $params['date_from'] = date("Y-m-d", ($today - (floor(YEAR_IN_SECONDS / 2))));
        } elseif (!empty($params['date_from']) && !empty($params['date_to'])) {
            if (!self::checkDates($params['date_from'], $params['date_to'])) {
                self::renderEmptyPage('Date "from" must be before date "to"');
            }
            $where = "ht.regdate BETWEEN '" . esc_sql($params['date_from']) . "' AND '" . esc_sql($params['date_to']) . "'";
        }
        if ($params['campaign_id'] != 0 && $params['campaign_id'] != '0') {
            $where .= " AND ht.campaign_id='" . $params['campaign_id'] . "'";
        }
        global $wpdb;
        $eventsClicks = $wpdb->get_results('SELECT ht.banner_id, sum(ht.amount) as amount_clicks, date(ht.regdate) AS regdate FROM ' . CMPOPFLY_HISTORY_TABLE . ' AS ht' .
                ' WHERE ' . $where . " AND ht.event_type = 'cl'" .
                ' GROUP BY date(ht.regdate)'
        );
        $eventsImpressions = $wpdb->get_results("SELECT ht.banner_id, ht.campaign_id, sum(ht.amount) as amount_impressions, pt.post_title, date(ht.regdate) AS regdate, DATE_FORMAT( ht.regdate,  '%m/%d/%Y' ) AS date_formatted FROM " . CMPOPFLY_HISTORY_TABLE . ' AS ht' .
                ' LEFT JOIN ' . CMPOPFLY_POST_TABLE . ' AS pt ON ht.campaign_id=pt.ID' .
                ' WHERE ' . $where . " AND event_type = 'im'" .
                ' GROUP BY date(ht.regdate)'
        );
        if (empty($eventsImpressions)) {
            self::renderEmptyPage();
        }
        foreach ($eventsImpressions AS $oneImpression) {
            $oneImpression->amount_clicks = 0;
            $total += $oneImpression->amount_impressions;
            foreach ($eventsClicks AS $oneClickKey => $oneClick) {
                if ($oneImpression->banner_id == $oneClick->banner_id && $oneImpression->regdate == $oneClick->regdate) {
                    $oneImpression->amount_clicks = $oneClick->amount_clicks;
                    $total += $oneClick->amount_clicks;
                    unset($eventsClicks[$oneClickKey]);
                }
            }
            $events[] = $oneImpression;
        }
        /*
         * Fill output array with date keys
         * Calculate number of days between date interval
         */
        switch ($params['group_by']) {
            case 'days' :
                $dateString = strtotime($params['date_from']);
                $outputEventsArray = array(date("m/d/Y", $dateString) => array('amount_impressions' => 0, 'amount_clicks' => 0));
                $numberOfDays = ceil((strtotime($params['date_to']) - strtotime($params['date_from'])) / DAY_IN_SECONDS);
                for ($i = 0, $ii = $numberOfDays; $i <= $ii; $i++) {
                    $curDateString = $dateString + (DAY_IN_SECONDS * $i);
                    $outputEventsArray[date("m/d/Y", $curDateString)] = array('amount_impressions' => 0, 'amount_clicks' => 0);
                }
                foreach ($events AS $oneEvent) {
                    $outputEventsArray[$oneEvent->date_formatted] = array(
                        'amount_impressions' => $oneEvent->amount_impressions,
                        'amount_clicks'      => $oneEvent->amount_clicks,
                    );
                }
                break;
            default : self::renderEmptyPage('Unsupported grouping type');
                break;
        }
        self::renderAccessLogStatisticsChart($outputEventsArray, $total);
    }

    private static function renderAccessLogStatisticsChart($events, $total) {
        $result = 'Total requests: ' . $total;
        $result .= "<script type=\"text/javascript\">\n";
        $result .= "var data = new Array();\n";
        $result .= 'data=[{"label": "Impressions", "data": [';
        foreach ($events as $time => $requests) {
            $requests_js[] = '["' . ((strtotime($time) * 1000) - 5000000) . '",' . $requests['amount_impressions'] . ']' . "\n";
        }
        $result .= implode(',', $requests_js) . ',' . ']}, {"label" : "clicks", "data" : [';
        $requests_js = array();
        foreach ($events as $time => $requests) {
            $requests_js[] = '["' . ((strtotime($time) * 1000) + 5000000) . '",' . $requests['amount_clicks'] . ']' . "\n";
        }
        $result .= implode(',', $requests_js) . ']}];' . "\n";
        $result .= "jQuery('#server_load_graph').show();\n";
        $result .= "draw_graph(data);";
        $result .= "\n</script>";
        $result .= '<div id="server_load_graph"></div>';
        echo json_encode(array('success' => true, 'content' => $result));
    }

    /*
     * function for inserting impressions
     */

    public static function insertImpressionEvent($data) {
        global $wpdb;
        if (!empty($data['campaign_id']) && !empty($data['banner_id'])
        ) {
            $result = $wpdb->query($wpdb->prepare('INSERT INTO ' . CMPOPFLY_HISTORY_TABLE .
                            ' SET event_type="im", '
                            . 'campaign_id=%d, '
                            . 'banner_id=%s, '
                            . 'referer_url=%s, '
                            . 'remote_ip=%s, '
                            . 'webpage_url=%s, '
                            . 'remote_country=%s, '
                            . 'remote_city=%s, '
                            . 'regdate=NOW()', $data['campaign_id'], $data['banner_id'], get_bloginfo('wpurl'), filter_input(INPUT_SERVER, 'REMOTE_ADDR'), filter_input(INPUT_SERVER, 'REQUEST_SCHEME') . '://' . filter_input(INPUT_SERVER, 'HTTP_HOST') . filter_input(INPUT_SERVER, 'REQUEST_URI'), "", ""
                    )
            );

            if (true != $result && $wpdb->last_error !== '') {
                $result = $wpdb->last_error;
            }
        }
        return $result;
    }

    /*
     * function for inserting clicks
     */

    public static function insertClickEvent($data) {
        global $wpdb;
        if (!empty($data['campaign_id']) && !empty($data['banner_id'])
        ) {
            $result = $wpdb->query($wpdb->prepare('INSERT INTO ' . CMPOPFLY_HISTORY_TABLE .
                            ' SET event_type="cl", '
                            . 'campaign_id=%d, '
                            . 'banner_id=%s, '
                            . 'amount=%d,'
                            . 'referer_url=%s, '
                            . 'remote_ip=%s, '
                            . 'webpage_url=%s, '
                            . 'remote_country=%s, '
                            . 'remote_city=%s, '
                            . 'regdate=NOW()', $data['campaign_id'], $data['banner_id'], $data['amount'], get_bloginfo('wpurl'), filter_input(INPUT_SERVER, 'REMOTE_ADDR'), filter_input(INPUT_SERVER, 'HTTP_REFERER'), "", ""
                    )
            );

            if (true != $result && $wpdb->last_error !== '') {
                $result = $wpdb->last_error;
            }
        }
        return $result;
    }

    public static function checkDateFrom($date = null) {
        if (!empty($date)) {
            $now = time();
            $dateString = strtotime($date);
            if ($dateString > $now) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    public static function checkDates($dateFrom = null, $dateTo = null) {
        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateStringFrom = strtotime($dateFrom);
            $dateStringTo = strtotime($dateTo);
            if ($dateStringFrom > $dateStringTo) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    static function clearPinnedPostsOnCampaignDelete($post_id) {
        $post_type = get_post_type($post_id);
        if ($post_type == CMPopUpBannersShared::POST_TYPE) {
            global $wpdb;
            $wpdb->query('
                UPDATE ' . CMPOPFLY_POST_META_TABLE . "
                    SET meta_value = -1
                    WHERE meta_key = '" . CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM . "'
                        AND meta_value = " . $post_id . ';');
            $wpdb->query('
                DELETE FROM ' . CMPOPFLY_HISTORY_TABLE . "
                    WHERE campaign_id = " . $post_id . ";");
            delete_option('cm-campaign-show-allpages');
        }
    }

    static function customListLabels($labels) {
        $labels->not_found = __('No campaigns found');
        return $labels;
    }

}