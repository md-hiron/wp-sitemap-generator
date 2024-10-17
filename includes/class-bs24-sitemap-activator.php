<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 */

 if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Your Name <email@example.com>
 */
class BS24_Sitemap_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//run rewrite rules during activation
		$sitemap_serve = new BS24_Sitemap_Serve();
		$sitemap_serve->add_rewrite_rules();

		flush_rewrite_rules();

		// event for create sitemap for daily basis
		if ( !wp_next_scheduled('bs24_sitemap_daily_sitemap_event') ) {
			wp_schedule_event(time() + 15, 'daily', 'bs24_sitemap_daily_sitemap_event');
		}
	}

}
