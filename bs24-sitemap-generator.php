<?php

/**
 * The plugin bootstrap file
 *
 * @since             1.0.0
 * @package           BS24_Sitemap
 *
 * @wordpress-plugin
 * Plugin Name:       BS24 Sitemap Generator
 * Description:       A sitemap generator plugin
 * Version:           1.0.0
 * Author:            Md Hiron Mia
 * Author URI:        https://hirondev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bs24-sitemap-generator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BS24_SITEMAP_VERSION', '1.0.0' );

/**
 * Define paths for the sitemap files
 */
define( 'BS24_SITEMAP_DIR', plugin_dir_path(__FILE__) );
define( 'BS24_SITEMAP_URL', plugin_dir_url(__FILE__) );
define('BS24_MAIN_SITEMAP', BS24_SITEMAP_DIR . 'sitemap/sitemap.xml');
define('BS24_POST_SITEMAP', BS24_SITEMAP_DIR . 'sitemap/post-sitemap.xml');
define('BS24_PAGE_SITEMAP', BS24_SITEMAP_DIR . 'sitemap/page-sitemap.xml');
define('BS24_JOBS_SITEMAP', BS24_SITEMAP_DIR . 'sitemap/jobs-sitemap.xml');
define('BS24_VIDEO_SITEMAP', BS24_SITEMAP_DIR . 'sitemap/videos-sitemap.xml');

//make display error 0
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	ini_set( 'display_errors', 0 ); // Disable error display
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bs24-sitemap-activator.php
 */
function activate_bs24_sitemap() {
	require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-activator.php';
	BS24_Sitemap_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bs24-sitemap-deactivator.php
 */
function deactivate_bs24_sitemap() {
	require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-deactivator.php';
	BS24_Sitemap_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bs24_sitemap' );
register_deactivation_hook( __FILE__, 'deactivate_bs24_sitemap' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bs24_sitemap() {

	$plugin = new BS24_Sitemap();
	$plugin->run();

}
run_bs24_sitemap();


