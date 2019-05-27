<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
use apex\core\io;


class cms_pages extends \apex\core\lib\abstracts\table
{

    // Columns
    public $columns = array(
        'uri' => 'URI', 
        'layout' => 'Layout', 
        'title' => 'Title'
    );

    //// Sortable columns
    public $sortable = array('id');

    // Other variables
    public $rows_per_page = 25;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'none';
    public $form_name = 'cms_pages_id';
    public $form_value = 'id'; 

/**
* Passes the attributes contained within the <e:function> tag that called the table.
* Used mainly to show/hide columns, and retrieve subsets of 
* data (eg. specific records for a user ID#).
* 
(     @param array $data The attributes contained within the <e:function> tag that called the table.
*/

public function get_attributes(array $data = array())
{
    $this->area = $data['area'];
}

/**
* Get the total number of rows available for this table.
* This is used to determine pagination links.
* 
*     @param string $search_term Only applicable if the AJAX search box has been submitted, and is the term being searched for.
*     @return int The total number of rows available for this table.
*/
public function get_total(string $search_term = ''):int 
{
    return 1;
}

/**
* Gets the actual rows to display to the web browser.
* Used for when initially displaying the table, plus AJAX based search, 
* sort, and pagination.
*
*     @param int $start The number to start retrieving rows at, used within the LIMIT clause of the SQL statement.
*     @param string $search_term Only applicable if the AJAX based search base is submitted, and is the term being searched form.
*     @param string $order_by Must have a default value, but changes when the sort arrows in column headers are clicked.  Used within the ORDER BY clause in the SQL statement.
*     @return array An array of associative arrays giving key-value pairs of the rows to display.
*/
public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'id asc'):array 
{

    // Get files
    $files = io::parse_dir(SITE_PATH . '/views/tpl/' . $this->area);

    // Get existing pages
    $pages = array();
    $rows = DB::query("SELECT * FROM cms_pages WHERE area = %s", $this->area);
    foreach ($rws as $row) { 
        $pages[$row['filename']] = array(
            'layout' => $row['layout'], 
            'title' => $row['title']
        );
    }

    // Get available layouts
    $var = $this->area == 'members' ? 'users:theme_members' : 'core:theme_public';
    $theme_dir = SITE_PATH . '/themes/' . registry::config($var) . '/layouts';
    $layout_files = io::parse_dir($theme_dir);

    // Go through layouts
    $layouts = array();
    foreach ($layout_files as $file) { 
        $file - preg_replace("/\.tpl$/", "", $file);
        $layouts[$file] = ucwords(str_replace("_", " ", $file));
    }

    // Go through files
    $results = array();
    foreach ($files as $file) { 
        $file = preg_replace("/\.tpl$/", "", $file);

        if (isset($pages[$file])) { 
            $layout = $pages[$file]['layout'];
            $title = $pages[$file]['title'];
        } else { 
            $layout = 'default';
            $title = '';
        }

        // Layout options
        $layout_options = '';
        foreach ($layouts as $lfile => $name) { 
            $chk = $lfile == $layout ? 'selected="selected"' : '';
            $layout_options .= "<option value=\"$lfile\" $chk>$name</option>";
        }

        $vars = array(
            'uri' => '/' . $file, 
            'layout' => "<select name=\"layout_" . $this->area . '_' . $file . "\">$layout_options</select>", 
            'title' => "<input type=\"text\" name=\"title_" . $this->area . '_' . $file . "\" size=\"20\" value=\"$title\">"
        );
        array_push($results, $vars);
    }

    // Return
    return $results;

}

/**
* Retrieves raw data from the database, which must be 
* formatted into user readable format (eg. format amounts, dates, etc.).
*
*     @param array $row The row from the database.
*     @return array The resulting array that should be displayed to the browser.
*/
public function format_row(array $row):array 
{

    // Format row


    // Return
    return $row;

}

}

