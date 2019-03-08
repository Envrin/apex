<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class db_servers extends \apex\abstracts\table
{

    // Columns
    public $columns = array(
        'type' => 'Type', 
        'dbhost' => 'Host', 
        'dbuser' => 'Username', 
        'manage' => 'Manage'
    );

// Other variables
    public $rows_per_page = 25;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'checkbox';
    public $form_name = 'db_server_id';
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
    $total = registry::$redis->llen('config:db_slaves');
    $total++;

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

    // Get master DB
    $vars = registry::$redis->hgetall('config:db_master');
    $vars['id'] = 'master';
    $vars['type'] = 'Master';
    $row['manage'] = "<center><a href=\"/admin/settings/general_db_manage?server_id=$vars[id]\" class=\"btn btn-primary btn-sm\">Manage</a></center>";
    $results = array($vars);

    // Go through slaves
    $num = 0;
    $rows = registry::$redis->lrange('config:db_slaves', 0, -1);
    foreach ($rows as $row) { 
        $row = json_decode($row, true);
        $row['id'] = $num;
        $row['type'] = 'Slave';
        $row['manage'] = "<center><a href=\"/admin/settings/general_db_manage?server_id=$row[id]\" class=\"btn btn-primary btn-sm\">Manage</a></center>";
        array_push($results, $row);
    $num++; }

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

