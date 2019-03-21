<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\IOException;
use ZipArchive;
use SqlParser;

/**
( Handles various input / output operations on files and 
* directories, plus allows sending of remote HTTP requests, and handling 
* zip archives.  Please refer to developer documentation for full details.
*/
class io
{

/**
* Parse a directory recursively, and return all 
* files and/or directories.
* 
*     @param string $rootdir The directory name / path to parse.
*     @param bool $return_dirs Whether or not to return directory names, or only filenames.
*     @return array An array of all resulting file / directory names.
*/
public static function parse_dir(string $rootdir, bool $return_dirs = false) 
{

    // Debug
    debug::add(5, fmsg("Parsing the directory, {1}", $rootdir), __FILE__, __LINE__);

    // Set variables
    $search_dirs = array('');
    $results = array();

    // Go through directories
    while ($search_dirs) {
        $dir = array_shift($search_dirs);

        // Add director, if needed
        if ($return_dirs === true && !empty($dir)) { $results[] = $dir; }

        // Open, and search directory
        if (!$handle = opendir("$rootdir/$dir")) {  
        throw new IOException('no_open_dir', "$rootdir/$dir");
    }
        while (false !== ($file = readdir($handle))) {
            if ($file == '.' || $file == '..') { continue; }

            // Parse file / directory
            if (is_dir("$rootdir/$dir/$file")) {
                if (empty($dir)) { $search_dirs[] = $file; }
                else { $search_dirs[] = "$dir/$file"; }
            } else {
                if (empty($dir)) { $results[] = $file; }
                else { $results[] = "$dir/$file"; }
            }
        }
        closedir($handle);
    }

    // Return
    return $results;

}

/**
* Creates a directory recursively.  Goes 
* through the parent directories, and creates them as necessary if they do not exist.
*
*     @param string $dirname The directory to create.
*/
public static function create_dir(string $dirname) 
{

    // Debug
    debug::add(4, fmsg("Creating new directory at {1}", $dirname), __FILE__, __LINE__);


    if (is_dir($dirname)) { return; }

    // Format dirname
    $dirname = trim(str_replace(SITE_PATH, "", $dirname), '/');
    $dirs = explode("/", $dirname);

    // Go through dirs
    $tmp_dir = '';
    foreach ($dirs as $dir) { 
        $tmp_dir .= '/' . $dir;
    if (is_dir(SITE_PATH . '/' . $tmp_dir)) { continue; }

        // Create directory
        try { 
            @mkdir(SITE_PATH . '/' . $tmp_dir);
        } catch (Exception $e) { 
            throw new IOException('no_mkdir', $tmp_dir);
        }
    }

    // Return
    return true;

}

/**
* Removes a directory recursively.  Goes through all 
* files and sub-directories, and deletes them before deleting the 
* parent directory.
*
*     @param string $dirname The directory name to delete.
*/
public static function remove_dir(string $dirname) 
{

    // Debug
    debug::add(4, fmsg("Remoing the directory at {1}", $dirname), __FILE__, __LINE__);

    if (!is_dir($dirname)) { return true; }

    // Parse dir
    $dirname = trim(str_replace(SITE_PATH, "", $dirname), '/');
    $files = self::parse_dir(SITE_PATH . '/' . $dirname, true);

    // Go through, and delete all files
    foreach ($files as $file) {
        if (is_dir(SITE_PATH . "/$dirname/$file")) { continue; }

        try { 
            unlink(SITE_PATH . "/$dirname/$file");
        } catch (Exception $e) { 
            throw new IOException('no_unlink', "$dirname/$file");
        
    }}

    // Delete directories
    $files = array_reverse($files);
    foreach ($files as $subdir) {
        if (!is_dir(SITE_PATH . "/$dirname/$subdir")) { continue; }

        try {
            rmdir(SITE_PATH . "/$dirname/$subdir");
        } catch (Exception $e) { 
            throw new IOException('normdir', "$dirname/$subdir");
        }
    }

    // Remove directory
    try {
    rmdir(SITE_PATH . '/' . $dirname);
    } catch (Exception $e) { 
        throw new IOException('no_rmdir', $dirname);
    }

    // Return
    return true;

}

/**
* Send a remote HTTP request
*
*     @param string $url The full URL to send hte HTTP request to.
*     @param string $method The method (GET/POST usually) of the request.  Defaults to GET.
*     @param array $request The request contents to send in array format.
*     @param string $content_type THe content type of the request.  Generally not needed, as the default works.
*     @param int $return_headers A 1 or 0 definine whether or not to return the HTTP readers of the response.
*     @return string Returns the response from the server.
*/
public static function send_http_request(string $url, string $method = 'GET', $request = array(), string $content_type = 'application/x-www-form-urlencoded', int $return_headers = 0) 
{

    // Debug
    debug::add(2, fmsg("Sending HTTP request to the URL: {1}", $url), __FILE__, __LINE__);

    // Send via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    //curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, $return_headers);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, SITE_PATH . '/data/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, SITE_PATH . '/data/tmp/cookies.txt');
    if (preg_match("/^https/", $url)) { curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); }

    // Set POST fields, if needed
    if ($method == 'POST') { 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    }

    // Send http request
    $response = curl_exec($ch);
    curl_close($ch);

    // Return
    return $response;

}

