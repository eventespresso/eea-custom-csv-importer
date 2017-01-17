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
                    $this->_get_where_txn_where_conditions()
                ),
                'TXN_ID',
                true
            )
        );
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Resending began successfully', 'event_espresso')
        );
    }

    protected function _get_where_txn_where_conditions(){
        // send to texact IDs
        // return array(
        //     'TXN_ID' => array('IN',
        //                       array(
        //                           636
        //                       ))
        // );

        //send to specific emails
        // return array(
        //     'Registration.Attendee.ATT_email' => array( 'IN',
        //                                                 array(
        //                                                     'joe.ayoub@gmail.com',
        //                                                     'smithb46@gmail.com',
        //                                                     'a.sajadi@yahoo.com',
        //                                                     'mitchell@cgcn.com',
        //                                                     'rsisson@conservamerica.org',
        //                                                     'Judi@CasaWinslow.com',
        //                                                     'benpsparks@gmail.com',
        //                                                     'lauren@cgcn.com',
        //                                                     'jodiw@century21bcs.com',
        //                                                     'spunkygal54@outlook.com',
        //                                                     'juliansamuel@mac.com',
        //                                                     'charitycapemay@gmail.com',
        //                                                     'Mcole@ifratelli.net',
        //                                                     'lreynosa@newpark.com',
        //                                                     'Bjgreynolds@gmail.com',
        //                                                     'swansbmg@hotmail.com',
        //                                                     'factoutlet@aol.com',
        //                                                     'ebourg@aol.com',
        //                                                     'teterex@earthlink.net',
        //                                                     'stacymanuelrm@yahoo.com',
        //                                                     'valkyrie610@yahoo.com',
        //                                                     'mgrovenstein@danieldefense.com',
        //                                                     'derrithb@yahoo.com',
        //                                                     'cdaniel@danieldefense.com',
        //                                                     'christy@edwardslawtonconsulting.com',
        //                                                     'Mark@tracestrats.com',
        //                                                     'lmeasterling@gmail.com',
        //                                                     'katherynburchett@gmail.com',
        //                                                     'H.j.howse@live.com',
        //                                                     'Yewgenesh_lara@yahoo.com',
        //                                                     'chairman@sjcgop.org',
        //                                                     'Plawton@bojh.com',
        //                                                     'grandnmanr@fuse.net',
        //                                                     'jsaylor@ctp-inc.com',
        //                                                     'rockstar888@me.com',
        //                                                     'rcrowe@embreegroup.com',
        //                                                     'chris@electchrisfielder.com',
        //                                                     'will@willmetcalf.com',
        //                                                     'markshaaber@hotmail.com',
        //                                                     'linda.nelson@memorialhermann.org',
        //                                                     'uttam.dhillon@gmail.com',
        //                                                     'jefftmajewski@gmail.com',
        //                                                     'meganb@earthlink.net',
        //                                                     'strochesset@comcast.net',
        //                                                     'Dio123@aol.com',
        //                                                     'Cgatlin@burr.com',
        //                                                     'mluttrell79@gmail.com',
        //                                                     'shoulder.md@gmail.com',
        //                                                     'scutrona@elevate.com',
        //                                                     'jboisvert.esq@gmail.com',
        //                                                 ))
        // );

        //send to transactions with GA tickets
        // return array(
        //     'Registration.Ticket.TKT_name' => array( '=', 'Black Tie & Boots 2017 Presidential Inaugural Ball (General Admission)')
        // );

        //send to transactions who have no message sent yet
        return array(
            'Message.TXN_ID' => array('IS_NULL')
        );
    }

    public function continue_job(JobParameters $job_parameters, $batch_size = 50)
    {
        $transactions = \EEM_Transaction::instance()->get_all(
            array(
                $this->_get_where_txn_where_conditions(),
                'limit' => array($job_parameters->units_processed(), $batch_size )
            )
        );
        $transaction_ids = array();
        foreach($transactions as $transaction){
            $success = $this->send_ticket_notice($transaction);
            if( $success ) {
                $transaction_ids[]=$transaction->ID();
            }
        }
        $job_parameters->mark_processed(count($transactions));
        if($job_parameters->job_size() <= $job_parameters->units_processed()){
           $job_parameters->set_status(JobParameters::status_complete);
        }
        return new JobStepResponse(
            $job_parameters,
            'Enqueued Ticket Messages for transactions ' . implode(', ', $transaction_ids)
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
        $data = array($transaction, null, \EEM_Registration::status_id_approved);
        try {
            $message_processor = \EE_Registry::instance()->load_lib( 'Messages_Processor' );
            $messages_to_generate = $message_processor->setup_mtgs_for_all_active_messengers( 'ticket_notice', $data );
            $message_processor->batch_queue_for_generation_and_persist( $messages_to_generate );
            $message_processor->get_queue()->initiate_request_by_priority();
            return true;
        } catch( \EE_Error $e ) {
            //do whatever error handling is necessary in here.  Maybe log the fails so we know what registrations didn't get added to the queue?
            trigger_error( 'Error sending message for transaction ' . $transaction->ID() . '. The error was ' . $e->getMessage() . ', with stack trace: ' . $e->getTraceAsString());
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
