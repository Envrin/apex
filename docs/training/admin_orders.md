
# Apex Training 0 Admin Panel -- Process Orders

One of the last development tasks for this package is the Marketplace->Orders menu we added to the
administration panel.  Let's start that by creating the necessary template:

`php apex.php create template admin/market/orders marketplace`

Open the newly created file at */views/tpl/admin/market/orders.tpl* and enter the following contents:

~~~

<h1>Marketplace Orders</h1>

<e:form>

<e:tab_control>

    <e:tab_page name="Pending Processing">
        <h3>Pending Processing</h3>

        <p>The below table lists all orders that have been approved, but are still awaiting processing by administration.  You may approve / decline the orders below.</p>

        <e:function alias="display_table" table="marketplace:orders" require_processing="1">

        <e:form_table>
            <e:ft_textbox name="shipping_id" label="Shipping ID">
            <e:ft_textarea name="note" label="Optional Note">
            <e:ft_submit value="process" label="Process Checked Orders">
        </e:form_table>

    </e:tab_page>

    <e:tab_page name="Approved">
        <h3>Recently Approved</h3>

        <p>The below table lists all approved product purchases starting with the most recent.</p>

        <e:function alias="display_table" table="transaction:transaction" controller="marketplace_order" status="approved">
    </e:tab_page>

    <e:tab_page name="Declined">
        <h3>Recently Declined</h3>

        <p>The below table lists all product purchases that have been declined starting with the most recent.</p>

        <e:function alias="display_table" table="transaction:transaction" controller="marketplace_order" status="declined">
    </e:tab_page>

</e:tab_control>

~~~

This template is a simple tab control with three tab pages, showing the orders that are currently pending
processing, and previously approved and declined orders.  Next, open the file at
*/views/php/admin/market/orders.php* and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex;

use apex\core\forms;


// Process orders, if needed
if (registry::$action == 'process') {

    // Get order IDs
    $order_ids = forms::get_chk('order_id');

    // Go through orders
    foreach ($orders as $order_id) {

        // Get order row
        if (!$row = DB::get_idrow('market_orders', $order_id)) {
            continue;
        }

        // Update database
        DB::update('market_orders', array(
            'require_processing' => 0,
            'shipping_id' => registry::post('shipping_id'),
            'note' => registry::post('note')),
        "id = %i", $order_id);

        // Process e-mails
        message::process_emails('marketplace_orders', (int) $row['userid'], array('action' => 'processed'), array('order_id' => $order_id));
    }

    // user message
    template::add_message("Successfully processed all checked orders");

}



~~~



### Table -- marketplace:orders

We also need to quickly create one more table to display the pending orders, so within terminal type:

`php apex.php create table marketplace:orders`

Then open the file at */src/marketplace/table/orders.php* and enter the following contents for the entire
file.  To help save time, we won't bother going through this PHP class function by function again.

~~~php
<?php
declare(strict_types = 1);

namespace apex\marketplace\table;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\transaction\transaction;
use apex\transaction\format;
use apex\users\user;


class orders extends \apex\abstracts\table
{

    // Columns
    public $columns = array(
        'date_added' => 'Date',
        'user' => 'User',
        'product' => 'Product Name',
        'amount' => 'Amount',
        'viewtx' => 'View Tx'
    );

    // Sortable columns
    public $sortable = array();

    // Other variables
    public $rows_per_page = 25;
    public $has_search = false;

    // Form field (left-most column)
    public $form_field = 'checkbox';
    public $form_name = 'order_id';
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
    $this->require_processing = $data['require_processing'] ?? 0;
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
    $total = DB::get_field("SELECT count(*) FROM market_orders WHERE require_processing = %i", $this->require_processing);
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
public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'id desc'):array
{

    // Get rows
    $rows = DB::query("SELECT * FROM market_orders WHERE require_processing = %i ORDER BY $order_by LIMIT $start,$this->rows_per_page", $this->require_processing);

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

    // Load transaction
    $trans = new transaction((int) $row['transaction_id']);
    $tx = format::transaction($trans->load());

    // Format row
    $row['date_added'] = $tx['date_added'];
    $row['user'] = $tx['username'];
    $row['product'] = DB::get_field("SELECT name FROM market_products WHERE id = %i", $tx['index_id']);
    $row['amount'] = $tx['amount'];
    $row['viewtx'] = "<center><a href=\"/admin/financial/viewtx?txid=$row[transaction_id]\" class=\"btn btn-primary btn-md\">View Tx</a></center>";


    // Return
    return $row;

}

}

~~~


### Conclusion

We've now fully developed our small marketplace package, and are ready to publish it to a repository.  To
continue, read the next page in the training guide, [Publish Package](publish_package.md).


