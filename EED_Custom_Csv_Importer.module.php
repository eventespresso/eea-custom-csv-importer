<?php

/**
 * Class  EED_Custom_Csv_Importer
 *
 * This is where miscellaneous action and filters callbacks should be setup to
 * do your addon's business logic (that doesn't fit neatly into one of the
 * other classes in the mock addon)
 *
 * @package         Event Espresso
 * @subpackage      eea-custom-csv-importer
 * @author              Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class EED_Custom_Csv_Importer extends EED_Module
{
    /**
     * @var         bool
     * @access  public
     */
    public static $shortcode_active = false;



    /**
     * @return EED_Custom_Csv_Importer
     */
    public static function instance()
    {
        return parent::get_instance(__CLASS__);
    }



     /**
      *     set_hooks - for hooking into EE Core, other modules, etc
      *
      *  @access    public
      *  @return    void
      */
    public static function set_hooks()
    {
        EE_Config::register_route('custom_csv_importer', 'EED_Custom_Csv_Importer', 'run');
        self::set_hooks_both();
    }

     /**
      *     set_hooks_admin - for hooking into EE Admin Core, other modules, etc
      *
      *  @access    public
      *  @return    void
      */
    public static function set_hooks_admin()
    {
        // ajax hooks
        add_action('wp_ajax_get_custom_csv_importer', array( 'EED_Custom_Csv_Importer', 'get_custom_csv_importer' ));
        add_action('wp_ajax_nopriv_get_custom_csv_importer', array( 'EED_Custom_Csv_Importer', 'get_custom_csv_importer' ));
        self::set_hooks_both();
    }



    /**
     * Hooks to run on both frontend and backend requests
     */
    public static function set_hooks_both()
    {
        // while running the importer, we don't want to send messages (because we'd like to verify they're a-ok)
        // so just turn off this plugin once importing is done, and then messages will begin to send
        add_filter('FHEE__EED_Messages__run_cron__user_wp_cron', '__return_false');
        add_filter('upload_mimes', array( 'EED_Custom_Csv_Importer', 'allow_csv_uploads'), 30);
        add_filter('cron_schedules', array('EED_Custom_Csv_Importer','add_more_frequent_cron_schedule'));
    }

    public static function add_more_frequent_cron_schedule($schedules)
    {
        if (!isset($schedules["1min"])) {
            $schedules["1min"] = array(
                'interval' => 60,
                'display' => __('Once every minute'));
        }
        return $schedules;
    }



    /**
     * Filters the list of file types allowed for uploading via the media uploader.
     * Adds csv files to that list.
     */
    public static function allow_csv_uploads($mime_types = array())
    {
        $mime_types['csv']  = 'text/csv';
        return $mime_types;
    }


    public static function get_custom_csv_importer()
    {
         echo wp_json_encode(array( 'response' => 'ok', 'details' => 'you have made an ajax request!'));
         die;
    }



    /**
     *    config
     *
     * @return EE_Custom_Csv_Importer_Config
     */
    public function config()
    {
        // config settings are setup up individually for EED_Modules via the EE_Configurable class that all modules inherit from, so
        // $this->config();  can be used anywhere to retrieve it's config, and:
        // $this->_update_config( $EE_Config_Base_object ); can be used to supply an updated instance of it's config object
        // to piggy back off of the config setup for the base EE_Custom_Csv_Importer class, just use the following (note: updates would have to occur from within that class)
        return EE_Registry::instance()->addons->EE_Custom_Csv_Importer->config();
    }






     /**
      *    run - initial module setup
      *
      * @access    public
      * @param  WP $WP
      * @return    void
      */
    public function run($WP)
    {
        add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
    }






    /**
     *  enqueue_scripts - Load the scripts and css
     *
     *  @access     public
     *  @return     void
     */
    public function enqueue_scripts()
    {
        // Check to see if the custom_csv_importer css file exists in the '/uploads/espresso/' directory
        if (is_readable(EVENT_ESPRESSO_UPLOAD_DIR . "css/custom_csv_importer.css")) {
            // This is the url to the css file if available
            wp_register_style('espresso_custom_csv_importer', EVENT_ESPRESSO_UPLOAD_URL . 'css/espresso_custom_csv_importer.css');
        } else {
            // EE custom_csv_importer style
            wp_register_style('espresso_custom_csv_importer', EE_CUSTOM_CSV_IMPORTER_URL . 'css/espresso_custom_csv_importer.css');
        }
        // custom_csv_importer script
        wp_register_script('espresso_custom_csv_importer', EE_CUSTOM_CSV_IMPORTER_URL . 'scripts/espresso_custom_csv_importer.js', array( 'jquery' ), EE_CUSTOM_CSV_IMPORTER_VERSION, true);

        // is the shortcode or widget in play?
        if (EED_Custom_Csv_Importer::$shortcode_active) {
            wp_enqueue_style('espresso_custom_csv_importer');
            wp_enqueue_script('espresso_custom_csv_importer');
        }
    }
}
// End of file EED_Custom_Csv_Importer.module.php
// Location: /wp-content/plugins/eea-custom-csv-importer/EED_Custom_Csv_Importer.module.php
