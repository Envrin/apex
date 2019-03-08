<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class cms_menus extends \apex\abstracts\table
{

    // Columns
    public $columns = array(
        'delete' => 'Delete', 
        'uri' => 'URI', 
        'order' => 'Order',
        'display_name' => 'Title', 
    'is_active' => 'Active', 
        'manage' => 'Manage' 
    );

    // Sortable columns
    //public $sortable = array('id');

    // Other variables
    public $rows_per_page = 100;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'none';
    public $form_name = 'cms_menus_id';
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
    // Set vars
    $prefix = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    // Get parent menus
    $results = array();
    $rows = DB::query("SELECT * FROM cms_menus WHERE area = %s AND parent = '' ORDER BY order_num", $this->area);
    foreach ($rows as $row) { 

        // Set vars
        $row['uri'] = '/' . $row['alias'];
        $row['order'] = "<input type=\"text\" name=\"order_" . $row['id'] . "\" value=\"$row[order_num]\" style=\"width: 50px;\">";
        $row['delete'] = $row['is_system'] == 1 ? '' : "<center><input type=\"checkbox\" name=\"delete[]\" value=\"$row[id]\"></center>"; 
        $chk = $row['is_active'] == 1 ? 'checked="checked"' : '';
        $row['is_active'] = "<center><input type=\"checkbox\" name=\"is_active[]\" value=\"$row[id]\" $chk></center>";
        $row['manage'] = "<center><a href=\"/admin/cms/menus_manage?menu_id=$row[id]\" class=\"btn btn-primary btn-sm\">Manage</a></center>";
        array_push($results, $row);

        // Go through child menus
        $crows = DB::query("SELECT * FROM cms_menus WHERE area = %s AND parent = %s ORDER BY order_num", $this->area, $row['alias']);
        foreach ($crows as $crow) { 

            // Set variables
            $crow['uri'] = $prefix . '/' . $row['alias'];
            $crow['order'] = $prefix . "<input type=\"text\" name=\"order_" . $crow['id'] . "\" value=\"$crow[order_num]\" style=\"width: 50px;\">";
            $crow['delete'] = $crow['is_system'] == 1 ? '' : "<center><input type=\"checkbox\" name=\"delete[]\" value=\"$crow[id]\"></center>"; 
            $chk = $crow['is_active'] == 1 ? 'checked="checked"' : '';
            $crow['is_active'] = "<center><input type=\"checkbox\" name=\"is_active[]\" value=\"$crow[id]\" $chk></center>";
            $crow['manage'] = "<center><a href=\"/admin/cms/menus_manage?menu_id=$crow[id]\" class=\"btn btn-primary btn-sm\">Manage</a></center>";
            array_push($results, $crow);
        }
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

