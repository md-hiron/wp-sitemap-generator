<?php

/**
 * The class used for serve xml content file to url
 *
 * @since      1.0.0
 *
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 */

/**
 * The class used for serve xml content file to url
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Md Hiron
 */
class BS24_Sitemap_Serve {

	/**
	 * Serve sitemap
	 *
	 * This method will serve xml file to url
	 *
	 * @since    1.0.0
	 */
    public function serve_sitemap(){
       
        if( isset( $_GET['bs4_sitemap'] ) ){
            $file = sanitize_text_field( $_GET['bs4_sitemap'] );
            $allowed_sitemaps = array('sitemap.xml', 'post-sitemap.xml', 'page-sitemap.xml', 'jobs-sitemap.xml');

            if( in_array( $file, $allowed_sitemaps ) && file_exists( BS24_SITEMAP_DIR . $file ) ){
                header('Content-Type: application/xml');
                readfile(BS24_SITEMAP_DIR . $file);
                exit;
            }
        }
        
    }
    /**
     * Add rewrite rules for the xml file
     * 
     * @since 1.0.0
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^sitemap(-([a-z]+))?\.xml$', 'index.php?bs24_sitemap=sitemap$2.xml', 'top');
    }

}
