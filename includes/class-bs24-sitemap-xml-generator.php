<?php

/**
 * The class used for generate xml files
 *
 * @since      1.0.0
 *
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 */

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

        $query = new WP_Query( array(
			'post_type'      => sanitize_text_field( $post_type ),
			'posts_per_page' => -1,
			'post_status'    => 'publish'
		) );

		// Get the total number of posts
		$total_posts = $query->found_posts;

		if ($total_posts == 0) {
			error_log("No posts found for post type: $post_type");
			return;
		}
	
		// Log that the sitemap generation is starting
		error_log("Starting to generate sitemap for post type: $post_type. Total posts: $total_posts");

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
    	$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		// Log points to track progress
		$progress_25 = round($total_posts * 0.25);
		$progress_50 = round($total_posts * 0.50);
		$progress_75 = round($total_posts * 0.75);
	
		$current_count = 0; // Counter to track the number of posts processed

		if( $query->have_posts() ){
			while( $query->have_posts() ) {
				$query->the_post();
				$url = $xml->addChild('url');
				$url->addChild('loc', esc_url( get_permalink() ) );
				$url->addChild('lastmod', get_the_modified_date( 'c' ) );
				$url->addChild('changefreq', 'daily' );
				$url->addChild('priority', '0.8' );

				// Increment the counter
				$current_count++;

				// Log progress at 25%, 50%, and 75%
				if ($current_count == $progress_25) {
					error_log("Sitemap for post type $post_type: 25% complete. Processed $current_count of $total_posts.");
				} elseif ($current_count == $progress_50) {
					error_log("Sitemap for post type $post_type: 50% complete. Processed $current_count of $total_posts.");
				} elseif ($current_count == $progress_75) {
					error_log("Sitemap for post type $post_type: 75% complete. Processed $current_count of $total_posts.");
				}

				wp_reset_postdata();
			}

			
		}

		// Final log when sitemap is complete
		error_log("Sitemap for post type $post_type is 100% complete. Total processed: $total_posts");


		// Save the XML content to a file
		if ($xml->asXML($file_path)) {
			error_log("Sitemap for post type $post_type successfully saved to $file_path");
		} else {
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
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex></sitemapindex>');
		$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$sitemap_types = array('post-sitemap.xml', 'page-sitemap.xml', 'jobs-sitemap.xml');

		foreach ($sitemap_types as $sitemap) {
			$sitemap_url = $xml->addChild('sitemap');
			$sitemap_url->addChild('loc', BS24_SITEMAP_URL . 'sitemap/' . $sitemap);
			$sitemap_url->addChild('lastmod', date('c', filemtime(BS24_SITEMAP_DIR . 'sitemap/' . $sitemap)));
		}

		// Save the main sitemap XML
		$xml->asXML(BS24_MAIN_SITEMAP);
	}

	/**
	 * Generate all sitemaps
	 */
	public function generate_sitemaps(){
		$this->generate_sitemap('post', BS24_POST_SITEMAP);
		$this->generate_sitemap('page', BS24_PAGE_SITEMAP);
		$this->generate_sitemap('jobs', BS24_JOBS_SITEMAP);

		$this->generate_main_sitemap();
	}


}
