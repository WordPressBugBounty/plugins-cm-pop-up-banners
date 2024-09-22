<?php
namespace com\cminds\popupfly;

use com\cminds\popupfly\CMPopUpBannersBackend as CMPopUpFlyInBackend;
use com\cminds\popupfly\CMPOPFLY_Settings;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class CMPopUpBannersShared {

    protected static $instance                   = NULL;
    public static $calledClassName;
    public static $lastProductQuery              = NULL;
    protected static $cssPath                    = NULL;
    protected static $jsPath                     = NULL;
    protected static $viewsPath                  = NULL;
    protected static $mediaPath                  = NULL;
    protected static $standardPopupSoundFilename = NULL;

    const POST_TYPE                               = 'cm-ad-item';
    const POST_TYPE_TEMPLATE                      = 'cm-ai-template';
    const POST_TYPE_TAXONOMY                      = 'cm-ai-category';
    const CMPOPFLY_SELECTED_AD_ITEM               = 'cmpub-selected-ai';
    const CMPOPFLY_SELECTED_AD_ITEM_OLD           = 'cm-selected-ai';
    const CMPOPFLY_SHOW_AD_ITEM                   = 'cm-show-ai';
    const CMPOPFLY_DISABLE_ADS                    = 'cm-disable-ai';
    const CMPOPFLY_CUSTOM_WIDGET_TYPE             = 'cmpopfly-custom-widget-type-hi';
    const CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY = '_cmpopfly-custom-activity_dates';
    const CMPOPFLY_CUSTOM_ACTIVITY_DAYS_META_KEY  = '_cmpopfly-custom-activity_days';
    const CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME = 'cmpopfly-all-unique-used-options';
    const CMPOPFLY_CUSTOM_USER_TYPES_META_KEY     = 'cmpopfly-custom-user_types';
	const CM_STAR_RATING                          =  'cm_star_rating';

    public static $x = 1;
    public static $widget;
    public static $widgetConfig;
    public static $widgetUnderlayType;
    public static $selectedCampaignBannerId;

    public static function instance() {
        $class = __CLASS__;
        if ( !isset( self::$instance ) && !( self::$instance instanceof $class ) ) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct() {
        if ( empty( self::$calledClassName ) ) {
            self::$calledClassName = __CLASS__;
        }

        self::$cssPath                    = CMPOPFLY_PLUGIN_URL . 'shared/assets/css/';
        self::$jsPath                     = CMPOPFLY_PLUGIN_URL . 'shared/assets/js/';
        self::$viewsPath                  = CMPOPFLY_PLUGIN_DIR . 'shared/views/';
        self::$mediaPath                  = CMPOPFLY_PLUGIN_URL . 'shared/assets/media/';
        self::$standardPopupSoundFilename = 'default_popup_sound.mp3';

        self::setupConstants();
        self::setupOptions();
        self::loadClasses();
        self::registerActions();
    }

    /**
     * Register the plugin's shared actions (both backend and frontend)
     */
    private static function registerActions() {
        add_shortcode( 'cminds_pro_ads', '__return_false' );
        add_filter( 'cmreg_registration_ajax_response', array( __CLASS__, 'cmpopfly_setup_cookie' ), 300, 2 );
        add_action( 'wp_loaded', array( __CLASS__, 'cmpopfly_check_cookie' ) );
        add_action( 'template_redirect', array( __CLASS__, 'setWidgetToDisplay' ) );
    }

    public static function setWidgetToDisplay() {
        global $post;
        /*
         * for normal page view
         */
        $widget = false;
        if ( !empty( $post ) ) {
            $postId = empty( $post->ID ) ? '' : $post->ID;
            $actual_url = ( isset($_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" :
                "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            if ( !empty( $postId ) && false !== (bool) strstr($actual_url, get_permalink())) {
                $widget = CMPopUpBannersBackend::getWidgetForPage( $postId );
            } else {
                $url    = '//' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
                $widget = CMPopUpBannersBackend::getWidgetForUrl( $url );
            }
        } elseif ( filter_input( INPUT_GET, 'campaign_id' ) ) {
            /*
             * for preview
             */
            $postId = filter_input( INPUT_GET, 'campaign_id' );
            $widget = get_post_meta( $postId );
        } else {
            $url    = '//' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
            $widget = CMPopUpBannersBackend::getWidgetForUrl( $url );
        }
        /*
         * if no widget or empty widget banners return no output
         */
        if ( !$widget || empty( $widget[ '_cm_advertisement_items' ] ) ) {
            self::$widget = false;
            return false;
        }

        self::$widget       = $widget;
        $widgetConfig       = maybe_unserialize( $widget[ '_cm_advertisement_items_custom_fields' ][ 0 ] );
        self::$widgetConfig = $widgetConfig;
    }

    /**
     * Check if we're right after registration (the cookie has been set)
     */
    public static function cmpopfly_check_cookie() {
        global $cmpopfly_reg_thank_you;

        if ( isset( $_COOKIE[ 'cmpopfly_reg_thank_you' ] ) ) {
            $name   = 'cmpopfly_reg_thank_you';
            $value  = '';
            $expire = time() - 1;
            $secure = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
            setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
            if ( SITECOOKIEPATH != COOKIEPATH ) {
                setcookie( $name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
            }
            $cmpopfly_reg_thank_you = true;
        } else {
            $cmpopfly_reg_thank_you = false;
        }
    }

    public static function cmpopfly_setup_cookie( $response, $userId ) {
        if ( isset( $response[ 'success' ] ) && $response[ 'success' ] ) {
            $name   = 'cmpopfly_reg_thank_you';
            $value  = 1;
            $expire = time() + 3600 * 24;
            $secure = ( 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME ) );
            setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
            if ( SITECOOKIEPATH != COOKIEPATH ) {
                setcookie( $name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
            }
        }
        return $response;
    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupConstants() {

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupOptions() {
        /*
         * Adding additional options
         */
        do_action( 'cmpopfly_setup_options' );
    }

    /**
     * Create taxonomies
     */
    public static function cmpopfly_create_taxonomies() {
        return;
    }

    /**
     * Load plugin's required classes
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function loadClasses() {
        /*
         * Load the file with shared global functions
         */
        include_once CMPOPFLY_PLUGIN_DIR . "shared/functions.php";
    }

    public function registerShortcodes() {
        return;
    }

    public function registerFilters() {
        return;
    }

    public static function initSession() {
        if ( !session_id() ) {
            session_start();
        }
    }

    /**
     * Create custom post type
     */
    public static function registerPostTypeAndTaxonomies() {
        return;
    }

    /**
     * Gets the list of the products
     * @param type $atts
     * @return type
     */
    public static function getItems( $atts = array() ) {
        $postTypes = array( self::POST_TYPE );

        $args = array(
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'post_type'        => $postTypes,
            'suppress_filters' => true
        );

        /*
         * Don't show paused products
         */
        if ( !empty( $atts[ 'paused' ] ) ) {
            $args[ 'meta_query' ] = array(
                'relation' => 'OR',
                array(
                    'key'   => 'CMPOPFLY_pause_prod',
                    'value' => '0',
                ),
                array(
                    'key'     => 'CMPOPFLY_pause_prod',
                    'value'   => '0',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        /*
         * Don't show paused products
         */
        if ( !empty( $atts[ 'from_edd' ] ) ) {
            $args[ 'meta_query' ] = array(
                'relation' => 'OR',
                array(
                    'key'   => 'CMPOPFLY_edd_product',
                    'value' => '1',
                )
            );
        }

        /*
         * Return in categories
         */
        if ( !empty( $atts[ 'cats' ] ) ) {
            $args[ 'tax_query' ] = array(
                array(
                    'taxonomy' => CMProductCatalogShared::POST_TYPE_TAXONOMY,
                    'terms'    => $atts[ 'cats' ],
                    'operator' => 'IN',
                    'field'    => 'slug',
                ),
            );
        }

        /*
         * Return with tags
         */
        if ( !empty( $atts[ 'tags' ] ) ) {
            $args[ 'tag_slug__in' ] = $atts[ 'tags' ];
        }

        /*
         * Return only products with given ids
         */
        if ( !empty( $atts[ 'item_ids' ] ) ) {
            $atts[ 'item_ids' ] = is_array( $atts[ 'item_ids' ] ) ? $atts[ 'item_ids' ] : array( $atts[ 'item_ids' ] );
            $args[ 'post__in' ] = $atts[ 'item_ids' ];
        }

        /*
         * Return only products which title/description includes the query
         */
        if ( !empty( $atts[ 'query' ] ) ) {
            $args[ 's' ] = $atts[ 'query' ];
        }

        $query                  = new \WP_Query( $args );
        /*
         * Store the query to save info about pagination
         */
        self::$lastProductQuery = $query;
        $items                  = $query->get_posts();

        return $items;
    }

    public static function getItem( $productIdName ) {
        return;
    }

    /**
     * Function returns the help item assigned to the page
     */
    public static function checkIfNotBlocked( $postId ) {
        $selectedHelpItem = get_post_meta( $postId, CMPopUpBannersShared::CMPOPFLY_DISABLE_ADS, true );
        return (bool) $selectedHelpItem;
    }

    /**
     * Function returns the help item assigned to the page
     */
    public static function getPostHelpItem( $postId ) {
        $selectedHelpItemOld = get_post_meta( $postId, CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM_OLD, true );
        $selectedHelpItem    = get_post_meta( $postId, CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM, true );
        /*
         * Moving the data from the old meta key to the new one
         */
        if ( !empty( $selectedHelpItemOld ) && empty( $selectedHelpItem ) ) {
            update_post_meta( $postId, CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM, $selectedHelpItemOld );
            delete_post_meta( $postId, CMPopUpBannersShared::CMPOPFLY_SELECTED_AD_ITEM_OLD );
            $selectedHelpItem = $selectedHelpItemOld;
        }
        return $selectedHelpItem;
    }

    /**
     * Function returns the help item which has the checkbox saying: "Show on all pages" selected, or FALSE
     */
    public static function getGlobalHelpItem( $onlyCleanValues = false ) {
		global $post;
		delete_option( 'cm-campaign-show-allpages' );
        $result = get_option( 'cm-campaign-show-allpages', FALSE );
        if ( !$result || $onlyCleanValues ) {
            $helpItems = self::getItems();
            foreach ( $helpItems as $helpItem ) {
                $helpItemMeta = self::getCampaignOptionsMeta( $helpItem->ID );
				//echo "<pre>"; print_r($helpItemMeta); echo "</pre>";
                if (isset($helpItemMeta['cm-campaign-show-allpages']) && $helpItemMeta['cm-campaign-show-allpages'] == 1) {
                    update_option( 'cm-campaign-show-allpages', $helpItem->ID );
                    $result = $helpItem->ID;
                    break;
                } else if(isset($helpItemMeta['cm-help-item-options'])) {
					foreach($helpItemMeta['cm-help-item-options'] as $links) {
						if($post) {
							if($links['cm-help-item-url'] == $post->ID && $helpItemMeta['cm-campaign-widget-disable'] == '0' && !(is_front_page() && is_home())) {
								update_option( 'cm-campaign-show-allpages', $helpItem->ID );
								$result = $helpItem->ID;
								break;
							}
						}
					}
				}
            }
        }
        return $result;
    }

    /*
     * function returns Campaign custom options
     */

    public static function getCampaignOptionsMeta( $id ) {
        $raw = get_post_meta( $id );
        return maybe_unserialize( $raw[ '_cm_advertisement_items_custom_fields' ][ 0 ] );
    }

    /**
     * Function returns the help item matching the pattern
     */
    public static function getHelpItemMatchingUrl( $url ) {
        $result    = FALSE;
        $helpItems = self::getItems();
        foreach ( $helpItems as $helpItem ) {
            $helpItemMeta = self::getCampaignOptionsMeta( $helpItem->ID );
            if ( !empty( $helpItemMeta[ 'cm-help-item-show-wildcard' ] ) && strstr( $url, $helpItemMeta[ 'cm-help-item-show-wildcard' ] ) !== FALSE ) {
                $result = $helpItem->ID;
                break;
            }
        }
        return $result;
    }

    /**
     * Function returns the help item for homepage
     */
    public static function getHelpItemForHomepage( $isHome ) {
        $result = FALSE;
        if ( !$isHome ) {
            return $result;
        }
        $helpItems = self::getItems();
        foreach ( $helpItems as $helpItem ) {
            $helpItemMeta = self::getCampaignOptionsMeta( $helpItem->ID );
            if ( !empty( $helpItemMeta[ 'cm-campaign-show-homepage' ] ) ) {
                $result = $helpItem->ID;
                break;
            }
        }
        return $result;
    }

    /**
     * Function returns the help item for homepage
     */
    public static function getHelpItemThankPopup() {
        global $cmpopfly_reg_thank_you;
        $result = FALSE;
        /*
         * We only search for the Thank You Popup if the cookie is set
         */
        if ( $cmpopfly_reg_thank_you ) {

            $helpItems = self::getItems();
            foreach ( $helpItems as $helpItem ) {
                $helpItemMeta = self::getCampaignOptionsMeta( $helpItem->ID );
                if ( !empty( $helpItemMeta[ 'cm-campaign-thank' ] ) && $helpItemMeta[ 'cm-campaign-thank' ] == '1' ) {
                    $result = $helpItem->ID;
                    break;
                }
            }
        }
        return $result;
    }

    public static function getExternalLinkIcon( $srcOnly = FALSE ) {
        $iconUrl = CMPOPFLY_PLUGIN_URL . 'shared/assets/images/external.png';
        $result  = $srcOnly ? $iconUrl : '<img src="' . $iconUrl . '" alt="External Link Icon" class="cmpopfly-external-link-icon" />';
        return $result;
    }

    public static function getWidgetOutput( $atts = array() ) {
        global $post, $cmpopfly_reg_thank_you;
        /*
         * We need to run it at least once
         */
        if ( self::$widget === null ) {
            CMPopUpBannersShared::setWidgetToDisplay();
        }
        $widgetOutput = '';
        $widget       = self::$widget;
        $widgetConfig = self::$widgetConfig;
        if ( !empty($widgetConfig[ 'cm-campaign-thank' ]) && $widgetConfig[ 'cm-campaign-thank' ] == '1' && !$cmpopfly_reg_thank_you ) {
            return $widgetOutput;
        }

        if ( !empty( $widgetConfig[ 'cm-campaign-widget-type' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-type' ] ) {
                case 'popup': $widgetOutput = self::getPopUpOutput();
                    break;
//                case 'flyin': $widgetOutput = self::getFlyingBottomOutput();
//                    break;
//                case 'full': $widgetOutput = self::getFullScreenOutput();
//                    break;
                default: $widgetOutput = FALSE;
                    break;
            }
        }

        return $widgetOutput;
    }

    static function isAutoSize( $width, $height ) {
        return 'auto' === trim( $width ) && 'auto' === trim( $height );
    }

    static function getBannerContent() {
        $preContent = maybe_unserialize( self::$widget[ '_cm_advertisement_items' ][ 0 ] );
        /*
         * switch for selected
         */
        if(!isset(self::$widgetConfig[ 'cm-campaign-display-method' ])){
            self::$widgetConfig[ 'cm-campaign-display-method' ] = null;
        }
        switch ( self::$widgetConfig[ 'cm-campaign-display-method' ] ) {
            case 'selected' : 
                $adKey = self::$widgetConfig[ 'cm-campaign-widget-selected-banner' ];
                break;
            default:
                $adKey = null;
                break;
        }
        /*
         * do impression event call
         */
        if ( !CMPopUpFlyInBackend::$isPreview ) {

        }
        if ( empty( $adKey ) ) {
            $adKey = key( $preContent[ 'cm-help-item-group' ] );
        }
        self::$selectedCampaignBannerId = $preContent[ 'cm-help-item-group' ][ $adKey ][ 'banner-uuid' ];
		global $wp_embed;
        return do_shortcode($wp_embed->run_shortcode( $preContent[ 'cm-help-item-group' ][ $adKey ][ 'textarea' ] ));
    }

    static function getFlyingBottomOutput() {
        wp_enqueue_script( 'cmpopfly-flying-bottom-core', self::$jsPath . 'flyingBottom.js', array( 'jquery' ), CMPOPFLY_VERSION );
        wp_enqueue_script( 'cmpopfly-flying-custom', self::$jsPath . 'flyingCustom.js', array( 'jquery', 'cmpopfly-flying-bottom-core' ), CMPOPFLY_VERSION );
        wp_enqueue_script( 'scrollspy', self::$jsPath . 'scrollspy.js', array( 'jquery' ), CMPOPFLY_VERSION );
        wp_enqueue_style( 'cm_ouibounce_css', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/ouibounce.css', array(), CMPOPFLY_VERSION );

        $widget                 = self::$widget;
        $widgetConfig           = self::$widgetConfig;

		$final_array = array();
		foreach($widgetConfig as $customFieldKey=>$customFieldValue) {
			if(!is_array($customFieldValue)) {
				$final_array[$customFieldKey] = cmpopupbanners_strip_tags($customFieldValue);
			} else {
				$final_array[$customFieldKey] = $customFieldValue;
			}
		}
		$widgetConfig = $final_array;

		wp_localize_script(
        'cmpopfly-flying-custom', 'WidgetConf', array(
            'closeTime' => isset( $widgetConfig[ 'cm-campaign-widget-close-time' ] ) ? $widgetConfig[ 'cm-campaign-widget-close-time' ] : null )
        );

        $minDeviceWidth         = ((!empty( $widgetConfig[ 'cm-campaign-min-device-width' ] ) ? (intval( $widgetConfig[ 'cm-campaign-min-device-width' ] )) : 0));
        $maxDeviceWidth         = ((!empty( $widgetConfig[ 'cm-campaign-max-device-width' ] ) ? (intval( $widgetConfig[ 'cm-campaign-max-device-width' ] )) : 0));
        $width                  = ((!empty( $widgetConfig[ 'cm-campaign-widget-width' ] ) ? $widgetConfig[ 'cm-campaign-widget-width' ] : 'auto'));
        $height                 = ((!empty( $widgetConfig[ 'cm-campaign-widget-height' ] ) ? $widgetConfig[ 'cm-campaign-widget-height' ] : 'auto'));
        $mobileWidthBoundary    = CMPOPFLY_Settings::get( CMPOPFLY_Settings::OPTION_MOBILE_MAX_WIDTH, '400px' );
        $mobileWidth            = ((!empty( $widgetConfig[ 'cm-campaign-widget-mobile-width' ] ) ? $widgetConfig[ 'cm-campaign-widget-mobile-width' ] : 'auto'));
        $mobileHeight           = ((!empty( $widgetConfig[ 'cm-campaign-widget-mobile-height' ] ) ? $widgetConfig[ 'cm-campaign-widget-mobile-height' ] : 'auto'));
        $zindex                 = ((!empty( $widgetConfig[ 'cm-campaign-zindex' ] ) ? (intval( $widgetConfig[ 'cm-campaign-zindex' ] )) : ('100')));
        $padding                = ((!empty( $widgetConfig[ 'cm-campaign-padding' ] ) ? (intval( $widgetConfig[ 'cm-campaign-padding' ] ) . 'px') : ('10px')));
        $background             = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-color' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-color' ]) : ('#f0f1f2')));
        $backgroundImage        = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-image' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-image' ]) : ('')));
        $backgroundUrl          = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-url' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-url' ]) : ('')));
        $userShowMethod         = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval' ] ) ? ($widgetConfig[ 'cm-campaign-widget-interval' ]) : ('always')));
        $resetTime              = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval_reset_time' ] ) ? ($widgetConfig[ 'cm-campaign-widget-interval_reset_time' ]) : (7)));
        $delay                  = (!empty( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) && (intval( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) > 0)) ? (intval( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) * 1000) : (0);
        $fireMethod             = (!empty( $widgetConfig[ 'cm-campaign-fire-method' ] )) ? ($widgetConfig[ 'cm-campaign-fire-method' ]) : ('pageload');
        $countingMethod         = (!empty( $widgetConfig[ 'cm-campaign-clicks-counting-method' ] )) ? ($widgetConfig[ 'cm-campaign-clicks-counting-method' ]) : ('one');
        $soundMethod            = (!empty( $widgetConfig[ 'cm-campaign-sound-effect-type' ] )) ? ($widgetConfig[ 'cm-campaign-sound-effect-type' ]) : ('none');
        $customSoundPath        = (!empty( $widgetConfig[ 'cm-campaign-sound-effect-custom-path' ] )) ? ($widgetConfig[ 'cm-campaign-sound-effect-custom-path' ]) : ('');
        $standardSound          = self::$mediaPath . self::$standardPopupSoundFilename;
        $inactivityTime         = ((!empty( $widgetConfig[ 'cm-campaign-fire-method-inactive-time-delay' ] ) ? ($widgetConfig[ 'cm-campaign-fire-method-inactive-time-delay' ]) : (10)));
        $bottomPageFireDistance = ((!empty( $widgetConfig[ 'cm-campaign-widget-fire-method-bottom-page-fire-distance' ] ) ? ($widgetConfig[ 'cm-campaign-widget-fire-method-bottom-page-fire-distance' ]) : (100)));
        $fixedNumberOfTimes     = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval_fixed_number_show_times' ] ) ? (intval( $widgetConfig[ 'cm-campaign-widget-interval_fixed_number_show_times' ] )) : (5)));
        if ( FALSE === strpos( $bottomPageFireDistance, 'px' ) ) {
            /*
             * Remove and readd the "px"
             */
            $bottomPageFireDistance = str_replace( 'px', '', $bottomPageFireDistance ) . 'px';
        }

        if ( FALSE === strpos( $width, '%' ) && FALSE === strpos( $width, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $width = str_replace( 'px', '', $width ) . 'px';
        }

        if ( FALSE === strpos( $height, '%' ) && FALSE === strpos( $height, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $height = str_replace( 'px', '', $height ) . 'px';
        }

        if ( FALSE === strpos( $mobileWidthBoundary, '%' ) && FALSE === strpos( $mobileWidthBoundary, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileWidthBoundary = str_replace( 'px', '', $mobileWidthBoundary ) . 'px';
        }

        if ( FALSE === strpos( $mobileWidth, '%' ) && FALSE === strpos( $mobileWidth, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileWidth = str_replace( 'px', '', $mobileWidth ) . 'px';
        }

        if ( FALSE === strpos( $mobileHeight, '%' ) && FALSE === strpos( $mobileHeight, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileHeight = str_replace( 'px', '', $mobileHeight ) . 'px';
        }

        /*
         * New feature background image
         */
        if ( !empty( $backgroundImage ) ) {
            $backgroundImage = 'background-image: url(\'' . $backgroundImage . '\');';
            $backgroundImage .= 'background-size: 100% 100%;';
        }

        /*
         * Add support for transparent backgrounds
         */
        if ( 'transparent' !== $background && !preg_match( "/#/", $background ) ) {
            $background = '#' . $background;
        }
        if ( !empty( $widgetConfig[ 'cm-campaign-widget-shape' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-shape' ] ) {
                case 'rounded' : $banner_edges = '4px';
                    break;
                case 'sharp' : $banner_edges = '0px';
                    break;
                default : $banner_edges = '4px';
            }
        } else {
            $banner_edges = '4px';
        }

        if ( !empty( $widgetConfig[ 'cm-campaign-widget-show-effect' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-show-effect' ] ) {
                case 'popin' : $show_effect = 'popin 1.0s';
                    break;
                case 'bounce' : $show_effect = 'bounce 1.0s';
                    break;
                case 'shake' : $show_effect = 'shake 1.0s';
                    break;
                case 'flash' : $show_effect = 'flash 0.5s';
                    break;
                case 'tada' : $show_effect = 'tada 1.5s';
                    break;
                case 'swing' : $show_effect = 'swing 1.0s';
                    break;
                case 'rotateIn' : $show_effect = 'rotateIn 1.0s';
                    break;
                default : $show_effect = 'popin 1.0s';
            }
        } else {
            $show_effect = 'popin 1.0s;';
        }
        $custom_css = '
            #flyingBottomAd {
            padding: ' . $padding . ';
            z-index: ' . $zindex . ';
            border-radius: ' . $banner_edges . ' 0 0;
            -moz-border-radius: ' . $banner_edges . ' 0 0;
            -webkit-border-radius: ' . $banner_edges . ' 0 0;
            background: ' . $background . ';
			' . $backgroundImage . '
            box-shadow: 0 0 20px rgba(0,0,0,.2);
            width: ' . $width . ';
            height: ' . $height . ';
            position: fixed;
            bottom: 0;
            right: 0;
			max-width: 85%;
			max-height: 85%;
            -webkit-backface-visibility: visible!important;
            -ms-backface-visibility: visible!important;
            backface-visibility: visible!important;
            -webkit-animation: ' . $show_effect . ';
            -moz-animation: ' . $show_effect . ';
            -o-animation: ' . $show_effect . ';
            animation: ' . $show_effect . ';
            -webkit-transition: bottom .5s ease,background-position .5s ease;
            transition: bottom .5s ease,background-position .5s ease;
        }';

        $current_banner = !empty( $widgetConfig[ 'cm-campaign-widget-selected-banner' ] ) ? $widgetConfig[ 'cm-campaign-widget-selected-banner' ] : 0;
        $current_item   = maybe_unserialize( self::$widget[ '_cm_advertisement_items' ][ 0 ] );
        $current_item   = $current_item[ 'cm-help-item-group' ][ $current_banner ][ 'banner-uuid' ];

        $current_banner = $current_banner + 1;
        $current_banner .= ' (' . $current_item . ')';

        $custom_css .= '#ouibounce-modal .modal .modal-body * {
                    max-width: 100%;
					max-height: 100%;
                }';
        $custom_css .= '#ouibounce-modal .modal .modal-body *:not(iframe) {
                    height: auto;
                }';
        $custom_css .= '#ouibounce-modal .modal .modal-body iframe {
                    display: flex;
                    align-items: center;
                    margin-bottom: 0;
                }';
        if ( $fireMethod == 'pageBottom' ) {
            $custom_css .= 'body {
                        position: relative!important;
                    }
                    #cm-pop-up-banners-scrollspy-marker{
                        width: 1px;
                        height: 1px;
                        background: none!important;
                        position: absolute;
                        bottom: ' . $bottomPageFireDistance . '
                    }
                    ';
        }

        if ( $mobileWidthBoundary ) {
            $custom_css .= '@media (max-width: ' . $mobileWidthBoundary . ') {#flyingBottomAd {width: ' . $mobileWidth . '; height: ' . $mobileHeight . ';} }';
        }

        $additionalClass = '';
        $bannerLink      = '';

        if ( !empty( $backgroundUrl ) ) {
            $bannerLink      = '<a class="cmpopfly-fullbody-link" href="' . esc_url($backgroundUrl) . '" target="_blank"></a>';
            $additionalClass = 'linked';
        }

        wp_add_inline_style( 'cm_ouibounce_css', $custom_css );

        $scriptData[ 'content' ]                = preg_replace( "/'/", "\"", self::getBannerContent() );
        $scriptData[ 'bannerLink' ]             = $bannerLink;
        $scriptData[ 'additionalClass' ]        = $additionalClass;
        $scriptData[ 'showMethod' ]             = $userShowMethod;
        $scriptData[ 'resetTime' ]              = $resetTime;
        $scriptData[ 'secondsToShow' ]          = $delay;
        $scriptData[ 'fireMethod' ]             = $fireMethod;
        $scriptData[ 'minDeviceWidth' ]         = (int) $minDeviceWidth;
        $scriptData[ 'maxDeviceWidth' ]         = (int) $maxDeviceWidth;
        $scriptData[ 'ajaxClickUrl' ]           = admin_url( 'admin-ajax.php' . '?action=cm_popupflyin_register_click' );
        $scriptData[ 'campaign_id' ]            = (isset( self::$widget[ 'campaign_id' ] ) ? self::$widget[ 'campaign_id' ] : '');
        $scriptData[ 'banner_id' ]              = $current_banner;
        $scriptData[ 'enableStatistics' ]       = self::enableStatistics();
        $scriptData[ 'countingMethod' ]         = $countingMethod;
        $scriptData[ 'soundMethod' ]            = $soundMethod;
        $scriptData[ 'customSoundPath' ]        = $customSoundPath;
        $scriptData[ 'standardSound' ]          = $standardSound;
        $scriptData[ 'inactivityTime' ]         = $inactivityTime;
        $scriptData[ 'showFixedNumberOfTimes' ] = $fixedNumberOfTimes;

        wp_localize_script( 'cmpopfly-flying-custom', 'flyin_custom_data', $scriptData );
        /*
         * initialize js watchers
         */
        if ( !CMPopUpBannersBackend::$isPreview ) {

        }
		update_option( 'cm-campaign-show-allpages', FALSE);
    }

    static function getPopUpOutput() {
        $widget       = self::$widget;
        $widgetConfig = self::$widgetConfig;
		
		$final_array = array();
		foreach($widgetConfig as $customFieldKey=>$customFieldValue) {
			if(!is_array($customFieldValue)) {
				$final_array[$customFieldKey] = cmpopupbanners_strip_tags($customFieldValue);
			} else {
				$final_array[$customFieldKey] = $customFieldValue;
			}
		}
		$widgetConfig = $final_array;

        wp_enqueue_script( 'cmpopfly-popup-core', self::$jsPath . 'ouibounce.js', array( 'jquery' ), CMPOPFLY_VERSION );
        wp_enqueue_script( 'cmpopfly-popup-custom', self::$jsPath . 'popupCustom.js', array( 'jquery', 'cmpopfly-popup-core' ), CMPOPFLY_VERSION );
        wp_enqueue_script( 'scrollspy', self::$jsPath . 'scrollspy.js', array( 'jquery' ), CMPOPFLY_VERSION );
        wp_enqueue_style( 'cm_ouibounce_css', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/ouibounce.css', array(), CMPOPFLY_VERSION );
        wp_localize_script(
        'cmpopfly-popup-custom', 'WidgetConf', array(
            'closeTime' => isset( $widgetConfig[ 'cm-campaign-widget-close-time' ] ) ? $widgetConfig[ 'cm-campaign-widget-close-time' ] : null )
        );

        /*
         * banner config resolve
         */
        $width                  = ((!empty( $widgetConfig[ 'cm-campaign-widget-width' ] ) ? $widgetConfig[ 'cm-campaign-widget-width' ] : 'auto'));
        $height                 = ((!empty( $widgetConfig[ 'cm-campaign-widget-height' ] ) ? $widgetConfig[ 'cm-campaign-widget-height' ] : 'auto'));
        $mobileWidthBoundary    = '400px';
        $mobileWidth            = ((!empty( $widgetConfig[ 'cm-campaign-widget-mobile-width' ] ) ? $widgetConfig[ 'cm-campaign-widget-mobile-width' ] : 'auto'));
        $mobileHeight           = ((!empty( $widgetConfig[ 'cm-campaign-widget-mobile-height' ] ) ? $widgetConfig[ 'cm-campaign-widget-mobile-height' ] : 'auto'));
        $background             = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-color' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-color' ]) : ('#f0f1f2')));
        $backgroundImage        = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-image' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-image' ]) : ('')));
        $backgroundUrl          = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-url' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-url' ]) : ('')));
        $userShowMethod         = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval' ] ) ? ($widgetConfig[ 'cm-campaign-widget-interval' ]) : ('always')));
        $underlayType           = ((!empty( $widgetConfig[ 'cm-campaign-widget-underlay-type' ] ) ? ($widgetConfig[ 'cm-campaign-widget-underlay-type' ]) : ('dark')));
        $resetTime              = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval_reset_time' ] ) ? ($widgetConfig[ 'cm-campaign-widget-interval_reset_time' ]) : (7)));
        $delay                  = (!empty( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) && (intval( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) > 0)) ? (intval( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) * 1000) : (0);
        $centerVertically       = ((!empty( $widgetConfig[ 'cm-campaign-widget-center-vertically' ] ) ? ($widgetConfig[ 'cm-campaign-widget-center-vertically' ]) : false));
        $centerHorizontally     = ((!empty( $widgetConfig[ 'cm-campaign-widget-center-horizontally' ] ) ? ($widgetConfig[ 'cm-campaign-widget-center-horizontally' ]) : false));
        $minDeviceWidth         = ((!empty( $widgetConfig[ 'cm-campaign-min-device-width' ] ) ? (intval( $widgetConfig[ 'cm-campaign-min-device-width' ] )) : 0));
        $maxDeviceWidth         = ((!empty( $widgetConfig[ 'cm-campaign-max-device-width' ] ) ? (intval( $widgetConfig[ 'cm-campaign-max-device-width' ] )) : 0));
        $fireMethod             = (!empty( $widgetConfig[ 'cm-campaign-fire-method' ] )) ? ($widgetConfig[ 'cm-campaign-fire-method' ]) : ('pageload');
        $padding                = ((!empty( $widgetConfig[ 'cm-campaign-padding' ] ) ? (intval( $widgetConfig[ 'cm-campaign-padding' ] ) . 'px') : ('10px')));
        $countingMethod         = (!empty( $widgetConfig[ 'cm-campaign-clicks-counting-method' ] )) ? ($widgetConfig[ 'cm-campaign-clicks-counting-method' ]) : ('one');
        $soundMethod            = (!empty( $widgetConfig[ 'cm-campaign-sound-effect-type' ] )) ? ($widgetConfig[ 'cm-campaign-sound-effect-type' ]) : ('none');
        $customSoundPath        = (!empty( $widgetConfig[ 'cm-campaign-sound-effect-custom-path' ] )) ? ($widgetConfig[ 'cm-campaign-sound-effect-custom-path' ]) : ('');
        $standardSound          = self::$mediaPath . self::$standardPopupSoundFilename;
        $inactivityTime         = ((!empty( $widgetConfig[ 'cm-campaign-fire-method-inactive-time-delay' ] ) ? ($widgetConfig[ 'cm-campaign-fire-method-inactive-time-delay' ]) : (10)));
        $bottomPageFireDistance = ((!empty( $widgetConfig[ 'cm-campaign-widget-fire-method-bottom-page-fire-distance' ] ) ? ($widgetConfig[ 'cm-campaign-widget-fire-method-bottom-page-fire-distance' ]) : (100)));
        $fixedNumberOfTimes     = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval_fixed_number_show_times' ] ) ? (intval( $widgetConfig[ 'cm-campaign-widget-interval_fixed_number_show_times' ] )) : (5)));
        $closeOnUnderlayClick     = ((isset( $widgetConfig[ 'cm-campaign-widget-close-on-underlay-click' ] ) ?  $widgetConfig[ 'cm-campaign-widget-close-on-underlay-click' ]  : 1));
        if ( FALSE === strpos( $bottomPageFireDistance, 'px' ) ) {
            /*
             * Remove and readd the "px"
             */
            $bottomPageFireDistance = str_replace( 'px', '', $bottomPageFireDistance ) . 'px';
        }

        if ( FALSE === strpos( $width, '%' ) && FALSE === strpos( $width, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $width = str_replace( 'px', '', $width ) . 'px';
        }

        if ( FALSE === strpos( $height, '%' ) && FALSE === strpos( $height, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $height = str_replace( 'px', '', $height ) . 'px';
        }

        if ( FALSE === strpos( $mobileWidthBoundary, '%' ) && FALSE === strpos( $mobileWidthBoundary, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileWidthBoundary = str_replace( 'px', '', $mobileWidthBoundary ) . 'px';
        }

        if ( FALSE === strpos( $mobileWidth, '%' ) && FALSE === strpos( $mobileWidth, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileWidth = str_replace( 'px', '', $mobileWidth ) . 'px';
        }

        if ( FALSE === strpos( $mobileHeight, '%' ) && FALSE === strpos( $mobileHeight, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileHeight = str_replace( 'px', '', $mobileHeight ) . 'px';
        }

        /*
         * New feature background image
         */
        if ( !empty( $backgroundImage ) ) {
            $backgroundImage = 'background-image: url(\'' . $backgroundImage . '\');';
            $backgroundImage .= 'background-size: 100% 100%;';
        }

        /*
         * Allow for transparent background
         */
        if ( FALSE === strpos( $background, "#" ) && 'transparent' !== $background ) {
            $background = '#' . $background;
        }
        switch ( $underlayType ) {
            case 'dark' : $underlayColor = 'rgba(0,0,0,0.5)';
                break;
            case 'light' : $underlayColor = 'rgba(0,0,0,0.2)';
                break;
            default : $underlayColor = 'rgba(0,0,0,0.5)';
                break;
        }
        if ( !empty( $widgetConfig[ 'cm-campaign-widget-shape' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-shape' ] ) {
                case 'rounded' : $banner_edges = '4px';
                    break;
                case 'sharp' : $banner_edges = '0px';
                    break;
                default : $banner_edges = '4px';
            }
        } else {
            $banner_edges = '4px';
        }

        if ( !empty( $widgetConfig[ 'cm-campaign-widget-show-effect' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-show-effect' ] ) {
                case 'popin' : $show_effect = 'popin 1.0s';
                    break;
                case 'bounce' : $show_effect = 'bounce 1.0s';
                    break;
                case 'shake' : $show_effect = 'shake 1.0s';
                    break;
                case 'flash' : $show_effect = 'flash 0.5s';
                    break;
                case 'tada' : $show_effect = 'tada 1.5s';
                    break;
                case 'swing' : $show_effect = 'swing 1.0s';
                    break;
                case 'rotateIn' : $show_effect = 'rotateIn 1.0s';
                    break;
                default : $show_effect = 'popin 1.0s';
            }
        } else {
            $show_effect = 'popin 1.0s;';
        }

        $additional_css = '';
        if ( $centerVertically ) {
            $additional_css .= 'align-items: center;';
        }
        if ( $centerHorizontally ) {
            $additional_css .= 'justify-content: center;';
        }
        /*
         * add custom html content filter
         */
        self::$widgetUnderlayType = $underlayType;
        $custom_css               = '
            #ouibounce-modal .modal {
                    width: ' . $width . ';
                    height: ' . $height . ';
                    padding: ' . $padding . ';
                    background-color: ' . $background . ';
					' . $backgroundImage . '
                    z-index: 20;
                    position: relative;
                    margin: auto;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
					display: flex;
					overflow: visible;
					opacity: 1;
					max-width: 85%;
					max-height: 85%;
                    border-radius: ' . $banner_edges . ';
					-webkit-animation: ' . $show_effect . ';
					-moz-animation: ' . $show_effect . ';
					-o-animation: ' . $show_effect . ';
					animation: ' . $show_effect . ';
					' . $additional_css . '
                  }'
        . (($underlayType != 'no') ? ('#ouibounce-modal .underlay {background-color: ' . $underlayColor . ';}') : (""))
        . (($minDeviceWidth && strpos( $minDeviceWidth, 'px' )) ? ('@media (max-width: ' . $minDeviceWidth . ') {#ouibounce-modal.cm-popup-modal {display: none !important;}}') : (''))
        . (($maxDeviceWidth && strpos( $maxDeviceWidth, 'px' )) ? ('@media (min-width: ' . $maxDeviceWidth . ') {#ouibounce-modal.cm-popup-modal {display: none !important;}}') : (''));

        $custom_css .= '#ouibounce-modal .modal .modal-body * {
                    max-width: 100%;
					max-height: 100%;
                }';
        $custom_css .= '#ouibounce-modal .modal .modal-body *:not(iframe) {
                    height: auto;
                }';
        $custom_css .= '#ouibounce-modal .modal .modal-body iframe {
                    display: flex;
                    align-items: center;
                }';
        $custom_css .= '#ouibounce-modal .modal.linked { cursor: pointer; }';

        if ( $mobileWidthBoundary ) {
            $custom_css .= '@media (max-width: ' . $mobileWidthBoundary . ') {#ouibounce-modal .modal {width: ' . $mobileWidth . '; height: ' . $mobileHeight . ';} }';
        }

        $additionalClass = self::isAutoSize( $width, $height ) ? 'auto-size' : '';

        if ( $fireMethod == 'pageBottom' ) {
            $custom_css .= 'body {
                        position: relative!important;
                    }
                    #cm-pop-up-banners-scrollspy-marker{
                        width: 1px;
                        height: 1px;
                        background: none!important;
                        position: absolute;
                        bottom: ' . $bottomPageFireDistance . '
                    }
                    ';
        }

        $additionalModalClass = '';
        $bannerLinkStart      = '';
        $bannerLinkEnd        = '';
        $bannerContent        = preg_replace( "/\"/", "'", self::getBannerContent() );
        if ( !empty( $backgroundUrl ) ) {
            $bannerLinkStart      = '<a class="cmpopfly-fullbody-link" href="' . esc_url($backgroundUrl) . '" target="_blank"></a>';
            $bannerLinkEnd        = '';
            $additionalModalClass = 'linked';
        }

        wp_add_inline_style( 'cm_ouibounce_css', $custom_css );
        $content = '<div id="ouibounce-modal" class="cm-popup-modal">
                ' . (($underlayType != 'no') ? ('<div class="underlay"></div>') : ("")) . '
                ' . $bannerLinkStart . '
                ' . $bannerLinkEnd . '
                <div class="modal ' . $additionalModalClass . '">
                <div id="close_button" class="popupflyin-close-button"></div>
                  <div class="modal-body popupflyin-clicks-area ' . $additionalClass . '">' . wp_kses_post($bannerContent) . '</div>
                </div>
              </div>';

        $current_banner = !empty( $widgetConfig[ 'cm-campaign-widget-selected-banner' ] ) ? $widgetConfig[ 'cm-campaign-widget-selected-banner' ] : 0;
        $current_item   = maybe_unserialize( self::$widget[ '_cm_advertisement_items' ][ 0 ] );
        $current_item   = $current_item[ 'cm-help-item-group' ][ $current_banner ][ 'banner-uuid' ];

        $current_banner = $current_banner + 1;
        $current_banner .= ' (' . $current_item . ')';

        $scriptData                             = array();
        $scriptData[ 'content' ]                = $content;
        $scriptData[ 'showMethod' ]             = $userShowMethod;
        $scriptData[ 'resetTime' ]              = $resetTime;
        $scriptData[ 'secondsToShow' ]          = $delay;
        $scriptData[ 'minDeviceWidth' ]         = (int) $minDeviceWidth;
        $scriptData[ 'maxDeviceWidth' ]         = (int) $maxDeviceWidth;
        $scriptData[ 'fireMethod' ]             = $fireMethod;
        $scriptData[ 'ajaxClickUrl' ]           = admin_url( 'admin-ajax.php' . '?action=cm_popupflyin_register_click' );
        $scriptData[ 'campaign_id' ]            = (isset( self::$widget[ 'campaign_id' ] ) ? self::$widget[ 'campaign_id' ] : '');
        $scriptData[ 'banner_id' ]              = $current_banner;
        $scriptData[ 'enableStatistics' ]       = self::enableStatistics();
        $scriptData[ 'countingMethod' ]         = $countingMethod;
        $scriptData[ 'soundMethod' ]            = $soundMethod;
        $scriptData[ 'customSoundPath' ]        = $customSoundPath;
        $scriptData[ 'standardSound' ]          = $standardSound;
        $scriptData[ 'closeOnUnderlayClick' ]   = (bool)$closeOnUnderlayClick;
        $scriptData[ 'inactivityTime' ]         = $inactivityTime;
        $scriptData[ 'showFixedNumberOfTimes' ] = $fixedNumberOfTimes;

        //wp_localize_script( 'cmpopfly-popup-custom', 'popup_custom_data', $scriptData );
		wp_add_inline_script( 'cmpopfly-popup-custom', 'var popup_custom_data = ' . wp_json_encode($scriptData ) . ';' );
		
        /*
         * initialize js watchers
         */
        if ( !CMPopUpBannersBackend::$isPreview ) {

        }
		update_option( 'cm-campaign-show-allpages', FALSE);
    }

    static function initializeWatchers( $widgetConfig ) {

    }

    public static function giveUniqueId() {
        $allExistingIds = get_option( self::CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME, '' );
        if ( empty( $allExistingIds ) ) {
            $newId    = self::giveNewUniqueId();
            $optArray = array( $newId );
            update_option( self::CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME, serialize( $optArray ) );
            return $newId;
        } else {
            $allOptions = unserialize( $allExistingIds );
            while ( in_array( $newId      = self::giveNewUniqueId(), $allOptions ) ) {

            }
            $allOptions[] = $newId;
            update_option( self::CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME, serialize( $allOptions ) );
            return $newId;
        }
        return false;
    }

    private static function giveNewUniqueId() {
        return floor( (microtime( 1 ) * floor( rand( 1, 10 ) * rand( 1, 10 ) )) / floor( rand( 1, 10 ) * rand( 1, 10 ) ) );
    }

    static function getFullScreenOutput() {
        $widget       = self::$widget;
        $widgetConfig = self::$widgetConfig;

		$final_array = array();
		foreach($widgetConfig as $customFieldKey=>$customFieldValue) {
			if(!is_array($customFieldValue)) {
				$final_array[$customFieldKey] = cmpopupbanners_strip_tags($customFieldValue);
			} else {
				$final_array[$customFieldKey] = $customFieldValue;
			}
		}
		$widgetConfig = $final_array;

        wp_enqueue_script( 'cmpopfly-popup-core', self::$jsPath . 'ouibounce.js', array( 'jquery' ) );
        wp_enqueue_script( 'cmpopfly-popup-custom', self::$jsPath . 'popupCustom.js', array( 'jquery', 'cmpopfly-popup-core' ), '456' );
        wp_enqueue_script( 'scrollspy', self::$jsPath . 'scrollspy.js', array( 'jquery' ) );
        wp_enqueue_style( 'cm_ouibounce_css', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/ouibounce.css' );
        wp_localize_script(
        'cmpopfly-popup-custom', 'WidgetConf', array(
            'closeTime' => isset( $widgetConfig[ 'cm-campaign-widget-close-time' ] ) ? $widgetConfig[ 'cm-campaign-widget-close-time' ] : null )
        );

        /*
         * banner config resolve
         */
        $width                  = ((!empty( $widgetConfig[ 'cm-campaign-widget-width' ] ) ? $widgetConfig[ 'cm-campaign-widget-width' ] : 'auto'));
        $height                 = ((!empty( $widgetConfig[ 'cm-campaign-widget-height' ] ) ? $widgetConfig[ 'cm-campaign-widget-height' ] : 'auto'));
        $mobileWidthBoundary    = '400px';
        $mobileWidth            = ((!empty( $widgetConfig[ 'cm-campaign-widget-mobile-width' ] ) ? $widgetConfig[ 'cm-campaign-widget-mobile-width' ] : 'auto'));
        $mobileHeight           = ((!empty( $widgetConfig[ 'cm-campaign-widget-mobile-height' ] ) ? $widgetConfig[ 'cm-campaign-widget-mobile-height' ] : 'auto'));
        $background             = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-color' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-color' ]) : ('#f0f1f2')));
        $backgroundImage        = ((!empty( $widgetConfig[ 'cm-campaign-widget-background-image' ] ) ? ($widgetConfig[ 'cm-campaign-widget-background-image' ]) : ('')));
        $userShowMethod         = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval' ] ) ? ($widgetConfig[ 'cm-campaign-widget-interval' ]) : ('always')));
        $underlayType           = ((!empty( $widgetConfig[ 'cm-campaign-widget-underlay-type' ] ) ? ($widgetConfig[ 'cm-campaign-widget-underlay-type' ]) : ('dark')));
        $resetTime              = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval_reset_time' ] ) ? ($widgetConfig[ 'cm-campaign-widget-interval_reset_time' ]) : (7)));
        $delay                  = (!empty( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) && (intval( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) > 0)) ? (intval( $widgetConfig[ 'cm-campaign-widget-delay-to-show' ] ) * 1000) : (0);
        $centerVertically       = ((!empty( $widgetConfig[ 'cm-campaign-widget-center-vertically' ] ) ? ($widgetConfig[ 'cm-campaign-widget-center-vertically' ]) : false));
        $centerHorizontally     = ((!empty( $widgetConfig[ 'cm-campaign-widget-center-horizontally' ] ) ? ($widgetConfig[ 'cm-campaign-widget-center-horizontally' ]) : false));
        $minDeviceWidth         = ((!empty( $widgetConfig[ 'cm-campaign-min-device-width' ] ) ? (intval( $widgetConfig[ 'cm-campaign-min-device-width' ] )) : 0));
        $maxDeviceWidth         = ((!empty( $widgetConfig[ 'cm-campaign-max-device-width' ] ) ? (intval( $widgetConfig[ 'cm-campaign-max-device-width' ] )) : 0));
        $fireMethod             = (!empty( $widgetConfig[ 'cm-campaign-fire-method' ] )) ? ($widgetConfig[ 'cm-campaign-fire-method' ]) : ('pageload');
        $padding                = ((!empty( $widgetConfig[ 'cm-campaign-padding' ] ) ? (intval( $widgetConfig[ 'cm-campaign-padding' ] ) . 'px') : ('10px')));
        $countingMethod         = (!empty( $widgetConfig[ 'cm-campaign-clicks-counting-method' ] )) ? ($widgetConfig[ 'cm-campaign-clicks-counting-method' ]) : ('one');
        $soundMethod            = (!empty( $widgetConfig[ 'cm-campaign-sound-effect-type' ] )) ? ($widgetConfig[ 'cm-campaign-sound-effect-type' ]) : ('none');
        $customSoundPath        = (!empty( $widgetConfig[ 'cm-campaign-sound-effect-custom-path' ] )) ? ($widgetConfig[ 'cm-campaign-sound-effect-custom-path' ]) : ('');
        $standardSound          = self::$mediaPath . self::$standardPopupSoundFilename;
        $inactivityTime         = ((!empty( $widgetConfig[ 'cm-campaign-fire-method-inactive-time-delay' ] ) ? ($widgetConfig[ 'cm-campaign-fire-method-inactive-time-delay' ]) : (10)));
        $bottomPageFireDistance = ((!empty( $widgetConfig[ 'cm-campaign-widget-fire-method-bottom-page-fire-distance' ] ) ? ($widgetConfig[ 'cm-campaign-widget-fire-method-bottom-page-fire-distance' ]) : (100)));
        $fixedNumberOfTimes     = ((!empty( $widgetConfig[ 'cm-campaign-widget-interval_fixed_number_show_times' ] ) ? (intval( $widgetConfig[ 'cm-campaign-widget-interval_fixed_number_show_times' ] )) : (5)));
        $closeOnUnderlayClick     = ((isset( $widgetConfig[ 'cm-campaign-widget-close-on-underlay-click' ] ) ?  $widgetConfig[ 'cm-campaign-widget-close-on-underlay-click' ]  : 1));

        if ( FALSE === strpos( $bottomPageFireDistance, 'px' ) ) {
            /*
             * Remove and readd the "px"
             */
            $bottomPageFireDistance = str_replace( 'px', '', $bottomPageFireDistance ) . 'px';
        }

        if ( FALSE === strpos( $width, '%' ) && FALSE === strpos( $width, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $width = str_replace( 'px', '', $width ) . 'px';
        }

        if ( FALSE === strpos( $height, '%' ) && FALSE === strpos( $height, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $height = str_replace( 'px', '', $height ) . 'px';
        }

        if ( FALSE === strpos( $mobileWidthBoundary, '%' ) && FALSE === strpos( $mobileWidthBoundary, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileWidthBoundary = str_replace( 'px', '', $mobileWidthBoundary ) . 'px';
        }

        if ( FALSE === strpos( $mobileWidth, '%' ) && FALSE === strpos( $mobileWidth, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileWidth = str_replace( 'px', '', $mobileWidth ) . 'px';
        }

        if ( FALSE === strpos( $mobileHeight, '%' ) && FALSE === strpos( $mobileHeight, 'auto' ) ) {
            /*
             * Remove and readd the "px"
             */
            $mobileHeight = str_replace( 'px', '', $mobileHeight ) . 'px';
        }
        /*
         * New feature background image
         */
        if ( !empty( $backgroundImage ) ) {
            $backgroundImage = 'background-image: url(\'' . $backgroundImage . '\');';
            $backgroundImage .= 'background-size: 100% 100%;';
        }

        /*
         * Allow for transparent background
         */
        if ( FALSE === strpos( $background, "#" ) && 'transparent' !== $background ) {
            $background = '#' . $background;
        }
        switch ( $underlayType ) {
            case 'dark' : $underlayColor = 'rgba(0,0,0,0.5)';
                break;
            case 'light' : $underlayColor = 'rgba(0,0,0,0.2)';
                break;
            default : $underlayColor = 'rgba(0,0,0,0.5)';
                break;
        }
        if ( !empty( $widgetConfig[ 'cm-campaign-widget-shape' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-shape' ] ) {
                case 'rounded' : $banner_edges = '4px';
                    break;
                case 'sharp' : $banner_edges = '0px';
                    break;
                default : $banner_edges = '4px';
            }
        } else {
            $banner_edges = '4px';
        }

        if ( !empty( $widgetConfig[ 'cm-campaign-widget-show-effect' ] ) ) {
            switch ( $widgetConfig[ 'cm-campaign-widget-show-effect' ] ) {
                case 'popin' : $show_effect = 'popin 1.0s';
                    break;
                case 'bounce' : $show_effect = 'bounce 1.0s';
                    break;
                case 'shake' : $show_effect = 'shake 1.0s';
                    break;
                case 'flash' : $show_effect = 'flash 0.5s';
                    break;
                case 'tada' : $show_effect = 'tada 1.5s';
                    break;
                case 'swing' : $show_effect = 'swing 1.0s';
                    break;
                case 'rotateIn' : $show_effect = 'rotateIn 1.0s';
                    break;
                default : $show_effect = 'popin 1.0s';
            }
        } else {
            $show_effect = 'popin 1.0s;';
        }

        $additional_css = '';
        if ( $centerVertically ) {
            $additional_css .= 'align-items: center;';
        }
        if ( $centerHorizontally ) {
            $additional_css .= 'justify-content: center;';
        }
        /*
         * add custom html content filter
         */
        self::$widgetUnderlayType = $underlayType;
        $custom_css               = '
            #ouibounce-modal .modal {
                    width: ' . $width . ';
                    min-height: ' . $height . ';
                    height: ' . $height . ';
                    padding: ' . $padding . ';
                    background-color: ' . $background . ';
					' . $backgroundImage . '
                    z-index: 20;
                    position: absolute;
                    margin: auto;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
					display: flex;
					overflow: visible;
					opacity: 1;
					max-width: 85% !important;
					max-height: 85% !important;
                    border-radius: ' . $banner_edges . ';
					-webkit-animation: ' . $show_effect . ';
					-moz-animation: ' . $show_effect . ';
					-o-animation: ' . $show_effect . ';
					animation: ' . $show_effect . ';
					' . $additional_css . '
                  }'
        . (($underlayType != 'no') ? ('#ouibounce-modal .underlay {background-color: ' . $background . ';}') : (""))
        . (($minDeviceWidth && strpos( $minDeviceWidth, 'px' )) ? ('@media (max-width: ' . $minDeviceWidth . ') {#ouibounce-modal.cm-popup-modal {display: none !important;}}') : (''));

        $custom_css .= '#ouibounce-modal .modal .modal-body * {
                    max-width: 100%;
					max-height: 100%;
                }';
        $custom_css .= '#ouibounce-modal .modal .modal-body *:not(iframe) {
                    height: auto;
                }';
        $custom_css .= '#ouibounce-modal .modal .modal-body iframe {
                    display: flex;
                    align-items: center;
                }';
        $custom_css .= '.popupflyin-close-button#close_button{
                    top: 2em;
                    right: 2em;
                }';

        $additionalClass = self::isAutoSize( $width, $height ) ? 'auto-size' : '';

        if ( $fireMethod == 'pageBottom' ) {
            $custom_css .= 'body {
                        position: relative!important;
                    }
                    #cm-pop-up-banners-scrollspy-marker{
                        width: 1px;
                        height: 1px;
                        background: none!important;
                        position: absolute;
                        bottom: ' . $bottomPageFireDistance . '
                    }
                    ';
        }

        if ( $mobileWidthBoundary ) {
            $custom_css .= '@media (max-width: ' . $mobileWidthBoundary . ') {#ouibounce-modal .modal {width: ' . $mobileWidth . '; height: ' . $mobileHeight . ';} }';
        }

        wp_add_inline_style( 'cm_ouibounce_css', $custom_css );
		
		$bannerContent = preg_replace( "/\"/", "'", self::getBannerContent() );
        $content = '<div id="ouibounce-modal" class="cm-popup-modal">
                ' . (($underlayType != 'no') ? ('<div class="underlay"></div>') : ("")) . '
                <div class="modal">
                  <div class="modal-body popupflyin-clicks-area ' . $additionalClass . '">' . wp_kses_post($bannerContent) . '</div>
                </div>
                <div id="close_button" class="popupflyin-close-button">
              </div></div>';

        $current_banner = !empty( $widgetConfig[ 'cm-campaign-widget-selected-banner' ] ) ? $widgetConfig[ 'cm-campaign-widget-selected-banner' ] : 0;
        $current_item   = maybe_unserialize( self::$widget[ '_cm_advertisement_items' ][ 0 ] );
        $current_item   = $current_item[ 'cm-help-item-group' ][ $current_banner ][ 'banner-uuid' ];

        $current_banner = $current_banner + 1;
        $current_banner .= ' (' . $current_item . ')';

        $scriptData                             = array();
        $scriptData[ 'content' ]                = $content;
        $scriptData[ 'showMethod' ]             = $userShowMethod;
        $scriptData[ 'resetTime' ]              = $resetTime;
        $scriptData[ 'secondsToShow' ]          = $delay;
        $scriptData[ 'minDeviceWidth' ]         = (int) $minDeviceWidth;
        $scriptData[ 'maxDeviceWidth' ]         = (int) $maxDeviceWidth;
        $scriptData[ 'fireMethod' ]             = $fireMethod;
        $scriptData[ 'ajaxClickUrl' ]           = admin_url( 'admin-ajax.php' . '?action=cm_popupflyin_register_click' );
        $scriptData[ 'campaign_id' ]            = (isset( self::$widget[ 'campaign_id' ] ) ? self::$widget[ 'campaign_id' ] : '');
        $scriptData[ 'banner_id' ]              = $current_banner;
        $scriptData[ 'enableStatistics' ]       = self::enableStatistics();
        $scriptData[ 'countingMethod' ]         = $countingMethod;
        $scriptData[ 'soundMethod' ]            = $soundMethod;
        $scriptData[ 'customSoundPath' ]        = $customSoundPath;
        $scriptData[ 'standardSound' ]          = $standardSound;
        $scriptData[ 'closeOnUnderlayClick' ]   = (bool)$closeOnUnderlayClick;
        $scriptData[ 'inactivityTime' ]         = $inactivityTime;
        $scriptData[ 'showFixedNumberOfTimes' ] = $fixedNumberOfTimes;

        //wp_localize_script( 'cmpopfly-popup-custom', 'popup_custom_data', $scriptData );
		wp_add_inline_script( 'cmpopfly-popup-custom', 'var popup_custom_data = ' . wp_json_encode($scriptData ) . ';' );
		
        /*
         * initialize js watchers
         */
        if ( !CMPopUpBannersBackend::$isPreview ) {

        }
		update_option( 'cm-campaign-show-allpages', FALSE);
    }

    protected static function enableStatistics() {
        $statistics = CMPOPFLY_Settings::get('enable_statistics');
        return (bool) $statistics;
    }

}