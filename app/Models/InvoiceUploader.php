<?php

namespace App\Models;

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
