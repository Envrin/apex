<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\ComponentException;

class components
{

/**
* Takes in the component type and alias, formatted in 
* standard Apex format (PACKAGE:[PARENT:]ALIAS), 
* checks to see if it exists, and returns an array of the package, 
* parent and alias.
*
*     @param string $type The component type (eg. htmlfunc, lib, modal, etc.)
*     @param string $alias Apex formatted component alias (PACAKGE:[PARENT:]ALIAS)
*     @return bool/array Returns false if no components exists, otherwise returns array of the package, parent and alias.
*/
public static function check(string $type, string $alias) 
{

    // Initialize
    $parts = explode(":", strtolower($alias));
    if (count($parts) == 3) { 
        list($package, $parent, $alias) = array($parts[0], $parts[1], $parts[2]);
    } elseif (count($parts) == 2) { 
        list($package, $parent, $alias) = array($parts[0], '', $parts[1]);
    } else { 
        list($package, $parent) = array('', '');
    }

    // Debug
    debug::add(5, fmsg("Checking if component exists, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

    // Check package
    if ($package == '') {
        $chk = $type . ':' . $alias;
        if (!$package = registry::$redis->hget('config:components_package', $chk)) { return false; }
        elseif ($package == 2) { return false; }
    }

    // Ensure component exists
    $chk = implode(":", array($type, $package, $parent, $alias));
    if (registry::$redis->sismember('config:components', $chk) == 0) { return false; }

    // Return
    return array($package, $parent, $alias);

}

/**
* Loads a components, and returns the initialized object.
*
*     @[aram string $type The component type (eg. htmlfunc, lib, modal)
*     @param string $alias The alias of the components
*     @param string $package The package alias of the components.
*      @param string $parent The parent alias of the components, if exists
*     @param array $data Optional array with extra data, generally the attributes of the <e:function> tag that is loading the components.
*     @return bool/object Returns false if unable to load components, otherwise returns the newly initialized object.
*/
public static function load(string $type, string $alias, string $package = '', string $parent = '', array $data = array()) 
{

    // Debug
    debug::add(5, fmsg("Starting load component, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

    // Get filename
    $file = self::get_file($type, $alias, $package, $parent);
    $file = SITE_PATH . '/' . $file;

    // Check if file exists
    if (!file_exists($file)) { 
        debug::add(4, fmsg("Component PHP file does not exist, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__, 'warning');
        return false; 
    }

    // Load file
    require_once($file);

    // Load object
    $class_vars = $parent == '' ? array('apex', $package, $type, $alias) : array('apex', $package, $type, $parent, $alias);
    $class_name = implode("\\", $class_vars);

    // Debug
    debug::add(5, fmsg("Loaded component, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

    // Return
    return new $class_name($data);

}

/**
* Get the location of a component's PHP file
*
*     @param string $type The component type (eg. htmlfunc, modal, lib, etc.)
*     @param string $alias The alias of the components
*     @param string $package The package alias of the components
*     @param string $parent The parent alias of the components, if one exists.
*      @return string The full path to the PHP class file of the components.
*/
public static function get_file(string $type, string $alias, string $package, string $parent = ''):string
{

    // Debug
    debug::add(5, fmsg("Getting PHP component file, type: {1}, package: {2}, parent: {3}, alias: {4}", $type, $package, $parent, $alias), __FILE__, __LINE__);

    // Ensure valid components type
    if (!in_array($type, COMPONENT_TYPES)) { 
        return '';
    }

    // Get template file, if needed
    if ($type == 'template') {
        $php_file = 'views/php/' . $alias . '.php';
        return $php_file;
    } 

    // Set variables
    $file_type = $type == 'tabpage' ? 'tabcontrol' : $type;

    // Get PHP file
    $php_file =  'src/' . $package . '/';
    if ($file_type != 'lib') { $php_file .= $file_type . '/'; }
    if ($parent != '') { $php_file .= $parent . '/'; }
    $php_file .= $alias . '.php';

    // Debug
    debug::add(5, fmsg("Got PHP component file, {1}", $php_file), __FILE__, __LINE__);

    // Return
    return $php_file;

}

/**
* Get the TPL file of any given template.
* 
*     @param string $type The component type (template, modal, htmlfunc, etc.)
*     @param string $alias The alias of the component
*     @param string $package The package alias of the component
*     @param string $parent The parent alias of the component, if exists
*     @return string The full path to the TPL file of the component
*/
public static function get_tpl_file(string $type, string $alias, string $package, string $parent = ''):string
{

    // Check template
    if ($type == 'template') { 
        $tpl_file = 'views/tpl/' . $alias . '.tpl';
        return $tpl_file;
    }

    // Get TPL file
if (in_array($type, array('tabpage', 'modal', 'htmlfunc'))) { 
        $tpl_file = 'views/' . $type . '/' . $package . '/';
        if ($parent != '') { $tpl_file .= $parent . '/'; }
        $tpl_file .= $alias . '.tpl';

        // Return
        return $tpl_file;
    }

    // Return
    return '';

}

/**
* Gets all files, .php and .tpl associated with a tab control.
* 
*     @param string $alias The alias of the tab control.
*     @param string $package The package alias of the tab control.
*     @return array One-dimensional array of files.
*/
public static function get_tabcontrol_files(string $alias, string $package):array
{

    // Load tab control
    if (!$tab = self::load('tabcontrol', $alias, $package)) { 
        throw new ComponentException('no_load', 'tabcontrol', '', $alias, $package); 
    }

    // Go through tab pages
    $files = array();
    foreach ($tab->tabpages as $tab_alias => $tab_name) { 
        $files[] = 'views/tabpage/' . $package . '/' . $alias . '/' . $tab_alias . '.tpl';
        $files[] = 'src/' . $package . '/tabcontrol/' . $alias . '/' . $tab_alias . '.php'; 
    }

    // Return
    return $files;

}

/**
* Get all files associated with the given component 
*     @param string $type The component type (template, modal, htmlfunc, etc.)
*     @param string $alias The alias of the component
*     @param string $package The package alias of the component
*     @param string $parent The parent alias of the component, if exists
*     @return array List of all files associated with the component
*/
public static function get_all_files(string $type, string $alias, string $package, string $parent = '')
{

    // PHP file
    $files = array();
    $php_file = self::get_file($type, $alias, $package, $parent);
    if ($php_file != '') { $files[] = $php_file; }

    // TPL file
    $tpl_file = self::get_tpl_file($type, $alias, $package, $parent);
    if ($tpl_file != '') { $files[] = $tpl_file; }

    // Check for tab control
    if ($type == 'tab_control') { 
        $tab_files = self::get_tabcontrol_files($alias, $package);
        $files = array_merge($files, $tab_files);
    }

    // Return
    return $files;

}
}

