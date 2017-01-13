<?php
namespace EventEspresso\CustomCsvImporter\core\batch\JobHandlers;
use EventEspressoBatchRequest\JobHandlerBaseClasses\JobHandler;
use EventEspressoBatchRequest\Helpers\BatchRequestException;
use EventEspressoBatchRequest\Helpers\JobParameters;
use EventEspressoBatchRequest\Helpers\JobStepResponse;
/**
 * Created by PhpStorm.
 * User: mnelson4
 * Date: 06/01/2017
 * Time: 11:55 AM
 */
class TexasBlackTieAndBootsSendTicketNotices extends JobHandler{

    /**
     * @param JobParameters $job_parameters
     *          Job parameters should contain the file_path where the file was uploaded to
     */
    public function create_job(JobParameters $job_parameters)
    {
        $job_parameters->set_job_size(
            \EEM_Transaction::instance()->count(
                array(
                    array(
                        'TXN_hash_salt' => array(
                            '!=',''
                        )
                    )
                )
            )
        );
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Resending began successfully', 'event_espresso')
        );
    }

    public function continue_job(JobParameters $job_parameters, $batch_size = 50)
    {
        $transactions = \EEM_Transaction::instance()->get_all(
            array(
                array(
                    'TXN_hash_salt' => array(
                        '!=', ''
                    )
                ),
                'limit' => array($job_parameters->units_processed(), $batch_size )
            )
        );
        $successes = 0;
        foreach($transactions as $transaction){
            $success = $this->send_ticket_notice($transaction);
            if( $success ) {
                $successes++;
            }
        }
        $job_parameters->mark_processed(count($transactions));
        if($job_parameters->job_size() <= $job_parameters->units_processed()){
           $job_parameters->set_status(JobParameters::status_complete);
        }
        return new JobStepResponse(
            $job_parameters,
            'Enqueued ' . $successes . ' Ticket Messages'
        );
    }



    /**
     * Sends them a ticket message.
     * Note: if you don't want the messages to be sent right away, set messages to be sent on a separate request
     * (in the dashboard ee messages settings) and add add_filter('FHEE__EED_Messages__run_cron__user_wp_cron', '__return_false');
     * which is currently in-place in EED_Custom_Csv_Importer::set_hooks_both())
     * @param $cols_n_values
     */
    protected function send_ticket_notice($transaction){
        //enqueue messages for General Admission primary registrants
        if(
            \EEM_Registration::instance()->exists(
                array(
                    array(
                        'TXN_ID' => $transaction->ID(),
                        'Ticket.TKT_name' => 'Black Tie & Boots 2017 Presidential Inaugural Ball (General Admission)'
                    )
                )
            )
        ){
            $data = array($transaction, null, \EEM_Registration::status_id_approved);
            try {
                $message_processor = \EE_Registry::instance()->load_lib( 'Messages_Processor' );
                $messages_to_generate = $message_processor->setup_mtgs_for_all_active_messengers( 'ticket_notice', $data );
                $message_processor->batch_queue_for_generation_and_persist( $messages_to_generate );
                $message_processor->get_queue()->initiate_request_by_priority();
                return true;
            } catch( \EE_Error $e ) {
                //do whatever error handling is necessary in here.  Maybe log the fails so we know what registrations didn't get added to the queue?
                error_log( 'Error sending message for transaction ' . $transaction->ID() . '. The error was ' . $e->getMessage() . ', with stack trace: ' . $e->getTraceAsString());
            }
        }
        return false;

    }

    public function cleanup_job(JobParameters $job_parameters)
    {
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Wrapping Up...', 'event_espresso')
        );
    }
}
