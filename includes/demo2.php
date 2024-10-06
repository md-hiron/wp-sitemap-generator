<?php
function bs24_generate_sitemap($post_type, $file_path) {
    $posts_per_page = 500; // Set the number of posts per batch
    $paged = 1; // Start with the first page
    $total_posts = 0; // Initialize total post count

    // Create an empty XML structure
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    // Loop through batches of posts
    do {
        // Query posts in batches
        $query = new WP_Query(array(
            'post_type'      => $post_type,
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish',
        ));

        // Update total posts count
        $total_posts += $query->found_posts;

        // Add each post URL to the sitemap
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $url = $xml->addChild('url');
                $url->addChild('loc', esc_url(get_permalink()));
                $url->addChild('lastmod', get_the_modified_date('c'));
                $url->addChild('changefreq', 'daily');
                $url->addChild('priority', '0.8');
            }
        }

        // Increment the page number
        $paged++;

        // Reset post data to free memory
        wp_reset_postdata();

        // Log progress
        $processed_posts = ($paged - 1) * $posts_per_page;
        error_log("Sitemap for post type $post_type: Processed $processed_posts of $total_posts");

    } while ($query->have_posts());

    // Final log when all posts are processed
    error_log("Sitemap for post type $post_type is complete. Total posts processed: $total_posts");

    // Save the XML content to a file
    if ($xml->asXML($file_path)) {
        error_log("Sitemap for post type $post_type successfully saved to $file_path");
    } else {
        error_log("Failed to save sitemap for post type: $post_type");
    }
}
