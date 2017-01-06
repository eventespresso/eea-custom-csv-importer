<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
*
* Custom_CSV_Importer_Admin_Page_Init class
*
* This is the init for the Custom_CSV_Importer Addon Admin Pages.  See EE_Admin_Page_Init for method inline docs.
*
* @package			Event Espresso (custom_csv_importer addon)
* @subpackage		admin/Custom_CSV_Importer_Admin_Page_Init.core.php
* @author				Darren Ethier
*
* ------------------------------------------------------------------------
*/
class Custom_CSV_Importer_Admin_Page_Init extends EE_Admin_Page_Init  {

	/**
	 * 	constructor
	 *
	 * @access public
	 * @return \Custom_CSV_Importer_Admin_Page_Init
	 */
	public function __construct() {

		do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );

		define( 'CUSTOM_CSV_IMPORTER_PG_SLUG', 'espresso_custom_csv_importer' );
		define( 'CUSTOM_CSV_IMPORTER_LABEL', __( 'Custom CSV Importer', 'event_espresso' ));
		define( 'EE_CUSTOM_CSV_IMPORTER_ADMIN_URL', admin_url( 'admin.php?page=' . CUSTOM_CSV_IMPORTER_PG_SLUG ));
		define( 'EE_CUSTOM_CSV_IMPORTER_ADMIN_ASSETS_PATH', EE_CUSTOM_CSV_IMPORTER_ADMIN . 'assets' . DS );
		define( 'EE_CUSTOM_CSV_IMPORTER_ADMIN_ASSETS_URL', EE_CUSTOM_CSV_IMPORTER_URL . 'admin' . DS . 'custom_csv_importer' . DS . 'assets' . DS );
		define( 'EE_CUSTOM_CSV_IMPORTER_ADMIN_TEMPLATE_PATH', EE_CUSTOM_CSV_IMPORTER_ADMIN . 'templates' . DS );
		define( 'EE_CUSTOM_CSV_IMPORTER_ADMIN_TEMPLATE_URL', EE_CUSTOM_CSV_IMPORTER_URL . 'admin' . DS . 'custom_csv_importer' . DS . 'templates' . DS );

		parent::__construct();
		$this->_folder_path = EE_CUSTOM_CSV_IMPORTER_ADMIN;

	}





	protected function _set_init_properties() {
		$this->label = CUSTOM_CSV_IMPORTER_LABEL;
	}



	/**
	*		_set_menu_map
	*
	*		@access 		protected
	*		@return 		void
	*/
	protected function _set_menu_map() {
		$this->_menu_map = new EE_Admin_Page_Sub_Menu( array(
			'menu_group' => 'addons',
			'menu_order' => 25,
			'show_on_menu' => EE_Admin_Page_Menu_Map::BLOG_ADMIN_ONLY,
			'parent_slug' => 'espresso_events',
			'menu_slug' => CUSTOM_CSV_IMPORTER_PG_SLUG,
			'menu_label' => CUSTOM_CSV_IMPORTER_LABEL,
			'capability' => 'administrator',
			'admin_init_page' => $this
		));
	}



}
// End of file Custom_CSV_Importer_Admin_Page_Init.core.php
// Location: /wp-content/plugins/eea-custom-csv-importer/admin/custom_csv_importer/Custom_CSV_Importer_Admin_Page_Init.core.php
