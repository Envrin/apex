
# I/O Library (files, directories, remote HTTP requests)

Apex contains an I/O library allowing for easy handline of files, directories, remote HTTP requests, and more.  All functions 
are static allowing them to be easily accessed from anywhere within the software.


### `array io::parse_dir(string $dir, bool $return_dirs = false)`

**Description:** Recursively parses a directory, and returns an array of all files within the given directory, relative to the directory.

**Example**

~~~php
namespace apex;

use apex\core\io;

$files = parse_dir(SITE_PATH . '/src/users', false);
~~~


### `bool io::create_dir(string $dir)`

**Description:** Recursively creates the specific directory.  Starts at the parent directory, and goes through each sub-directory, making sure each exists and creates them if they don't.  Useful to ensure all parent directories of the directory you're trying to create exist and no errors occur.

**Example**

~~~php
namespace apex;

use apex\core\io;

if (!io::create_dir($some_dir)) { 
    echo "Unable to create directory";
}
~~~


### `bool io::remove_dir(string $dir)`

**Description:** Recursively removes a directory.  Will go through all files within the directory, deleing them one at a time, then will delete all sub-directories before finally delete the parent directory.

**Example**
~~~php
namespace apex;

use apex\core\io;

if (!io::remove_dir($some_dir)) { 
    echo "Unable to delete dorectory";
}
~~~


### `string io::send_http_request(string $url, string $method = 'GET', array $request = array(), string $content_type = 'application/x-www-form-urlencoded', int $return_headers = 0)`

**Description:* Send a remote HTTP request via cURL, and retusn the response given in string format.

**NOTE:** If a hidden service, you may also use the `io::send_tor_request()` method instead with the exact same parameters, and the HTTP request will be sent via Tor instead.



### `string io::generate_random_string(int $length, bool $include_chars = false)`

**Description:** Generates a random string with the given length.  The `$include_chars` variable defines whether or not to include special characters in the random string.


### `io::create_zip_archive(string $tmp_dir, string $archive_file)`

**Description:** Creates a zip archive.  Zips up the contents of `$tmp_dir`, and creates the zip archive of `$archive_file`.


### `io::unpack_zip_archive(string $zip_file, string $dirname)`

**Description:** Unpacks the zip archive located at `$zip_file` into the directory at `$dirname`.  Will delete the directory if it currently exists, and will create it.



