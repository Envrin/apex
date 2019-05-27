<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class login_notices extends \apex\core\lib\abstracts\table
{

    // Columns
    public $columns = array(
        'id' => 'ID', 
        'require_agree' => 'Require Agree?', 
        'title' => 'Title'
    );

    // Sortable columns
    public $sortable = array('id', 'title');

    // Other variables
    public $rows_per_page = 25;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'checkbox';
    public $form_name = 'notice_id';
    public $form_value = 'id'; 


/**
* Get the total number of rows available for this table.
* This is used to determine pagination links.
* 
*     @param string $search_term Only applicable if the AJAX search box has been submitted, and is the term being searched for.
*     @return int The total number of rows available for this table.
*/
public function get_total(string $search_term = ''):int 
{

    // Get total
    $total = DB::get_field("SELECT count(*) FROM notifications_login_notices");
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
public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'id asc'):array 
{

    // Get rows
    $rows = DB::query("SELECT id,require_agree,title FROM notifications_login_notices ORDER BY $order_by LIMIT $start,$this->rows_per_page");

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
    $row['require_agree'] = $row['require_agree'] == 1 ? 'Yes' : 'No';

    // Return
    return $row;

}

}

