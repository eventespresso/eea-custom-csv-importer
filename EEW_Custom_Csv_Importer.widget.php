<?php

if (! defined('EVENT_ESPRESSO_VERSION')) {
exit();
}
/*
 * EEW_Custom_Csv_Importer
 * Displays a List of Custom_Csv_Importer in the Sidebar
 *
 * @package			Event Espresso
 * @subpackage 	eea-custom-csv-importer
 * @author				Brent Christensen
 * @since 				4.3
 *
 * ------------------------------------------------------------------------
 */
class EEW_Custom_Csv_Importer extends WP_Widget
{
    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'ee-custom_csv_importer-widget',
            __('Event Espresso Custom CSV Importer Widget', 'event_espresso'),
            array(
                'description' => __('Displays Espresso Custom CSV Importer in a widget.', 'event_espresso')
             ),
            array(
                'width' => 300,
                'height' => 350,
                'id_base' => 'ee-custom_csv_importer-widget'
            )
        );
    }



    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance)
    {

        EE_Registry::instance()->load_class('Question_Option', array(), false, false, true);

        // Set up some default widget settings.
        $defaults = array(
            'title' => 'Custom CSV Importer'
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        add_filter('FHEE__EEH_Form_Fields__label_html', '__return_empty_string');
        $yes_no_values = array(
            EE_Question_Option::new_instance(array( 'QSO_value' => 0, 'QSO_desc' => __('No', 'event_espresso'))),
            EE_Question_Option::new_instance(array( 'QSO_value' => 1, 'QSO_desc' => __('Yes', 'event_espresso')))
        );

        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e('Title:', 'event_espresso'); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" width="20" value="<?php echo $instance['title']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('yes_or_no_question'); ?>">
                <?php _e('Yes or No?', 'event_espresso'); ?>
            </label>
            <?php
                echo EEH_Form_Fields::select(
                    __('Yes or No?', 'event_espresso'),
                    $instance['yes_or_no_question'],
                    $yes_no_values,
                    $this->get_field_name('yes_or_no_question'),
                    $this->get_field_id('yes_or_no_question')
                );
            ?>
        </p>
<?php
    }



    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $instance)
    {
        // Strip tags (if needed) and update the widget settings.
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }



    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        // get the current post
        global $post;
        if (isset($post->post_content)) {
             // check the post content for the short code
            if (strpos($post->post_content, '[ESPRESSO_CUSTOM_CSV_IMPORTER') === false) {
                EED_Custom_Csv_Importer::$shortcode_active = true;
                // Before widget (defined by themes).
                echo $args['before_widget'];
                // Title of widget (before and after defined by themes).
                $title = apply_filters('widget_title', $instance['title']);
                if (! empty($title)) {
                    echo $args['before_title'] . $title . $args['after_title'];
                }
                // load scripts
                EE_Custom_Csv_Importer::instance()->enqueue_scripts();
                // settings
                $attributes = array();
                echo EE_Custom_Csv_Importer::instance()->display_custom_csv_importer($attributes);
                // After widget (defined by themes).
                echo $args['after_widget'];
            }
        }
    }
}

// End of file EEW_Custom_Csv_Importer.widget.php
// Location: /wp-content/plugins/eea-custom-csv-importer/EEW_Custom_Csv_Importer.widget.php