<?php

/**
 *
 * espresso_events_Registration_Form_Hooks
 * Hooks various messages logic so that it runs on indicated Events Admin Pages.
 * Commenting/docs common to all children classes is found in the EE_Admin_Hooks parent.
 *
 *
 * @package         espresso_events_Custom_Csv_Importer_Hooks
 * @subpackage      wp-content/plugins/eea-custom-csv-importer/admin/custom_csv_importer/espresso_events_Custom_Csv_Importer_Hooks.class.php
 * @author              Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class espresso_events_Custom_Csv_Importer_Hooks extends EE_Admin_Hooks
{
    protected function _set_hooks_properties()
    {
        $this->_name = 'custom_csv_importer';
    }

    public function _redirect_action_early_update_category($redirection_query_args)
    {
    }

    public function _redirect_action_early_insert_category($redirection_query_args)
    {
    }
}
// End of file espresso_events_Custom_Csv_Importer_Hooks.class.php
// Location: /wp-content/plugins/eea-custom-csv-importer/admin/custom_csv_importer/espresso_events_Custom_Csv_Importer_Hooks.class.php
