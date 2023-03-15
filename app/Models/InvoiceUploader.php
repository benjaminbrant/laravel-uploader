<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Models\Job;
use App\Models\Invoice;

class InvoiceUploader
{
    public function __construct()
    {
        //if needed
    }

    /**
     * Generate a payload array of all files in the inbound folder
     * ready for upload to sftp endpoint
     * @return array
     */
    public function generatePayload()
    {
        $payload = [];

        //Obtain a list of files to process from the storage/app/invoices/outbound mounted samba share folder
        $files = Storage::disk('outbound')->files();

        //Create a payload entry from each filename
        foreach ($files as $file)
        {
            //Obtain PO number from filename and add to payload as key
            $components = explode('_', $file);
            $po = trim($components[0]);

            //Create additional entry fields per po invoice
            $payload[$po] = [
                "filename" => $file,
                "po" => $po,
                "local-size" => Storage::disk('outbound')->size($file),
                "remote-size" => null,
                "is-uploaded" => false,
                "is-processed" => false,
                "is-identical-filesize" => null,
            ];
        }

        //Return payload to be processed by sftp upload function
        return $payload;
    }

    /**
     * Generate new job and create model entries for files pending upload
     * @return int
     */
    public function generateModelPayload()
    {
        //generate new job
        $job = new Job(["errors_encountered" => false]);
        $job->save();
        $jobId = $job->id;

        //Obtain a list of files to process from the storage/app/invoices/outbound mounted samba share folder
        $files = Storage::disk('outbound')->files();

        //Create a payload entry from each filename
        foreach ($files as $file)
        {
            //Obtain PO number from filename and add to payload as key
            $components = explode('_', $file);
            $po = trim($components[0]);

            //Create and save model
            $invoice = new Invoice([
                "filename" => $file,
                "po" => $po,
                "local_size" => Storage::disk('outbound')->size($file),
                "is_uploaded" => false,
                "is_processed" => false,
            ]);

            $job->invoices()->save($invoice);
        }

        return $jobId;
    }

    /**
     * Take payload array and attempt to upload files to sftp endpoint
     * record any errors and move files accordingly
     * @param array $payload
     * @return array
     */
    public function processPayload(array $payload)
    {
        $files = &$payload;
        foreach ($files as $po => $data)
        {
            //Attempt upload of file to sftp endpoint
            $result = Storage::disk('sftp')
                ->put(
                    $data["filename"],
                    Storage::disk('outbound')->get($data["filename"])
                );

            //If result successful and file exists at endpoint process normally
            if ($result && Storage::disk('sftp')->exists($data["filename"]))
            {
                $files[$po]["remote-size"] = Storage::disk('sftp')->size($data["filename"]);
                $files[$po]["is-uploaded"] = true;
                $files[$po]["is-identical-filesize"] = ($files[$po]["local-size"] === $files[$po]["remote-size"]);
            }
            else
            {
                //file flagged as having error at upload
                $files[$po]["is-uploaded"] = false;
                //@todo move file to error folder
            }
        }

        //return completed payload information
        return $payload;
    }

    public function processModelPayload(int $jobId)
    {
        //attempt to pull job model
        $job = $this->pullJobRecord($jobId);

        if ($job)
        {
            foreach ($job->invoices()->getResults() as $invoice)
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
                    //indicate errors encountered on main job
                    $job->errors_encountered = true;
                    $job->save();
                }

                //save model back to db
                $invoice->save();
            }
            return true;
        }
        else
        {
            //job model not found in db
            return false;
        }
    }

    public function archiveUploadedFiles(int $jobId)
    {
        $job = $this->pullJobRecord($jobId);

        if ($job)
        {
            //Get string of current date archival path
            $path = $this->getArchiveSToragePath();

            foreach ($job->invoices()->getResults() as $invoice)
            {
                if ($invoice->is_uploaded)
                {
                    //archive
                    $result = Storage::disk('archive')
                        ->put(
                            $path . $invoice->filename,
                            Storage::disk('outbound')->get($invoice->filename)
                        );
                    if ($result)
                    {
                        //remove original
                        Storage::disk('outbound')->delete($invoice->filename);
                        //write archive location
                        $invoice->archive_location = asset('storage/archive/' . $path . $invoice->filename);
                        $invoice->archival_error = false;
                        //save changes back to model
                        $invoice->save();
                    }
                    else
                    {
                        //error
                        $invoice->archival_error = true;
                        //@todo add an archive error field
                    }
                }
                else
                {
                    //move to error folder
                    $result = Storage::disk('errors')
                        ->put(
                            $path . $invoice->filename,
                            Storage::disk('outbound')->get($invoice->filename)
                        );
                    $invoice->archival_error = true;
                    $invoice->save();
                    $job->errors_encountered = true;
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

    /**
     * Get date specific folder string for use with archiving files uploaded
     * @return false|string
     */
    protected function getArchiveSToragePath()
    {
        $timezone = new \DateTimeZone('Europe/London');

        try
        {
            $now = new \DateTime("now", $timezone);
        }
        catch (\Exception $e)
        {
            return false;
        }

        //Return today's date string folder path
        return "{$now->format('Y')}/{$now->format('M')}/{$now->format('d')}/";
    }

    //Scratch functions
    public function test()
    {
        return Storage::disk('sftp')->files();
    }

    public function testUpload(array $payload)
    {
        $files = &$payload;

        foreach ($files as $po => $data)
        {
            echo $data["filename"] . "\n";
        }
    }

    public function createRecords()
    {
        $job = new Job;
    }


}
