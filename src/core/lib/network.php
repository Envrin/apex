<?php
declare(strict_types = 1);

namespace apex\core\lib;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
use apex\core\io;
use apex\core\components;
use apex\core\lib\encrypt;
use ZipArchive;
use CurlFile;
use SqlParser;

/**
* Handles various general repository communication such as 
* retrieving a list of packages and themes, checking for available upgrades, 
* and so on.
*/
class Network 
{

/**
* List all available packages on a repo
*     @return array An array of arrays, with each element being an array containing details on one page that is available.
*/
public function list_packages()
{

    // Debug
    debug::add(5, "Starting to list_packages from all repos", __FILE__, __LINE__);

    // Go through all repos
    $packages = array(); $done = array();
    $rows = DB::query("SELECT * FROM internal_repos WHERE is_active = 1 ORDER BY id");
    foreach ($rows as $row) {

        // Debug
        debug::add(5, fmsg("Sending list_packages request to repo ID# {1}, URL: {2}", $row['id'], $row['url']), __FILE__, __LINE__);

        // Send repo request
        iF (!$response = $this->send_repo_request((int) $row['id'], '', 'list_packages', array(), true)) { 
            continue;
        }
        if (!isset($response['packages'])) { continue; }

        // Go through packages
        foreach ($response['packages'] as $alias => $vars) { 
            if (in_array($alias, $done)) { continue; }

            $vars['name'] .= ' v' . $vars['version'];
            $vars['date_created'] = fdate($vars['date_created']);
            $vars['last_modified'] = fdate($vars['last_modified'], true);
            $vars['alias'] = $alias;

            array_push($packages, $vars);
            $done[] = $alias;
        }

        // Debug
        debug::add(5, fmsg("Finished getting list_packages from repo ID# {1}, URL: {2}", $row['id'], $row['url']), __FILE__, __LINE__);
    }

    // Debug
    debug::add(3, "Finished executing list_packages on all repos, returning results", __FILE__, __LINE__);

    // Return
    return $packages;

}

/**
* Checks to see whether or not a package alias exists 
* in any of the repositories configured on this system.
*
*     @param string $pkg_alias The package alias to search for.
*     @return array An array of all repos that contain the package.
*/
public function check_package(string $pkg_alias):array 
{

    // Debug
    debug::add(5, fmsg("Starting check_package of all repos for package alias: {1}", $pkg_alias), __FILE__, __LINE__);

    // Go through repos
    $repos = array();
    $rows = DB::query("SELECT * FROM internal_repos WHERE is_active = 1 ORDER BY id");
    foreach ($rows as $row) { 

        // Debug
        debug::add(5, fmsg("Sending check_package repo request for package alias: {1}, to repo ID# {2}, URL: {3}", $pkg_alias, $row['id'], $row['url']), __FILE__, __LINE__);

        // Send request
        if (!$vars = $this->send_repo_request((int) $row['id'], $pkg_alias, 'check_package', array(), true)) { 
            continue;
        }

        // Check
        $ok = $vars['exists'] ?? 0;
        debug::add(5, fmsg("Received check_package response from repo of {1} for package alias {2} from repo ID# {3}, URL: {4}", $ok, $pkg_alias, $row['id'], $row['url']), __FILE__, __LINE__);

        // Continue, if not exists
        if ($ok != 1) { continue; }

        // Add to results
        $name = $vars['repo_name'] . '(' . $vars['repo_host'] . ')';
        $repos[$row['id']] = $name;
    }

    // Debug
    debug::add(3, "Finished sending check_package repo request to all repos", __FILE__, __LINE__);

    // Return
    return $repos;

} 

/**
* Searches all repos configured on this system for a given term, for any 
* packages that match.  Unlike the 'check_package' method, this 
* does not have to be the exact alias, and will search the alias, name and 
* description of all packages for given term.
* 
*     @param string $term The term to search for.
*     @return string A message containg all packages and repos that match the term.
*/
public function search(string $term):string 
{

    // Debug
    debug::add(5, fmsg("Starting to search packages on all repos for term: {1}", $term), __FILE__, __LINE__);

    // Go through repos
    $results = '';
    $rows = DB::query("SELECT * FROM internal_repos WHERE is_active = 1 ORDER BY id");
    foreach ($rows as $row) { 

        // Set request
        $request = array(
            'term' => $term
        );

        // Debug
        debug::add(5, fmsg("Searching packages for term: {1} on repo ID# {2}, URL: {3}", $term, $row['id'], $row['url']), __FILE__, __LINE__);

        // Send request
        if (!$vars = $this->send_repo_request((int) $row['id'], '', 'search', $request, true)) { 
            continue;
        }
        if (count($vars['packages']) == 0) { continue; }

        // Add to results
        $host = preg_replace("/^https?:\/\//", "", $row['url']);
        $results .= "Repo: " . $row['display_name'] . " (" . $host . ")\n\n";
        foreach ($vars['packages'] as $alias => $name) { 
            $results .= "\t$alias -- $name\n";
        }

    }

    // Check if no results
    if ($results == '') { 
        $results = "No packages found matching that term.\n\n";
    }

    // Debug
    debug::add(3, fmsg("Finished search packages on all repos for term: {1}", $term), __FILE__, __LINE__);

    // Return
    return $results;

}

/**
* Check for upgrades
*     @param array $packages An array of packages to check for upgrades.  If none specified, all packages installed in the system will be checked.
*/
public function check_upgrades(array $packages = array())
{

    // Get all packages, if needed
    if (count($packages) == 0) { 
        $packages = DB::get_column("SELECT alias FROM internal_packages");
    }

    // Go through packages
    $requests = array();
    foreach ($packages as $pkg_alias) { 

        // Get row
        if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $pkg_alias)) { 
            continue;
        }

