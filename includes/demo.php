<?php

function generate_sitemap( $post_type, $file_path ) {
    if( empty( $post_type ) || empty( $file_path ) ){
        return false;
    }

    $posts_per_page = 500;
    $paged = 1;
    $total_posts = 0;

    //create empty xml structure
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'. BS24_SITEMAP_URL .'xslt/sitemap.xsl"?><urlset></urlset>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    //Loop through batches of posts
    do{
        $query = new WP_Query( array(
            'post_type'      => sanitize_text_field( $post_type ),
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish'
        ) );

            // Get the total number of posts
        $total_posts += $query->found_posts;

        if ($total_posts == 0) {
            error_log("No posts found for post type: $post_type");
            return;
        }

        if( $query->have_posts() ){
            while( $query->have_posts() ) {
                $query->the_post();
                $url = $xml->addChild('url');
                $url->addChild('loc', esc_url( get_permalink() ) );
                $url->addChild('lastmod', get_the_modified_date( 'c' ) );
                $url->addChild('changefreq', 'daily' );
                $url->addChild('priority', '0.8' );

            }
        }

        //increament page number
        $paged++;

        //reset query
        wp_reset_postdata();

    }while( $query->have_posts() );


    $xml->asXML($file_path);
}


function generate_sitemap2( $post_type, $file_path ) {
    if( empty( $post_type ) || empty( $file_path ) ){
        return false;
    }

    $posts_per_page = 500;
    $paged = 1;
    $total_posts = 0;

    // Create empty xml structure using SimpleXMLElement
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    // Loop through batches of posts
    do {
        $query = new WP_Query( array(
            'post_type'      => sanitize_text_field( $post_type ),
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'post_status'    => 'publish'
        ) );

        // Get the total number of posts
        $total_posts += $query->found_posts;

        if ($total_posts == 0) {
            error_log("No posts found for post type: $post_type");
            return;
        }

        if( $query->have_posts() ) {
            while( $query->have_posts() ) {
                $query->the_post();
                $url = $xml->addChild('url');
                $url->addChild('loc', esc_url( get_permalink() ) );
                $url->addChild('lastmod', get_the_modified_date( 'c' ) );
                $url->addChild('changefreq', 'daily' );
                $url->addChild('priority', '0.8' );
            }
        }

        // Increment page number
        $paged++;

        // Reset query
        wp_reset_postdata();

    } while( $query->have_posts() );


    // Format the XML for readability using DOMDocument
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true; // Enables indentation and line breaks

    // Load SimpleXMLElement into DOMDocument for formatting
    $dom->loadXML($xml->asXML());

    // Save the formatted XML to the file
    $dom->save($file_path);

    error_log("Sitemap for post type $post_type successfully saved to $file_path");
}
