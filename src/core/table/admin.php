<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\lib\registry;

class admin extends \apex\core\lib\abstracts\table
{ 

    // Set columns
    public $columns = array(
        'id_html' => 'ID', 
        'username' => 'Username', 
        'full_name' => 'Full Name', 
        'last_seen' => 'Last Seen', 
        'manage' => 'Manage'
    );

    // Sortable columns
    public $sortable = array('username', 'full_name', 'last_seen');

    // Rows per page
    public $rows_per_page = 25;

    // Form field
    public $form_field = 'checkbox';
    public $form_name = 'admin_id';
    public $form_value = 'id';

    // Delete button
    public $delete_button = 'Delete Checked Administrators';
    public $delete_dbtable = 'admin';
    public $delete_dbcolumn = 'id';


/**
* Get the total number of rows available for this table.
* This is used to determine pagination links.
* 
*     @param string $search_term Only applicable if the AJAX search box has been submitted, and is the term being searched for.
*     @return int The total number of rows available for this table.
*/
public function get_total(string $search_term = ''):int
{

    $total = DB::get_field("SELECT count(*) FROM admin");
    return (int) $total;

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

    // Get rows
    $results = array();
    $result = DB::query("SELECT * FROM admin ORDER BY $order_by LIMIT $start,$this->rows_per_page");
    while ($row = DB::fetch_assoc($result)) { 
        array_push($results, $this->format_row($row));
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

    // Set variables
    $row['id_html'] = '<center>' . $row['id'] . '</center>';
    $row['full_name'] .= ' (' . $row['email'] . ')';
    $row['manage'] = '<center><a href="/admin/settings/admin_manage?admin_id=' . $row['id'] . '" class="btn btn-primary btn-md">Manage</a></center>';

    // Return
    return $row;

}

}

