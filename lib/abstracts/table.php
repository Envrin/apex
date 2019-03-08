<?php
declare(strict_types = 1);

namespace apex\abstracts;

abstract class table 
{

    // Properties
    public $columns;
    public $sortable = array();
    public $ROWS_PER_PAGE = 25;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'checkbox';
    public $form_name = 'record_id';
    public $form_value = 'id'; 

    // Delete button
    public $delete_button = '';
    public $delete_dbtable = '';
    public $dbcolumn = '';

/**
* Passes the attributes contained within the <e:function> tag that called the table.
* Used mainly to show/hide columns, and retrieve subsets of 
* data (eg. specific records for a user ID#).
* 
(     @param array $data The attributes contained within the <e:function> tag that called the table.
*/
public function get_attributes(array $data = array()) { }


/**
* Get the total number of rows available for this table.
* This is used to determine pagination links.
* 
*     @param string $search_term Only applicable if the AJAX search box has been submitted, and is the term being searched for.
*     @return int The total number of rows available for this table.
*/
abstract public function get_total(string $search_term = ''):int;


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
abstract public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'id asc'):array;


/**
* Retrieves raw data from the database, which must be 
* formatted into user readable format (eg. format amounts, dates, etc.).
*
*     @param array $row The row from the database.
*     @return array The resulting array that should be displayed to the browser.
*/
abstract public function format_row(array $row):array;


}

