<?php

namespace EventEspresso\CustomCsvImporter\core\forms;

/**
 * Created by PhpStorm.
 * User: mnelson4
 * Date: 06/01/2017
 * Time: 10:54 AM
 */
class SubmissionForm extends \EE_Form_Section_Proper
{
    public function __construct(array $options_array = array())
    {
        $options_array['subsections'] = array(
            'file_url' => new \EE_Admin_File_Uploader_Input(),
            'file_path' => new \EE_Hidden_Input(),
            'submit' => new \EE_Submit_Input(
                array(
                    'default' => esc_html__('Submit Now', 'event_espresso')
                )
            )
        );
        parent::__construct($options_array);
    }



    /**
     * Verifies the file submission is a real local file, and populates
     * the hidden file_path input
     * @param array $req_data
     * @throws \EE_Validation_Error
     */
    public function _normalize($req_data)
    {
        parent::_normalize($req_data);
        $uploaded_file_url = $this->get_input_value('file_url');
        global $wpdb;
        $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s' LIMIT 1;", $uploaded_file_url));
        if (! $attachment_id) {
            $this->add_validation_error(new \EE_Validation_Error(sprintf(esc_html__('No attachment found at %1$s', 'event_espresso'), $uploaded_file_url)));
            return;
        }
        $file_path = get_attached_file($attachment_id);
        if (! $file_path) {
            $this->add_validation_error(
                new \EE_Validation_Error(
                    sprintf(
                        esc_html__('Although there is a database record for the file %1$s, no actual file was found.', 'event_espresso'),
                        $file_path
                    )
                )
            );
            return;
        }
        $file_path_input = $this->get_input('file_path');
        $file_path_input->set_default($file_path);
    }
}
