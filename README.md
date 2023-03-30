<h1 align="center">Laravel File Uploader Project</h1>

## About

This is a little project for a Laravel microsite which can be hosted within the network and serve to upload files to a SFTP endpoint. The site works on the premise of the need to upload invoices in pdf format to the endpoint where they can be logged and accessed via the frontend interface whilst providing visibility of associated error handling.

The site will process using three stages:

1. Payload Generation
   - A job entry will be created in the database job table
   - A list of valid files in the outbound directory will be itemised and entered as child records of the active job into their own table
   - Invalid filenames identified by a regex will be error handled at this point being moved into a separate error folder and the error status logged against their records in the db and on the main job table
   - A valid job number or a status of false indicating no files to process will be returned at this stage.
2. Upload Processing & Archival Stage
   - A list of files to be processed will be gathering from the db based on the job number
   - These will then be uploaded with associated error handling if this process fails for any reason
   - Successfully transferred files will then be archived and removed from the outbound directory. In this example I have symlinked the archive directory into the public directory to allow the use of the asset('storage') function in Laravel to provide a hyperlink to view these pdfs from the frontend after successful upload.

Current active file pregmatch for stage 1: PO(\w*)(\d*)_(\w*)(\d*)_*

The site can be logged into which will show jobs processed and the ability of viewing uploaded invoices by job id or as a whole on a searchable page. 

## Project Cloning And Initial Setup

1. The project needs to be cloned to a new vhost directory using git.

2. Dependencies can be installed via:
     <br><code>composer install</code>
     <br><code>npm install</code>

3. The <strong>.env</strong> needs to be copied from the default <strong>.env.example</strong> file.
   (Add database connection details)

4. A new Laravel key needs to be generated via: 
<br><code>php artisan key:generate</code>

5. Create a new database for use with the project and enter the connection details into the .env file

6. Create the base folders for operation:
<br><code>/storage/app/invoices/outbound</code>
<br><code>/storage/app/invoices/archive</code>

<italic>Optional:
You could mount a network samba share to the invoices folder and then create the outbound and archive folders there to make it easy for users to add files to the outbound directory for upload.</italic>

7. Create the symbolic link from the /public directory to the /storage/app/public directory for use by the asset('storage') function:
<br><code>php artisan storage:link</code>

8. Create a symbolic link from the /storage/app/invoices/archive folder to /public/archive
<br><code>ln -s <full path to vhost>/storage/app/invoices/archive <full path to vhost>/public/archive</code>

9. Add associated sftp credentials into the <strong>.env</strong> file:
<pre>SFTP_HOST=ip address
SFTP_USERNAME=username
SFTP_PASSWORD=password
SFTP_ROOT=upload path</pre>

10. Run <code>php artisan migrate</code> to create the table structure

11. Run <code>npm run build</code> to create necessary files

12. When you first access the homepage you will need to log in. In order to create a new user go to <code>webiste url/register</code> you will then be able to login to the main site.  
