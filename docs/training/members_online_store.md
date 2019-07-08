
# Apex Training - Members Area - Online Store

Now that we can manage products via the administration panel, we need a way for users to purcjase products. We
already defined the "Online SStore" menu within the member's area via the package.php file, plus defined the
"market_orders" database table within the install.sql file.  Let's continue by creating a few templates we
will need by typing the following in terminal:

~~~
php apex.php create template members/financial/store marketplace
php apex.php create template members/financial/store_order marketplace
php apex.php create template members/financial/store_order2 marketplace
~~~


### Templates

Open the newly created file at */views/tpl/members/financial/store.tpl* and enter the following contents:

~~~

<h1>Online Store</h1>

<e:form>

<p>The below table lists all products that are currently on offer.  Click on the desired order button below to order the product.</p>

<e:function alias="display_table" table="marketplace:products">

~~~

A small template that simply displays the "marketplace:products" table we created in the previous step.  Next
open the file at */views/tpl/members/financial/store_order.tpl" and enter the following contents:

~~~

<h1>Order Product</h1>

<e:form action="members/financial/store_order2">
<input type="hidden" name="product_id" value="~product.id~">

<p>You may order the product by completing the below form.</p>

<e:form_table><tr>
    <td><b>Product Name:</b></td>
    <td>~product.name~</td>
</tr><tr>
    <td valign="top"><b>Description:</b></td>
    <td valign="top">~product.description~</td>
</tr></e:form_table>


<e:function alias="transaction:payment_form" method="deposit" amount="~amount~" show_balance="1">

~~~

Thankfully, the transaction package contains a HTML function called "payment_form", which allows us to easily
display a payment form for any transaction, and is used on this template as the order form for the product.
Noe open up the file at */views/php/members/financial/store_order.php* and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex;


// Get product
if (!$row = DB::get_idrow('market_products', registry::get('product_id'))) {
    throw new ApexException('error', fmsg("Product does not exist, ID# {1}", registry::get('product_id')));
}
$row['description'] = str_replace("\n", "<br />", $row['description']);

// Template variables
template::assign('amount', $row['amount']);
template::assign('product', $row);

~~~

This simply retrives the product information from the database, and assigns a couple template variables to
personalize the *store_order.tpl* template with the product's information.  Next, open the file at
*/views/tpl/members/financial/store_order2.tpl" and enter the following contents:

~~~

<h1>Product Purchase Completed</h1>

<e:box>
    <e:box_header title="Purchase Details">
        <p>Your recent purchase has been successfully processed, and the details can be found below.</p>
    </e:box_header>

    <e:form_table>
        <e:ft_custom label="ID" contents="~trans.id~">
        <e:ft_custom label="Product Name" contents="~trans.name~">
        <e:ft_custom label="Amount" contents="~trans.amount_gross~">
        <e:ft_custom label="Fee" contents="~trans.fee~">
        <e:ft_custom label="Status" contents="~trans.status~">
        <e:ft_custom label="Date Added" contents="~trans.date_added~">
    </e:form_table>

</e:box>

<e:if ~deposit_id~ != 0>
    <e:function alias="transaction:payment_instructions" transaction_id="~deposit_id~">
</e:if>

~~~

This template simply shows a confirmation / receipt of the product purchase, but if necessary, any additional
information regarding physical payment if required (eg. Western Union recipient info).  Now open up the file
at */views/php/members/financial/store_order2.tpl* and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex;

use apex\transaction\format;


// Set vars
$vars = registry::getall_post();
$vars['userid'] = registry::$userid;

// Send RPC call
$response = message::rpc('marketplace.orders.place_order', json_encode($vars), 'marketplace');

// Template variablews
template::assign('trans', format::transaction($response['tx']));
template::assign('deposit_id', $response['deposit_id']);

~~~

The one thing to take notice of above is the `message::rpc()` call, which in Apex is used to support
horizontal scaling.  This will send a RPC call via RabbitMQ to the "marketplace.orders" routing key, and
execute the "place_order" method found within any of the corresponding PHP classes.  Take note of this for the
upcoming section.


### Worker -- marketplace:orders

As shown just above in the "store_order2.php" template, product orders are sent via the `message::rpc()` call,
which sends a RPC call via RabbitMQ to support horizontal scaling. RabbitMQ then evenly distributes all the
purchase orders amongst the back-end application servers, so if the volume of orders is too high for one
server to handle, additional servers can be easily added to help balance the resource load.

