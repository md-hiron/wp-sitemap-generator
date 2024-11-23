<?php

/**
 * Fired during plugin deactivation
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
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Your Name <email@example.com>
 */
class BS24_Sitemap_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		wp_clear_scheduled_hook('bs24_sitemap_one_time_sitemap_generation_event');
		wp_clear_scheduled_hook('bs24_sitemap_daily_sitemap_event');
		wp_clear_scheduled_hook('retry_post_sitemap_generation');
		wp_clear_scheduled_hook('bs24_sitemap_one_time_video_sitemap_generation_event');
		wp_clear_scheduled_hook('bs24_sitemap_daily_video_sitemap_event');
		wp_clear_scheduled_hook('retry_video_sitemap_generation');

		flush_rewrite_rules();
	}

}
