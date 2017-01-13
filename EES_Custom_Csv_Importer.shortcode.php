<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
/*
 * EES_Custom_Csv_Importer
 *
 * @package			Event Espresso
 * @subpackage		eea-custom-csv-importer
 * @author 				Brent Christensen
 * @ version		 	$VID:$
 *
 * ------------------------------------------------------------------------
 */
class EES_Custom_Csv_Importer  extends EES_Shortcode {



	/**
	 * 	set_hooks - for hooking into EE Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks() {
	}



	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
	}



	/**
	 * 	set_definitions
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_definitions() {
	}



	/**
	 * 	run - initial shortcode module setup called during "wp_loaded" hook
	 * 	this method is primarily used for loading resources that will be required by the shortcode when it is actually processed
	 *
	 *  @access 	public
	 *  @param 	 WP $WP
	 *  @return 	void
	 */
	public function run( WP $WP ) {
		// this will trigger the EED_Custom_Csv_Importer module's run() method during the pre_get_posts hook point,
		// this allows us to initialize things, enqueue assets, etc,
		// as well, this saves an instantiation of the module in an array, using 'custom_csv_importer' as the key, so that we can retrieve it
		EE_Registry::instance()->REQ->set( 'ee', 'custom_csv_importer' );
		EED_Custom_Csv_Importer::$shortcode_active = TRUE;
	}



	/**
	 *    process_shortcode
	 *
	 *    [ESPRESSO_CUSTOM_CSV_IMPORTER]
	 *
	 * @access 	public
	 * @param 	array $attributes
	 * @return 	void
	 */
	public function process_shortcode( $attributes = array() ) {
		// make sure $attributes is an array
		$attributes = array_merge(
			// defaults
			array(),
			(array)$attributes
		);
		return EE_Registry::instance()->modules['custom_csv_importer']->display_custom_csv_importer( $attributes );
	}


}
// End of file EES_Custom_Csv_Importer.shortcode.php
// Location: /wp-content/plugins/eea-custom-csv-importer/EES_Custom_Csv_Importer.shortcode.php