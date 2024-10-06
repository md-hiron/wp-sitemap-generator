<?php
function bs24_generate_sitemap($post_type, $file_path) {
    // Create a WP_Query to fetch all published posts of the given post type
    $query = new WP_Query(array(
        'post_type'      => sanitize_text_field( $post_type ),
        'posts_per_page' => -1,    // Fetch all posts
        'post_status'    => 'publish', // Only published posts
    ));

    // Get the total number of posts
    $total_posts = $query->found_posts;

    if ($total_posts == 0) {
        error_log("No posts found for post type: $post_type");
        return;
    }

    // Log that the sitemap generation is starting
    error_log("Starting to generate sitemap for post type: $post_type. Total posts: $total_posts");

    // Initialize XML structure
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    // Log points to track progress
    $progress_25 = round($total_posts * 0.25);
    $progress_50 = round($total_posts * 0.50);
    $progress_75 = round($total_posts * 0.75);

    $current_count = 0; // Counter to track the number of posts processed

    // Add each post URL to the sitemap
    while ($query->have_posts()) {
        $query->the_post();

        // Add the post details to the XML
        $url = $xml->addChild('url');
        $url->addChild('loc', esc_url(get_permalink()));
        $url->addChild('lastmod', get_the_modified_date('c'));
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

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
    }

    // Final log when sitemap is complete
    error_log("Sitemap for post type $post_type is 100% complete. Total processed: $total_posts");

    // Reset post data
    wp_reset_postdata();

    // Save the XML content to a file
    if ($xml->asXML($file_path)) {
        error_log("Sitemap for post type $post_type successfully saved to $file_path");
    } else {
        error_log("Failed to save sitemap for post type: $post_type");
    }
}
