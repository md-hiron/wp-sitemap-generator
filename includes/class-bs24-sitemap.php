<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 */

 if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Your Name <email@example.com>
 */
class BS24_Sitemap {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      BS24_Sitemap_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $bs24_sitemap    The string used to uniquely identify this plugin.
	 */
	protected $bs24_sitemap;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BS24_SITEMAP_VERSION' ) ) {
			$this->version = BS24_SITEMAP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->bs24_sitemap = 'bs24-sitemap-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->generate_sitemap_xml();
		$this->serve_sitemap_xml_file();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - BS24_Sitemap_Loader. Orchestrates the hooks of the plugin.
	 * - BS24_Sitemap_i18n. Defines internationalization functionality.
	 * - BS24_Sitemap_Admin. Defines all hooks for the admin area.
	 * - BS24_Sitemap_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-loader.php';

		$this->loader = new BS24_Sitemap_Loader();

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-i18n.php';

		/**
		 * The class responsible for generating xml content file
		 * of the plugin.
		 */
		require_once BS24_SITEMAP_DIR . 'includes/class-bs24-video-sitemap-generator.php';

		/**
		 * The class responsible for generating xml content file
		 * of the plugin.
		 */
		require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-xml-generator.php';

		/**
		 * The class responsible for serve xml file to url
		 * of the plugin.
		 */
		require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-serve.php';

		/**
		 * The class responsible for custom field for No index for google 
		 * of the plugin.
		 */
		require_once BS24_SITEMAP_DIR . 'includes/class-bs24-sitemap-custom-field.php';

		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the BS24_Sitemap_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new BS24_Sitemap_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Generate xml content
	 */
	private function generate_sitemap_xml(){

		$sitemap_generator       = new BS24_Sitemap_XML_Generator();
		$video_sitemap_generator = new Video_Sitemap_Generator();
		$no_index_custom_field   = new BS24_Custom_Field();

		//load custom field in post and page
		$this->loader->add_action( 'add_meta_boxes', $no_index_custom_field, 'add_custom_field_box' );
		$this->loader->add_action( 'save_post', $no_index_custom_field, 'save_no_index_meta_box' );
		$this->loader->add_action( 'template_redirect', $sitemap_generator, 'add_no_index_header_for_googlebot' );

		//generate post, page, jobs sitemap after install plugin
		$this->loader->add_action( 'bs24_sitemap_one_time_sitemap_generation_event', $sitemap_generator, 'generate_daily_sitemaps' );

		//generate post, page, jobs sitemap for daily basis
		$this->loader->add_action( 'bs24_sitemap_daily_sitemap_event', $sitemap_generator, 'generate_daily_sitemaps' );

		//generate post, page, jobs sitemap if sitemap generation fails
		$this->loader->add_action( 'retry_post_sitemap_generation', $sitemap_generator, 'generate_daily_sitemaps' );
		
		//generate video sitemap after install plugin
		$this->loader->add_action( 'bs24_sitemap_one_time_video_sitemap_generation_event', $video_sitemap_generator, 'generate_video_sitemap' );
		
		//generate video sitemap for daily basis
		$this->loader->add_action( 'bs24_sitemap_daily_video_sitemap_event', $video_sitemap_generator, 'generate_video_sitemap' );

		//generate video sitemap if generation fails
		$this->loader->add_action( 'retry_video_sitemap_generation', $video_sitemap_generator, 'generate_video_sitemap' );

		//gnerate main sitemap after install plugin
		$this->loader->add_action( 'bs24_sitemap_one_time_video_sitemap_generation_event', $sitemap_generator, 'generate_main_sitemap' );

		//gnerate main sitemap for daily basis
		$this->loader->add_action( 'bs24_sitemap_daily_video_sitemap_event', $sitemap_generator, 'generate_main_sitemap' );
	}

	/**
	 * Serve xml file to url
	 */
	private function serve_sitemap_xml_file(){

		$sitemap_serve = new BS24_Sitemap_Serve();

		$this->loader->add_action( 'init', $sitemap_serve, 'add_rewrite_rules' );
		$this->loader->add_filter( 'query_vars', $sitemap_serve, 'add_query_ver' );
		$this->loader->add_action( 'template_redirect', $sitemap_serve, 'sitemap_redirect' );
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_bs24_sitemap() {
		return $this->bs24_sitemap;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    BS24_Sitemap_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
