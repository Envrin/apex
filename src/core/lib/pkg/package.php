<?php
declare(strict_types = 1); 

namespace apex\core\lib\pkg;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
use apex\core\lib\network;
use apex\core\lib\exceptions\ApexException;
use apex\core\lib\exceptions\PackageException;
use apex\core\lib\exceptions\RepoException;
use apex\core\io;
use apex\core\components;
use apex\core\lib\pkg\package_config;
use ZipArchive;
use CurlFile;
use apex\core\lib\third_party\SqlParser;


/**
* Handles all package functions -- create, compile, download, install, remove.
*/
class package
{

    // Properties
    private $tmp_dir;
    private $toc = array();
    private $file_num = 1;
    private $pkg_alias;

/**
* Insert a new package into the database
*     @param int $repo_id The ID# of the repo the package belongs to
*     @param string $pkg_alias The alias of the package
*     @param string $name The full name of the package
*     @param string $access Access level of the package (public / private), defaults to 'public'
*     @param string $version The version of the package, defaults to 1.0.0
*/
public function insert(int $repo_id, string $pkg_alias, string $name, string $access = 'public', string $version = '1.0.0')
{

    // Validate the alias
    if (!$this->validate_alias($pkg_alias)) { 
        throw new PackageException('invalid_alias', $pkg_alias);
    }

    // Debug
    debug::add(2, fmsg("Inserting new package into database, alias: {1}, name: {2}, repo_id: {3}", $pkg_alias, $name, $repo_id), __FILE__, __LINE__, 'info');

    // Insert into db
    DB::insert('internal_packages', array(
        'is_private' => ($access == 'private' ? 1 : 0), 
        'repo_id' => $repo_id, 
        'version' => $version, 
        'last_modified' => date('Y-m-d H:i:s'), 
        'alias' => strtolower($pkg_alias), 
        'display_name' => $name)
    );
    $package_id = DB::insert_id();

    // Return
    return $package_id;

}

/**
* Create a new package for development.
*     @param int $repo_id The ID# of the repo the package belongs to
*     @param string $pkg_alias The alias of the package
*     @param string $name The full name of the package
*     @param string $access Access level of the package (public / private), defaults to 'public'
*     @return int The ID# of the newly created package
*     @param string $version The version of the package, defaults to 1.0.0
*     @return int The ID# of the new package
*/
public function create(int $repo_id, string $pkg_alias, string $name, string $access = 'public', string $version = '1.0.0')
{

    // Debug
    debug::add(5, fmsg("Starting creation of package alias: {1}, name: {2}, repo_id: {3}", $pkg_alias, $name, $repo_id), __FILE__, __LINE__);

    // Insert package to database
    $package_id = $this->insert($repo_id, $pkg_alias, $name, $access, $version);
    $pkg_alias = strtolower($pkg_alias);

    // Create directories
    $pkg_dir = SITE_PATH . '/etc/' . $pkg_alias;
    io::create_dir($pkg_dir);
    io::create_dir("$pkg_dir/upgrades");
    io::create_dir(SITE_PATH . '/src/' . $pkg_alias);

    // Save blank files
    file_put_contents("$pkg_dir/install.sql", '');
    file_put_contents("$pkg_dir/reset.sql", '');
    file_put_contents("$pkg_dir/remove.sql", '');

    // Save package.php file
    $pkg_file = base64_decode('PD9waHAKCm5hbWVzcGFjZSBhcGV4OwoKdXNlIGFwZXhccGtnXHBhY2thZ2U7CgoKY2xhc3MgcGtnX35hbGlhc34gCnsKCiAgICAvLyBCYXNpYyBwYWNrYWdlIHZhcmlhYmxlcwogICAgcHVibGljICRhY2Nlc3MgPSAnfmFjY2Vzc34nOwogICAgcHVibGljICRuYW1lID0gJ35uYW1lfic7CiAgICBwdWJsaWMgJGRlc2NyaXB0aW9uID0gJyc7CgovKioKKiBUaGUgY29uc3RydWN0b3IgdGhhdCBkZWZpbmVzIHRoZSB2YXJpb3VzIGNvbmZpZ3VyYXRpb24gCiogYXJyYXlzIG9mIHRoZSBwYWNrYWdlIHN1Y2ggYXMgY29uZmlnIHZhcnMsIGhhc2hlcywgCiogbWVudXMsIGFuZCBzbyBvbi4KKgoqIFBsZWFzZSBzZWUgdGhlIEFwZXggZG9jdW1lbnRhdGlvbiBmb3IgYSBmdWxsIGV4cGxhbmF0aW9uLgoqLwpwdWJsaWMgZnVuY3Rpb24gX19jb25zdHJ1Y3QoKSAKewoKLyoqKioqKioqKioKKiBDb25maWcgVmFyaWFibGVzCiogICAgIEFycmF5IG9mIGtleS12YWx1ZSBwYWlycyBmb3IgYWRtaW4gL3N5c3RlbSAKKiAgICAgZGVmaW5lZCBzZXR0aW5ncywgYXZhaWxhYmxlIHZpYSB0aGUgcmVnaXN0cnk6OiRjb25maWcgYXJyYXkuCioqKioqKioqKiovCgokdGhpcy0+Y29uZmlnID0gYXJyYXkoKTsKCgovKioqKioqKioqKgoqIEhhc2hlcwoqICAgICBBcnJheSBvZiBhc3NvY2lhdGl2ZSBhcnJheXMgdGhhdCBkZWZpbmUgdmFyaW91cyBsaXN0cyBvZiBrZXktdmFsdWUgcGFpcnMgdXNlZC4KKiAgICAgVXNlZCBmb3IgcXVpY2tseSBnZW5lcmF0aW5nIHNlbGVjdCAvIGNoZWNrYm94IC8gcmFkaW8gbGlzdHMgdmlhIGNyZWF0ZV9oYXNoX29wdGlvbnMoKSBhbmQgZ2V0X2hhc2hfdmFyaWFibGUoKSBmdW5jdGlvbnMuCioqKioqKioqKiovCgokdGhpcy0+aGFzaCA9IGFycmF5KCk7CgoKLyoqKioqKioqKioKKiBNZW51cwoqICAgICBNZW51cyBmb3IgdGhlIGFkbWluaXN0cmF0aW9uIHBhbmVsLCBtZW1iZXIncyBhcmVhLCAKKiAgICAgIGFuZCBwdWJsaWMgc2l0ZS4gIFBsZWFzZSByZWZlciB0byBkZXZlbG9wZXIgZG9jdW1lbnRhdGlvbiAKKiAgICAgIGZvciBmdWxsIGRldGFpbHMuCioqKioqKioqKiovCgokdGhpcy0+bWVudXMgPSBhcnJheSgpOwoKCi8qKioqKioqKioqCiogRXh0ZXJuYWwgRmlsZXMKKiAgICAgT25lIGRpbWVuc2lvbmFsIGFycmF5IG9mIGFsbCBleHRlcm5hbCBmaWxlcywgcmVsYXRpdmUgdG8gdGhlIGluc3RhbGxhdGlvbiBkaXJlY3RvcnkuCiogICAgIChlZy4gJy9wbHVnaW5zL3NvbWVkaXIvbXlfcGx1Z2luLmpzJykKKioqKioqKioqKi8KCiR0aGlzLT5leHRfZmlsZXMgPSBhcnJheSgpOwoKCn0KCi8qKgoqIEluc3RhbGwgQmVmb3JlCiogICAgICBFeGVjdXRlZCBiZWZvcmV0aGUgaW5zdGFsbGF0aW9uIG9mIGEgcGFja2FnZSBiZWdpbnMuCiovCnB1YmxpYyBmdW5jdGlvbiBpbnN0YWxsX2JlZm9yZSgpCnsKCn0KCi8qKgoqIEluc3RhbGwgQWZ0ZXIKKiAgICAgRXhlY3V0ZWQgYWZ0ZXIgaW5zdGFsbGF0aW9uIG9mIGEgcGFja2FnZSwgb25jZSBhbGwgU1FMIGlzIGV4ZWN1dGVkIGFuZCBjb21wb25lbnRzIGFyZSBpbiBwbGFjZS4KKi8KcHVibGljIGZ1bmN0aW9uIGluc3RhbGxfYWZ0ZXIoKSAKewoKfQoKLyoqCiogUmVzZXQKKiAgICAgIEV4ZWN1dGVkIHdoZW4gYWRtaW4gcmVzZXRzIHRoZSBwYWNrYWdlIHRvIGRlZmF1bHQgc3RhdGUgYWZ0ZXIgaXQgd2FzIGluc3RhbGxlZC4KKi8KcHVibGljIGZ1bmN0aW9uIHJlc2V0KCkgCnsKCn0KCi8qKgoqIFJlc2V0IHJlZGlzLiAgSXMgZXhlY3V0ZWQgd2hlbiBhZG1pbmlzdHJhdG9yIHJlc2V0cyB0aGUgcmVkaXMgZGF0YWJhc2UsIAoqIGFuZCBzaG91bGQgcmVnZW5lcmF0ZSBhbGwgcmVkaXMga2V5cyBhcyBuZWNlc3NhcnkgZnJvbSB0aGUgbXlTUUwgZGF0YWJhc2UKKi8KcHVibGljIGZ1bmN0aW9uIHJlc2V0X3JlZGlzKCkKewoKfQoKLyoqCiogUmVtb3ZlCiogICAgICBFeGVjdXRlZCB3aGVuIHRoZSBwYWNrYWdlIGlzIHJlbW92ZWQgZnJvbSB0aGUgc2VydmVyLgoqLwpwdWJsaWMgZnVuY3Rpb24gcmVtb3ZlKCkgCnsKCn0KCgp9Cgo=');
    $pkg_file = str_replace("~alias~", $pkg_alias, $pkg_file);
    $pkg_file = str_replace("~version~", $version, $pkg_file);
    $pkg_file = str_replace("~name~", $name, $pkg_file);
    $pkg_file = str_replace("~access~", $access, $pkg_file);
    file_put_contents("$pkg_dir/package.php", $pkg_file);

    // Debug
    debug::add(1, fmsg("Successfully created new package with alias: {1}, name: {2}, repo_id: {3}", $pkg_alias, $name, $repo_id), __FILE__, __LINE__, 'info');

    // Return
    return $package_id;

}

/**
* Validate a package alias for proper format, and ensure it 
* does not already exist in the system.
*     @param string $pkg_alias The package alias to validate
*     @return bool Whether or not the alias is valid
*/
public function validate_alias(string $pkg_alias):bool
{

    // Debug
    debug::add(5, fmsg("Validating package alias: {1}", $pkg_alias), __FILE__, __LINE__);

    // Ensure valid alias
    if ($pkg_alias == '') { return false; }
    elseif (preg_match("/[\W\s]/", $pkg_alias)) { return false; }

    // Check if package already exists
    if ($row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", strtolower($pkg_alias))) { 
        return false;
    }

    // Return
    return true;

}

/**
* Compiles a package for publication to a repository.
* Goes through all package components, and compiles as necessary into the system tmp directory.
* 
*     @param string $Pkg_alias The alias of the package to compile.
*     @return string The filename or the created archive file,
*/
public function compile(string $pkg_alias):string 
{

    // Debug
    debug::add(3, fmsg("Start compiling pacakge for publication to repository, {1}", $pkg_alias), __FILE__, __LINE__);

    // Load package
    $client = new package_config($pkg_alias);
    $pkg = $client->load();

    // Create tmp directory
    $tmp_dir = sys_get_temp_dir() . '/apex_' . $pkg_alias;
    io::remove_dir($tmp_dir);
    io::create_dir($tmp_dir);
    io::create_dir("$tmp_dir/files");
    $this->tmp_dir = $tmp_dir;

    // Debug
    debug::add(4, fmsg("Compiling, loaded package configuration and created tmp directory for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Go through components
    $components = array();
    $rows = DB::query("SELECT * FROM internal_components WHERE owner = %s ORDER BY id", $pkg_alias);
    foreach ($rows as $row) { 

        // Go through files
        $has_php = false;
        $files = components::get_all_files($row['type'], $row['alias'], $row['package'], $row['parent']);
    foreach ($files as $file) { 
            if (preg_match("/\.php$/", $file)) { $has_php = true; }
            if (!file_exists(SITE_PATH . '/' . $file)) { continue; }
            $this->add_file($file);
        }
        if ($has_php === false) { continue; }

        // Add to $components array
        $vars = array(
            'type' => $row['type'], 
            'order_num' => $row['order_num'], 
            'package' => $row['package'], 
            'parent' => $row['parent'], 
            'alias' => $row['alias'], 
            'value' => $row['value']
        );
        array_push($components, $vars);
    }
    file_put_contents(SITE_PATH . '/etc/' . $pkg_alias . '/components.json', json_encode($components));

    // Debug
    debug::add(4, fmsg("Compiling package, successfully compiled aall components and created componentss.sjon file for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Copy over basic package files
    $pkg_dir = SITE_PATH . '/etc/' . $pkg_alias;
    $files = array('components.json', 'package.php', 'install.sql', 'install_after.sql', 'reset.sql', 'remove.sql');
    foreach ($files as $file) {
        if (!file_exists("$pkg_dir/$file")) { continue; }
        copy("$pkg_dir/$file", "$tmp_dir/$file");
    }

    // External files
    foreach ($pkg->ext_files as $file) { 

        // Check for * mark
        if (preg_match("/^(.+?)\*$/", $file, $match)) { 
            $files = io::parse_dir(SITE_PATH . '/' . $match[1]);
            foreach ($files as $tmp_file) { $this->add_file($match[1] . $tmp_file); }
        } else { 
            $this->add_file($file);
        }
    }

    // docs and /src/tpl/ dirclearectories
    $addl_dirs = array(
        'docs/' . $pkg_alias, 
        'src/' . $pkg_alias . '/tpl'
    );
    foreach ($addl_dirs as $dir) { 
        if (!is_dir(SITE_PATH . '/' . $dir)) { continue; }
        $addl_files = io::parse_dir(SITE_PATH . '/' . $dir);
        foreach ($addl_files as $file) { 
            $this->add_file($dir . '/' . $file);
        }
    }

    // Save JSON file
    file_put_contents("$tmp_dir/toc.json", json_encode($this->toc));

    // Debug
    debug::add(4, fmsg("Compiling, gatheered all files and saved toc.json for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Create archive
    $version = DB::get_field("SELECT version FROM internal_packages WHERE alias = %s", $pkg_alias);
    $archive_file = sys_get_temp_dir() . '/' . $pkg_alias . '-' . str_replace(".", "_", $version) . '.zip';
    io::create_zip_archive($tmp_dir, $archive_file);

    // Debug
    debug::add(3, fmsg("Successfully compiled package for publication, {1}", $pkg_alias), __FILE__, __LINE__);

    // Return
    return $archive_file;

}

/**
* Compiles a package, and uploads it to the appropriate repository.
* 
*     @param string $pkg_alias The alias of the package to publish.
*     @param string $version The version of the package being published (eg. 1.0.4)
*     @return bool Whther or not the operation was successful.
*/
public function publish(string $pkg_alias, string $version = ''):bool 
{

    // Get package
    if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $pkg_alias)) { 
        throw new PackageException('not_exists', $pkg_alias);
    }
    if ($version == '') { $version = $row['version']; }

    // Compile
    $archive_file = $this->compile($pkg_alias);

    // Load package
    $client = new package_config($pkg_alias);
    $pkg = $client->load();

    // Set request
    $request = array(
        'access' => $pkg->access, 
        'name' => $pkg->name, 
        'version' => $version, 
        'contents' => new CurlFile($archive_file, 'application/gzip', $archive_file)
    );

    // Send HTTP request
    $network = new network();
    $vars = $network->send_repo_request((int) $row['repo_id'], $pkg_alias, 'publish', $request);

    // Delete archive file
    unlink($archive_file);

    // Debug
    debug::add(1, fmsg("Successfully published the package to repository, {1}", $pkg_alias), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Fully install a package.  Downoads the package from the 
* appropriate repository, unpacks it, and installed it.
*     @param string $pkg_alias The alias of the packagte to install
*     @param int $repo_id Optional ID# of repo to download from.  If not specified, all repos are searched.
*/
public function install(string $pkg_alias, int $repo_id = 0)
{

    // Debug
    debug::add(3, fmsg("Starting download and install of package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Download package
    list($tmp_dir, $repo_id, $vars) = $this->download($pkg_alias, $repo_id);

    // Add to database
    $package_id = $this->insert($repo_id, $pkg_alias, $vars['name'], 'public', $vars['version']);

    // Install
    $this->install_from_dir($pkg_alias, $tmp_dir);

}

/**
* Download a package from a repository, and 
* unpack it into the tmp system directory
*     @param string $pkg_alias The alias of the package to download
*     @param int $repo_id Optional ID# of repo to download from.  If not specified, all repos are searched.
*     @return string Directory path of where the package was unpacked at
*      @return array Second element of retnr is the response from repo
*/
public function download(string $pkg_alias, int $repo_id = 0)
{

    // Debug
    debug::add(3, fmsg("Starting download of package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Initialize
    $network = new network();

    // Get repo, if needed
    if ($repo_id == 0) { 

        // Check package on all repos
        $repos = $network->check_package($pkg_alias);
        if (count($repos) == 0) { 
            throw new ApexException('error', "The package does not exist in any repositories listed within the system, {1}", $pkg_alias);
        }
        $repo_id = array_keys($repos)[0];
    }

    // Get repo
    if (!$repo = DB::get_idrow('internal_repos', $repo_id)) { 
        throw new RepoException('not_exists', $repo_id);
    }

    // Send request
    $vars = $network->send_repo_request((int) $repo_id, $pkg_alias, 'download');

// Save contents
    $zip_file = sys_get_temp_dir() . '/apex_' . $pkg_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    file_put_contents($zip_file, base64_decode($vars['contents']));

    // Unpack zip file
    $tmp_dir = sys_get_temp_dir() . '/apex_' . $pkg_alias;
    io::unpack_zip_archive($zip_file, $tmp_dir);
    @unlink($zip_file);

    // Debug
    debug::add(3, fmsg("Successfully downloaded package {1} and unpacked it at {2}", $pkg_alias, $tmp_dir), __FILE__, __LINE__);

    // Return
    return array($tmp_dir, $repo_id, $vars);

}

/**
* Install a package from a directory.  This assumes 
* the package has already been downloaded, unpacked on the server, and added to the database
*
*     @param string $pkg_alias The alias of the package being installed
*     @param string $tmp_dir The directory where the package is currently unpacked
*/
public function install_from_dir(string $pkg_alias, string $tmp_dir)
{

    // Create /pkg/ directory
    $pkg_dir = SITE_PATH . '/etc/' . $pkg_alias;
    io::create_dir($pkg_dir);

    // Debug
    debug::add(4, fmsg("Starting package install from unpacked directory of package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Copy over /pkg/ files
    $files = array('components.json', 'package.php', 'install.sql', 'install_after.sql', 'reset.sql', 'remove.sql');
    foreach ($files as $file) { 
        if (!file_exists("$tmp_dir/$file")) { continue; }
        copy("$tmp_dir/$file", "$pkg_dir/$file");
    }

    // Copy over all files
    $toc = json_decode(file_get_contents("$tmp_dir/toc.json"), true);
    foreach ($toc as $file => $file_num) { 
        io::create_dir(dirname(SITE_PATH . '/' . $file));

        if (!file_exists("$tmp_dir/files/$file_num")) { 
            file_put_contents(SITE_PATH .'/' . $file, '');
        } else { 
            copy("$tmp_dir/files/$file_num", SITE_PATH .'/' . $file);
        }
    }

    // Debug
    debug::add(4, fmsg("Installing package, copied over all files to correct location, package {1}", $pkg_alias), __FILE__, __LINE__);

    // Run install SQL, if needed
    io::execute_sqlfile("$pkg_dir/install.sql");

    // Debug
    debug::add(4, fmsg("Installing package, ran install.sql file for package {1}", $pkg_alias), __FILE__, __LINE__);

    // Load package
    $client = new Package_config($pkg_alias);
    $pkg = $client->load();

    // Execute PHP, if needed
    if (method_exists($pkg, 'install_before')) { 
        $pkg->install_before();
    }

    // Debug
    debug::add(4, fmsg("Installing package, loaded configuration and executed any needed PHP for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Install configuration
    $client->install_configuration();
    $client->install_notifications($pkg);

    // Debug
    debug::add(4, fmsg("Installing package, successfully installed configuration for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Go through components
    $components = json_decode(file_get_contents("$pkg_dir/components.json"), true);
    foreach ($components as $row) { 
        if ($row['type'] == 'template') { $comp_alias = $row['alias']; }
        else { $comp_alias = $row['parent'] == '' ? $row['package'] . ':' . $row['alias'] : $row['package'] . ':' . $row['parent'] . ':' . $row['alias']; }

        pkg_component::add($row['type'], $comp_alias, $row['value'], (int) $row['order_num'], $pkg_alias);
    }

    // Run install_after SQL, if needed
    io::execute_sqlfile("$pkg_dir/install_after.sql");

    // Debug
    debug::add(4, fmsg("Installing package, successfully installed all components for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Execute PHP, if needed
    if (method_exists($pkg, 'install_after')) { 
        $pkg->install_after();
    }

    // Copy over addl .tpl files, if needed
    $tpl_dir = SITE_PATH . '/src/' . $pkg_alias . '/tpl';
    if (is_dir($tpl_dir)) { 
        $tpl_files = io::parse_dir($tpl_dir);
        foreach ($tpl_files as $file) { 
            $dest_file = SITE_PATH . '/views/tpl/' . $file;

            // Copy over backup, if needed
            if (file_exists($dest_file)) { 
                $bak_file = SITE_PATH . '/views/tpl_bak/' . $file;
                io::create_dir(dirname($bak_file));
                copy($dest_file, $bak_file);
                @unlink($dest_file);
            }
            copy("$tpl_dir/$file", $dest_file);
        }
    }

    // Clean up
    io::remove_dir($tmp_dir);

    // Debug
    debug::add(1, fmsg("Successfully installed package from directory, {1}", $pkg_alias), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Remove a package
*     @param string $pkg_alias The alias of the package to remove
*/
public function remove(string $pkg_alias)
{

    // Debug
    debug::add(1, fmsg("Starting removal of package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Get package from DB
    if (!$pkg_row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $pkg_alias)) { 
        throw new PackageException('not_exists', $pkg_alias);
    }
    if ($pkg_alias == 'core') { 
        throw new ApexException('error', "You can not remove the core package!");
    }

    // Load package
    $pkg_client = new package_config($pkg_alias);
    $pkg = $pkg_client->load();
    $pkg_dir = SITE_PATH . '/etc/' . $pkg_alias;

    // Run remove_after SQL, if needed
    if (file_exists("$pkg_dir/remove.sql")) { 
        $sql_lines = SqlParser::parse(file_get_contents("$pkg_dir/remove.sql"));
        foreach ($sql_lines as $sql) { DB::query($sql); }
    }

    // Debug
    debug::add(4, fmsg("Removing package, successfully loaded configuration and executed remove.sql SQL for package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Delete all components
    $comp_rows = DB::query("SELECT * FROM internal_components WHERE owner = %s OR package = %s ORDER BY id DESC", $pkg_alias, $pkg_alias);
    foreach ($comp_rows as $crow) { 
        $comp_alias = $crow['parent'] == '' ? $crow['package'] . ':' . $crow['alias'] : $crow['package'] . ':' . $crow['parent'] . ':' . $crow['alias'];
        pkg_component::remove($crow['type'], $comp_alias);
    }

    // Delete all ext files
    foreach ($pkg->ext_files as $file) { 
        if (preg_match("/^(.+)\*$/", $file, $match)) { 
            io::remove_dir(SITE_PATH . '/' . $match[1]);
        } elseif (file_exists(SITE_PATH . '/' . $file)) { 
            @unlink(SITE_PATH . '/' . $file);
        }
    }
    if (is_dir(SITE_PATH . '/docs/' . $pkg_alias)) { io::remove_dir(SITE_PATH . '/docs/' . $pkg_alias); }

    // Debug
    debug::add(4, fmsg("Removing package, successfully deleted all components from package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Remove package directories
    io::remove_dir($pkg_dir);
    io::remove_dir(SITE_PATH . '/src/' . $pkg_alias);

    // Remove from database
    DB::query("DELETE FROM internal_packages WHERE alias = %s", $pkg_alias);
    DB::query("DELETE FROM cms_menus WHERE package = %s", $pkg_alias);

    // Update redis menus
    $pkg_client->update_redis_menus();


    // Execute PHP, if needed
    if (method_exists($pkg, 'remove')) { 
        $pkg->remove();
    }

    // Debug
    debug::add(1, fmsg("Successfully removed the package, {1}", $pkg_alias), __FILE__, __LINE__);

    // Return
    return true;

}

/**     
* Adds a file to an archive,, and is used while compiling a package.
*
*     @param string $filename The filename to add, relative to the / installation directory.
*/
private function add_file(string $filename) 
{

    // Copy file
    copy(SITE_PATH . '/' . $filename, $this->tmp_dir . '/files/' . $this->file_num);

    // Add to TOC
    $this->toc[$filename] = $this->file_num;
    $this->file_num++;

    // Debug
    debug::add(5, fmsg("Added file to TOC during package compile, {1}", $filename), __FILE__, __LINE__);


}

/**
* Compiles the core Apex framework into a temporary directory.  Gnerally only 
* used by Apex to generate the necessary directory / file structure for the Github repo.
*/
public function compile_core() 
{

    // Load core package
    require_once(SITE_PATH . '/etc/core/package.php');
    $pkg = new \apex\pkg_core();

    // Create destination dir
    $destdir = sys_get_temp_dir() . '/apex';
    if (is_dir($destdir)) { io::remove_dir($destdir); }
    io::create_dir($destdir);

    // Create base dirs
    $dirs = array(
        'etc', 
        'etc/core',  
        'log',
        'log/pids', 
        'log/services',  
        'public',
        'public/plugins',  
        'public/plugins/sounds', 
        'public/themes', 
        'src', 
        'src/core', 
        'src/core/lib/abstracts', 
        'src/core/lib/db',
        'src/core/lib/exceptions', 
        'src/core/lib/pkg',  
        'src/core/lib/third_party', 
        'storage', 
        'storage/backups', 
        'themes', 
        'views', 
        'views/htmlfunc', 
        'views/php', 
        'views/modal', 
        'views/tabpage', 
        'views/tpl'
    );
    foreach ($dirs as $dir) { io::create_dir("$destdir/$dir"); }

    // Save blank index.html files
    file_put_contents("$destdir/log/index.html", "");
    file_put_contents("$destdir/log/pids/index.html", "");
    file_put_contents("$destdir/log/services/index.html", "");
    file_put_contents("$destdir/storage/index.html", "");
    file_put_contents("$destdir/storage/backups/index.html", "");

    // Copy default themes
    $themes = array(
        'limitless', 
        'supradmin', 
        'koupon' 
    );
    foreach ($themes as $theme) { 
        system("cp -R " . SITE_PATH . "/themes/$theme $destdir/themes/");
        system("cp -R " . SITE_PATH . "/public/themes/$theme $destdir/public/themes/");
    }

    // Go through external files
    foreach ($pkg->ext_files as $file) { 

        // Copy directory
        if (preg_match("/\*$/", $file)) { 
            $file = preg_replace("/\*$/", "", $file);
            system("cp -R " . SITE_PATH . "/$file $destdir/$file");
            continue;
        }

        // Check if file exists
        if (!file_exists(SITE_PATH . "/$file")) { 
            throw new ApexException('error', "The external file does not exist, {1}", $file);
        }

        // Copy file as needed
        io::create_dir(dirname("$destdir/$file"));
        copy(SITE_PATH . "/$file", "$destdir/$file");
    }

    // Go through components
    $components = array();
    $rows = DB::query("SELECT * FROm internal_components WHERE owner = 'core' ORDER BY id");
    foreach ($rows as $row) {

        // Copy PHP file, if needed
        $php_file = components::get_file($row['type'], $row['alias'], $row['package'], $row['parent']);
        if ($php_file != '' && file_exists(SITE_PATH . '/' . $php_file)) { 
            io::create_dir(dirname($destdir . '/' . $php_file));
            copy(SITE_PATH . '/' . $php_file, $destdir . '/' . $php_file);
        }

        // Copy .tpl file, if needed
        $tpl_file = components::get_tpl_file($row['type'], $row['alias'], $row['package'], $row['parent']);
        if ($tpl_file != '' && file_exists(SITE_PATH . '/' . $tpl_file)) { 
            io::create_dir(dirname($destdir . '/' . $tpl_file));
            copy(SITE_PATH . '/' . $tpl_file, $destdir . '/' . $tpl_file);
        }

        // Tab control files, if needed
        if ($row['type'] == 'tabcontrol') { 

            $files = components::get_tabcontrol_files($row['alias'], $row['package']);
            foreach ($files as $file) { 
                if (!file_exists(SITE_PATH . '/' . $file)) { continue; }
                io::create_dir(dirname($destdir . '/' . $file));
                copy(SITE_PATH .'/' . $file, $destdir . '/' . $file);
            }
        }

        // Add to $components array
        if ($php_file != '') { 
            $vars = array(
                'order_num' => $row['order_num'], 
                'type' => $row['type'], 
                'package' => $row['package'], 
                'parent' => $row['parent'], 
                'alias' => $row['alias'], 
                'value' => $row['value']
            );
            array_push($components, $vars);
        }
    }

    // Save components.json file
    file_put_contents(SITE_PATH .'/etc/core/components.json', json_encode($components));

    // Copy over package files
    $pkg_files = array(
        'components.json', 
        'install.sql', 
        'reset.sql', 
        'remove.sql', 
        'package.php', 
        'stdlists'
    );
    foreach ($pkg_files as $file) { 
        if (!file_exists(SITE_PATH . '/etc/core/' . $file)) { continue; }

        // Copy, if not package.php
        if ($file != 'package.php') { 
            copy(SITE_PATH . '/etc/core/' . $file, $destdir . '/etc/core/' . $file);
            continue;
        }

        // Update version in package.php file
        $version = DB::get_field("SELECT version FROM internal_packages WHERE alias = 'core'");
        $text = str_replace("~version~", $version, file_get_contents(SITE_PATH . '/etc/core/package.php'));
        file_put_contents($destdir . '/etc/core/package.php', $text);
    }

    // Save blank /etc/config.php file
    file_put_contents("$destdir/etc/config.php", "<?php\n\n");

    // Create /docs directory
    io::create_dir("$destdir/docs");
    $files = io::parse_dir(SITE_PATH . '/docs', false);
    foreach ($files as $file) { 
        if (preg_match("/\//", $file)) { continue; }
        copy(SITE_PATH . "/docs/$file", "$destdir/docs/$file");
    }

    // Copy over /docs/ sub-directories
    $docs_dir = SITE_PATH . '/docs';
    system("cp -R $docs_dir/components $destdir/docs/");
    system("cp -R $docs_dir/core $destdir/docs/");
    system("cp -R $docs_dir/training $destdir/docs/");
    system("cp -R $docs_dir/user_manual $destdir/docs/");

    // Update GIthub repo
    $this->update_github_repo();

    // Return
    return $destdir;

}

/**
* Update the Github repo hosted locally
*/
private function update_github_repo()
{

    // Initialize
    $rootdir = sys_get_temp_dir() . '/apex';
    if (!$git_dir = realpath('../apex_git')) { return; }
    if (!is_dir($git_dir)) { return; }
    $git_cmds = array('#!/bin/sh');

    // Create Git file hash
    $git_hash = array();
    $files = io::parse_dir($git_dir);
    foreach ($files as $file) {
        if (preg_match("/^\.git/", $file)) { continue; }
        $git_hash[$file] = sha1(file_get_contents("$git_dir/$file"));
    }

    // Go through all files
    $files = io::parse_dir($rootdir);
    foreach ($files as $file) { 

        // Get file hashes
    $hash = sha1(file_get_contents("$rootdir/$file"));
        $chk_hash = $git_hash[$file] ?? '';

        // Check file hash
    if ($chk_hash == '' || $hash != $chk_hash) { 
        if (file_exists("$git_dir/$file")) { @unlink("$git_dir/$file"); }
        io::create_dir(dirname("$git_dir/$file"));
        copy("$rootdir/$file", "$git_dir/$file");
        $git_cmds[] = "git add $file";
    }
        if (isset($git_hash[$file])) { unset($git_hash[$file]); }
    }

    // Delete files
    foreach ($git_hash as $file => $hash) { 
        if (preg_match("/^\./", $file)) { continue; }
        @unlink("$git_dir/$file");
        $git_cmds[] = "rm $file";
    }

    // Save git.sh file
    file_put_contents("$git_dir/git.sh", implode("\n", $git_cmds));
    chmod ("$git_dir/git.sh", 0755);

}

/**
* Reset a package.  Executes any SQL file at /etc/PKG_ALIAS/reset.sql, and executes 
* any reset() function within the package.php file.
*     @param string $pkg_alias The alias of the package to reset.
*/
public function reset(string $pkg_alias)
{

    // Load package
    $client = new package_config($pkg_alias);
    $pkg = $client->load();

    // Execute SQL, if available
    io::execute_sqlfile(SITE_PATH . '/etc/' . $pkg_alias . '/reset.sql');

    // Execute reset method, if available
    if (method_exists($pkg, 'reset')) { 
        $pkg->reset();
    }

    // Execute reset_redis method, if available
    if (method_exists($pkg, 'reset_redis')) { 
        $pkg->reset_redis();
    }

    // Return
    return true;

}

/**
* Reinstall components.  Deletes all existing components of a packagew from 
* the database, and re-installs them using the /etc/PACKAGE/components.json file.
*     @param string $pkg_alias The alias of the package to re-install components for.
*/
public function reinstall_components(string $pkg_alias)
{

    // Remove existing component rows
    DB::query("DELETE FROM internal_components WHERE owner = %s", $pkg_alias);

    // Go through components
    $components = json_decode(file_get_contents(SITE_PATH . '/etc/' . $pkg_alias . '/components.json'), true);
    foreach ($components as $row) { 
        if ($row['type'] == 'template') { $comp_alias = $row['alias']; }
        else { $comp_alias = $row['parent'] == '' ? $row['package'] . ':' . $row['alias'] : $row['package'] . ':' . $row['parent'] . ':' . $row['alias']; }

        pkg_component::add($row['type'], $comp_alias, $row['value'], (int) $row['order_num'], $pkg_alias);
    }

}



}

