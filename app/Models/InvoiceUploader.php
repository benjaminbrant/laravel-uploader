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
        $job = new Job();
        $job->save();
        $jobId = $job->id;

        //to record invalid file names if encountered
        $invalidFiles = [];

        //Obtain a list of files to process from the storage/app/invoices/outbound mounted samba share folder
        $files = Storage::disk('outbound')->files();

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
            //$path = $this->getArchiveSToragePath();

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

//                if ($invoice->is_uploaded)
//                {
//                    //archive
//                    $result = Storage::disk('archive')
//                        ->put(
//                            $path . $invoice->filename,
//                            Storage::disk('outbound')->get($invoice->filename)
//                        );
//                    if ($result)
//                    {
//                        //remove original
//                        Storage::disk('outbound')->delete($invoice->filename);
//                        //write archive location
//                        $invoice->archive_location = asset('storage/archive/' . $path . $invoice->filename);
//                        $invoice->archival_error = false;
//                        //save changes back to model
//                        $invoice->save();
//                    }
//                    else
//                    {
//                        //error
//                        $invoice->archival_error = true;
//                        //@todo add an archive error field
//                    }
//                }
//                else
//                {
//                    //move to error folder
//                    $result = Storage::disk('errors')
//                        ->put(
//                            $path . $invoice->filename,
//                            Storage::disk('outbound')->get($invoice->filename)
//                        );
//                    $invoice->archival_error = true;
//                    $invoice->save();
//                    $job->errors_encountered = true;
//                    $job->save();
//                }
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