Now we need a worker component to actually receive and process that RPC call.  Within terminal, type:

`php apex.php create worker marketplace:orders marketplace.orders`

This creates a blank PHP class at */src/marketplace/worder/orders.php*, and any RPC calls made to the
"marketplace.orders.METHOD" routing key will be passed to the appropriate method in this class.  Open this new
PHP file and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex\marketplace\worker;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\encrypt;
use apex\transaction\transaction;
use apex\transaction\payment_methods;
use apex\transaction\format;

/**
* Handles all worker processes for the marketplace
* package, such as placing a new order.
*/
class orders
{


/**
* Place new order
*/
public function place_order($data)
{

    // Decode JSON
    $vars = json_decode($data, true);
    $userid = (int) $vars['userid'];
    $currency = registry::config('transaction:base_currency');

    // Get account
    $trans = new transaction();
    $account = $trans->get_account($userid, $currency);

    // Get product
    if (!$prow = DB::get_idrow('market_products', $vars['product_id'])) {
        throw new ApexException('error', fmsg("Product does not exist within database, ID# {1}", $vars['product_id']));
    }

    // Set variables
    $amount = (float) $prow['amount'];
    $balance = (float) $account['balance'];
    $require_processing = $balance >= $amount ? $prow['require_processing'] : 0;

    // Add product transaction
    $trans = new transaction();
    $trans->account_id = $account['id'];
    $trans->controller = 'marketplace_order';
    $trans->index_id = $prow['id'];
    $trans->status = ($balance >= $amount) ? 'approved' : 'pending';
    $trans->amount = $amount;
    $trans->currency = $currency;
    $tx = $trans->create();

    // Add order to database
    DB::insert('market_orders', array(
        'require_processing' => $require_processing,
        'userid' => $userid,
        'transaction_id' => $tx['id'])
    );
    $order_id = DB::insert_id();
    // Update transaction with order ID
    DB::query("UPDATE transaction SET reference_id = %s WHERE id = %i", $order_id, $tx['id']);

    // Start response
    $response = array(
        'tx' => $tx,
        'order_id' => $order_id,
        'deposit_id' => 0
    );

    // Add system deposit, if needed
    if ($amount > $balance) {
        $client = new transaction();
        $deptx = $client->add_system_payment($account_id, $deposit_amount, 'deposit', $order_id);
        $response['deposit_id'] = $deptx['id'];
    }

    // Return
    return $response;

}
}

~~~

This PHP class simply contains one method named "place_order", corresponding to the routing key we make the
RPC call to within the "store_order2.php" template.  This method mainly deals with the transaction package,
which we will not get into much within this training guide.  For full details on how to develop with the
transaction package, please refer to the package specific developer documentation.


### Controller -- transaction:transaction:marketplace_order

One thing to note in our worker class is when creating the transaction for the product purchase, we define the
controller of "marketplace_order".  Again, this is specific to the transaction package itself and not Apex,
hence it will not be covered in detail here. Nonetheless, we do need to create a controller for this
transaction type, so within terminal type:

`php apex.php create controller transaction:transaction:marketplace_order marketplace`

Now open the newly created file at */src/transaction/controller/transaction/marketplace_order.php* and enter
the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex\transaction\controller\transaction;

use apex\DB;
use apex\registry;
use apex\debug;
use apex\log;
use apex\message;

/**
* This class handles the processing of different transaction
* types (eg. deposit, membership fee, commission, etc.), can obtain
* dynamic amounts, and process different actions depending on status.
*
* All transaction are always available to this class via the parent:: constructor
*/
class marketplace_order extends \apex\transaction\controller\transaction
{