/**
/ Send a remote HTTP request via the Tor network
*
*     @param string $url The full URL to send hte HTTP request to.
*     @param string $method The method (GET/POST usually) of the request.  Defaults to GET.
*     @param array $request The request contents to send in array format.
*     @param string $content_type THe content type of the request.  Generally not needed, as the default works.
*     @param int $return_headers A 1 or 0 definine whether or not to return the HTTP readers of the response.
*     @return string Returns the response from the server.
*/
public static function send_tor_request(string $url, string $method = 'GET', array $request = array(), string $content_type = 'application/x-www-form-urlencoded', int $return_headers = 0) 
{

    // Debug
    //debug::add(3, fmsg("Sending HTTP request ia Tor to the URL: {1}"< $url), __FILE__, __LINE__);

    // Send via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1');
    curl_setopt($ch, CURLOPT_PROXYPORT, 9050);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    //curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, $return_headers);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, SITE_PATH . '/data/tmp/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, SITE_PATH . '/data/tmp/cookies.txt');
    if (preg_match("/^https/", $url)) { curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); }

    // Set POST fields, if needed
    if ($method == 'POST') { 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    }

    // Send http request
    $response = curl_exec($ch);
    curl_close($ch);

    // Return
    return $response;

}

/**
* Generate a random string.
*
*     @param int $length The length of the random string.
*     @param bool $include_chars Whether or not to include special characters.
*     @return string The generated random string.
*/
public static function generate_random_string(int $length = 6, bool $include_chars = false):string 
{

    // Debug
    debug::add(5, fmsg("Generating random string of length {1}", $length), __FILE__, __LINE__);

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($include_chars === true) { $characters = '!@#$%^&*()_-+=' . $characters . '!@#$%^&*()_-+='; }
    
    // Generate random string
    $string = '';
    for ($x = 1; $x <= $length; $x++) {
        $num = sprintf("%0d", rand(1, strlen($characters) - 1));
        $string .= $characters[$num];
    }
    
    // Return
    return $string;

}

/** 
* Creates a new zip archive from the 
* given directory name.
* 
*     @param string $dir The directory to archive
*     @param string $archive_file The location of the resulting archive file.
*/
public static function create_zip_archive(string $tmp_dir, string $archive_file)
{

    // Debug
    debug::add(2, fmsg("Creating a new zip archive from directory {1} and aving at {2}", $tmp_dir, $archive_file), __FILE__, __LINE__);
 
    if (file_exists(SITE_PATH . '/tmp/' . $archive_file)) { @unlink(SITE_PATH . '/tmp/' . $archive_file); }
    $zip = new ZipArchive();
    $zip->open(SITE_PATH . "/tmp/$archive_file", ZIPARCHIVE::CREATE);

    // Go through files
    $files = self::parse_dir($tmp_dir, true);
    foreach ($files as $file) { 
        if (is_dir($tmp_dir . '/' . $file)) {
            $zip->addEmptyDir($file);
        } else {
            $zip->addFile($tmp_dir . '/' . $file, $file);
        }
    }
    $zip->close();

    // Return
    return true;

}

/**
* Unpack a zip archive
*     @param string $zip_file The path to the .zip archive
*     @param string $dirname The directory to create and unpack the archive to
*     @return bool Whether or not the operation was successful.
*/
public static function unpack_zip_archive(string $zip_file, string $dirname)
{

    // Debug
    debug::add(2, fmsg("Unpacking zip archive {1} into the directory {2}", $zip_file, $dirname), __FILE__, __LINE__);
 
    // Debug
    debug::add(3, fmsg("Unpacking zip archive {1} to directory {2}", $zip_file, $dirname), __FILE__, __LINE__);

    // Ensure archive file exists
    if (!file_exists($zip_file)) { 
        throw new IOException('zip_not_exists', $zip_file);
    }

    // Create directory to unpack to
    if (is_dir($dirname)) { self::remove_dir($dirname); }
    self::create_dir($dirname);

    // Open zip file
    if (!$zip = zip_open($zip_file)) { 
        throw new IOException('zip_invalid', $zip_file);
    }

    // Unzip package file
    while ($file = zip_read($zip)) {

        // Format filename
        $filename = zip_entry_name($file);
        $filename = str_replace("\\", "/", $filename);
        if ($filename == '') { continue; }

        // Get contents
        $contents = '';
        while ($line = zip_entry_read($file)) { $contents .= $line; }
        if ($contents == '') { continue; }

        // Debug
        debug::add(5, fmsg("Unpacking file from zip archive, {1}", $filename), __FILE__, __LINE__);

        // Save file
        self::create_dir(dirname("$dirname/$filename"));
        file_put_contents("$dirname/$filename", $contents); 
    }
    zip_close($zip);

    // Debug
    debug::add(3, fmsg("Successfully unpacked zip archive {1} to directory {2}", $zip_file, $dirname), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Execute SQL file
*      @param string $sqlfile The path to the SQL file to execute against the database
*/
public static function execute_sqlfile(string $sqlfile)
{

    // Check if SQL file exists
    if (!file_exists($sqlfile)) { 
        return;
    }

    // Debug
    debug::add(4, fmsg("Starting to execute SQL file, {1}", $sqlfile), __FILE__, __LINE__);

    // Execute SQL
    $sql_lines = SqlParser::parse(file_get_contents($sqlfile));
    foreach ($sql_lines as $sql) { 
        DB::query($sql); 
    }

    // Debug
    debug::add(2, fmsg("Successfully executed SQL file against database, {1}", $sqlfile), __FILE__, __LINE__, 'info');

}
}

