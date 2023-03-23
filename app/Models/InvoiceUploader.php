<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class InvoiceUploader
{
    public function __construct()
    {
        //if needed
    }

    /**
     * Generate new job and create model entries for files pending upload
     * @return int
     */
    public function generateModelPayload()
    {
        //generate new job
        $job = new Job();
        $job->save();
        $jobId = $job->id;

        //to record invalid file names if encountered
        $invalidFiles = [];

        //Obtain a list of files to process from the storage/app/invoices/outbound mounted samba share folder
        $files = Storage::disk('outbound')->files();

        if (count($files) > 0)
        {
            //Create a payload entry from each filename
            foreach ($files as $file)
            {
                //Check if file is in valid format to be processed
                if (preg_match('/PO(\w*)(\d*)_(\w*)(\d*)_*/', $file))
                {
                    //Obtain PO number from filename and add to payload as key
                    $components = explode('_', $file);
                    //strip 'PO' from string
                    $po = substr($components[0], 2);
                    $factory = $components[1];

                    //Create and save model
                    $invoice = new Invoice([
                        "filename" => $file,
                        "po" => $po,
                        "factory" => strtolower($factory),
                        "local_size" => Storage::disk('outbound')->size($file),
                    ]);

                    $job->invoices()->save($invoice);
                }
                else
                {
                    //Add invalid filename to error array
                    $invalidFiles[] = $file;

                    //add entry to invoice that file in invalid format
                    $invoice = new Invoice([
                        "filename" => $file,
                        "po" => "0",
                        "factory" => "0",
                        "is_invalid_filename" => true
                    ]);

                    //Move to outbound error payload folder
                    $result = Storage::disk('outbound')->put(
                        'errors/payload/' . $invoice->filename,
                        Storage::disk('outbound')->get($invoice->filename)
                    );
                    //if successful move delete original
                    if ($result)
                    {
                        Storage::disk('outbound')->delete($invoice->filename);
                    }

                    $job->invoices()->save($invoice);
                }
            }

            //Record job error if needed
            if (count($invalidFiles) > 0)
            {
                $job->is_payload_error = true;
                $job->payload_error_msg = "Invalid filenames encountered while generating payload: " . implode(", ", $invalidFiles);
                $job->save();
            }

            return $jobId;
        }
        else
        {
            $job->no_invoices_to_process = true;
            $job->save();
            exit;
        }

    }

    public function processModelPayload(int $jobId)
    {
        if (!$jobId)
        {
            //No valid Job ID Provided to method
            return false;
        }

        //Set archive flag for later use filing files, default false
        $archiveFlag = false;

        //attempt to pull job model
        $job = $this->pullJobRecord($jobId);

        if ($job)
        {
            $uploadedFileErrors = [];
            foreach ($job->invoices()->where('is_invalid_filename', '=', NULL)->getResults() as $invoice)
            {
                if ($this->uploadFile($invoice->filename))
                {
                    //file uploaded
                    $invoice->remote_size = Storage::disk('sftp')->size($invoice->filename);
                    $invoice->is_uploaded = true;
                    $invoice->is_identical_filesize = ($invoice->local_size === $invoice->remote_size);
                }
                else
                {
                    //file failed upload
                    $invoice->is_uploaded = false;
                    //Add filename to array indicating errors
                    $uploadedFileErrors[] = $invoice->filename;
                    //move file to errors/upload
                    $result = Storage::disk('outbound')->put(
                        'errors/upload/' . $invoice->filename,
                        Storage::disk('outbound')->get($invoice->filename)
                    );
                    //if successful move delete original
                    if ($result)
                    {
                        Storage::disk('outbound')->delete($invoice->filename);
                    }
                }

                //save model back to db
                $invoice->save();
            }

            if (count($uploadedFileErrors) > 0)
            {
                //indicate errors encountered on main job
                $job->is_upload_error = true;
                $job->upload_error_msg = "Upload errors encountered for the following files: " . implode(', ', $uploadedFileErrors);
                $job->save();
            }

            $archiveFlag = true;
        }
        else
        {
            //job model not found in db
            $archiveFlag = false;
        }

        //If archive flag valid run job
        $archiveFlag ? $this->archiveUploadedFiles($jobId) : exit;
    }

    protected function archiveUploadedFiles(int $jobId)
    {
        $job = $this->pullJobRecord($jobId);

        if ($job)
        {
            //To record archival error filenames
            $archivalErrors = [];

            foreach ($job->invoices()->where('is_uploaded', '=', true)->getResults() as $invoice)
            {

                //@todo attempt to archive file record error if encountered
                if (strlen($invoice->factory) > 1)
                {
                    //archive file to factory folder
                    $result = Storage::disk('archive')
                        ->put(
                            $invoice->factory . '/' . $invoice->filename,
                            Storage::disk('outbound')->get($invoice->filename)
                        );

                    if ($result)
                    {
                        //remove original
                        Storage::disk('outbound')->delete($invoice->filename);
                        //write archive location to open files from frontend
                        $invoice->archive_location = asset('storage/archive/') . $invoice->factory . '/' . $invoice->filename;
                        //save changes back to model
                        $invoice->save();
                    }
                    else
                    {
                        //add file to archive error list
                        $archivalErrors[] = $invoice->filename;

                        //move file to errors folder
                        Storage::disk('outbound')->put(
                            'errors/' . $invoice->filename,
                            Storage::disk('outbound')->get($invoice->filename)
                        );

                        //save model
                        $invoice->archival_error = true;
                        $invoice->save();
                    }
                }

                if (count($archivalErrors) > 0)
                {
                    $job->is_archive_error = true;
                    $job->archive_error_msg = "Errors encountered while archiving for the following files: " . implode(', ', $archivalErrors);
                    $job->save();
                }

            }
        }
        else
        {
            return false;
        }
    }

    //Utility Functions

    /**
     * Upload file to SFTP endpoint
     * @param string $filename
     * @return bool
     */
    protected function uploadFile(string $filename): bool
    {
        return Storage::disk('sftp')
            ->put(
                $filename,
                Storage::disk('outbound')->get($filename)
            );
    }

    /**
     * Pull a job record back if found
     * @param int $jobId
     * @return Job | false
     */
    protected function pullJobRecord(int $jobId): Job | false
    {
        try {
            return Job::findOrFail($jobId);
        }
        catch (ModelNotFoundException $e)
        {
            return false;
        }
    }

}
