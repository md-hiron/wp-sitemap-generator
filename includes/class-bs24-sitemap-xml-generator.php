<?php

/**
 * The class used for generate xml files
 *
 * @since      1.0.0
 *
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 */

 if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The class used for generate xml files
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Md Hiron
 */
class BS24_Sitemap_XML_Generator {

	/**
	 * Generate sitemap
	 *
	 * This method will be used for generate single sitemap
	 *
     * @param post_type string need the post type to generate specific sitemap
     * @param file_path string need the file path to generate the file
	 * @since    1.0.0
	 */
	public function generate_sitemap( $post_type, $file_path ) {

        if( empty( $post_type ) || empty( $file_path ) ){
			return false;
		}

		// Start time
		$start_time = microtime(true);

		// Increase memory limit and timeout to prevent errors
		@ini_set('memory_limit', '512M');
		@ini_set('max_execution_time', 300); // 5 minutes
	
		// Your sitemap generation code...
	
		// Check memory usage
		$memory_used = memory_get_usage(true);
		$this->log_sitemap_status($post_type . ' sitemap Memory used: ' . size_format($memory_used));
	
		// Check execution time
		$execution_time = microtime(true) - $start_time;
		$this->log_sitemap_status($post_type . ' sitemap Execution time: ' . $execution_time . ' seconds.');
	
	
		// If you need to explicitly throw an error for testing
		if ($execution_time > 290) {
			error_log('Warning: Execution time nearing limit.');
		}
	
		if ($memory_used > (512 * 1024 * 1024)) { // 512 MB
			error_log('Warning: Memory usage nearing limit.');
		}

		register_shutdown_function(function() {
			$error = error_get_last();
			if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
				error_log('Fatal error during '. $post_type .' sitemap generation: ' . $error['message']);
			}
		});
	
		// Directory path for sitemaps
		$sitemap_dir = BS24_SITEMAP_DIR . 'sitemap';
	
		// Check if the 'sitemap' directory exists, if not, create it
		if (!file_exists($sitemap_dir)) {
			if (!mkdir($sitemap_dir, 0755, true)) { // Create the directory with proper permissions
				error_log("Failed to create directory: $sitemap_dir. Check permissions.");
				return false;
			}
		}
	
		$posts_per_page = 500;
		$paged = 1;
	
		// Query the total number of posts once, without pagination
		$query_all_posts = new WP_Query( array(
			'post_type'      => sanitize_text_field( $post_type ),
			'post_status'    => 'publish',
			'posts_per_page' => -1, // Get all posts to count the total
			'fields'         => 'ids'
		));
	
		$new_file_path = $sitemap_dir . '/' . $file_path;
	
		// Get the total number of posts
		$total_posts = $query_all_posts->found_posts;
	
		// Create the DOMDocument instance for generating the XML sitemap
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;
	
		// Add the XML stylesheet processing instruction
		$stylesheet = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . BS24_SITEMAP_URL . 'xslt/sitemap.xsl"');
		$dom->appendChild($stylesheet);
	
		// Create the <urlset> element and add attributes
		$urlset = $dom->createElement('urlset');
		$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$dom->appendChild($urlset);
	
		// If no posts found, log error and exit
		if ($total_posts == 0) {
			// Log the event but still create the empty XML file
			error_log("No posts found for post type: $post_type. Creating an empty sitemap.");
			
			// Save the empty XML structure to file
			if (!$dom->save($new_file_path)) {
				error_log("Failed to save empty sitemap for post type: $post_type");
			}
			return;
		}
	
		// Loop through batches of posts
		do {
			$query = new WP_Query( array(
				'post_type'      => sanitize_text_field( $post_type ),
				'posts_per_page' => $posts_per_page,
				'paged'          => $paged,
				'post_status'    => 'publish',
				'fields'         => 'ids'
			));
	
			if ( $query->have_posts() ) {
				foreach ( $query->posts as $post_id ) {
					// Create <url> element
					$url = $dom->createElement('url');
	
					// Add <loc> element
					$loc = $dom->createElement('loc', esc_url( get_permalink( $post_id ) ));
					$url->appendChild($loc);
	
					// Add <lastmod> element with modified date in ISO 8601 format
					$lastmod = $dom->createElement('lastmod', get_the_modified_date('c', $post_id));
					$url->appendChild($lastmod);
	
					// Add <changefreq> element
					$changefreq = $dom->createElement('changefreq', 'daily');
					$url->appendChild($changefreq);
	
					// Add <priority> element
					$priority = $dom->createElement('priority', '0.8');
					$url->appendChild($priority);
	
					// Append <url> to <urlset>
					$urlset->appendChild($url);
				}
			}
	
			// Increment page number for next batch
			$paged++;
	
			// Reset query
			wp_reset_postdata();
	
		} while ( $query->have_posts() && $paged <= $query->max_num_pages );
	
		// Save the XML content to a file
		if (!$dom->save($new_file_path)) {
			error_log("Failed to save sitemap for post type: $post_type");
		}
	}

	/**
	 * Generate main sitemap
	 *
	 * This method will be used for generate main sitemap
	 * @since    1.0.0
	 */
	public function generate_main_sitemap(){

		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;

		// Add the XML stylesheet processing instruction
		$stylesheet = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'. BS24_SITEMAP_URL .'xslt/mainsitemap.xsl"');

		$dom->appendChild($stylesheet);
		
		// Create <urlset> element
		$sitemapIndex = $dom->createElement('sitemapindex');

		$sitemapIndex->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$dom->appendChild($sitemapIndex);

		$sitemap_types = array('post-sitemap.xml', 'page-sitemap.xml', 'jobs-sitemap.xml', 'videos-sitemap.xml');

		// Directory path for sitemaps
		$sitemap_dir = BS24_SITEMAP_DIR . 'sitemap';

		// Check if the 'sitemap' directory exists, if not, create it
		if ( ! file_exists( $sitemap_dir ) ) {
			if ( ! mkdir( $sitemap_dir, 0755, true ) ) { // Create the directory with proper permissions
				error_log("Failed to create directory: $sitemap_dir");
				return false;
			}
		}

		foreach ($sitemap_types as $sitemap) {

			// Create <url> element
			$sitemap_url = $dom->createElement('sitemap');
	
			// Add <loc> element
			$loc = $dom->createElement('loc', get_site_url() . '/' . $sitemap );
			$sitemap_url->appendChild($loc);

			// Add <lastmod> element with modified date in ISO 8601 format
			$lastmod = $dom->createElement('lastmod',  gmdate('c', filemtime( $sitemap_dir . '/' . $sitemap)) );
			$sitemap_url->appendChild($lastmod);

			$sitemapIndex->appendChild( $sitemap_url );

		}

		// Save the main sitemap XML
		if( ! $dom->save(BS24_MAIN_SITEMAP) ){
			error_log("Failed to save sitemap for main sitemap");
		}
	}

	/**
	 * Generate daily sitemaps
	 */
	public function generate_daily_sitemaps(){
		// Start output buffering to prevent any accidental output during activation
		ob_start();

		$this->generate_sitemap('post', 'post-sitemap.xml');
		$this->generate_sitemap('page', 'page-sitemap.xml');
		$this->generate_sitemap('jobs', 'jobs-sitemap.xml');

		$video_sitemap_generator = new Video_Sitemap_Generator();

		$video_sitemap_generator->generate_video_sitemap();

		$this->generate_main_sitemap();

		// End and clean output buffer
		ob_end_clean();
	}

	// Logging function to write messages to the log file
	private function log_sitemap_status($message) {
		error_log('[sitemap generator] ' . $message);
	}


}
