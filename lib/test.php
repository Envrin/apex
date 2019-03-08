<?php
declare(strict_types = 1);

namespace apex;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint_IsEqual;

class test extends TestCase
{

/**
* Check page title
*/
final public function assertpagetitle($expected)
{
    $this->assertequals($expected, template::$page_title, tr("Page title at /%s/%s does not equal: %s", registry::$panel, registry::$route, $expected));
}

/**
* Page title contains
*/
final public function assertpagetitlecontains($text)
{

    if (!preg_match("/$text/", template::$page_title)) { 
        $this->asserttrue(false, tr("Page title at /%s/%s does not contain the text: %x", registry::$panel, registry::$route, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Ensure page contains certain text
*/
final public function assertpagecontains($text)
{

    // Get HTML
    $html = registry::get_response();

    // Check if text exists
    if (!preg_match("/$text/", $html)) { 
        $this->asserttrue(false, tr("Page /%s/%s does not contain the text: %s", registry::$panel, registry::$route, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check for user message type / contents
*/
final public function asserthasusermessage($type = 'success', $text = '')
{

    // Initialize
    $msg = template::$user_messages;
    if (!isset($msg[$type])) { $msg[$type] = array(); }

    // Check if message exists
    if (count($msg[$type]) == 0) { 
        $this->asserttrue(false, tr("No user message of type '%s' appeared on the page /%s/%s", $type, registry::$panel, registry::$route));
        return;
    }

// Check for text, if needed
    if ($text != '') {
        $text = str_replace("/", "\\/", $text);
        $this->assertpagecontains($text);

        $found = false; 
        foreach ($msg[$type] as $msg) { 
            if (preg_match("/$text/", $msg)) { $found = true; }
        }

        // Check
        if ($found === false) { 
            $this->asserttrue(false, tr("No user message with type '%s' on page /%s/%s contains the text: %s", $type, registry::$panel, registry::$route, $text));
            return;
        }
    }

    // True
    $this->asserttrue(true);

}

/**
* Check if page contains submit button
'*/
final public function asserthassubmit(string $value, string $label)
{

    // Set variables
    $html = registry::get_response();
    $chk = "<button type=\"submit\" name=\"submit\" value=\"$value\" class=\"btn btn-primary btn-lg\">$label</button>";

    // Check
    if (!strpos($html, $chk)) { 
        $this->asserttrue(false, tr("Page does not contain submit button with value '%s', label '%s'", $value, $label));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Contains a form error
*/
final public function asserthasformerror(string $type, string $name)
{

    // Set variables
    $name = ucwords(str_replace("_", " ", $name));

    // Create message
    if ($type == 'blank') { $msg = "The form field $name was left blank, and is required"; } 
    elseif ($type == 'email') { $msg = "The form field $name must be a valid e-mail address."; }
    elseif ($type == 'alphanum') { $msg = "The form field $name must be alpha-numeric, and can not contain spaces or special characters."; }
    else { return; }

    // Assert
    $this->asserthasusermessage('error', $msg);

}

/**
* Check if table exists within HTML response
*/
final public function asserthastable(string $table_alias)
{

    // Set variables
    $html = registry::get_response();
    $chk = 'tbl_' . str_replace(":", "_", $table_alias);

    // GO through all tables on page
    $found = false;
    preg_match_all("/<table(.+?)>(.*?)<\/table>/si", $html, $table_match, PREG_SET_ORDER);
    foreach ($table_match as $match) { 
        $attr = template::parse_attr($match[1]);
        $id = $attr['id'] ?? '';
        if ($id == $chk) { $found = true; }
    }

    // Assert
    $this->asserttrue($found, "Unable to find table within HTML with ID: $chk");

}

/**
* Check a database field exists, and contains a certain value
*/
final public function asserthasdbfield(string $sql, string $column, string $value)
{

    // Execute SQL
    $row = DB::get_row($sql);
    $this->assertnotfalse($row, "Unable to retrieve row with SQL statement: $sql");
    $this->assertarrayhaskey($column, $row, "Database row found, but no column exists with $column");
    $this->assertequals($row[$column], $value, "Database row is found, but field does not equal $value");

}

/**
* Check that column within HTML table contains a certain value
*/
final public function asserthastablefield(string $table_alias, int $column_num, string $value)
{

    // Check table
    $found = $this->check_table_field($table_alias, $column_num, $value);

    // Assert
    $this->asserttrue($found, tr("Unable to find table field within table ID: %s, column#: %s, value: %s", $table_alias, $column_num, $value));

}

/**
* Check that a table does not contain a certain value within the column.
*/
final public function assertnothastablefield(string $table_alias, int $column_num, string $value)
{

    // Check table
    $found = $this->check_table_field($table_alias, $column_num, $value);

    // Assert
    $this->assertfalse($found, tr("Table field exists when it should not, table field within table ID: %s, column#: %s, value: %s", $table_alias, $column_num, $value));

}

/**
* Check if value exists within table column.  Private function, used 
* by the other two assert functions to obtain the value.
*/
final private function check_table_field(string $table_alias, int $column_num, string $value)
{

    // Set variables
    $html = registry::get_response();
    $table_alias = 'tbl_' . str_replace(":", "_", $table_alias);

    // Go through tables
    $found = false;
    preg_match_all("/<table(.+?)>(.*?)<\/table>/si", $html, $table_match, PREG_SET_ORDER);
    foreach ($table_match as $match) { 

        // Check table ID
        $attr = template::parse_attr($match[1]);
        $id = $attr['id'] ?? '';
        if ($id != $table_alias) { continue; }

        // Get tbody contents
        if (!preg_match("/<tbody(.*?)>(.*?)<\/tbody>/si", $match[2], $tbody)) { 
            continue;
        }

        // Go through all rows
        preg_match_all("/<tr>(.*?)<\/tr>/si", $tbody[2], $row_match, PREG_SET_ORDER);
        foreach ($row_match as $row) { 

            // Go through cells
            preg_match_all("/<td(.*?)>(.*?)<\/td>/si", $row[1], $cell_match, PREG_SET_ORDER);
            $chk = $cell_match[$column_num][2] ?? '';

            if ($chk == $value) { 
                $found = true;
                break;
            }

        }
        if ($found === true) { break; }

    }

    // Return
    return $found;

}

/**
* Assert does not have a table row matching the given SQL statement
*/
final public function assertnothasdbrow(string $sql)
{

    // Check
    $row = DB::get_row($sql);

    // Assert
    $this->assertfalse($row, "Database table exists when it shoul not with SQL: $sql");

}

/**
* Has form field
*/
final public function asserthasformfield(string $name)
{

    // Get HTML
    $html = registry::get_response();
//file_put_contents(SITE_PATH . '/public/error.html', $html);
    // Go through form fields
    $found = false;
    preg_match_all("/<input(.*?)>/si", $html, $field_match, PREG_SET_ORDER);
    foreach ($field_match as $match) { 
        $attr = template::parse_attr($match[1]);
        if (!isset($attr['name'])) { continue; }
        if ($attr['name'] != $name) { continue; }
        $found = true;
        break;
    }

    // Go through select lists
    preg_match_all("/<select(.*?)>/si", $html, $select_match, PREG_SET_ORDER);
    foreach ($select_match as $match) { 
        $attr = template::parse_attr($match[1]);
        if (!isset($attr['name'])) { continue; }
        if ($attr['name'] != $name) { continue; }
        $found = true;
        break;
    }

    // Assert
    $this->asserttrue($found, "No form field exists with the name, $name");

}

/**
* Assert a string contains certain text
*      @param string $string The string
*       @param string $text The text to see if it's contained within the string
*/
final public function assertstringcontains(string $string, string $text)
{

    // Check if text exists
    if (!preg_match("/$text/", $string)) { 
        $this->asserttrue(false, tr("String '%s's does not contain the text: %s", $string, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Assert a string does NOT contain certain text
*      @param string $string The string
*       @param string $text The text to see if it's contained within the string
*/
final public function assertstringnotcontains(string $string, string $text)
{

    // Check if text exists
    if (preg_match("/$text/", $string)) { 
        $this->asserttrue(false, tr("String '%s' contains the text: %s", $string, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Assert that the contents of a file contains a certain text
*     @param string $file The filename to check
*     @param string $text The text to check if the file contents contains
*/
final public function assertfilecontains(string $filename, string $text)
{

    // Get file contents
    $contents = file_get_contents($filename);

    // Check if text exists
    if (!preg_match("/$text/", $contents)) { 
        $this->asserttrue(false, "The file $filename does not contain the text $text");
    } else { 
        $this->asserttrue(true);
    }

}





}

