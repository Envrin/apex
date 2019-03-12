<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\ComponentException;
use apex\core\components;


/**
* Handles the various has operations against the hashes 
* defined within the $this->hash array within package.php configuration 
* files.  Allows you to easily parse a hash, obtain select options, etc.
*/
class hashes
{

/**
* Uses key-value pairs of a hash, and creates the necessary 
( HTML code for select, radio, or checkbox lists.
*     @param string $hash_alias The alias of the hash in standard Apex format (eg. PACKAGE:ALIAS).
*     @param string/array $value Optional, and the value of the select / radio list.  Pass array if form field is 'checkbox'.
*     @param string $form_field Can be either 'select', 'radio', or 'checkbox'.  Defaults to 'select'.
*     @param string $form_name Only valid if $form_field is 'radio' or 'checkbox', and is the name of the form field.
*     @return string The resulting HTML code for the select / ario / checkbox list.
*/
public static function create_options(string $hash_alias, $value = '', string $form_field = 'select', string $form_name = ''):string
{

    // Check hash
    if (!list($package, $parent, $alias) = components::check('hash', $hash_alias)) { 
        throw new ComponentException('not_exists_alias', 'hash', $hash_alias);
    }

    // Ensure hash exists
    $hash_alias = $package . ':' . $alias;
    if (registry::$redis->hexists('hash', $hash_alias) == 0) {
        throw new ComponentException('hash_no_redis', 'hash', $hash_alias);
    }

    // Go through all hash variables
    $html = '';	
    $rows = json_decode(registry::$redis->hget('hash', $hash_alias), true);
    foreach ($rows as $hkey => $hvalue) { 
        $hvalue = tr($hvalue);

        // Select
        if ($form_field == 'select') { 
            $chk = $value == $hkey ? 'selected="selected"' : '';
            $html .= "<option value=\"$hkey\" $chk>$hvalue</option>";

        // Checkbox
        } elseif ($form_field == 'checkbox') { 
            $chk = (is_array($value) && in_array($hvalue, $value)) || ($value == $hvalue) ? 'checked="checked"' : '';
            $html .= "<input type=\"checkbox\" name=\"" . $form_name . "[]\" value=\"$hkey\" $chk> $hvalue<br />";

        } elseif ($form_field == 'radio') { 
            $chk = $value == $hvalue ? 'checked="checked"' : '';
            $html .= "<input type=\"radio\" name=\"$form_name\" value=\"$hkey\" $chk> $hvalue<br />";
        }
    }

    // Debug
    debug::add(4, fmsg("Created hash options for the hash: {1}", $hash_alias), __FILE__, __LINE__);

    // Return
    return $html;

}

/**
* Returns the value of a hash variable.
* 
*     @param string $hash_alias The alis of the ash in standard Apex format (ie. PACKAGE:ALIAS).
*     @param $var_alias The alias / key of the variable to return the value of.
*     @return string The value of the variable.
*/
public static function get_hash_var(string $hash_alias, string $var_alias) 
{

    // Check component
    if (!list($package, $parent, $alias) = components::check('hash', $hash_alias)) { 
        throw new ComponentException('not_exists_alias', 'hash', $hash_alias);
    }

    // Get hash variable
    $hash_alias = $package . ':' . $alias;
    if (!$data = registry::$redis->hget('hash', $hash_alias)) { 
        return false;
    }

    // Decode JSON
    if (!$vars = json_decode($data, true)) { return false; }

    if (!isset($vars[$var_alias])) { return false; }

    // Debug
    debug::add(5, fmsg("Retrieved alue of hash ariable from hash: {1}, key: {2}", $hash_alias, $alias), __FILE__, __LINE__);

    // Return
    return $vars[$var_alias];

}

/**
* Parses a database source, and returns the necessary select list.  
* This allows you to pull information from a variety of ways, 
* and siplay a select list of options.
*
*     @param string $data_source The data source.  See documentation for full details.
*     @param string $value The value of the select list, if an option is selected.
*     @param string $form_field The form field to output, can be 'select', 'radio' or 'checkbox'.  Defaults to 'select'.
*     @param string $form_name Only applicable if $form_field is 'radio' or 'checkbox', and is the name of the form field.
*     @return string The resulting HTML code.
*/
public static function parse_data_source(string $data_source, string $value = '', string $form_field = 'select', string $form_name = ''):string
{

    // Debug
    debug::add(5, fmsg("Parsing hash data source, {1}", $data_source), __FILE__, __LINE__);

    // Initialize
    $source = explode(":", $data_source);

    // Hash
    if ($source[0] == 'hash') {  
        $hash_alias = $source[1] . ':' . $source[2];
        $html = self::create_options($hash_alias, $value, $form_field, $form_name);

    // Function
    } elseif ($source[0] == 'function') { 
        $class_name = "\\apex\\" . $source[1] . "\\" . $source[2];
        $func_name = $source[3];

        $class = new $class_name();
        $html = $class->$func_name($value);

    // Stdlist
    } elseif ($source[0] == 'stdlist') {

        // Get active languages

        if ($source[1] == 'language' && isset($source[2]) && $source[2] == 1) { 
            $active_lang = registry::$redis->lrange('config:language', 0, -1);
        } else { $active_lang = array(); }

        // Get active currencies
        if ($source[1] == 'currency') { 
        $active_currency = registry::$redis->lrange('config:currency', 0, -1);
    } else { $active_currency = array(); } 

        $html = '';
        $rows = registry::$redis->hgetall('std:' . $source[1]);
        foreach ($rows as $abbr => $line) { 
            $vars = explode("::", $line);

            if ($source[1] == 'currency' && isset($source[2]) && $source[2] == 1 && !in-array($abbr, $active_currency)) { continue; }
            if ($source[1] == 'language' && !in_array($abbr, $active_lang)) { continue; }

            $chk = $value == $abbr && $abbr != '' ? 'selected="selected"' : '';
            $name = $source[1] == 'country' && isset($source[2]) && $source[2] == 'calling_code' ? $vars[4] : $vars[0];
            $html .= "<option value=\"$abbr\" $chk>$name</option>\n";
        }

    // Table
    } elseif ($source[0] == 'table') { 

        // Go through rows
        $html = '';
        $result = DB::query("SELECT * FROM $source[1] ORDER BY $source[2]");
        while ($row = DB::fetch_assoc($result)) {

            // Parse name
            $temp = $source[3];
            foreach ($row as $key => $value) { $temp = str_ireplace("~$key~", $value, $temp); }

            // Add to options
            $idcol = $source[4] ?? 'id'; 
            $chk = $row[$idcol] == $value && $row[$idcol] != '' ? 'selected="selected"' : '';
            $html .= "<option value=\"$row[$idcol]\" $chk>$temp</option>\n";
        }
    }

    // Return
    return $html;

}

/**
* Gets value of a stdlist variable (timezone, language, country, currency)
* 
*     @param string $type The list to retrieve value from.  Must be 'timezone', 'country', 'language', or 'currency'.
*     @param string $abbr The abbreviation of the value (eg. 'en' = English).  This is the value stored within the database.
*     @param $column The column from the internal_stdlists table to return.  Defaults to 'name'
*     @return string The value of the stdlist variable.
*/
public static function get_stdvar(string $type, string $abbr, string $column = 'name'):string 
{

    // Get variable
    if ($value = registry::$redis->hget('std:' . $type, $abbr)) { 
        $vars = explode("::", $value);
        $value = $vars[0];
    } else { $value = ''; }

    // Debug
    debug::add(5, fmsg("Retrieed stdlist ariable of type {1} with abbr {2}", $type, $abbr), __FILE__, __LINE__);

    // Return
    return $value;

}





}

