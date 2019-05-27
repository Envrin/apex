<?php
declare(strict_types = 1);

namespace apex\core\lib;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint_IsEqual;


/**
* Providers support for various custom assertions that can be executed within 
* unit test classes.  Please refer to the developer documentation 
* for full details on all custom assertions available within Apex.
*/
class test extends TestCase
{


/**
* Check title of most recently requested page to see if it equals expected title.
*     Assertions:   assertpagetitle / assertnotpagetitle
* 
*     @param string $title The page of the page that must match
*/
final public function assertpagetitle(string $title) { $this->checkpagetitle($title, true); }
final public function assertnotpagetitle(string $title) { $this->checkpagetitle($title, true); }

private function checkpagetitle(string $title, bool $has = true)
{

    // Assert
    $ok = $title == template::$page_title ? true : false;
    if ($ok !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("Title of page at {1}/{2} does $not equal the title: {3}", registry::$panel, registry::$route, $title));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check if the title of the most recently requested page contains a string of text.
*     Assertions:  assertpagetitlecontains / assertpagetitlenotcontains
* 
*     @param string $text The string of text to check the page title for.
*/
final public function assertpagetitlecontains(string $text) { $this->checkpagetitlecontains($text, true); }
final public function assertpagetitlenotcontains(string $text) { $this->checkpagetitlecontains($text, true); }

private function checkpagetitlecontains(string $text, bool $has = true)
{

    // Assert
    $ok = strpos(template::$page_title, $text) === false ? false : true;
    if ($ok !== $has) { 
        $not = $has === true ? ' NOT ' : array();
        $this->asserttrue(false, fmsg("Title of page {1}/{2} does $not contain the text: {3}", registry::$panel, registry::$route, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check if the most recently requested page contains a string of text.
*     Assertions:   assertpagecontains / assertpagenotcontains
* 
*     @param string $text The string of text to check if the page contains.
*/
final public function assertpagecontains(string $text) { $this->checkpagecontains($text, true); }
final public function assertpagenotcontains(string $text) { $this->checkpagecontains($text, false); }

private function checkpagecontains(string $text, bool $has = true)
{

    // Check
    $ok = strpos(registry::get_response(), $text) === false ? false : true;

    // Assert
    if ($ok !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The page {1}/{2} does $not contain the text {3}", registry::$panel, registry::$route, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check if most recent page requested contains user message / callout of 
* specified type and contains specified text.
*     Assertions:  asserthasusermessage / assertnothasusermessage
*
*     @param string $type The type of message (success, error, info, warning)
*     @param string $text The text should one of the messages should contain
*/
final public function asserthasusermessage($type = 'success', $text = '') { $this->checkhasusermessage($type, $text, true); }
final public function assertnowhasusermessage($type = 'success', $text = '') { $this->checkhasusermessage($type, $text, true); }

private function checkhasusermessage(string $type, string $text = '', bool $has = true)
{

    // Get the messages to check
    $msg = template::$user_messages;
    if (!isset($msg[$type])) { $msg[$type] = array(); }

    // Check message type
    $found = count($msg[$type]) > 0 && $text == '' ? true : false;

    // Check for text, if needed
    if ($text != '') {
        foreach ($msg[$type] as $message) { 
            if (strpos($message, $text) !== false) { $found = true; }
        }

        // Ensure it appears on page
        if (strpos(registry::get_response(), $text) === false) { $found = false; }
    }

    // Assert
    if ($found !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The page {1}/{2} does $not contain a user message of type {3} that contains the text: {4}", registry::$panel, registry::$route, $type, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Checks the most recently requested page to see whether or not it contains the specified 
* form validation error.  These are the validation errors given off by the 
* forms::validate_form() method.
*     Assertions:  asserthasformerror / assertnothasformerror
*
*     @param string $type The type of validation error (blank, email, alphanum)
*     @param string $name The name of the form field to check. 
*/
final public function asserthasformerror(string $type, string $name) { $this->checkhasformerror($type, $name, true); }
final public function assertnothasformerror(string $type, string $name) { $this->checkhasformerror($type, $name, false); }

private function checkhasformerror(string $type, string $name, bool $has = true)
{

    // Set variables
    $name = ucwords(str_replace("_", " ", $name));
    $errors = template::$user_messages['error'] ?? array();

    // Create message
    if ($type == 'blank') { $msg = "The form field $name was left blank, and is required"; } 
    elseif ($type == 'email') { $msg = "The form field $name must be a valid e-mail address."; }
    elseif ($type == 'alphanum') { $msg = "The form field $name must be alpha-numeric, and can not contain spaces or special characters."; }
    else { return; }

    // Check messages
    $found = false;
    foreach ($errors as $message) { 
        if (strpos($msg, $message) !== false) { $found = true; }
    }

    // Assert
    if ($found !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The page {1}/{2} does $not contain a form error of type: {3} for the form field: {4}", registry::$panel, registry::$route, $type, $name));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check if the page contains the specified <hX> tag with the specified text.
*      Assertions:  asserthasheading / assertnothasheading
* 
*      @param int $hnum The heading number (1 - 6)
*      @param string $text The text to check for
*/
final public function asserthasheading($hnum, string $text) { $this->checkhasheading($hnum, $text, true); }
final public function assertnothasheading($hnum, string $text) { $this->checkhasheading($hnum, $text, false); }

private function checkhasheading($hnum, string $text, bool $has = true)
{

    // Check for heading
    $found = false;
    preg_match_all("/<h" . $hnum . ">(.*?)<\/h" . $hnum . ">/si", registry::get_response(), $hmatch, PREG_SET_ORDER);
    foreach ($hmatch as $match) { 
        if ($match[1] == $text) { $found = true; }
    }

    // Assert
    if ($found !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The page {1}/{2} does $not contain a heading of h{3} with the text: {4}", registry::$panel, registry::$route, $hnum, $text));
    } else { 
        $this->asserttrue(true);
    }

}

/** 
* Check whether or not most recent page requested contains a submit button 
* with specified value and label.
*     Assertions:  asserthassubmit / assertnothassubmit
*  
*     @param string $value The value of the submit button
*     @param string $label The label of the submit button (what is shown in the web browser)
*/
final public function asserthassubmit(string $value, string $label) { $this->checkhassubmit($value, $label, true); }
final public function assertnothassubmit(string $value, string $label) { $this->checkhassubmit($value, $label, false); }

private function checkhassubmit(string $value, string $label, $has = true)
{

    // Set variables
    $html = registry::get_response();
    $chk = "<button type=\"submit\" name=\"submit\" value=\"$value\" class=\"btn btn-primary btn-lg\">$label</button>";

    // Assert
    $ok = strpos($html, $chk) === false ? false : true;
    if ($ok !== $has) { 
        $word = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, "The page does $word contain a submit button with the value: $value, and label: $label");
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check if most recently requested page contains a specific HTML table 
* that is displayed via the <e:function> tag.
*     Assertions:  asserthastable / assertnothastable
* 
*     @param string $Table_alias The alias of the table in Apex format (ie. PACKAGE:ALIAS)
*/
final public function asserthastable(string $table_alias) { $this->checkhastable($table_alias, true); }
final public function assertnothastable(string $table_alias) { $this->checkhastable($table_alias, true); }

private function checkhastable(string $table_alias, bool $has = true)
{

    // Set variables
    $html = registry::get_response();
    $chk = 'tbl_' . str_replace(":", "_", $table_alias);

    // GO through all tables on page
    $found = false;
    preg_match_all("/<table(.*?)>/si", $html, $table_match, PREG_SET_ORDER);
    foreach ($table_match as $match) { 
        $attr = template::parse_attr($match[1]);
        $id = $attr['id'] ?? '';
        if ($id == $chk) { $found = true; }
    }

    // Assert
    if ($found !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The page {1}/{2} does $not contain a table with the alias: {3}", registry::$panel, registry::$route, $table_alias));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check all rows within a HTML tab and see if a column contains specified value.
*     Assertions:asserthastablefield / assertnothastablefield
* 
*     @param string $table_alias The alias of the HTML tab in Apex format (ie. PACKAGE:ALIAS)
*     @param int $col_num The number of the column, 0 being the left most column
*     @param string $value The value to check the column for.  
*/
final public function asserthastablefield(string $table_alias, int $col_num, string $value) { $this->checkhastablefield($table_alias, $col_num, $value, true); }
final public function assertnothastablefield(string $table_alias, int $col_num, string $value) { $this->checkhastablefield($table_alias, $col_num, $value, false); }

private function checkhastablefield(string $table_alias, int $column_num, string $value, bool $has = true)
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

    // Assert
    if ($found !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("On the page {1}/{2} the table with alias {3} does $not have a row that contains the text {4} on column number {5}", registry::$panel, registry::$route, $table_alias, $value, $column_num));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Checks if a row exists within the database with the specified SQL statement.
*     Assertions:  asserthasdbrow / assertnothasdbrow
* 
*     @param string $sql The SQL statement to check if row exists
*/
final public function asserthasdbrow(string $sql) { $this->checkhasdbrow($sql, true); }
final public function assertnothasdbrow(string $sql) { $this->checkhasdbrow($sql, false); }

private function checkhasdbrow(string $sql, bool $has = true)
{

    // Assert
    $row = DB::get_row($sql);
    if (($row === false && $has === true) || (is_array($row) && $has === false)) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, "Database row does $not exist for the SQL statement, $sql");
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Retrieve one row from the mySQL database using a SQL query, and check to ensure the 
* row exists, and contains a column with the specified value.
*     Assertions:   asserthasdbfield / assertnothasdbfield
* 
*     @param string $sql The SQL query to perform to retrieve the one row
*     @param string $column The name of the column to check.
*      @param string $value The value the column name should match
*/
final public function asserthasdbfield(string $sql, string $column, string $value) { $this->checkhasdbfield($sql, $column, $value, true); }
final public function assertnowhasdbfield(string $sql, string $column, string $value) { $this->checkhasdbfield($sql, $column, $value, false); }

private function checkhasdbfield(string $sql, string $column, string $value, bool $has = true)
{

    // Perform check
    $ok = false;
    if ($row = DB::get_row($sql)) { 
        $ok = isset($row[$column]) && $row[$column] == $value ? true : false;
    }

    // Assert
    if ($ok !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("Database row does $not contain a column with the name {1} with the value {2}, retrived from the SQL query: {3}", $column, $value, $sql));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check if the most recently requested page contains a form field with 
* the specified name.
*     Assertions:   asserthasformfield / assertnothasformfield
* 
*     @param string $name The name of the form field. 
*/
final public function asserthasformfield(string $name) { $this->checkhasformfield($name, true); }
final public function assertnowhasformfield(string $name) { $this->checkhasformfield($name, true); }

private function checkhasformfield(string $name, bool $has = true)
{

    // Get HTML
    $html = registry::get_response();

    // Go through form fields
    $found = false;
    preg_match_all("/<input(.*?)>/si", $html, $field_match, PREG_SET_ORDER);
    foreach ($field_match as $match) { 
        $attr = template::parse_attr($match[1]);
        if (isset($attr['name']) && $attr['name'] == $name) { 
            $found = true;
            break;
        }
    }

    // Go through select lists
    preg_match_all("/<select(.*?)>/si", $html, $select_match, PREG_SET_ORDER);
    foreach ($select_match as $match) { 
        $attr = template::parse_attr($match[1]);
        if (isset($attr['name']) && $attr['name'] == $name) { 
            $found = true;
            break;
        }
    }

    // Assert
    if ($found !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("Page at {1}/{2} does $not contain a form field with the name {3}", registry::$panel, registry::$route, $name));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Assert a string contains certain text
*     Assertions:  assertstringcontains / assertstringnotcontains
* 
*      @param string $string The string
*       @param string $text The text to see if it's contained within the string
*/
final public function assertstringcontains(string $string, string $text) { $this->checkstringcontains($string, $text, true); }
final public function assertstringnotcontains(string $string, string $text) { $this->checkstringcontains($string, $text, false); }

private function checkstringcontains(string $string, string $text, bool $has = true)
{

    // Check
    $ok = strpos($string, $text) === false ? false : true;
    if ($ok !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The provided string does $not contain the text: {1}", $text));
    } else { 
        $this->asserttrue(true);
    }

}

/**
* Check that the contents of a file contains the specified text.
*      Assertions:   assertfilecontains / assertfilenotcontains
* 
*     @param string $file The filename to check
*     @param string $text The text to check if the file contents contains
*/
final public function assertfilecontains(string $filename, string $text) { $this->checkfilecontains($filename, $text, true); }
final public function assertfilenotcontains(string $filename, string $text) { $this->checkfilecontains($filename, $text, false); }

private function checkfilecontains(string $filename, string $text, bool $has = true)
{

    // Check
    $ok = false;
    if (file_exists($filename)) { 
        $contents = file_get_contents($filename);
        $ok = strpos($contents, $text) === false ? false : true;
    }

    // Assert
    if ($ok !== $has) { 
        $not = $has === true ? ' NOT ' : '';
        $this->asserttrue(false, fmsg("The file {1} does $not contain the text: {2}", $filename, $text));
    } else { 
        $this->asserttrue(true);
    }

}

}



