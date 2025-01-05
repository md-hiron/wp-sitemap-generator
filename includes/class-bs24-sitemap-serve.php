<?php

/**
 * The class used for serve xml content file to url
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
        $sitemap_xml_generator = new BS24_Sitemap_XML_Generator();
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

            $is_googlebot = ( isset( $_SERVER['HTTP_USER_AGENT'] ) && stripos( $_SERVER['HTTP_USER_AGENT'], 'Googlebot' ) !== false );
            if( $is_googlebot ){
               $dynamic_content =  $sitemap_xml_generator->generate_sitemap( $sitemap_type, $sitemap_type . '-sitemap.xml', true, $is_googlebot );
               if( $dynamic_content ){
                    header('Content-Type: application/xml; charset=utf-8');
                    echo $dynamic_content;
                    exit;
               }
            }

            if( $file && file_exists( $file ) ){
                // Serve the XML file
                header('Content-Type: application/xml; charset=utf-8');
                readfile($file);
                exit;
            }else{
                 // If the file doesn't exist, handle the error (e.g., serve 404)
                 status_header(404);
                 error_log("Sitemap not found");
                 exit;
            }
        }

        $video_sitemap = get_query_var('video_sitemap_type');
        if( !empty( $video_sitemap ) ){
            if( file_exists( BS24_VIDEO_SITEMAP ) ){
                // Serve the XML file
                header('Content-Type: application/xml; charset=utf-8');
                readfile(BS24_VIDEO_SITEMAP);
                exit;
            }else{
                // If the file doesn't exist, handle the error (e.g., serve 404)
                status_header(404);
                error_log("Video Sitemap not found");
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
        add_rewrite_rule('^post-sitemap\.xml$', 'index.php?sitemap_type=post', 'top');
        add_rewrite_rule('^page-sitemap\.xml$', 'index.php?sitemap_type=page', 'top');
        add_rewrite_rule('^jobs-sitemap\.xml$', 'index.php?sitemap_type=jobs', 'top');
        add_rewrite_rule('^videos-sitemap\.xml$', 'index.php?video_sitemap_type=1', 'top');
        add_rewrite_rule('^sitemap\.xml$', 'index.php?sitemap_type=main', 'top');
    }

    /**
     * Add query var for sitemap rewrite rules
     */
    public function add_query_ver( $vars ){
        $vars[] = 'sitemap_type';
        $vars[] = 'video_sitemap_type';

        return $vars;
    }

}
