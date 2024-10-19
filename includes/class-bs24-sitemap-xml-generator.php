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

	protected $start_time;
	protected $memory_limit = 512 * 1024 * 1024; // 512 MB
    protected $execution_limit = 290;

	/**
	 * contractor function
	 */
	public function __construct(){
		$this->start_time = microtime(true);
	}

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

		// Register custom error handler
		set_error_handler([$this, 'custom_error_handler']);
    
		// Register shutdown function for catching fatal errors
		register_shutdown_function([$this, 'shutdown_handler']);

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
	
		$new_temp_file_path = $sitemap_dir . '/temp-' . $file_path;
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
			if (!$dom->save($new_temp_file_path)) {
				error_log("Failed to save empty sitemap for post type: $post_type");
			}else{
				if( !rename( $new_temp_file_path, $new_file_path ) ){
					error_log("Failed to rename temp file: $post_type");
				}
			}
			return;
		}
		try{
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
		
				// Reset query
				wp_reset_postdata();
		
				// Increment page number for next batch
				$paged++;

				// Monitor memory usage and execution time
				if (!$this->check_memory_and_time()) {
					return false; // Stop execution if nearing limits
				}
			} while ( $query->have_posts() && $paged <= $query->max_num_pages );
		
			// Save the XML content to a file
			if (!$dom->save($new_temp_file_path)) {
				error_log("Failed to save sitemap for post type: $post_type");
			}else{
				if( !rename($new_temp_file_path, $new_file_path) ){
					error_log("Failed to rename temp file: $post_type");
				}
			}
			
		}catch( Exception $e ){
			// Log the error and schedule the retry
			error_log("Sitemap generation failed: " . $e->getMessage());
			$this->schedule_sitemap_retry(); // Schedule retry
			return false;
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

		$sitemap_temp_file = $sitemap_dir . '/temp-sitemap.xml';
		$sitemap_file = $sitemap_dir . '/sitemap.xml';
		
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
		if( ! $dom->save($sitemap_temp_file) ){
			error_log("Failed to save sitemap for main sitemap");
		}else {
			if( !rename( $sitemap_temp_file, $sitemap_file ) ){
				error_log("Failed to rename sitemap for main sitemap");
			}
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
		

		// End and clean output buffer
		ob_end_clean();
	}

	//retry if fail video sitemap creation
	private function schedule_sitemap_retry(){
		// Check if the event is already scheduled to avoid duplication
		if (!wp_next_scheduled('retry_post_sitemap_generation')) {
		   // Schedule the retry to run after one hour (3600 seconds)
		   wp_schedule_single_event(time() + 180, 'retry_post_sitemap_generation');
	   }
   	}

	// Custom error handler for memory exhaustion
	public function custom_error_handler($errno, $errstr, $errfile, $errline) {
		if (strpos($errstr, 'Allowed memory size') !== false) {
			// Memory exhaustion detected
			error_log("Memory exhaustion detected in $errfile on line $errline: $errstr");
			$this->schedule_sitemap_retry();
			return true; // Handle error
		}
		return false; // Let other errors proceed to default handler
	}

	// Shutdown function to handle fatal errors
	public function shutdown_handler() {
		$error = error_get_last();
		if ($error && $error['type'] === E_ERROR) {
			if (strpos($error['message'], 'Allowed memory size') !== false) {
				error_log("Fatal error due to memory exhaustion. Triggering retry.");
				$this->schedule_sitemap_retry();
			}
		}
	}

	protected function check_memory_and_time() {
		$memory_used = memory_get_usage(true);
		$execution_time = microtime(true) - $this->start_time;

		if ($execution_time > $this->execution_limit) {
			error_log("Warning: Execution time nearing limit. Triggering retry.");
			$this->schedule_sitemap_retry();
			return false;
		}

		if ($memory_used > $this->memory_limit) {
			error_log("Warning: Memory usage nearing limit. Triggering retry.");
			$this->schedule_sitemap_retry();
			return false;
		}

		return true; // Continue if within limits
	}


}
