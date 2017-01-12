<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit();
}
// define the plugin directory path and URL
define( 'EE_CUSTOM_CSV_IMPORTER_BASENAME', plugin_basename( EE_CUSTOM_CSV_IMPORTER_PLUGIN_FILE ) );
define( 'EE_CUSTOM_CSV_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_CUSTOM_CSV_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_CUSTOM_CSV_IMPORTER_ADMIN', EE_CUSTOM_CSV_IMPORTER_PATH . 'admin' . DS . 'custom_csv_importer' . DS );

/**
 * Class  EE_Custom_Csv_Importer
 *
 * @package     Event Espresso
 * @subpackage  eea-custom-csv-importer
 * @author      Brent Christensen
 */
Class  EE_Custom_Csv_Importer extends EE_Addon {

	/**
	 * this is not the place to perform any logic or add any other filter or action callbacks
	 * this is just to bootstrap your addon; and keep in mind the addon might be DE-registered
	 * in which case your callbacks should probably not be executed.
	 * EED_Custom_Csv_Importer is the place for most filter and action callbacks (relating
	 * the the primary business logic of your addon) to be placed
	 *
	 * @throws \EE_Error
	 */
	public static function register_addon() {
		// register addon via Plugin API
		EE_Register_Addon::register(
			'Custom_Csv_Importer',
			array(
				'version'               => EE_CUSTOM_CSV_IMPORTER_VERSION,
				'plugin_slug'           => 'espresso_custom_csv_importer',
				'min_core_version'      => EE_CUSTOM_CSV_IMPORTER_CORE_VERSION_REQUIRED,
				'main_file_path'        => EE_CUSTOM_CSV_IMPORTER_PLUGIN_FILE,
				'namespace'             => array(
					'FQNS' => 'EventEspresso\CustomCsvImporter',
					'DIR'  => __DIR__,
				),
				'admin_path'            => EE_CUSTOM_CSV_IMPORTER_ADMIN,
				'admin_callback'        => '',
				'config_class'          => 'EE_Custom_Csv_Importer_Config',
				'config_name'           => 'EE_Custom_Csv_Importer',
				'autoloader_paths'      => array(
					'EE_Custom_Csv_Importer_Config'       => EE_CUSTOM_CSV_IMPORTER_PATH . 'EE_Custom_Csv_Importer_Config.php',
					'Custom_Csv_Importer_Admin_Page'      => EE_CUSTOM_CSV_IMPORTER_ADMIN . 'Custom_Csv_Importer_Admin_Page.core.php',
					'Custom_Csv_Importer_Admin_Page_Init' => EE_CUSTOM_CSV_IMPORTER_ADMIN . 'Custom_Csv_Importer_Admin_Page_Init.core.php',
				),
				'dms_paths'             => array( EE_CUSTOM_CSV_IMPORTER_PATH . 'core' . DS . 'data_migration_scripts' . DS ),
				'module_paths'          => array( EE_CUSTOM_CSV_IMPORTER_PATH . 'EED_Custom_Csv_Importer.module.php' ),
				'shortcode_paths'       => array( EE_CUSTOM_CSV_IMPORTER_PATH . 'EES_Custom_Csv_Importer.shortcode.php' ),
				'widget_paths'          => array( EE_CUSTOM_CSV_IMPORTER_PATH . 'EEW_Custom_Csv_Importer.widget.php' ),
				// if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
				'pue_options'           => array(
					'pue_plugin_slug' => 'eea-custom-csv-importer',
					'plugin_basename' => EE_CUSTOM_CSV_IMPORTER_BASENAME,
					'checkPeriod'     => '24',
					'use_wp_update'   => false,
				),
				'capabilities'          => array(
					'administrator' => array(
						'edit_thing',
						'edit_things',
						'edit_others_things',
						'edit_private_things',
					),
				),
				//note for the mock we're not actually adding any custom cpt stuff yet.
				'custom_post_types'     => array(),
				'custom_taxonomies'     => array(),
				'default_terms'         => array(),
			)
		);
	}



}
// End of file EE_Custom_Csv_Importer.class.php
// Location: wp-content/plugins/eea-custom-csv-importer/EE_Custom_Csv_Importer.class.php
