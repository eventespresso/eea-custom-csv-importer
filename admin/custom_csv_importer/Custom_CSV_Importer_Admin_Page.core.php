<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }
/**
 *
 * Custom_CSV_Importer_Admin_Page
 *
 * This contains the logic for setting up the Custom_CSV_Importer Addon Admin related pages.  Any methods without PHP doc comments have inline docs with parent class.
 *
 *
 * @package			Custom_CSV_Importer_Admin_Page (custom_csv_importer addon)
 * @subpackage 	admin/Custom_CSV_Importer_Admin_Page.core.php
 * @author				Darren Ethier, Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class Custom_CSV_Importer_Admin_Page extends EE_Admin_Page {


	protected function _init_page_props() {
		$this->page_slug = CUSTOM_CSV_IMPORTER_PG_SLUG;
		$this->page_label = CUSTOM_CSV_IMPORTER_LABEL;
		$this->_admin_base_url = EE_CUSTOM_CSV_IMPORTER_ADMIN_URL;
		$this->_admin_base_path = EE_CUSTOM_CSV_IMPORTER_ADMIN;
	}




	protected function _ajax_hooks() {}





	protected function _define_page_props() {
		$this->_admin_page_title = CUSTOM_CSV_IMPORTER_LABEL;
		$this->_labels = array(
			'publishbox' => __('Update Settings', 'event_espresso')
		);
	}




	protected function _set_page_routes() {
		$this->_page_routes = array(
			'default' => '_import_page',
			'import_now' => array(
				'func' => '_import_now',
				'noheader' => TRUE
			),
			'usage' => '_usage'
		);
	}





	protected function _set_page_config() {

		$this->_page_config = array(
			'default' => array(
				'nav' => array(
					'label' => __('Settings', 'event_espresso'),
					'order' => 10
					),
				'metaboxes' => array_merge( $this->_default_espresso_metaboxes, array( '_publish_post_box') ),
				'require_nonce' => FALSE
			),
			'usage' => array(
				'nav' => array(
					'label' => __('Custom CSV Importer Usage', 'event_espresso'),
					'order' => 30
					),
				'require_nonce' => FALSE
			)
		);
	}


	protected function _add_screen_options() {}
	protected function _add_screen_options_default() {}
	protected function _add_feature_pointers() {}

	public function load_scripts_styles() {
		wp_register_script( 'espresso_custom_csv_importer_admin', EE_CUSTOM_CSV_IMPORTER_ADMIN_ASSETS_URL . 'espresso_custom_csv_importer_admin.js', array( 'espresso_core' ), EE_CUSTOM_CSV_IMPORTER_VERSION, TRUE );
		wp_enqueue_script( 'espresso_custom_csv_importer_admin');
	}

	public function admin_init() {
		EE_Registry::$i18n_js_strings[ 'confirm_reset' ] = __( 'Are you sure you want to reset ALL your Event Espresso Custom CSV Importer Information? This cannot be undone.', 'event_espresso' );
	}

	public function admin_notices() {}
	public function admin_footer_scripts() {}




	/**
	 * _settings_page
	 * @param $template
	 */
	protected function _import_page() {
		$form = new EventEspresso\CustomCSVImporter\core\forms\SubmissionForm();
        $form->populate_from_session();
		$this->_template_args['admin_page_content'] = $form->form_open(
                EE_Admin_Page::add_query_args_and_nonce(
                    array(
                    'action' => 'import_now'
                    ),
                    EE_CUSTOM_CSV_IMPORTER_ADMIN_URL
                ),
                'post'
            )
            . $form->get_html_and_js()
            . $form->form_close();
		$this->display_admin_page_with_no_sidebar();
	}
    protected function _import_now(){
        $form = new EventEspresso\CustomCSVImporter\core\forms\SubmissionForm();
        $form->receive_form_submission( $_REQUEST );
        if($form->is_valid()){
            wp_redirect(EE_Admin_Page::add_query_args_and_nonce(array(
                'page'        => 'espresso_batch',
                'batch'       => EED_Batch::batch_job,
                'file_path'      => $form->get_input_value('file_path'),
                'job_handler' => urlencode('EventEspresso\CustomCSVImporter\core\batch\JobHandlers\TexasBlackTieAndBootsCSVImport'),
                'return_url'  => urlencode(EE_CUSTOM_CSV_IMPORTER_ADMIN_URL),
            )));
            die;
        }else{
            $this->redirect_after_action(false, 'form', 'processed', array( 'action' => 'default' ) );
        }
    }

	protected function _usage() {
		$this->_template_args['admin_page_content'] = EEH_Template::display_template( EE_CUSTOM_CSV_IMPORTER_ADMIN_TEMPLATE_PATH . 'custom_csv_importer_usage_info.template.php', array(), TRUE );
		$this->display_admin_page_with_no_sidebar();
	}

}
// End of file Custom_CSV_Importer_Admin_Page.core.php
// Location: /wp-content/plugins/eea-custom-csv-importer/admin/custom_csv_importer/Custom_CSV_Importer_Admin_Page.core.php