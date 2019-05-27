<?php
declare(strict_types = 1);

namespace apex\core\table;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class cms_placeholders extends \apex\core\lib\abstracts\table
{

    // Columns
    public $columns = array(
        'uri' => 'URI', 
        'num' => '#', 
        'manage' => 'Manage'
    );

    // Sortable columns
    //public $sortable = array('id');

    // Other variables
    public $rows_per_page = 100;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'none';
    public $form_name = 'cms_placeholders_id';
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

    // Get rows
    $rows = DB::query("SELECT uri, count(*) as num FROM cms_placeholders WHERE uri LIKE '" . $this->area . "/%' GROUP BY uri ORDER BY uri");

    $results = array();
    foreach ($rows as $row) { 
        $row['manage'] = "<center><a href=\"/admin/cms/placeholders_manage?uri=$row[uri]\" class=\"btn btn-primary btn-sm\">Manage</a></center>";
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

