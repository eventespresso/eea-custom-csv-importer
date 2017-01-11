<?php
namespace EventEspresso\CustomCSVImporter\core\batch\JobHandlers;
use EventEspressoBatchRequest\JobHandlerBaseClasses\JobHandler;
use EventEspressoBatchRequest\Helpers\BatchRequestException;
use EventEspressoBatchRequest\Helpers\JobParameters;
use EventEspressoBatchRequest\Helpers\JobStepResponse;

use EventEspresso\core\domain\entities\RegCode;
use EventEspresso\core\domain\entities\RegUrlLink;
/**
 * Created by PhpStorm.
 * User: mnelson4
 * Date: 06/01/2017
 * Time: 11:55 AM
 */
class TexasBlackTieAndBootsCSVImport extends JobHandler{

    /**
     * @param JobParameters $job_parameters
     *          Job parameters should contain the file_path where the file was uploaded to
     */
    public function create_job(JobParameters $job_parameters)
    {
        $file_path = $job_parameters->request_datum('file_path');
        $lines_of_file_as_array = \EE_CSV::instance()->import_csv_to_multi_dimensional_array($file_path);
        $job_parameters->set_job_size(count($lines_of_file_as_array));
        $job_parameters->add_extra_data('column_mapping', $this->rememberCSVHeaders($file_path));
        //the headers row was one of them that needed processing and it's done now
        $job_parameters->mark_processed(1);
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Import began successfully', 'event_espresso')
        );
    }



    /**
     * @param $file_path
     * @param $start_line
     * @param $num_lines
     * @return array
     */
    protected function read_lines_into_array($file_path, $start_line, $num_lines){
        //some day we will probably want to do something more efficient, like
        //only reading the needed lines from the CSV
        // $file = new SplFileObject($file_path);
        //
        // // Loop until we reach the end of the file.
        // while (!$file->eof()) {
        //     // Echo one line from the file.
        //     echo $file->fgets();
        // }
        //
        // // Unset the file to call __destruct(), closing the file handle.
        // $file = null;

        //but for now let's just get something in here. If efficiency becomes a problem, we'll work on it more
        $entire_file = \EE_CSV::instance()->import_csv_to_multi_dimensional_array($file_path);
        return array_slice($entire_file, $start_line, $num_lines);
    }



    /**
     * Returns an array mapping CSV header column headers to their position number
     * @param $file_path
     * @return array
     * @throws \EventEspressoBatchRequest\Helpers\BatchRequestException
     */
    protected function rememberCSVHeaders($file_path){
        $entire_file = \EE_CSV::instance()->import_csv_to_multi_dimensional_array($file_path);
        if( ! is_array( $entire_file ) ){
            throw new BatchRequestException(sprintf(esc_html__('Could not read headers from file $1$s', 'event_espresso'), $file_path));
        }
        $headers = $entire_file[0];
        return array_flip( $headers );
    }

    public function continue_job(JobParameters $job_parameters, $batch_size = 50)
    {
        $csv_rows = $this->read_lines_into_array($job_parameters->request_datum('file_path'), $job_parameters->units_processed(), $batch_size );
        $header_mapping = $job_parameters->extra_datum('column_mapping');
        foreach($csv_rows as $csv_row){
            $mapped_row_data = array();
            foreach($header_mapping as $column_name => $column_number){
                $mapped_row_data[$column_name] = $csv_row[$column_number];
            }
            $this->processRow($mapped_row_data);
        }
        $job_parameters->mark_processed(count($csv_rows));
        if($job_parameters->job_size() <= $job_parameters->units_processed()){
           $job_parameters->set_status(JobParameters::status_complete);
        }
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Going good', 'event_espresso')
        );
    }



    /**
     * Takes a row from the DB, where keys are their column's name, and values are its value in the row
     * and processes it
     * @param $cols_n_values
     */
    protected function processRow($cols_n_values){
        //is this is the first registration in this transaction?
        //if first: create line item(S), transaction, attendee, registration, payment, registration_payment, answers, enqueue message, line items, update ticket and datetime tickets available
        $payment_method = \EEM_Payment_Method::instance()->get_one_by_slug('other');
        //check for a transaction with the CSV's "Registrant ID"
        $csv_reg_id = $cols_n_values['Registrant ID'];
        $transaction = \EEM_Transaction::instance()->get_one(
            array(
                array(
                    'TXN_hash_salt' => $csv_reg_id
                )
            )
        );
        if( ! $transaction instanceof \EE_Transaction ) {
            $transaction = \EE_Transaction::new_instance(
                array(
                    'PMD_ID'        => $payment_method->ID(),
                    'TXN_hash_salt' => $csv_reg_id,
                    'STS_ID' => \EEM_Transaction::complete_status_code
                )
            );
            $transaction->save();
            $grand_total_li = \EEH_Line_Item::create_total_line_item($transaction);
            $grand_total_li->save();
        }else{
            $grand_total_li = $transaction->total_line_item();
        }
        $ticket = $this->findOrCreateTicket($cols_n_values['Event Fee Name']);
        $line_item = \EEH_Line_Item::add_ticket_purchase($grand_total_li, $ticket);
        $attendee_data = array(
            'ATT_fname' => $cols_n_values['Badge First Name'],
            'ATT_lname' => $cols_n_values['Badge Last Name'],
            'ATT_email' => $cols_n_values['E-Mail Address']
        );
        $attendee = \EEM_Attendee::instance()->find_existing_attendee( $attendee_data );
        if( ! $attendee instanceof \EE_Attendee){
            $attendee = \EE_Attendee::new_instance($attendee_data);
            $attendee->save();
        }


        $payment = \EE_Payment::new_instance(
            array(
                'TXN_ID' => $transaction->ID(),
                'PMD_ID' => $payment_method->ID(),
                'PAY_amount' => $ticket->price(),
                'PAY_txn_id_chq_nmbr' => $csv_reg_id,
                'STS_ID' => \EEM_Payment::status_id_approved,
                'PAY_source' => \EEM_Payment_Method::scope_admin,
            )
        );
        $payment->save();
        $transaction->set_paid( $transaction->paid() + $payment->amount() );
        $transaction->save();
        $other_regs_on_txn = \EEM_Registration::instance()->count(
            array(
                array(
                    'TXN_ID' => $transaction->ID(),
                )
            )
        );
        $reg_url_link = new RegUrlLink($other_regs_on_txn + 1, $line_item);
        $reg_code = new RegCode($reg_url_link, $transaction, $ticket);
        $reg = \EE_Registration::new_instance(
            array(
                'EVT_ID' => $ticket->get_event_ID(),
                'TXN_ID' => $transaction->ID(),
                'TKT_ID' => $ticket->ID(),
                'ATT_ID' => $attendee->ID(),
                'STS_ID' => \EEM_Registration::status_id_approved,
                'REG_final_price' => $ticket->price(),
                'REG_paid' => $ticket->price(),
                'REG_code' => $reg_code,
                'REG_url_link' => $reg_url_link,
                'REG_count' => $other_regs_on_txn + 1,
                'REG_group_size' => $other_regs_on_txn + 1,
                'REG_att_is_going' => true,
            )
        );
        $reg->save();
        //retroactively update all sister registrations' group sizes
        $regs_updated = \EEM_Registration::instance()->update(
            array(
                'REG_group_size' => $other_regs_on_txn + 1
            ),
            array(
                array(
                    'TXN_ID' => $transaction->ID()
                )
            )
        );
        $reg_payment = \EE_Registration_Payment::new_instance(
            array(
                'REG_ID' => $reg->ID(),
                'PAY_ID' => $payment->ID(),
                'RPY_amount' => $ticket->price()
            )
        );
        $reg_payment->save();
        //add answer?
        if( isset( $cols_n_values['Are you 21 or older?'] ) ) {
            $answer = \EE_Answer::new_instance(
                array(
                    'ANS_value' => $cols_n_values['Are you 21 or older?'],
                    'QST_ID'    => $this->findOrCreateAgeQuestionID(),
                    'REG_ID'    => $reg->ID()
                )
            );
            $answer->save();
        }
        //enqueue message?
    }

    protected function findOrCreateAgeQuestionID(){
        $question_id = \EEM_Question::instance()->get_var(
            array(
                array(
                    'QST_display_text' => 'Are you 21 or older?',
                    'QST_admin_label' => 'are-you-21-or-older'
                ),
                'limit' => 1
            )
        );
        if( ! $question_id ) {
            $question = \EE_Question::new_instance(
                array(
                    'QST_display_text' => 'Are you 21 or older?',
                    'QST_admin_label' => 'are-you-21-or-older',

                )
            );
            $question->save();
            $question_id = $question->ID();
        }
        return $question_id;
    }
    protected function findOrCreateTicket($ticket_name){
        $ticket = \EEM_Ticket::instance()->get_one(
            array(
                array(
                    'TKT_name' => $ticket_name
                )
            )
        );
        if( ! $ticket instanceof \EE_Ticket ) {
            $ticket = \EE_Ticket::new_instance(
                array(
                    'TKT_name' => $ticket_name
                )
            );
            $ticket->save();
            $event = \EE_Event::new_instance(
                array(
                    'EVT_name' => 'Auto-created',
                    'status' => \EEM_CPT_Base::post_status_publish
                )
            );
            $event->save();
            $datetime = \EE_Datetime::new_instance(
                array(
                    'EVT_ID' => $event->ID(),
                )
            );
            $datetime->save();
            $ticket->_add_relation_to($datetime, 'Datetime');
        }
        return $ticket;
    }

    public function cleanup_job(JobParameters $job_parameters)
    {
        //make sure the ticket and datetime sold counts are accurate
        \EEM_Datetime::instance()->update_sold(\EEM_Datetime::instance()->get_all());
        \EEM_Ticket::instance()->update_tickets_sold(\EEM_Ticket::instance()->get_all());
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Wrapping Up...', 'event_espresso')
        );
    }
}
