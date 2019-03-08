<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class repos extends \apex\abstracts\table
{

    // Columns
    public $columns = array(
        'display_name' => 'Name', 
        'url' => 'url', 
        'is_active' => 'Active', 
        'username' => 'Username', 
        'manage' => 'Manage'
    );

    // Sortable columns
    //public $sortable = array('id');

    // Other variables
    public $rows_per_page = 50;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'checkbox';
    public $form_name = 'repo_id';
    public $form_value = 'id'; 

    // Delete button
    public $delete_button = 'Delete Checked Reposs';
    public $delete_dbtable = 'internal_repos';
    public $dbcolumn = 'id';

/**
* Get the total number of rows available for this table.
* This is used to determine pagination links.
* 
*     @param string $search_term Only applicable if the AJAX search box has been submitted, and is the term being searched for.
*     @return int The total number of rows available for this table.
*/
public function get_total(string $search_term = ''):int 
{

$total = DB::get_field("SELECT count(*) FROM internal_repos WHERE is_local = 0");
    if ($total == '') { $total = 0; }

    // Return
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
public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'display_name asc'):array 
{

    // Get rows
$rows = DB::query("SELECT * FROM internal_repos WHERE is_local = 0 ORDER BY $order_by LIMIT $start,$this->rows_per_page");

    // Go through rows
    $results = array();
    foreach ($rows as $row) { 
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

    // Format row
    $row['is_active'] = $row['is_active'] == 1 ? 'Yes' : 'No';

    // Return
    return $row;

}

}

