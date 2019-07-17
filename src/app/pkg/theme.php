<?php
declare(strict_types = 1);

namespace apex\app\pkg;

use apex\app;
use apex\svc\db;
use apex\svc\debug;
use apex\app\sys\network;
use apex\svc\io;
use apex\app\exceptions\RepoException;
use apex\app\exceptions\ThemeException;
use CurlFile;


/**
 * Handles all theme functions including create, publish, download, install 
 * and remove. 
 */
class theme
{




/**
 * Create a new theme 
 *
 * @param string $theme_alias The alias of the new theme to create
 * @param int $repo_id The ID# of the repo to publish the theme to
 * @param string $area The area the theme is for ('members' or 'public'), defaults to 'public'
 *
 * @return int The ID# of the newly created theme
 */
public function create(string $theme_alias, int $repo_id, string $area = 'public')
{ 

    // Debug
    debug::add(2, tr("Starting create theme with alias, {1}", $theme_alias), __FILE__, __LINE__);

    // Initial check
    if ($theme_alias == '') { 
        throw new ThemeException('invalid_alias');
    } elseif (preg_match("/\s\W]/", $theme_alias)) { 
        throw new ThemeException('invalid_alias', $theme_alias);
    }
    $theme_alias = strtolower($theme_alias);

    // Check if theme exists
    if ($row = db::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('theme_exists', $theme_alias);
    }

    // Create directories
    $theme_dir = SITE_PATH . '/views/themes/' . $theme_alias;
    io::create_dir($theme_dir);
    io::create_dir("$theme_dir/sections");
    io::create_dir("$theme_dir/components");
    io::create_dir("$theme_dir/layouts");
    io::create_dir(SITE_PATH . '/public/themes/' . $theme_alias);

    // Save theme.php file
    $conf = base64_decode('PD9waHAKZGVjbGFyZShzdHJpY3RfdHlwZXMgPSAxKTsKCi8qKgoqIFVzZWQgdG8gZGVmaW5lIHZhcmlvdXMgcHJvcGVydGllcyBhYm91dCB0aGUgdGhlbWUsIHBsdXMgd2lsbCAKKiBvdmVycmlkZSBhbnkgbWV0aG9kcyB3aXRoaW4gdGhlIC9saWIvaHRtbF90YWdzLnBocCBjbGFzcy4gIElmIHRoZSBzYW1lIAoqIG1ldGhvZCBleGlzdHMgd2l0aGluIHRoaXMgY2xhc3MsIHRoaXMgbWV0aG9kIHdpbGwgYmUgZXhlY3V0ZWQgaW5zdGVhZCBvZiB0aGUgb25lIAoqIHdpdGhpbiAvbGliL2h0bWxfbGlicy5waHAKKi8KY2xhc3MgdGhlbWVffmFsaWFzfiAKewoKICAgIC8vIFByb3BlcnRpZXMKICAgIHB1YmxpYyAkYXJlYSA9ICd+YXJlYX4nOwogICAgcHVibGljICRhY2Nlc3MgPSAncHVibGljJzsKICAgIHB1YmxpYyAkbmFtZSA9ICd+YWxpYXN+JzsKICAgIHB1YmxpYyAkZGVzY3JpcHRpb24gPSAnJzsKCiAgICAvLyBBdXRob3IgZGV0YWlscwogICAgcHVibGljICRhdXRob3JfbmFtZSA9ICcnOwogICAgcHVibGljICRhdXRob3JfZW1haWwgPSAnJzsKICAgIHB1YmxpYyAkYXV0aG9yX3VybCA9ICcnOwoKICAgIC8qKgogICAgKiBFbnZhdG8gaXRlbSBJRC4gIGlmIHRoaXMgaXMgZGVmaW5lZCwgdXNlcnMgd2lsbCBuZWVkIHRvIHB1cmNoYXNlIHRoZSB0aGVtZSBmcm9tIFRoZW1lRm9yZXN0IGZpcnN0LCAKICAgICogYW5kIGVudGVyIHRoZWlyIGxpY2Vuc2Uga2V5IGJlZm9yZSBkb3dubG9hZGluZyB0aGUgdGhlbWUgdG8gQXBleC4gIFRoZSBsaWNlbnNlIGtleSAKICAgICogd2lsbCBiZSB2ZXJpZmllZCB2aWEgRW52YXRvJ3MgQVBJLCB0byBlbnN1cmUgdGhlIHVzZXIgcHVyY2hhc2VkIHRoZSB0aGVtZS4KICAgICogCiAgICAqIFlvdSBtdXN0IGFsc28gc3BlY2lmeSB5b3VyIEVudmF0byB1c2VybmFtZSwgYW5kIHRoZSBmdWxsIAogICAgKiBVUkwgdG8gdGhlIHRoZW1lIG9uIHRoZSBUaGVtZUZvcmVzdCBtYXJrZXRwbGFjZS4gIFBsZWFzZSBhbHNvIAogICAgKiBlbnN1cmUgeW91IGFscmVhZHkgaGF2ZSBhIGRlc2lnbmVyIGFjY291bnQgd2l0aCB1cywgYXMgd2UgZG8gbmVlZCB0byAKICAgICogc3RvcmUgeW91ciBFbnZhdG8gQVBJIGtleSBpbiBvdXIgc3lzdGVtIGluIG9yZGVyIHRvIHZlcmlmeSBwdXJjaGFzZXMuICBDb250YWN0IHVzIHRvIG9wZW4gYSBmcmVlIGFjY291bnQuCiAgICAqLwogICAgcHVibGljICRlbnZhdG9faXRlbV9pZCA9ICcnOwogICAgcHVibGljICRlbnZhdG9fdXNlcm5hbWUgPSAnJzsKICAgIHB1YmxpYyAkZW52YXRvX3VybCA9ICcnOwoKfQoK');
    $conf = str_replace("~alias~", $theme_alias, $conf);
    $conf = str_replace("~area~", $area, $conf);
    file_put_contents("$theme_dir/theme.php", $conf);

    // Add to database
    db::insert('internal_themes', array(
        'is_owner' => 1,
        'repo_id' => $repo_id,
        'area' => $area,
        'alias' => $theme_alias,
        'name' => $theme_alias)
    );
    $theme_id = db::insert_id();

    // Debug
    debug::add(1, tr("Successfully created new theme with alias {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return $theme_id;

}

/**
 * Publish a theme to a repository 
 *
 * @param string $theme_alias The alias of the theme to publish
 */
public function publish(string $theme_alias)
{ 

    // Debug
    debug::add(3, tr("Starting to publish theme with alias, {1}", $theme_alias), __FILE__, __LINE__);

    // Get theme
    if (!$row = db::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Load theme file
    $class_name = 'theme_' . $theme_alias;
    require_once(SITE_PATH . '/views/themes/' . $theme_alias . '/theme.php');
    $theme = new $class_name();

    // Debug
    debug::add(4, tr("Publishing theme, successfully loaded theme configuration for alias, {1}", $theme_alias), __FILE__, __LINE__);

    // Set variables
    $access = $theme->access ?? $row['access'];
    $area = $theme->area ?? $row['area'];
    $name = $theme->name ?? $row['name'];

    // Update database
    db::update('internal_themes', array(
        'area' => $area,
        'name' => $name),
    "alias = %s", $theme_alias);

    // Get repo
    if (!$repo_id = db::get_idrow('internal_repos', $row['repo_id'])) { 
        throw new RepoException('not_exists', $repo_id);
    }

    // Compile theme
    $zip_file = $this->compile($theme_alias);

    // Set request
    $request = array(
        'type' => 'theme',
        'version' => '1.0.0',
        'access' => $access,
        'area' => $area,
        'name' => $name,
        'description' => ($theme->description ?? ''),
        'author_name' => ($theme->author_name ?? ''),
        'author_email' => ($theme->author_email ?? ''),
        'author_url' => ($theme->author_url ?? ''),
        'envato_item_id' => ($theme->envato_item_id ?? ''),
        'envato_username' => ($theme->envato_username ?? ''),
        'envato_url' => ($theme->envato_url ?? ''),
        'contents' => new CurlFile($zip_file, 'application/gzip', $theme_alias . '.zip')
    );

    // Send repo request
    $client = app::make(network::class);
    $vars = $client->send_repo_request((int) $row['repo_id'], $theme_alias, 'publish', $request);

    // Debug
    debug::add(1, tr("Successfully published theme to repository, {1}", $theme_alias), __FILE__, __LINE__);


}

/**
 * Compile a theme into a zip archive 
 *
 * @param string $theme_alias The alias of the theme to archive
 */
protected function compile(string $theme_alias)
{ 

    // Debug
    debug::add(4, tr("Start compile theme with alias, {1}", $theme_alias), __FILE__, __LINE__);

// Create /public/ directory within theme
    $theme_dir = SITE_PATH . '/views/themes/' . $theme_alias;
    if (is_dir("$theme_dir/public")) { io::remove_dir("$theme_dir/public"); }
    io::create_dir("$theme_dir/public");

    // Copy over public directory
    $files = io::parse_dir(SITE_PATH . '/public/themes/' . $theme_alias);
    foreach ($files as $file) { 
        io::create_dir(dirname("$theme_dir/public/$file"));
    copy(SITE_PATH . '/public/themes/' . $theme_alias . '/' . $file, "$theme_dir/public/$file");
    }

    // Archive theme
    $zip_file = sys_get_temp_dir() . '/apex_theme_' . $theme_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    io::create_zip_archive($theme_dir, $zip_file);

    // Clean up
    io::remove_dir("$theme_dir/public");

    // Debug
    debug::add(4, tr("Successfully compiled theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return $zip_file;

}

/**
 * Download and install a theme 
 * 
 * @param string $theme_alias The alias of the theme to install. 
 * @param int $repo_id Optional ID# of the repo to download from.  If unspecified, all repos will be checked.
 */
public function install(string $theme_alias, int $repo_id = 0)
{ 

    // Debug
    debug::add(2, tr("Starting to download and install theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Download
    list($repo_id, $zip_file, $vars) = $this->download($theme_alias, $repo_id);

    // Unpack zip archive
    $theme_dir = SITE_PATH . '/views/themes/' . $theme_alias;
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
    db::insert('internal_themes', array(
        'is_owner' => 0,
        'repo_id' => $repo_id,
        'area' => $vars['area'],
        'alias' => $theme_alias,
        'name' => $vars['name'])
    );

    // Activate theme
    if ($vars['area'] == 'members') { 
        app::update_config_var('users:theme_members', $theme_alias);
    } else { 
        app::update_config_var('core:theme_public', $theme_alias);
    }

    // Debug
    debug::add(1, tr("Successfully downloaded and installed theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return true;

}

/**
 * Download a theme from the repository. 
 * 
 * @param string $theme_alias The alias of the theme to download. 
 * @param int $repo_id Optional ID# of the repo to download from.  If unspecified, all repos will be checked.
 */
protected function download(string $theme_alias, int $repo_id = 0)
{ 

    // Debug
    debug::add(4, tr("Starting to download theme from repository, {1}", $theme_alias), __FILE__, __LINE__);

    // Initialize network client
    $network = app::make(network::class);

    // Get repo, if needed
    if ($repo_id == 0) { 

        // Check theme on all repos
        $repos = $network->check_package($theme_alias, 'theme');
        if (count($repos) == 0) { 
            throw new ThemeException('not_exists_repo', $theme_alias);
        }
        $repo_id = array_keys($repos)[0];
    }

    // Get repo
    if (!$repo = db::get_idrow('internal_repos', $repo_id)) { 
throw RepoException('not_exists', $repo_id);
    }

    // Download theme
    $vars = $network->send_repo_request((int) $repo_id, $theme_alias, 'download', array('type' => 'theme'));

    // Save zip file
    $zip_file = sys_get_temp_dir() . '/apex_theme_' . $theme_alias . '.zip';
    if (file_exists($zip_file)) { @unlink($zip_file); }
    file_put_contents($zip_file, base64_decode($vars['contents']));

    // Debug
    debug::add(4, tr("Successfully downloaded theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
    return array($repo_id, $zip_file, $vars);

}

/**
 * Remove a theme from the system. 
 *
 * @param string $theme_alias The alias of the theme to remove.
 */
public function remove(string $theme_alias)
{ 

    // Debug
    debug::add(4, tr("Starting removal of theme, {1}", $theme_alias), __FILE__, __LINE__);

    // Ensure theme exists
    if (!$row = db::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Remove dirs
    io::remove_dir(SITE_PATH . '/views/themes/' . $theme_alias);
    io::remove_dir(SITE_PATH . '/public/themes/' . $theme_alias);

    // Delete from database
    db::query("DELETE FROM internal_themes WHERE alias = %s", $theme_alias);

    // Debug
    debug::add(1, tr("Successfully deleted theme from system, {1}", $theme_alias), __FILE__, __LINE__);

    // Return
return true;

}


}

