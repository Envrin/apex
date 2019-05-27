<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
use apex\core\lib\exceptions\ApexException;
use apex\core\io;
use apex\core\date;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Exceptions\DropboxClientException;


/**
* Handles all backup functionality, including performing the 
* local backups, plus uploading to the appropriate remote service (AWS, Dropbox, Google Drive, etc.)
*/
class backups
{


/**
* Performs a backup of the system, and stores archive 
* file locally within the /storage/backups/ directory.
*     @param string $type The type of backup to perform (db or full)
*     @return string The name or the .tar.gz archive that was created.
*/
public function perform_backup(string $type = 'full')
{

    // Check if backups enabled
    if (registry::config('core:backups_enable') != 1) { 
        return false;
    }

    // Remove expired archives
    $this->remove_expired_archives();

    // Perform local backup
    $archive_file = $this->backup_local($type);

    // Upload to remote server, if needed
    if (registry::config('backups_remote_service') == 'aws') { 
        $this->upload_aws($archive_file);
    } elseif (registry::config('backups_remote_service') == 'dropbox') { 
        $this->upload_dropbox($archive_file);
    } elseif (registry::config('backups_remote_service') == 'google_drive') { 
        $this->upload_google_drive($archive_file);
    }

    // Delete local file, if needed
    if (registry::config('core:backups_save_locally') != 1) { 
        @unlink(SITE_PATH . '/storage/backups/' . $archive_file);
    }

    // Return
    return $archive_file;

}

/**
* Remove expired archives from /storage/backups/ directory
*/
private function remove_expired_archives()
{

    // Get time
    if (!$start_time = date::subtract_interval(registry::config('core:backups_retain_length'), time(), false)) { 
        return;
    }

    // Go through all files
    $files = io::parse_dir(SITE_PATH . '/storage/backups', false);
    foreach ($files as $file) { 

        // Check filename
        if (!preg_match("/^(\w+)-(\d\d\d\d)-(\d\d)-(\d\d)_(\d+)\.tar\.gz$/", $file, $match)) { 
            continue;
        }

        // Check time
        $secs = mktime(0, 0, 0, (int) $match[3], (int) $match[4], (int) $match[2]) + (int) $match[5];
        if ($start_time> $secs) { continue; }

        // Delete file
        @unlink(SITE_PATH . '/storage/backups/' . $file);

        // Debug
        debug::add(4, fmsg("Deleted backup file, as it has expired, {1}", $file), __FILE__, __LINE__, 'info');
    }

}

/**
* Perform a local backup, and save archive file to /storage/backups/ directory
*     @param string $type The type of backup to perform (db / full)
*     @return string The name of the archive file within /storage/backups/ directory
*/
private function backup_local(string $type)
{

    // Get database info
    $dbinfo = registry::$redis->hgetall('config:db_master');

    // Define .sql dump file
    $sqlfile = SITE_PATH . '/dump.sql';
    if (file_exists($sqlfile)) { @unlink($sqlfile); }

    // Dump mySQL database
    $dump_cmd = "mysqldump -u" . $dbinfo['dbuser'] . " -p" . $dbinfo['dbpass'] . " -h" . $dbinfo['dbhost'] . " -P" . $dbinfo['dbport'] . " " . $dbinfo['dbname'] . " > $sqlfile";
    system($dump_cmd);

    // Get filename
    $secs = (date('H') * 3600) + (date('i') * 60) + date('s');
    $archive_file = $type . '-' . date('Y-m-d_') . $secs . '.tar';
    io::create_dir(SITE_PATH . '/storage/backups');
    chdir(SITE_PATH);

    // Archive the system
    $backup_source = $type == 'db' ? "./dump.sql" : "./";
    $tar_cmd = "tar --exclude='storage/backups/*' -cf " . SITE_PATH . '/storage/backups/' . $archive_file . " $backup_source";
    system($tar_cmd);
    system("gzip " . SITE_PATH . "/storage/backups/$archive_file");

    // Update next time to run
    $interval = registry::config('core:backups_' . $type . '_interval');
    $next_date = registry::add_interval($interval, time(), false);
    registry::update_config_var('core:backups_nexet_' . $type, $next_date);

    // Return
    return $archive_file . '.gz';

}

/**
* Upload a backup archive to DropBox
*     @param string $filename The name of the file within /storage/backups/ to upload
*/
private function upload_dropbox(string $filename)
{

    // Start client
    $app = new DropboxApp(registry::config('core:backups:dropbox_client_id'), registry::config('core:backups_dropbox_client_secret'), registry::config('backups_dropbox_access_token'));
    $dropbox = new Dropbox($app);

    // Upload file
    try {
        $dropboxFile = new DropboxFile(SITE_PATH . '/storage/backups/' . $filename);
        $uploadedFile = $dropbox->upload($dropboxFile, "/" . $filename, ['autorename' => true]);
    } catch (Exception $E) {
        throw new ApexException('error', "Unable to upload to Dropbox: " . $e->getMessage());
    }

    // Return
    return true;

}

/**
* Upload backup archive to Google Drive
*     @param string $filename The filename from the /storage/backups/ directory to upload
*/
private function upload_google_drive(string $filename)
{

    // Start client
    $client = new \Google_Client();
    $client->setClientId(registry::config('core:backups_gdrive_client_id'));
    $client->setClientSecret(registry::config('core:backups_gdrive_client_secret'));
    $client->refreshToken(registry::config('core:backups_gdrive_refresh_token'));

    // Start service
    $service = new \Google_Service_Drive($client);
    $adapter = new \Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter($service, 'root');

}

}

