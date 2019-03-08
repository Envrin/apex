<?php
declare(strict_types = 1);

namespace apex\pkg;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\network;
use apex\RepoException;
use apex\ThemeException;
use apex\core\io;
use ZipArchive;
use CurlFile;


/**
* Handles all theme functions including create, publish, 
* download, install and remove.
*/
class theme
{

/**
* Create a new theme
*     @param string $theme_alias The alias of the new theme to create
*     @param int $repo_id The ID# of the repo to publish the theme to
*     @param string $area The area the theme is for ('members' or 'public'), defaults to 'public'
*     @return int The ID# of the newly created theme
*/
public function create(string $theme_alias, int $repo_id, string $area = 'public')
{

    // Debug
    debug::add(2, fmsg("Starting create theme with alias, {1}", $theme_alias), __FILE__, __LINE__);

    // Initial check
    if ($theme_alias == '') { 
        throw new ThemeException('invalid_alias');
    } elseif (preg_match("/\s\W]/", $theme_alias)) { 
        throw new ThemeException('invalid_alias', $theme_alias);
    }
    $theme_alias = strtolower($theme_alias);

    // Check if theme exists
    if ($row = DB::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('theme_exists', $theme_alias);
    }

    // Create directories
    $theme_dir = SITE_PATH . '/themes/' . $theme_alias;
    io::create_dir($theme_dir);
    io::create_dir("$theme_dir/sections");
    io::create_dir("$theme_dir/components");
    io::create_dir("$theme_dir/layouts");
    io::create_dir(SITE_PATH . '/public/themes/' . $theme_alias);

    // Save theme.php file
    $conf = base64_decode('PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCi8qKgoqIFVzZWQgdG8gZGVmaW5lIHZhcmlvdXMgcHJvcGVydGllcyBhYm91dCB0aGUgdGhlbWUsIHBsdXMgd2lsbCAKKiBvdmVycmlkZSBhbnkgbWV0aG9kcyB3aXRoaW4gdGhlIC9saWIvaHRtbF90YWdzLnBocCBjbGFzcy4gIElmIHRoZSBzYW1lIAoqIG1ldGhvZCBleGlzdHMgd2l0aGluIHRoaXMgY2xhc3MsIHRoaXMgbWV0aG9kIHdpbGwgYmUgZXhlY3V0ZWQgaW5zdGVhZCBvZiB0aGUgb25lIAoqIHdpdGhpbiAvbGliL2h0bWxfbGlicy5waHAKKi8KY2xhc3MgdGhlbWVffmFsaWFzfiAKewoKICAgIC8vIFByb3BlcnRpZXMKICAgIHB1YmxpYyAkYXJlYSA9ICd+YXJlYX4nOwogICAgcHVibGljICRhY2Nlc3MgPSAncHVibGljJzsKICAgIHB1YmxpYyAkbmFtZSA9ICd+YWxpYXN+JzsKCgp9Cgo=');
    $conf = str_replace("~alias~", $theme_alias, $conf);
    $conf = str_replace("~area~", $area, $conf);
    file_put_contents("$theme_dir/theme.php", $conf);

    // Add to database
    DB::insert('internal_themes', array(
        'is_owner' => 1,
        'repo_id' => $repo_id,  
        'area' => $area, 
        'alias' => $theme_alias, 
        'name' => $theme_alias)
    );
    $theme_id = DB::insert_id();

    // Debug
    debug::add(1, fmsg("Successfully created new theme with alias {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return $theme_id;

}

/**
* Publish a theme to a repository
*     @param string $theme_alias The alias of the theme to publish
*/
public function publish(string $theme_alias)
{

    // Debug
    debug::add(3, fmsg("Starting to publish theme with alias, {1}", $theme_alias), __FILE__, __LINE__);

    // Get theme
    if (!$row = DB::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Load theme file
    $class_name = 'theme_' . $theme_alias;
    require_once(SITE_PATH . '/themes/' . $theme_alias . '/theme.php');
    $theme = new $class_name();

    // Debug
    debug::add(4, fmsg("Publishing theme, successfully loaded theme configuration for alias, {1}", $theme_alias), __FILE__, __LINE__);

    // Set variables
    $access = $theme->access ?? $row['access'];
    $area = $theme->area ?? $row['area'];
    $name = $theme->name ?? $row['name'];

    // Update database
    DB::update('internal_themes', array(
        'area' => $area, 
        'name' => $name), 
    "alias = %s", $theme_alias);

    // Get repo
    if (!$repo_id = DB::get_idrow('internal_repos', $row['repo_id'])) { 
        throw new RepoException('not_exists', $repo_id);
    }

    // Compile theme
    $zip_file = $this->compile($theme_alias);

    // Set request
    $request = array(
        'theme_alias' => $theme_alias, 
        'access' => $access, 
        'area' => $area, 
        'name' => $name, 
        'contents' => new CurlFile($zip_file, 'application/gzip', $theme_alias . '.zip')
    );

    // Send repo request
    $client = new network();    
    $vars = $client->send_repo_request((int) $row['repo_id'], '', 'publish_theme', $request);

    // Debug
    debug::add(1, fmsg("Successfully published theme to repository, {1}", $theme_alias), __FILE__, __LINE__);


}

/**
* Compile a theme into a zip archive
*     @param string $theme_alias The alias of the theme to archive
*/
protected function compile(string $theme_alias)
{

    // Debug
    debug::add(4, fmsg("Start compile theme with alias, {1}", $theme_alias), __FILE__, __LINE__);

// Create /public/ directory within theme
    $theme_dir = SITE_PATH . '/themes/' . $theme_alias;
    if (is_dir("$theme_dir/public")) { io::remove_dir("$theme_dir/public"); }
    io::create_dir("$theme_dir/public");

    // Copy over public directory
    $files = io::parse_dir(SITE_PATH . '/public/themes/' . $theme_alias);
    foreach ($files as $file) { 
        io::create_dir(dirname("$theme_dir/public/$file"));
    copy(SITE_PATH . '/public/themes/' . $theme_alias . '/' . $file, "$theme_dir/public/$file");
    }

    // Archive theme
    $zip_file = SITE_PATH . '/tmp/' . $theme_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    io::create_zip_archive($theme_dir, $theme_alias . '.zip');

    // Clean up
    io::remove_dir("$theme_dir/public");

    // Debug
    debug::add(4, fmsg("Successfully compiled theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return $zip_file;

}

/**
* Download and install a theme
*     #param string $theme_alias The alias of the theme to install
*     @param int $repo_id Optional ID# of the repo to download from.  If unspecified, all repos will be checked.
*/
public function install(string $theme_alias, int $repo_id = 0)
{

    // Debug
    debug::add(2, fmsg("Starting to download and install theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Download
    list($repo_id, $zip_file, $vars) = $this->download($theme_alias, $repo_id);

    // Unpack zip archive
    $theme_dir = SITE_PATH . '/themes/' . $theme_alias;
    if (is_dir($theme_dir)) { io::remove_dir($theme_dir); }
    io::unpack_zip_archive($zip_file, $theme_dir);

    // Create /public/ directory
    $public_dir = SITE_PATH . '/public/themes/' . $theme_alias;
        if (is_dir($public_dir)) { io::remove_dir($public_dir); }
    io::create_dir($public_dir);

    // Copy over /public/ directory
    $files = io::parse_dir("$theme_dir/public");
    foreach ($files as $file) { 
        io::create_dir(dirname("$public_dir/$file"));
    copy("$theme_dir/public/$file", "$public_dir/$file");
    }
    io::remove_dir("$theme_dir/public");

    // Add to database
    DB::insert('internal_themes', array(
        'is_owner' => 0, 
        'repo_id' => $repo_id, 
        'area' => $vars['area'], 
        'alias' => $theme_alias, 
        'name' => $vars['name'])
    );

    // Activate theme
    if ($vars['area'] == 'members') { 
        registry::update_config_var('users:theme_members', $theme_alias);
    } else { 
        registry::update_config_var('core:theme_public', $theme_alias);
    }

    // Debug
    debug::add(1, fmsg("Successfully downloaded and installed theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return true;

}

/**
* Download a theme from the repository.
*     #param string $theme_alias The alias of the theme to install
*     @param int $repo_id Optional ID# of the repo to download from.  If unspecified, all repos will be checked.
*/
protected function download(string $theme_alias, int $repo_id = 0) 
{

    // Debug
    debug::add(4, fmsg("Starting to download theme from repository, {1}", $theme_alias), __FILE__, __LINE__);

    // Set variables
    $network = new network();

    // Get repo, if needed
    if ($repo_id == 0) { 

        // Check theme on all repos
        $repos = $network->check_theme($theme_alias);
        if (count($repos) == 0) { 
            throw new ThemeException('not_exists_repo', $theme_alias);
        }
        $repo_id = array_keys($repos)[0];
    }

    // Get repo
    if (!$repo = DB::get_idrow('internal_repos', $repo_id)) { 
throw RepoException('not_exists', $repo_id);
    }

    // Set request
    $request = array(
        'theme_alias' => $theme_alias
    );

    // Download theme
    $vars = $network->send_repo_request((int) $repo_id, '', 'download_theme', $request);

    // Save zip file
    $zip_file = SITE_PATH . '/tmp/' . $theme_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    file_put_contents($zip_file, base64_decode($vars['contents']));

    // Debug
    debug::add(4, fmsg("Successfully downloaded theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return array($repo_id, $zip_file, $vars);

}

/**
* Remove a theme from the system.
*     @param string $theme_alias The alias of the theme to remove.
*/
public function remove(string $theme_alias)
{

    // Debug
    debug::add(4, fmsg("Starting removal of theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Ensure theme exists
    if (!$row = DB::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Remove dirs
    io::remove_dir(SITE_PATH . '/themes/' . $theme_alias);
    io::remove_dir(SITE_PATH . '/public/themes/' . $theme_alias);

    // Delete from database
    DB::query("DELETE FROM internal_themes WHERE alias = %s", $theme_alias);

    // Debug
    debug::add(1, fmsg("Successfully deleted theme from system, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
return true;

}
}
