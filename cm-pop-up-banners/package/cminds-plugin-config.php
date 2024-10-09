<?php
ob_start();
include plugin_dir_path(__FILE__) . 'views/plugin_compare_table.php';
$plugin_compare_table = ob_get_contents();
ob_end_clean();
$cminds_plugin_config = array(
    'plugin-is-pro'                 => FALSE,
    'plugin-free-only'              => FALSE,
    'plugin-has-addons'             => FALSE,
    'plugin-version'                => '1.7.4',
    'plugin-abbrev'                 => 'cmpopfly',
    'plugin-short-slug'             => 'cmpopfly',
    'plugin-parent-short-slug'      => '',
    'plugin-affiliate'              => '',
    'plugin-redirect-after-install' => admin_url( 'admin.php?page=cm-popupflyin-settings' ),
    'plugin-settings-url'           => admin_url( 'admin.php?page=cm-popupflyin-settings' ),
    'plugin-show-guide'             => TRUE,
    'plugin-show-upgrade'           => TRUE,
    'plugin-show-upgrade-first'     => TRUE,
    'plugin-guide-text'             => '<div style="display:block">
        <ol>
            <li>Go to <strong>"Add New Campaign"</strong></li>
            <li>Fill the <strong>"Title"</strong> of the campaign and <strong>"Content"</strong> of one or many Advertisement Items</li>
            <li>(Only in Pro!) Click <strong>"Add Advertisement Item"</strong> to dynamically add more items</li>
            <li>Check <strong>"Show on every page"</strong></li>
            <li>Pick the <strong>"Selected banner"</strong> near the "Display method"</li>
            <li>Click <strong>"Publish" </strong> in the right column.</li>
            <li>Go to any page of your website</li>
            <li>Watch the banner with Advertisement Item</li>
            <li>Close the banner clicking "X" icon</li>
        </ol>
    </div>',
    'plugin-guide-video-height'     => 240,
    'plugin-guide-videos'           => array(
        array( 'title' => 'Installation tutorial', 'video_id' => '157541754' ),
    ),
	'plugin-upgrade-text'           => 'Our WordPress popup plugin helps you add responsive popup banners to your site with custom messages and effects.',
    'plugin-upgrade-text-list'      => array(
        array( 'title' => 'Creating Custom Banners', 'video_time' => '0:04' ),
        array( 'title' => 'Creating Random Banners', 'video_time' => '0:50' ),
        array( 'title' => 'Autoplay Video Banner', 'video_time' => '1:28' ),
        array( 'title' => 'Javascript Triggered Banner', 'video_time' => '2:01' ),
        array( 'title' => 'Using the Ad Designer', 'video_time' => '3:56' ),
        array( 'title' => 'Controling Delay and Interval', 'video_time' => '5:07' ),
        array( 'title' => 'Setting Display Effects', 'video_time' => '6:33' ),
        array( 'title' => 'Restrict Campaign By Period', 'video_time' => '7:11' ),
        array( 'title' => 'Statistics and Reports', 'video_time' => '8:00' ),
        array( 'title' => 'Target Users', 'video_time' => '9:06' ),
        array( 'title' => 'Restrict by Days of the Week', 'video_time' => '9:55' )
      ),
    'plugin-upgrade-video-height' => 240,
    'plugin-upgrade-videos'       => array(
        array( 'title' => 'PopUp Introduction', 'video_id' => '287417713' ),
    ),
    'plugin-file'                   => CMPOPFLY_PLUGIN_FILE,
    'plugin-dir-path'               => plugin_dir_path( CMPOPFLY_PLUGIN_FILE ),
    'plugin-dir-url'                => plugin_dir_url( CMPOPFLY_PLUGIN_FILE ),
    'plugin-basename'               => plugin_basename( CMPOPFLY_PLUGIN_FILE ),
    'plugin-icon'                   => '',
    'plugin-name'                   => CMPOPFLY_NAME,
    'plugin-license-name'           => CMPOPFLY_NAME,
    'plugin-slug'                   => '',
    'plugin-menu-item'              => CMPOPFLY_SLUG_NAME,
    'plugin-textdomain'             => CMPOPFLY_SLUG_NAME,
    'plugin-campign'                => '?utm_source=popupfree&utm_campaign=freeupgrade',
    'plugin-userguide-key'          => '2229-cm-pop-up-banners-cmpb-free-version-guide',
    'plugin-store-url'              => 'https://www.cminds.com/wordpress-plugins-library/cm-pop-up-banners-plugin-for-wordpress?utm_source=popupree&utm_campaign=freeupgrade&upgrade=1',
    'plugin-support-url'            => 'https://www.cminds.com/contact/',
    'plugin-video-tutorials-url'    => 'https://www.videolessonsplugin.com/video-lesson/lesson/popup-banners-plugin/',
    'plugin-review-url'             => 'https://www.cminds.com/wordpress-plugins-library/pop-up-banners-plugin-for-wordpress#reviews',
    'plugin-changelog-url'          => 'https://www.cminds.com/wordpress-plugins-library/pop-up-banners-plugin-for-wordpress#changelog',
    'plugin-licensing-aliases'      => array(),
    'plugin-compare-table'          => $plugin_compare_table,
);