        // Add to requests
        $repo_id = $row['repo_id'];
        if (!isset($requests[$repo_id])) { $requests[$repo_id] = array(); }
        $requests[$repo_id]['pkg_' . $pkg_alias] = $row['version'];
    }

// Go through requests
    $upgrades = array();
    foreach ($requests as $repo_id => $request) { 

// Ensure repo exists
        if (!$repo = DB::get_idrow('internal_repos', $repo_id)) { 
            continue;
        }

        // Send request
        if (!$vars = $this->send_repo_request((int) $repo_id, '', 'check_upgrades', $request, true)) { 
            continue;
        }
        if (!isset($vars['upgrades'])) { continue; }
        $upgrades = array_merge($upgrades, $vars['upgrades']);
    }

    // Return
    return $upgrades;

}

/**
* List themes
*/
public function list_themes()
{

    // GO through all repos
    $themes = array();
    $rows = DB::query("SELECT * FROM internal_repos ORDER BY id");
    foreach ($rows as $row) { 

        // Send request
        if (!$vars = $this->send_repo_request((int) $row['id'], '', 'list_themes', array(), true)) { 
            continue; 
        }
        if (!isset($vars['themes'])) { continue; }

        // Go through themes
        foreach ($vars['themes'] as $alias => $theme) { 
            $theme['repo_url'] = $row['url'];
            $themes[$alias] = $theme;
        }
    }

    // Return
    return $themes;

}

/**
* Checks all repos for a specific theme, and return 
* alls repos the theme resides on.
*     @param string $theme_alias The alias of the theme to search for.
*/
public function check_theme(string $theme_alias):array 
{

    // Initialize
    $request = array('theme_alias' => $theme_alias);

    // Go through repos
    $repos = array();
    $rows = DB::query("SELECT * FROM internal_repos WHERE is_active = 1 ORDER BY id");
    foreach ($rows as $row) { 

        // Send request
        if (!$vars = $this->send_repo_request((int) $row['id'], '', 'check_theme', $request, true)) { 
            continue;
        }

        // Check theme
        $ok = $vars['exists'] ?? 0;
        if ($ok != 1) { continue; }

        // Add to repots
        $host = preg_replace("/^https?:\/\//", '', $row['url']);
        $repos[$row['id']] = $row['display_name'] . ' (' . $host . ')';
    }

    // Return
    return $repos;

}

/**
* Send HTTP request to repository
*
*     @param int $repo_id The ID# of the repository to send the request to
*     @param string $pkg_alias The alias of the package the request is for
*     @param string $action The action being performed
*     @param array $request The contents of the POST request
*     @param bool $noerror If true, will return false instead of triggering an error
*     @return mixed Array of the JSON response, or false if failed 
*/
public function send_repo_request(int $repo_id, string $pkg_alias, string $action, array $request = array(), bool $noerror = false)
{

    // Get repo
    if (!$repo = DB::get_idrow('internal_repos', $repo_id)) {
if ($noerror === true) { return false; }
        throw new RepoException('not_exists', $repo_id);
    }

    // Set request
    $request['action'] = $action;
    if ($repo['username'] != '') { $request['username'] = encrypt::decrypt_basic($repo['username']); }
    if ($repo['password'] != '') { $request['password'] = encrypt::decrypt_basic($repo['password']); }

    // Send HTTP request
    $url = trim($repo['url'], '/') . '/repo/' . $pkg_alias;
    if (!$response = io::send_http_request($url, 'POST', $request)) { 
        return false;

    // Decode response
    } elseif (!$vars = json_decode($response, true)) { 
        return false;
    }

    // Check response status
    if ($vars['status'] != 'ok') {
        if ($noerror === true) { return false; }
        throw new RepoException('remote_error', 0, '', $vars['errmsg']);
    }

    // Return
    return $vars;

}




}