    public $name = 'Product Purchase';

/**
* Get the amount of the transaction.  Can be used to either,
* retrieve amount from the database, add / subtract fees, change the inputted
* amount as needed, etc.
*
* To change the amount, just modify the parent::$amount variable directly.
*/
public function get_amount($trans)
{
    return (0 - abs($trans->amount));
}

/**
* Gets the full name of the transaction to display within the table listing transactions.
*     @param array $row The row from the 'transaction' table.
*     @return string The name to display in the web browser.
*/
public function get_name(array $row):string
{

    // Get name
    $name = 'Product Purchase';
    if ($product_name = DB::get_field("SELECT name FROM market_products WHERE id = %i", $row['index_id'])) {
        $name .= ' - ' . $product_name;
    }


    // Return
    return $name;
}

/**
* Executes when a transaction is either aded as 'approved', or
* the status has changed to 'approved'.
*/
public function approved($trans)
{

    // Update order, if needed
    if (!$prow = DB::get_idrow('market_products', $trans->index_id)) {
        return;
    }

    // Update database, as needed
    if ($trans->reference_id > 0) {
        DB::query("UPDATE market_orders SET require_processing = %i WHERE id = %i", $prow['require_processing'], $trans->reference_id);

        // Process e-amils
        message::process_emails('marketplace_orders', (int) $trans->userid, array('action' => 'pending'), array('order_id' => $trans->reference_id));
    }


}

/**
* Executes when a transaction is either aded as 'declined', or
* the status has changed to 'declined'.
*/
public function declined()
{

}

/**
* Executes when a transaction is either aded as 'pending', or
* the status has changed to 'pending'.
*/
public function pending($trans)
{
    $amount = abs($trans->amount);
    DB::query("UPDATE transaction_accounts SET frozen = frozen + $amount WHERE id = %i", $trans->account_id);

}

/**
* Executes when a transaction is either aded as 'refunded', or
* the status has changed to 'refunded'.
*/
public function refunded()
{

}

}

~~~

This PHP class simply contains a few methods that help define how transactions of the type "marketplace_order"
are handled and processed.  It does ensure all transactions added are withdrawals from the account instead of
deposits, it obtains the correct name, and sets the product order to pending processing status upon approval
of the transaction.


### Controller -- core:notifications:marketplace_orders

In the above controller PHP class we defined, you will notice it calls the `message::process_emails()`
function, which checks all e-mail notifications of a certain controller created within the
Settings->Notifications menu of the administration panel, and sends them as required.  We need to create
another controller for this, so within terminal type:

`php apex.php create controller core:notifications:marketplace_orders marketplace`

Open the newly created file at */src/core/controller/notifications/marketplace_orders.php* and enter the
following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex\core\controller\notifications;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\transaction\transaction;
use apex\transaction\format;
use apex\ApexException;


class marketplace_orders
{

    // Properties
    public $display_name = 'Marketplace Product Orders';

    // Set fields
    public $fields = array(
        'action' => array('field' => 'select', 'label' => 'Action', 'data_source' => 'hash:marketplace:notification_orders_actions')
    );

    // Senders
    public $senders = array(
        'admin' => 'Administrator',
        'user' => 'User'
    );

    // Recipients
    public $recipients = array(
        'admin' => 'Administrator',
        'user' => 'User'
    );

/**
* Get merge fields
*/
public function get_merge_fields()
{

    // Set fields
    $fields = array();
    $fields['Product Order'] = array(
        'order-id' => 'Order ID',
        'order-status' => 'Status',
        'order-amount' => 'Amount',
        'order-product_id' => 'Product ID',
        'order-product_name' => 'Product Name',
        'order_date_added' => 'Date Added',
        'order-date_processed' => 'Date Processed',
        'order-shipping_id' => 'Shipping ID',
        'order-note' => 'Note'
    );

    // Return
    return $fields;

}

/**
* Get merge vars
*/
public function get_merge_vars(int $userid, array $data):array
{

    // Initial checks
    if (!isset($data['order_id'])) {
        return array();
    }

    // Get order row
    if (!$row = DB::get_idrow('market_orders', $data['order_id'])) {
        throw new ApexException('error', fmsg("Product order does not exist within database, ID# {1}", $data['order_id']));
    }

    // Load transaction
    $trans = new transaction((int) $row['transaction_id']);
    $tx = format::transaction($trans->load());

    // Set variables
    $vars = array();
    foreach ($tx as $key => $value) {
        if (is_array($value)) { continue; }
    $vars['order-' . $key] = $value;
    }

    // Add product order vars
    $vars['order-date_processed'] = fdate($row['date_processed'], true);
    $vars['order-shipping_id'] = $row['shipping_id'];
    $vars['order-note'] = $row['note'];

    // Return
    return $vars;

}

}


~~~

This PHP class simply retrieves and returns the additional merge fields that are available for notifications
of this type.



### Conclusion

Ok, ordering products from within the members area should now be fully working with our new templates, worker
class, plus transaction and notification controllers.  Now let's move to the next step of the training guide,
[Admin Panel -- Process Orders](admin_orders.md).



