<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class auth_history_pages extends \apex\abstracts\table
{

    // Columns
    public $columns = array(
        'date_added' => 'Date', 
        'request_method' => 'Method', 
        'uri' => 'URI', 
        'view' => 'View Request'
    );

    // Sortable columns
    //public $sortable = array('id');

    // Other variables
    public $rows_per_page = 100;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'none';
    public $form_name = 'auth_history_pages_id';
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
    $this->history_id = $data['history_id'];
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

    // Get total
$total = DB::get_field("SELECT count(*) FROM auth_history_pages WHERE history_id = %i", $this->history_id);
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
public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'date_added asc'):array 
{

    // Get rows
    $rows = DB::query("SELECT * FROM auth_history_pages WHERE history_id = %i ORDER BY $order_by LIMIT $start,$this->rows_per_page", $this->history_id);

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
    $row['date_added'] = fdate($row['date_added'], true);
    $row['view'] = "<center><a href=\"/admin/users/view_auth_session_request?request_id=$row[id]\" target=\"_blank\" class=\"btn btn-primary btn-sm\">View Request</a></center>";

    // Return
    return $row;

}

}

