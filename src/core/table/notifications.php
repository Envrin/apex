<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\components;

class notifications extends \apex\abstracts\table
{

    // Set columns
    public $columns = array(
        'id' => 'ID', 
        'controller' => 'Type', 
        'recipient' => 'Recipient', 
        'subject' => 'Subject', 
        'manage' => 'Manage'
    );

    // Other variables
    public $sortable = array('id', 'controller','recipient','subject');
    public $rows_per_page = 25;
    public $delete_button = 'Delete Checked Notifications';
    public $delete_dbtable = 'notifications';
    public $delete_dbcolumn = 'id';

    // Form field
    public $form_field = 'checkbox';
    public $form_name = 'notification_id';
    public $form_value = 'id_raw';

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
    $total = DB::get_field("SELECT count(*) FROM notifications");
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
public function get_rows(int $start = 0, string $search_term = '',string $order_by = 'id'):array
{

    // Get SQL 
    $result = DB::query("SELECT id,controller,recipient,subject FROM notifications ORDER BY $order_by LIMIT $start,$this->rows_per_page");

    // Get rows
$results = array();
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

    // Load controller
    $controller = components::load('controller', $row['controller'], 'core', 'notifications'); 

    // Format row
    $row['id_raw'] = $row['id'];
    $row['controller'] = $controller->display_name ?? 'Unknown';
    $row['recipient'] = method_exists($controller, 'get_recipient_name') === true ? $controller->get_recipient_name($row['recipient']) : $row['recipient'];
    $row['manage'] = "<center><a href=\"/admin/settings/notifications_edit?notification_id=$row[id]\" class=\"btn btn-primary btn-md\">Manage</a></center>";
    $row['id'] = '<center>' . $row['id'] . '</center>';

    // Return
    return $row;

}

}

