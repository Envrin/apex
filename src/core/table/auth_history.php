<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class auth_history extends \apex\core\lib\abstracts\table
{

    // Columns
    public $columns = array(
        'date_added' => 'Date Added', 
        'logout_date' => 'Logout Date', 
        'username' => 'Username', 
        'ip_address' => 'IP Address', 
        'manage' => 'Manage'
    );

    //// Sortable columns
    public $sortable = array('id');

    // Other variables
    public $rows_per_page = 50;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'none';
    public $form_name = 'auth_history_id';
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
    $this->type = $data['type'];
    $this->userid = $data['userid'] ?? 0;

    if ($this->userid > 0) { unset($this->columns['username']); }

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
    if ($this->userid > 0) { 
        $total = DB::get_field("SELECT count(*) FROM auth_history WHERE type = %s AND userid = %i", $this->type, $this->userid);
    } else { 
        $total = DB::get_field("SELECT count(*) FROM auth_history WHERE type = %s", $this->type);
    }
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
public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'date_added desc'):array 
{

    // Get rows
    if ($this->userid > 0) { 
        $rows = DB::query("SELECT * FROM auth_history WHERE type = %s AND userid = %i ORDER BY $order_by LIMIT $start,$this->rows_per_page", $this->type, $this->userid);
    } else { 
        $rows = DB::query("SELECT * FROM auth_history WHERE type = %s ORDER BY $order_by LIMIT $start,$this->rows_per_page", $this->type);
    }

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
    $row['logout_date'] = preg_match("/^0000/", $row['logout_date']) ? '-' : fdate($row['logout_date'], true);
    if ($this->userid == 0) { 
        $redis_hash = $this->type == 'user' ? 'users:' . $row['userid'] : 'admin:' . $row['userid'];
        $row['username'] = registry::$redis->hget($redis_hash, 'username');
    }
    $row['manage'] = "<center><a href=\"/admin/users/view_auth_session?session_id=$row[id]\" class=\"btn btn-primary btn-sm\">Manage</a></center>";

    // Return
    return $row;

}

}

