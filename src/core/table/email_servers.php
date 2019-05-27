<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class email_servers extends \apex\core\lib\abstracts\table
{

    // Columns
    public $columns = array(
        'host' => 'Host', 
        'port' => 'Port', 
        'username' => 'Username'
    );

    // Other variables
    public $rows_per_page = 25;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'radio';
    public $form_name = 'email_server_id';
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
    $total = registry::$redis->llen('config:email_servers');

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
    $results = array(); $num = 0;
    $rows = registry::$redis->lrange('config:email_servers', 0, -1);
    foreach ($rows as $row) { 
        $row = json_decode($row, true);
        $row['id'] = $num;
        array_push($results, $row);
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

