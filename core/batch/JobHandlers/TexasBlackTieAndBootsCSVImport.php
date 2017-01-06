<?php
namespace EventEspresso\CustomCSVImporter\core\batch\JobHandlers;
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

    public function continue_job(JobParameters $job_parameters, $batch_size = 50)
    {
        $job_parameters->mark_processed($batch_size);
        return new JobStepResponse(
            $job_parameters,
            esc_html__('Going good', 'event_espresso')
        );
    }

    public function cleanup_job(JobParameters $job_parameters)
    {
        return new JobStepResponse(
            $job_parameters,
            esc_html__('aLL DONE', 'event_espresso')
        );
    }
}
