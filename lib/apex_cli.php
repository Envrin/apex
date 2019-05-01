<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\network;
use apex\log;
use apex\debug;
use apex\core\components;
use apex\core\io;
use apex\pkg\package_config;
use apex\pkg\package;
use apex\pkg\theme;
use apex\pkg\upgrade;
use apex\pkg\pkg_component;

/**
* Class that handles all functions executed via the apex.php CLI script 
* within the installation directory of Apex.
*/
class apex_cli
{

/**
* Processes a command
*/
public function process(string $action, $vars)
{

    // Check if method exists
    if (!method_exists($this, $action)) { 
        $response =  "The command '$action' is not supported.  Please use --help to view a list of all supported commands.\n"; 
    } else { 
        $response = $this->$action($vars);
    }

    // Set response
    registry::set_response($response);

}

/**
* Display help
*     Usage:  php apex.php help
*/
protected function help($vars)
{

    // General commands
    $response = "\n\tGENERAL\n\n";
    $response .= str_pad("search TERM", 40) . "Searches all configured repos for packages matching TERM\n";
    $response .= str_pad("list_packages", 40) . "Lists all packages available to the system from all configured repos\n";
    $response .= str_pad('list_themes', 40) . "Lists all themes available to the system\n";
    $response .= str_pad('install PACKAGE', 40) . "Downloads and installs the specified package\n";
    $response .= str_pad('install_theme THEME', 40) . "Downloads and installs the specified theme\n";
    $response .= str_pad('upgrade [PACKAGE]', 40) . "Downloads and installs all upgrades available.\n";
    $response .= str_pad('change_theme AREA THEME', 40) . "Changes active theme on AREA (public / members) to the specified theme\n\n";

    // Package commands
    $response .= "\tPACKAGES\n";
    $response .= str_pad('create_package PACKAGE', 40) . "Creates a new package for development\n";
    $response .= str_pad('scan PACKAGE', 40) . "Scans configuration file of package, and updates database as needed\n";
    $response .= str_pad('publish PACKAGE', 40) . "Publishes package to repository\n";
    $response .= str_pad('delete_package PACKAGE', 40) . "Deletes specified package from the system\n\n";

    // Upgrade
    $response .= "\tUPGRADES\n";
    $response .= str_pad('create_upgrade PACKAGE [VERSION]', 40) . "Create new upgrade point on specified package.  optionally, specify version of upgrade\n\n";
    $response .= str_pad('publish_upgrade PACKAGE', 40) . "Publish open upgrade on specified package\n";
    $response .= str_pad('upgrade [PACKAGE]', 40) . "Downloads and installd all upgrades available.\n";
    $response .= str_pad('check_upgrades', 40) . "Lists all upgrades available to the system\n";

    // Theme commands
    $response .= "\tTHEMES\n";
    $response .= str_pad('create_theme THEME', 40) . "Creates a new theme for development with specified alias\n";
    $response .= str_pad('publish_theme THEME', 40) . "Publishes theme to repository\n";
    $response .= str_pad('list_themes', 40) . "Lists all themes available to the system\n";
    $response .= str_pad('install_theme THEME', 40) . "Downloads theme from repository, and installs on system\n";
    $response .= str_pad('delete_theme THEME', 40) . "Deletes theme from system\n";
    $response .= str_pad('change_theme AREA THEME', 40) . "Changes active theme on AREA (public / members) to the specified theme\n\n";

// Component commands
    $response .= "\tCOMPONENTS\n";
    $response .= str_pad('create TYPE PACKAGE:[PARENT:]:ALIAS [OWNER]', 40) . "Creates a new component.  See documentation for details\n";
    $response .= str_pad('delete TYPE PACKAGE:[PARENT:]ALIAS', 40) . "Deletes a component.  See documentation for details\n\n";

    // Unit ets
    $response .= "\tUNIT ESTS / DEBUG\n";
    $response .= str_pad('test PACKAGE [CLASS_NAME]', 40) . "Executes unit tests of specified package, and optionally class name\n";
    $response .= str_pad('testall', 40) . "Executes all unit ests from all packages\n";
    $response .= str_pad('debug LEVEL', 40) . "Sets current debug more, 0 = off, 1 = next request, 2 = always on\n\n";

    // System maintenance
    $response .= "\tSYSTEM / MAINTENANCE\n";
    $response .= str_pad('add_repo URL [USERNAME] [PASSWORD]', 40) . "Adds new repository to system with optional username / password\n";
    $response .= str_pad('update_repo URL', 40) . "Updates existing repo with new username / password\n";
    $response .= str_pad('update_masterdb', 40) . "Update connection information for master mySQL database\n";
    $response .= str_pad('create_dbslaves', 40) . "Clear all slave mySQL database servers\n";
    $response .= str_pad('update_rabbitmq', 40) . "Update connection information for RabbitMQ\n\n\n";

    // Debug
    debug::add(4, "CLI: help", __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* List all available packages
*     Usage: php apex.php list_packages
*/
protected function list_packages($vars)
{

    // Get packages
    $client = new network();
    $packages = $client->list_packages();

    // Get response
    $response = '';
    foreach ($packages as $vars) { 
        $response .= $vars['alias'] . ' -- ' . $vars['name'] . ' (' . $vars['author_name'] . ')';
    }

    // Debugh   // Debug
    debug::add(4, 'CLI: list_packages', __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* Searches all repositories configured on the system for a package(s) that 
* meet the specified search term.
* 
*  Usage:  php apex.php search TERM
*/
protected function search($vars) 
{

    // Initialize
    $term = implode(" ", $vars);

    // Search
    $client = new Network();
    $response = $client->search($term);

    // Debug
    debug::add(4, fmsg("CLI: search -- term: {1}", $term), __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* Downloads a package from the repository, and 
* installs it on the system.  
*  Usage:  php apex.php install PACAKGE_ALIAS
*/
protected function install($vars) 
{

    // Install
    $response = '';
    foreach ($vars as $alias) {

        // Debug
        debug::add(4, fmsg("CLI: Starting install of package: {1}", $alias), __FILE__, __LINE__, 'info');

        // Install package
        $package = new package();
        $package->install($alias);

        // Debug
        debug::add(4, fmsg("CLI: Complete install of package: {1}", $alias), __FILE__, __LINE__, 'info');

        $response .= "Successfully installed the package, $alias\n";
    }

    // Return
    return $response;

}

/**
* Scans the package.php configuration file of a package, and updates 
* the database as necessary.  Used during development, when you modify things 
* like config variables, hashes, or menus.
*   Usage:  php apex.php scan PACKAGE_ALIAS
*/
protected function scan($vars) 
{

    // Checks
    if (!isset($vars[0])) { 
        throw new PackageException('undefined');
    }

    // Go through packages
    $response = '';
    foreach ($vars as $alias) { 

        // Get package from db
        if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $alias)) { 
            $response .= "The package '$alias' does not exist.\n";
            continue;
        }

        // Scan package
        $client = new package_config($alias);
        $client->install_configuration();

        // Debug
        debug::add(4, fmsg("CLI: Scanned package: {1}", $alias), __FILE__, __LINE__, 'info');

        // Success
        $response .= "Succesfully scanned the package, $alias\n";
    }

    // Return
    return $response;

}

/**
* Creates a new package.  Inserts a row into the 'internal_packages' table, 
* and creates the necessary base directories and configuration files.
* 
*  Usage:  php apex.php create_package PACKAGE_ALIAS
*/
protected function create_package($vars) 
{

// Initialize
    $pkg_alias = $vars[0] ?? '';
    $repo_id = $vars[1] ?? 0;
    $name = $vars[2] ?? $pkg_alias;

    // Debug
    debug::add(4, fmsg("CLI: Starting creation of package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

    // CHeck if package exists
    if ($row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $pkg_alias)) { 
        throw new PackageException('exists', $pkg_alias);
    }

    // Validate alias
    $pkg_client = new package();
    if (!$pkg_client->validate_alias($pkg_alias)) { 
        throw new PackageException('invalid_alias', $pkg_alias);
    }

    // Check if package exists in any repos
    $client = new Network();
    $repos = $client->check_package($pkg_alias);

    // Ask to continue if package exists in any repos
    if (count($repos) > 0) { 
        echo "The package '$pkg_alias' already exists in the following repositories:\n";
        foreach ($repos as $repo) { echo "\t$repo\n"; }
    echo "\nAre you sure you want to create the package '$pkg_alias' (yes / no) [no]: "; $ok = strtolower(trim(readline()));
    if ($ok != 'y' || $ok != 'yes') { echo "\nOk.  Exiting.\n"; exit(0); }
    }

    // Get repo ID#, if needed
    if ($repo_id == 0) { 
        $repo_id = $this->get_repo();
    }

    // Create package
    $package = new package();
    $package_id = $package->create((int) $repo_id, $pkg_alias, $name);

    // Debug
    debug::add(4, fmsg("CLI: Completed creation of package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully created the new package '$pkg_alias', and you may begin development.\n\n";

}

/**
* Delete a package.  Removes the package from the current system, 
* please deletes it altogether.  Only to be used if you do not 
* plan on developing this package anymore.
*     Usage:  php apex.php delete_package PACKAGE
*/
protected function delete_package($vars)
{

    // Debug
    debug::add(4, fmsg("CLI: Starting deletion of package: {1}", $vars[0]), __FILE__, __LINE__, 'info');

    // Ensure package exists
    if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $vars[0])) { 
        throw new PackageException('not_exists', $vars[0]);
    }

    // Delete package
    $package = new package();
    $package->remove($vars[0]);

    // Debug
    debug::add(4, fmsg("CLI: Completed deletion of package: {1}", $vars[0]), __FILE__, __LINE__, 'info');

    // Response
    return "Successfully deleted the package, $vars[0]\n";

}

/**
* Publishes a package to the appropriate repository, from where 
* it can be instantly installed on other Apex systems.
*   Usage: php apex.php publish PACKAGE_ALIAS
*/
protected function publish($vars) 
{

    // Checks
    if (!isset($vars[0])) { 
        throw new PackageException('undefined');
    }

    // Publish
    $response = '';
    foreach ($vars as $alias) { 

        // Check package exists
        if (!$row = DB::get_row("SELECT * FROm internal_packages WHERE alias = %s", $alias)) { 
            $response .= "Package does not exist in this system, $alias\n";
            continue;
        }

        // Debug
        debug::add(4, fmsg("CLI: Starting to publish package: {1}", $alias), __FILE__, __LINE__, 'info');

        // Publish
        $client = new package();
        $client->publish($alias);

        // Debug
        debug::add(4, fmsg("CLI: Completed publishing package: {1}", $alias), __FILE__, __LINE__, 'info');

        // Success message
        $response .= "Successfully published the package, $alias\n";
    }

    // Return
    return $response;

}

/**
* Creates a new upgrade point, which is stored in 
* /etc/PACKAGE/upgrades/VERSION/ directory.  Takes a SHA1 hash of all 
* files pertaining to the package, which is used to check which files 
* were modified upon publishing the upgrade.  
*
*  Usage:  php apex.php create_upgrade PACKAGE ]VERSION]
*/
protected function create_upgrade($vars) 
{

    // Set variables
    $pkg_alias = $vars[0] ?? '';
    $version = $vars[1] ?? '';

    // Debug
    debug::add(4, fmsg("CLI: Starting to create upgrade point for package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

    // Check package
    if ($pkg_alias == '') { 
        throw new PackageException('undefined');
    }

    // Check for open upgrades
    $count = DB::get_field("SELECT count(*) FROM internal_upgrades WHERE package = %s AND status = 'open'", $pkg_alias);
    if ($count > 0) { 
        echo "There are currently open upgrades already on this package.  Are you sure you want to create another upgrade point? (y/n) [n]: ";
        $ok = strtolower(trim(readline()));
        if ($ok != 'y') { echo "Ok, exiting.\n"; exit(0); }
    }

    // Create upgrade
    $client = new upgrade();
    $upgrade_id = $client->create($pkg_alias, $version);

    // Debug
    debug::add(4, fmsg("CLI: Completed vreating upgrade point for package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

    // Return response
    $version = DB::get_field("SELECT version FROM internal_upgrades WHERE id = %i", $upgrade_id);
    return "Successfully created upgrade point on package $vars[0] to upgrade version $version\n\n";

}

/**
* Publishes an upgrade to the appropriate repository.
*   Usage: php apex.php publish_upgrade PACKAGE
*/
protected function publish_upgrade($vars) 
{

    // Set variables
    $pkg_alias = $vars[0] ?? '';
    $version = $vars[1] ?? '';

    // Debug
    debug::add(4, fmsg("CLI: Start publishing upgrade point for package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

    // Initial checks
    if ($pkg_alias == '') { 
        throw new PackageException('undefined');
    }

    // Get package
    if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $vars[0])) { 
        throw new PackageException('not_exists', $vars[0]); 
    }

    // Check number of open upgrades
    $num = DB::get_field("SELECT count(*) FROM internal_upgrades WHERE package = %s AND status = 'open'", $pkg_alias);
    if ($num == 0) { 
        throw new PackageException('no_open_upgrades', $pkg_alias);

    // Ask which upgrade to publish
    } elseif ($num > 1) { 

        // Echo message
        echo "More than one open upgrade was found for this package.  Please specify which upgrade you would like to publish.\n\n";

        $available = array(); $x=1;
        $rows = DB::query("SELECT * FROM internal_upgrades WHERE package = %s AND status = 'open' ORDER BY id", $pkg_alias);
        foreach ($rows as $row) { 
            echo "[$x] v$row[version]\n";
            $available[$x] = $row['id'];
        $x++; }

        // Ask for upgrade
        while (1 == 1) { 
            echo "Upgrade to publish: "; $upgrade_id = trim(readline());
            if (isset($available[$upgrade_id])) { break; }
            echo "You did not specify an upgrade to publish.\n\n";
        }

        // Get upgrade row
        if (!$upgrade = DB::get_idrow('internal_upgrades', $upgrade_id)) { 
            throw new UpgradeException('not_exists', $upgrade_id);
        }

    // Get one available upgrade
    } else { 
        $upgrade = DB::get_row("SELECT * FROM internal_upgrades WHERE package = %s AND status = 'open' LIMIT 0,1", $pkg_alias);
    }

    // Publish upgrade
    $client = new upgrade();
    $client->publish((int) $upgrade['id']);

    // Debug
    debug::add(4, fmsg("CLI: Completed publishing upgrade point for package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

    // Set response
    $response = "Successfully published the appropriate upgrade for package, $pkg_alias\n\n";

    // Ask to create new upgrade point
    $ok = $vars[2] ?? '';
    if ($ok == '') { 
        echo "Would you like to create a new upgrade point? (y\n) [y]: "; 
        $ok = strtolower(trim(readline()));
    }

    // Create upgrade point, if needed
    if ($ok == '' || $ok == 1 || strtolower($ok) == 'y') { 
        $response .= $this->create_upgrade(array($pkg_alias));
    }

    // Return
    return $response;

}

/**
* Check for upgrades
*     Usage:  php apex.php check_upgrades [PACKAGE]
*/
protected function check_upgrades($vars)
{

    // Get packages
    $packages = array();
    foreach ($vars as $alias) { 
        $packages[] = $alias;
    }

    // Check upgrades
    $client = new network();
    $upgrades = $client->check_upgrades($packages);

    // Go through upgrades
    $results = '';
    foreach ($upgrades as $pkg_alias => $version) { 

        // Get package
        if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $pkg_alias)) { 
            continue;
        }

        // Add to results
        $results .= '[' . $pkg_alias . '] ' . $row['display_name'] . ' v' . $version . "\n";
    }

    // Give response
    if ($results == '') { 
        $response = "No upgrades were found for any installed packages.\n";
    } else { 
        $response = "The following available upgrades were found:\n\n";
        $response .= $results . "\n";
        $response .= "If desired, you may install the upgrades with: php apex.php upgrade\n";
    }

    // Debug
    debug::add(4, "CLI: check_upgrades done", __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* Install upgrades
*     Usage:  php apex.php upgrade [PACKAGE]
*/
protected function upgrade($vars)
{

    // Get available upgrades, if needed
    if (count($vars) == 0) { 
        $client = new network();
        $upgrades = $client->check_upgrades();
        $vars = array_keys($upgrades);
    }

    // Go through packages
    $response = '';
    foreach ($vars as $pkg_alias) { 

        // Debug
        debug::add(4, fmsg("CLI: Starting upgrade of package: {1}", $pkg_alias), __FILE__, __LINE__, 'info');

        // Install upgrades
        $client = new upgrade();
        $new_version = $client->install($pkg_alias);

        // Debug
        debug::add(4, fmsg("CLI: Completed upgrade of package: {1} to version {2}", $pkg_alias, $new_version), __FILE__, __LINE__, 'info');

        // Add to response
        $response .= "Successfully upgraded the packages $pkg_alias to v$new_version\n";
    }

    // Return
    return $response;

}

/**
* create a new theme
*     Usage:  php apex.php create_theme ALIAS [AREA]
*/
protected function create_theme($vars) 
{

    // Set variables
    $alias = strtolower($vars[0]) ?? '';
    $area = $vars[1] ?? 'public';
    $repo_id = $vars[2] ?? 0;

    // Debug
    debug::add(4, fmsg("CLI: Start theme creation, alias: {1}, area: {2}", $alias, $area), __FILE__, __LINE__, 'info');

    // Get repo ID
    if ($repo_id == 0) { 
        $repo_id = $this->get_repo();
    }

    // Create theme
    $theme = new theme();
    $theme->create($alias, (int) $repo_id, $area);

    // Echo message
    $response = "Successfully created new theme, $alias.  New directories to implment the theme are now available at:\n\n";
    $response .= "\t/themes/$alias\n";
    $response .= "\t/public/themes/$alias\n\n";

    // Debug
    debug::add(4, fmsg("CLI: Completed theme creation, alias: {1}, area: {2}", $alias, $area), __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* Publish a theme to a repository.
*     Usage:  php apex.php publish_theme ALIAS
*/
protected function publish_theme($vars)
{

    // Debug
    debug::add(4, fmsg("CLI: Start publishing theme: {1}", $vars[0]), __FILE__, __LINE__, 'info');

    // Upload theme
    $theme = new theme();
    $theme->publish($vars[0]);

    // Debug
    debug::add(4, fmsg("CLI: Completed publishing theme: {1}", $vars[0]), __FILE__, __LINE__, 'info');

    // Give response
    return "Successfully published the theme, $vars[0]\n";

}

/**
* List all available themes
*     Usage:  php apex.php list_themes
*/
protected function list_themes($vars)
{

    // Get themes
    $client = new network();
    $themes = $client->list_themes();

    // Check for no themes
    if (count($themes) == 0) { 
        return "No themes are avilable from any repositories.\n";
    }

    // Go through themes
    $public_themes = ''; $members_themes = '';
    foreach ($themes as $alias => $vars) { 
        $line = $alias . ' -- ' . $vars['name'] . ' (' . $vars['author_name'] . ' <' . $vars['author_email'] . ">\n";
        if ($vars['area'] == 'members') { 
            $members_themes .= $line;
        } else { 
            $public_themes .= $line;
        }
    }

    // Get response
    $response = '';
    if ($public_themes != '') { 
        $response .= "--- Public Site Themes ---\n";
        $response .= "$public_themes\n";
    }
    if ($members_themes != '') { 
        $response .= "--- Member Area Themes ---\n";
        $response .= "$members_themes\n";
    }

    // Debug
    debug::add(4, "CLI: list_themes", __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* Download and install a theme
*     Usage:  php apex.php install_theme THEME_ALIAS
*/
protected function install_theme($vars)
{

    // Set variables
    $theme_alias = $vars[0] ?? '';

    // Debug
    debug::add(4, fmsg("CLI: Start installing theme: {1}", $theme_alias), __FILE__, __LINE__, 'info');


    // Install theme
    $theme = new theme();
    $theme->install($theme_alias);

    // Debug
    debug::add(4, fmsg("CLI: Completed installing theme: {1}", $theme_alias), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully downloaded and installed the theme, $theme_alias\n";

}

/**
* Delete theme
*     Usage:  php apex.php delete_theme THEME_ALIAS
*/
protected function delete_theme($vars)
{

    // Set variables
    $theme_alias = $vars[0] ?? '';

    // Debug
    debug::add(4, fmsg("CLI: Start theme deletion: {1}", $theme_alias), __FILE__, __LINE__, 'info');

    // Delete theme
    $theme = new theme();
    $theme->remove($theme_alias);

    // Debug
    debug::add(4, fmsg("CLI: Completed theme deletion: {1}", $theme_alias), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully deleted the theme, $theme_alias\n";

}

/**
* Change theme on an area to another theme.
*     Usage:  php apex.php change_theme AREA THEME_ALIAS
*/
protected function change_theme($vars) 
{

    // Set variables
    $area = $vars[0] ?? '';
    $theme_alias = $vars[1] ?? '';

    // Perform checks
    if ($area != 'public' && $area != 'members' && $area != 'admin') { 
        throw new ApexException('error', "Invalid area specified, {1}", $area);
    } elseif (!$row = DB::get_row("SELECT * FROM internal_themes WHERE alias = %s", $theme_alias)) { 
        throw new ThemeException('not_exists', $theme_alias);
    }

    // Update theme
    if ($area == 'members') { 
        registry::update_config_var('users:theme_members', $theme_alias);
    } else { 
        registry::update_config_var('core:' . $area, $theme_alias);
    }

    // Debug
    debug::add(4, fmsg("CLI: Changed theme on area '{1}' to theme: {2}", $area, $theme_alias), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully changed the theme of area %area to the theme $theme_alias\n";

}

/**
* Creates a new component (eg. htmlfunc, modal, template, lib, etc.) and 
* should be used during development.  Do not manually create the files on 
* the server, and instead use this create command to ensure proper records are added to the database for packaging.
*
*   Usage:  php apex.php create TYPE PACKAGE:[PARENT:]ALIAS OWNER
*          php apex.php create template URI OWNER
* 
* TYPE is the component type (eg. htmlfunc, table, form).
* PACKAGE is required, and is the package alias the component is being created under.
* PARENT is only required for the types 'tabpage', 'controller', and 'trigger'.
* ALIAS is the alias of the new component.
* OWNER is only required for type 'template', 'trigger', 'controller', and 'tabpage'.  This is the package who owns the package, even though the component may reside in another package.
* URI is only required for 'template', and is the URI of the new template.
*/ 
protected function create($vars) 
{

    // Set variables
    $type = strtolower($vars[0]) ?? '';
    $comp_alias = $vars[1] ?? '';
    $owner = $vars[2] ?? '';

    // Debug
    debug::add(4, fmsg("CLI: Start component creation, type: {1}, component alias: {2}, owner: {3}", $type, $comp_alias, $owner), __FILE__, __LINE__, 'info');


    // Perform checks
    if ($type == '') { 
        throw new ComponentException('undefined_type');
    } elseif (!in_array($type, COMPONENT_TYPES)) { 
        throw new ComponentException('invalid_type', $type);
    } elseif ($type != 'template' && ($comp_alias == '' || !preg_match("/^(\w+):(\w+)/", $comp_alias)) ){ 
        throw new ComponentException('invalid_comp_alias', $type, $comp_alias);
    } 

    // Create component
    list($type, $alias, $package, $parent) = pkg_component::create($type, $comp_alias, $owner);

    // Get files
    $files = components::get_all_files($type, $alias, $package, $parent);

    // Set response
    $response = "Successfully created new $type, $comp_alias.  New files have been created at:\n\n";
    foreach ($files as $file) { 
        $response .= "\t\t$file\n";
    }

    // Debug
    debug::add(4, fmsg("CLI: Completed component creation, type: {1}, component alias: {2}, owner: {3}", $type, $comp_alias, $owner), __FILE__, __LINE__, 'info');

    // Return
    return $response;

}

/**
* Delete a component from the system, including file 
* and records from database.
*   Usage:  php apex.php delete TYPE PACKAGE:[PARENT:]ALIAS
           php apex.php delete template URI
*/
protected function delete($vars) 
{

    // Initialize
    $type = strtolower($vars[0]);

    // Debug
    debug::add(4, fmsg("CLI: Start component deletion, type: {1}, component alias: {2}", $type, $vars[1]), __FILE__, __LINE__, 'info');

    // Check if component exists
    if (!list($package, $parent, $alias) = components::check($type, $vars[1])) { 
        throw new ComponentException('not_exists', $type, $vars[1]);
    }

    // Delete 
    pkg_component::remove($type, $vars[1]);

    // Debug
    debug::add(4, fmsg("CLI: Completed component deletion, type: {1}, component alias: {2}", $type, $vars[1]), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully deleted the component of type $type, with alias $vars[1]\n\n";

}

/**
* Update the debug variable, telling them system whether or not to 
* save debug information.
*     0 - Debugging off
*     1 - Debugging on, but only for next request
*      2 - Debugging on, do not turn off
* 
*     Usage: php apex.php debug NUM
*/
protected function debug($vars)
{

    // Update config
    registry::update_config_var('core:debug', $vars[0]);

    // Debug
    debug::add(4, fmsg("CLI: Updated debug mode to {1}", $vars[0]), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully changed debugging mode to $vars[0]\n";

}

/**
* Change the server mode between development / production, and the debug level
*     Usage: php apex.php mode [devel|prod] [DEBUG_LEVEL]
*/
protected function mode($vars) 
{

    // CHeck
    $mode = strtolower($vars[0]);
    if ($mode != 'devel' && $mode != 'prod') { 
        throw new ApexException('error', "You must specify the mode as either 'devel' or 'prod'");
    }

    // Update config
    registry::update_config_var('core:mode', $mode);
    if (isset($vars[1])) { 
        registry::update_config_var('core:debug_level', $vars[1]);
    }

    // Debug
    $level = $vars[1] ?? registry::config('core:debug_level');
    debug::add(4, fmsg("CLI: Updated server mode to {1}, debug level to {2}", $mode, $level), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully updated server mode to $mode, and debug level to $level\n";

}

/**
* Changes the server type as necessary.
*     Usage:  php apex.php server_type TYPE
*/
protected function server_type($vars) { 

    // Check
    $type = $vars[0] ?? '';
    if (!in_array($type, array('all','web','app','dbs','dbm','msg'))) { 
        throw new ApexException('error', "INvalid server type, {1}", $type);
    }

    // Update config
    registry::update_config_var('core:server_type', $type);

    // Debug
debug::add(2, fmsg("CLI: Updated server type to {1}", $type), __FILE__, __LINE__);

    // Return
    return "Successfully updated server type to $type\n";

}

/**
* Execute unit tests for a given package.
*     Usage:  php apex.php test PACKAGE [CLASS_NAME]
*/
protected function test($vars)
{

    // Get package
    if (!$row = DB::get_row("SELECT * FROM internal_packages WHERE alias = %s", $vars[0])) { 
        throw new PackageException('not_exists', $vars[0]);
    }

    // Set variables
    $pkg_alias = $vars[0];
    $test = isset($vars[1]) ? $vars[1] . '.php' : '*';

    // Execute single test
    if ($test != '*') { 
        debug::add(4, fmsg("CLI: Executing unit tests, package: {1}, class: {2}", $pkg_alias, $test), __FILE__, __LINE__, 'info');
        system("./vendor/bin/phpunit --bootstrap src/load.php src/$pkg_alias/test/$test");
        return "Successfully run specified unit test\n";
    }

    // Run all tests
    $dir = SITE_PATH . '/src/' . $pkg_alias . '/test';
    $tests = io::parse_dir($dir);

    // Go through tets
    foreach ($tests as $test) { 
        if (!preg_match("/\.php$/", $test)) { continue; }
        debug::add(4, fmsg("CLI: Executing unit tests, package: {1}, class: {2}", $pkg_alias, $test), __FILE__, __LINE__, 'info');
        system("./vendor/bin/phpunit --bootstrap src/load.php src/$pkg_alias/test/$test");
    }

    // Return
    return "Successfully ran all unit tests for the package, $pkg_alias\n";

}

/**
* Perform all unit tests available on all packages installed on the system
*     Usage:  php apex.php testall
*/
protected function testall($vars)
{

    // Debug
    debug::add(4, "CLI: Start executing all unit tests", __FILE__, __LINE__, 'info');

    // Go through packages
    foreach ($vars as $pkg_alias) { 
        $dir = SITE_PATH . '/src/' . $pkg_alias . '/test/';
        if (!is_dir($dir)) { continue; }

        // Get tests
        $tests = io::parse_dir($dir);

        // Go through tests
        foreach ($tests as $test) { 
            if (!preg_match("/\.php$/", $test)) { continue; }  
            debug::add(4, fmsg("CLI: Executing unit tests, package: {1}, class: {2}", $pkg_alias, $test), __FILE__, __LINE__, 'info');
            system("./vendor/bin/phpunit --bootstrap src/load.php src/$pkg_alias/test/$test");
        }
    }

    // Debug
    debug::add(4, "CLI: Completed executing all unit tests", __FILE__, __LINE__, 'info');

    // Return
    return "Successfully executed all unit tests within all installed packages\n";

}

/**
* Add a new repo
*     Usage:  php apex.php add_repo URL [USERNAME] [PASSWORD]
*/
protected function add_repo($vars)
{

    // Set variables
    $url = $vars[0] ?? '';
    $username = $vars[1] ?? '';
    $password = $vars[2] ?? '';

    // Initial checks
    if ($url == '' || !preg_match("/^https?:\/\//", $url)) { 
        throw new RepoException('not_exists', 0, $url);
    }

    // Get repo info
    $url = trim($url, '/');
    $response = io::send_http_request($url . '/repo/get_info');

    // Check response
    if ($response == '') { 
        throw new RepoException('invalid_repo', 0, $url);
    } elseif (!$vars = json_decode($response, true)) { 
        throw new RepoException('invalid_repo', 0, $url);
    } elseif (!isset($vars['is_apex_repo'])) { 
        throw new RepoException('invalid_repo', 0, $url);
    } elseif ($vars['is_apex_repo'] != 1) { 
        throw new RepoException('invalid_repo', 0, $url);
    }

    // Add to database
    DB::insert('internal_repos', array(
        'username' => encrypt::encrypt_basic($username), 
        'password' => encrypt::encrypt_basic($password), 
        'url' => $url, 
        'display_name' => $vars['name'], 
        'description' => $vars['tagline'])
    );

    // Debug
    debug::add(4, fmsg("CLI: Added new repository, {1}", $url), __FILE__, __LINE__, 'info');

    // Return
    return "Successfully added new repository, $url\n";

} 

/**
* Update a repo with username and password
*/
protected function update_repo($vars)
{

    // Check repo
    if (!$row = DB::get_row("SELECT * FROM internal_repos WHERE url LIKE %ls", $vars[0])) { 
        throw new RepoException('host_not_exists', 0, $vars[0]);
    }

    // Get username / password
    echo "Username: "; $username = trim(readline());
    echo "Password: "; $password = trim(readline()); 

    // Update database
    DB::update('internal_repos', array(
        'username' => encrypt::encrypt_basic($username), 
        'password' => encrypt::encrypt_basic($password)), 
    "id = %i", $row['id']);

    // Debug
    debug::add(4, fmsg("CLI: Updated repository login information, URL: {1}", $vars[0]), __FILE__, __LINE__, 'info');

    // Give response
    return "Successfully updated repo with new username and password.\n";

}

/**
* Update master database info
*     Usage:  php apex.php update_masterdb
*/
protected function update_masterdb($vars)
{

    // Get info
    echo "DB Name: "; $dbname = trim(readline());
    echo "DB User: "; $dbuser = trim(readline());
    echo "DB Pass: "; $dbpass = trim(readline());
    echo "DB Host [localhost]: "; $dbhost = trim(readline());
    echo "DB Port [3306]: "; $dbport = trim(readline());
    if ($dbhost == '') { $dbhost = 'localhost'; }
    if ($dbport == '') { $dbport = '3306'; }

    // Read only user info
    echo "\n----- Optional Read Only Info -----\n\n";
    echo "Read-Only User: "; $dbuser_readonly = trim(readline());
    echo "Read-Only Pass: "; $dbpass_readonly = trim(readline());

    // Set vars
    $vars = array(
        'dbname' => $dbname, 
        'dbuser' => $dbuser, 
        'dbpass' => $dbpass, 
        'dbhost' => $dbhost, 
        'dbport' => $dbport, 
        'dbuser_readonly' => $dbuser_readonly, 
        'dbpass_readonly' => $dbpass_readonly
    );

    // Update redis
    registry::$redis->del('config:db_master');
    registry::$redis->hmset('config:db_master', $vars);

    // Debug
    debug::add(4, "Updated master database connection information", __FILE__, __LINE__, 'info');

    // Return
    return "SUccessfully updated master database information.\n";

}

/**
* Clear all db slave servers
*     Usage:  php apex.php clear_dbslaves
*/
protected function clear_dbslaves()
{

    // Delete
    registry::$redis->del('config:db_slaves');

    // Debug
    debug::add(4, "CLI: Removed all database slave servers", __FILE__, __LINE__, 'info');

    // Return
    return "Successfully cleared all database slave servers.\n";

}

/**
* Update RabbitMQ info
*     Usage:  php apex.php update_rabbitmq
*/
protected function update_rabbitmq()
{

    // Get info
    echo "Host [localhost]: "; $host = trim(readline());
    echo "User [guest]: "; $user = trim(readline());
    echo "Pass [guest]: "; $pass = trim(readline());
    echo "Port [5672]: "; $port = trim(readline());

    // Set default variables
    if ($host == '') { $host = 'localhost'; }
    if ($user == '') { $user = 'guest'; }
    if ($pass == '') { $pass = 'guest'; }
    if ($port == '') { $port = '5672'; }

    // Set vars
    $vars = array(
        'host' => $host, 
        'user' => $user, 
        'pass' => $pass, 
        'port' => $port
    );

    // Update redis
    registry::$redis->del('config:rabbitmq');
    registry::$redis->hmset('config:rabbitmq', $vars);

    // Debug
    debug::add(4, "CLI: Updated RabbitMQ connection information", __FILE__, __LINE__, 'info');

    // Return
    return "Successfully updated RabbitMQ connection information.\n";

}

/**
* Compiles the core Apex framework.  This is generally only needed 
* by the Apex development team, and basically packages Apex to exactly 
* what you see in the Github repository.
*   Usage:  php apex.php compile_core
*/
protected function compile_core($vars)
{

    // Compile
    $client = new package();
    $destdir = $client->compile_core();

    // Debug
    debug::add(4, "CLI: Compiled core Apex framework", __FILE__, __LINE__, 'info');

    // Return
    return  "Successfully compiled the core Apex framework, and it is located at $destdir\n\n";

}

/**
* Get a repository.  Lists a list of all repos on the system, and 
* has user choose one.
*/
private function get_repo()
{

    // Check number of repos
    $count = DB::get_field("SELECT count(*) FROM internal_repos WHERE is_active = 1");
    if ($count == 0) { 
        throw new RepoException('no_repos_exist');
    }

    // Ask to select repo
    if ($count > 1) {

        // List repos 
        echo "\nAvailable Repositories:\n";
        $rows = DB::query("SELECT * FROM internal_repos WHERE is_active = 1 ORDER BY id");
        foreach ($rows as $row) { 
            echo "\t[" . $row['id'] . "] $row[display_name] ($row[url])\n";
        }
        echo "\nWhich repository to save package on? "; 
        $repo_id = trim(readline());

    // Get the only available repo
    } else { 
        $repo_id = DB::get_field("SELECT id FROM internal_repos WHERE is_active = 1"); 
    }

    // Ensure repo exists
    if (!$row = DB::get_idrow('internal_repos', $repo_id)) { 
        throw new RepoException('not_exists', $repo_id);
    }

    // Return
    return (int) $repo_id;

}
}

