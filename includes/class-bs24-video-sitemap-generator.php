<?php

/**
 * The class used for generate video sitemap xml files
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
 * The class used for generate video sitemap xml files
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Md Hiron
 */
Class Video_Sitemap_Generator{
	//Yoututbe video API
    private $youtube_API;
	protected $start_time;
	protected $memory_limit; // 512 MB
    protected $execution_limit;


    public function __construct(){
        $this->youtube_API     = 'AIzaSyBiv1WYEoaNcl2wZaxq5A-T64pMUAg7iDU';
		$this->start_time      = microtime(true);
		$this->memory_limit    = wp_convert_hr_to_bytes( ini_get('memory_limit') );
		$this->execution_limit = ini_get('max_execution_time') - 10;
    }

    /**
	 * Generate video sitemap
	 * 
	 * This will create video sitemap on every post and page
	 */
	public function generate_video_sitemap(){

		// Register custom error handler
		set_error_handler([$this, 'custom_error_handler']);
    
		// Register shutdown function for catching fatal errors
		register_shutdown_function([$this, 'shutdown_handler']);

		// Initiate DOMDocument (optimized, use directly instead of SimpleXMLElement for large datasets)
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;

		// Add the XML stylesheet processing instruction
		$stylesheet = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'. BS24_SITEMAP_URL .'xslt/video-sitemap.xsl"');

		$dom->appendChild($stylesheet);
		
		// Create <urlset> element
		$urlset = $dom->createElement('urlset');

		$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$urlset->setAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');
		$dom->appendChild($urlset);

		$posts_per_page = 500; // Fetch 500 posts per batch to reduce memory load
		$paged = 1;
		$videos_found = false;

		$unique_videos = [];
		try{
			do {
				$query = new WP_Query(array(
					'post_type'      => array('post', 'page'),
					'posts_per_page' => $posts_per_page,
					'post_status'    => 'publish',
					'paged'          => $paged,
					'fields'         => 'ids'
				));

				if ($query->have_posts()) {
					foreach( $query->posts as $post_id ) {

						//exclude pages which is set no index from rankmath SEO plugin
						$robot_meta = get_post_meta( $post_id, 'rank_math_robots', true );
						if( is_array( $robot_meta ) && in_array( 'noindex', $robot_meta ) ){
							continue;
						}

						$post_content = sanitize_post_field('post_content', get_post_field('post_content', $post_id), $post_id, 'display');

						// Extract video URLs from post content
						$videos = $this->get_videos_from_post($post_content, get_the_title( $post_id ));

						// If no videos, skip further processing for this post
						if (empty($videos)) {
							continue;
						}

						$videos_found = true;
						$permalink = esc_url(get_permalink($post_id)); // Compute permalink once

						foreach ($videos as $video) {
							// Skip this video if we've already added it
							if (isset($unique_videos[$video['url']])) {
								continue;
							}
		
							// Mark this video as added
							$unique_videos[$video['url']] = true;

							// Create <url> element
							$urlXML = $dom->createElement('url');
							$urlset->appendChild($urlXML);

							// Add post permalink
							$loc = $dom->createElement('loc', $permalink);
							$urlXML->appendChild($loc);

							// Create <video:video> element
							$videoXML = $dom->createElement('video:video');
							$urlXML->appendChild($videoXML);

							// Add video URL
							$content_loc = $dom->createElement('video:content_loc', esc_url($video['url']));
							$videoXML->appendChild($content_loc);

							// Add video title
							$title = $dom->createElement('video:title', esc_html( $video['title'] ));
							$videoXML->appendChild($title);
						}
					}
					wp_reset_postdata();
					$paged++;
				} else {
					break; // Exit the loop if no more posts are found
				}

				// Monitor memory usage and execution time
				if (!$this->check_memory_and_time()) {
					return false; // Stop execution if nearing limits
				}
			} while ( $query->have_posts() && $paged <= $query->max_num_pages );

			// Save the XML file if videos were found
			$sitemap_dir = BS24_SITEMAP_DIR . 'sitemap';

			// Create directory if it doesn't exist
			if (!file_exists($sitemap_dir)) {
				if (!mkdir($sitemap_dir, 0755, true)) {
					error_log("Failed to create directory: $sitemap_dir");
					return false;
				}
			}

			$temp_file = $sitemap_dir . '/videos-sitemap-temp.xml';
			$final_file = $sitemap_dir . '/videos-sitemap.xml';
		
			if ($videos_found) {
				// Save the final XML to a temporary file first
				$dom->save($temp_file);
		
				// Rename the temp file to the final file if everything is fine
				rename($temp_file, $final_file);
			} else {
				error_log("No videos found. Creating empty sitemap.");
				$dom->save($final_file);
			}
		}catch( Exception $e ){
			// Log the error and schedule the retry
			error_log("Sitemap generation failed: " . $e->getMessage());
			$this->schedule_sitemap_retry(); // Schedule retry
			return false;
		}
		
	
		
	}

	/**
	 * Get videos from the post
	 */
	public function get_videos_from_post( $content, $post_title ){
		if( ! $content ){
			return [];
		}
   
		// Parse Gutenberg blocks
		$blocks = parse_blocks($content);
	
		$videos = $this->extract_videos_from_blocks($blocks, $post_title);
	
		return $videos;
	}

	/**
	 * Extract video from Block
	 */
	public function extract_videos_from_blocks( $blocks, $post_title ) {
		if( empty( $blocks ) ){
			return [];
		}

		$videos = array();
	
		foreach ($blocks as $block) {
			// Handle core/video block (for local videos)
			if ($block['blockName'] === 'core/video' && isset($block['attrs']['id'])) {
				// Extract the video src attribute from the <video> tag in innerHTML
				preg_match('/<video[^>]+src="([^"]+)"/', $block['innerHTML'], $matches);
                $video_url = '';
				if (!empty($matches[1])) {
					$video_url = $matches[1]; // Add the local video URL
				}

                $video_title = $this->get_video_title_from_id( $block['attrs']['id'], $post_title ); // For local videos
                $videos[] = array('url' => $video_url, 'title' => $video_title);
			}
	
			// Handle core/embed block for video providers (like YouTube or Vimeo)
			if ($block['blockName'] === 'core/embed' && isset($block['attrs']['providerNameSlug'])) {
				// Only add video embeds (e.g., YouTube, Vimeo, etc.)
				if (in_array($block['attrs']['providerNameSlug'], array('youtube'))) {
					if (!empty($block['attrs']['url'])) {
                        $video_url = $block['attrs']['url'];
                        $video_id = $this->get_video_id_from_url($video_url);

						if( !empty( $video_id ) ){
							$video_title = $this->get_youtube_video_title($video_id, $this->youtube_API, $post_title);

							$videos[] = array('url' => $video_url, 'title' => $video_title);
						}
                        
					}
				}
			}

			// Handle core/shortcode block for videos from shortcode
			if( $block['blockName'] === 'core/shortcode' ){
				$shortcode_content = $block['innerHTML'];

				if( has_shortcode( $shortcode_content, 'lyte' ) ){
					$video_id = $this->get_video_id_from_shortcode( $shortcode_content );
					if( $video_id ){
						$video_url   = esc_url( 'https://www.youtube.com/watch?v='. $video_id );
						$video_title = $this->get_youtube_video_title( $video_id, $this->youtube_API, $post_title );
						$videos[] = array( 'url' => $video_url, 'title' => $video_title );
					}
					
				}
			}
	
			// If there are innerBlocks, recursively extract videos from them
			if (!empty($block['innerBlocks'])) {
				$inner_videos = $this->extract_videos_from_blocks( $block['innerBlocks'], $post_title );
				$videos = array_merge($videos, $inner_videos);
			}
		}
	
		return $videos;
	}

    /**
     * Get self hosted video title
     */
    public function get_video_title_from_id($attachment_id, $post_title) {
		if( empty( $attachment_id ) || empty( $post_title ) ){
			return;
		}
        // Get the video attachment post object
        $attachment = get_post($attachment_id);
        
        // If the attachment exists, return its title
        if ($attachment) {
            return $attachment->post_title;
        }
        
        return $post_title;
    }
    
    /**
     * Get youtube video title from api
     */
    private function get_youtube_video_title($video_id, $api_key, $post_title ) {
		if( empty( $video_id ) || empty( $api_key ) ){
			return $post_title;
		}

        $url = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=snippet";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return $post_title;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        if (!empty($data->items[0]->snippet->title)) {
            return $data->items[0]->snippet->title;
        }
    
        return $post_title;
    }

    /**
     * Get video ID from url
     */
    private function get_video_id_from_url($url) {
        // For YouTube (extract the video ID from URL)
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            preg_match('/(youtu\.be\/|v=)([a-zA-Z0-9_-]{11})/', $url, $matches);
            return $matches[2] ?? null;
        }
        
        return null;
    }

	/**
	 * get video ID from shortcode
	 */
	private function get_video_id_from_shortcode( $content ){
		if( empty( $content ) ){
			return false;
		}

		$pattern = '/\[lyte\s+id=["\']([a-zA-Z0-9_-]+)["\']\s*\/?\]/';
		preg_match( $pattern, $content, $matches );

		if( !empty( $matches[1] ) ){
			return $matches[1];
		}else{
			return false;
		}	
	}

	//retry if fail video sitemap creation
	private function schedule_sitemap_retry(){
		 // Check if the event is already scheduled to avoid duplication
		 if (!wp_next_scheduled('retry_video_sitemap_generation')) {
			// Schedule the retry to run after one hour (3600 seconds)
			wp_schedule_single_event(time() + 180, 'retry_video_sitemap_generation');
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