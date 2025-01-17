<?php

/**
 * Create custom field on post and page
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
 * Create custom field on post and page
 *
 * @since      1.0.0
 * @package    BS24_Sitemap
 * @subpackage BS24_Sitemap/includes
 * @author     Md Hiron
 */
class BS24_Custom_Field {

	/**
	 * Add custom field box
	 *
	 * @since    1.0.0
	 */
	public function add_custom_field_box(){
		add_meta_box(
			'no_index_google',
			__( 'No Index in Google', 'bs24-sitemap-generator' ),
			array( $this, 'render_no_index_metabox' ),
			array( 'post', 'page', 'page-generator-pro' ),
			'side',
			'high'
		);
	}

	/**
	 * Render No Index in google form
	 */
	public function render_no_index_metabox( $post ){
		$value = get_post_meta( $post->ID, '_no_index_google', true );

		?>
		<label for="no_index_google_checkbox">
			<input type="checkbox" id="no_index_google_checkbox" name="no_index_google_checkbox" value="1" <?php checked( $value, '1' )?> >
			<?php _e( 'Exclude from Google index', 'bs24-sitemap-generator' ); ?>
		</label>
		<?php
	}

	/**
	 * save no index meta box
	 */
	public function save_no_index_meta_box( $post_id ){
		if( isset( $_POST['no_index_google_checkbox'] ) ){
			update_post_meta( $post_id, '_no_index_google', '1' );
		}else{
			delete_post_meta( $post_id, '_no_index_google' );
		}
	}

}
