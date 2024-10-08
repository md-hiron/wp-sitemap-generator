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
    public function sitemap_redirect(){
        // get our custom query var
        $sitemap_type = get_query_var('sitemap_type');
        // Initialize the $file variable to false
        $file = false;

        if( $sitemap_type ){

            switch ($sitemap_type) {
                case 'post':
                    $file = BS24_POST_SITEMAP;
                    break;
                case 'page':
                    $file = BS24_PAGE_SITEMAP;
                    break;
                case 'jobs':
                    $file = BS24_JOBS_SITEMAP;
                    break;
                case 'main':
                    $file = BS24_MAIN_SITEMAP;
                    break;
                default:
                    return; // Do nothing if an unknown sitemap is requested.
            }

            error_log("Serving sitemap: $file");

            if( $file && file_exists( $file ) ){
                // Serve the XML file
                header('Content-Type: application/xml; charset=utf-8');
                readfile($file);
                exit;
            }else{
                 // If the file doesn't exist, handle the error (e.g., serve 404)
                 status_header(404);
                 echo __( 'Sitemap not found', 'bs24-sitemap' );
                 exit;
            }
        }

        //check if file exist
        
        
    }

    /**
     * Add rewrite rules for the xml file
     * 
     * @since 1.0.0
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^post-sitemap\.xml$', 'index.php?sitemap_type=post', 'top');
        add_rewrite_rule('^page-sitemap\.xml$', 'index.php?sitemap_type=page', 'top');
        add_rewrite_rule('^jobs-sitemap\.xml$', 'index.php?sitemap_type=jobs', 'top');
        add_rewrite_rule('^sitemap\.xml$', 'index.php?sitemap_type=main', 'top');
    }

    /**
     * Add query var for sitemap rewrite rules
     */
    public function add_query_ver( $vars ){
        $vars[] = 'sitemap_type';

        return $vars;
    }

}
