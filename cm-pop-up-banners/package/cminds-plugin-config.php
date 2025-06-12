<?php
ob_start();
include plugin_dir_path(__FILE__) . 'views/plugin_compare_table.php';
$plugin_compare_table = ob_get_contents();
ob_end_clean();
$cminds_plugin_config = array(
    'plugin-is-pro'                 => FALSE,
    'plugin-free-only'              => FALSE,
    'plugin-has-addons'             => TRUE,
    'plugin-version'                => '1.8.4',
	'plugin-addons'        => array(
		array(
			'title' => 'Ad Changer Manager',
			'description' => 'Manage banner ad campaigns with the WordPress ad management plugin. Display ads via shortcodes or widgets and control how banners rotate.',
			'link' => 'https://wordpress.org/plugins/cm-ad-changer/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPAdManagerAndServerS.png',
		),
		array(
			'title' => 'Context Product Recommendations',
			'description' => 'Display recommended products on your website post or pages based on the content of the post.',
			'link' => 'https://wordpress.org/plugins/cm-context-related-product-recommendations/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPProductRecommendationsS.png',
		),
		array(
			'title' => 'FAQ Plugin',
			'description' => 'Create and manage a user-friendly FAQ section on your site with this FAQ plugin. Answer common questions and improve user experience.',
			'link' => 'https://wordpress.org/plugins/cm-faq/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPFAQS.png',
		),
		array(
			'title' => 'Tooltip Glossary Plugin',
			'description' => 'Create a WordPress glossary, encyclopedia, or dictionary of terms and display responsive tooltips on hover.',
			'link' => 'https://wordpress.org/plugins/enhanced-tooltipglossary/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPTooltipGlossaryS.png',
		),
		array(
			'title' => 'Curated List Manager',
			'description' => 'Create and manage curated lists with this content curation plugin. Share & Organize content, resources, links, images and engage your audience.',
			'link' => 'https://wordpress.org/plugins/cm-curated-list-manager/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPCuratedListManagerS.png',
		),
		array(
			'title' => 'Table of Contents Plugin',
			'description' => 'Create and display a table of contents for your posts and pages. Improve navigation with an easy-to-use TOC generator.',
			'link' => 'https://wordpress.org/plugins/cm-table-of-content/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPTableOfContentS.png',
		),
	),
	'plugin-specials'        => array(
		array(
			'title' => 'RSS Post Importer Plugin',
			'description' => 'Support importing and displaying external posts using RSS, Atom feeds and scraping tool to your WordPress site.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/rss-post-importer-plugin-wordpress-creativeminds/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPRSSPostImporterS.png',
		),
		array(
			'title' => 'Reviews and Rating Plugin',
			'description' => 'Allow visitors and users to submit reviews and ratings, and display them on any product, posts, or pages.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/customer-reviews-plugin-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPCustomerReviewsS.png',
		),
		array(
			'title' => 'Booking Calendar',
			'description' => 'Enable customers to effortlessly schedule appointments and make payments directly through your website.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/schedule-appointments-manage-bookings-plugin-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBookingCalendarS.png',
		),
		array(
			'title' => 'Download Manager Plugin',
			'description' => 'Download Manager plugin provides a secure file-sharing directory for easy uploading, downloading, and sharing of files, videos, and images.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/downloadsmanager/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPDownloadManagerS.png',
		),
		array(
			'title' => 'MicroPayments Digital Wallet',
			'description' => 'MicroPayments establishes a digital wallet system, allowing users to seamlessly exchange points for goods, gift points, or directly purchase points using credit cards.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/micropayments/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/MicropaymentsS.png',
		),
		array(
			'title' => 'Registration and Invitation Codes',
			'description' => 'Adds a registration and login popup to your WP site. Supports invitation codes, email verification and assign user roles.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/registration-and-invitation-codes-plugin-for-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPUserRegistrationAndInvitationCodesS.png',
		),
	),
	'plugin-bundles'        => array(
		array(
			'title' => '99+ Free Pass Plugins Suite',
			'description' => 'Get all CM 99+ WordPress plugins and addons. Includes unlimited updates and one year of priority support.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/cm-wordpress-plugins-yearly-membership/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundleWPSuiteS.png',
		),
		array(
			'title' => 'Essential Publishing Plugin Package',
			'description' => 'Enhance your WordPress publishing with a bundle of seven plugins that elevate content generation, presentation, and user engagement on your site.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/essential-wordpress-publishing-tools-bundle/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundlePublishingS.png',
		),
		array(
			'title' => 'Essential Content Marketing Tools',
			'description' => 'Enhance your WordPress content marketing with seven plugins for improved content generation, presentation, and user engagement.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/essential-wordpress-content-marketing-tools-bundle/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundleContentS.png',
		),
		array(
			'title' => 'Essential Security Plugins',
			'description' => 'Enhance your WordPress security with a bundle of five plugins that provide additional ways to protect your content and site from spammers, hackers, and exploiters.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/essential-wordpress-security-tools-plugin-bundle/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundleSecurityS.png',
		),
	),
	'plugin-services'        => array(
		array(
			'title' => 'WordPress Custom Hourly Support',
			'description' => 'Hire our expert WordPress developers on an hourly basis, offering a-la-carte service to craft your custom WordPress solution.',
			'link' => 'https://www.cminds.com/wordpress-services/wordpress-custom-hourly-support-package/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesHourlySupportS.png',
		),
		array(
			'title' => 'Performance and Optimization Analysis',
			'description' => 'Receive a comprehensive review of your WordPress website with optimization suggestions to enhance its speed and performance.',
			'link' => 'https://www.cminds.com/wordpress-services/wordpress-performance-and-speed-optimization-analysis-service/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesPerformanceS.png',
		),
		array(
			'title' => 'WordPress Plugin Installation',
			'description' => 'We offer professional installation and configuration of plugins or add-ons on your site, tailored to your specified requirements.',
			'link' => 'https://www.cminds.com/wordpress-services/plugin-installation-service-for-wordpress-by-creativeminds/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesExtensionInstallationS.png',
		),
		array(
			'title' => 'WordPress Consulting',
			'description' => 'Purchase consulting hours to receive assistance in designing or planning your WordPress solution. Our expert consultants are here to help bring your vision to life.',
			'link' => 'https://www.cminds.com/wordpress-services/consulting-planning-hourly-support-service-wordpress-creativeminds/#description',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesConsultingS.png',
		),
	),
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