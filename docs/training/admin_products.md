
# Apex Training - Admin Panel Product Management

In the previous step we got our fingers wet with our first template, but let's jump into one a tad more
complex and fill out the new Marketplace->Products menu that we previously defined.  To start, open terminal
and run the following to create a few necessary components:

~~~
php apex.php create template admin/market/products marketplace
php apex.php create template admin/market/products_manage marketplace
php apex.php create table marketplace:products
php apex.php create form marketplace:product
~~~

Ok, so we created two templates, plus one table and form, which will be used to display a list of all existing
products and a form to add / update product details.


### Template -- admin/market/products.tpl

Open the newly created file at */views/tpl/admin/market/products.tpl* and enter the following contents:

~~~

<h1>Manage Products</h1>

<e:box>
    <e:box_header title="Existing Products">
        <p>The below table lists all existing products, which you may manage or delete as desired.</p>
    </e:box_header>

    <e:form>
    <e:function alias="display_table" table="marketplace:products">
    </form>

</e:box>


<e:box>
    <e:box_header title="Add New Product">
        <p>You may add a new product by completing the below form as necessary.
    </e:box_header>

    <e:form file_upload="1">
    <e:function alias="display_form" form="marketplace:product">
    </form>

</e:box>

~~~

The above template is just a simple two sections, the top being a data table that lists all existing products,
and the bottom a HTML form that allows the administrator to add a new product.


Last, open the file at t*/views/tpl/admin/market/products_manage.tpl* and enter the following contents:

~~~

<h1>Update Product</h1>

<e:form file_upload="1" action="/admin/market/products">
<input type="hidden" name="product_id" value="~product_id~">

<e:box>
    <e:box_header title="Product Details">
        <p>Make any desired changes to the product details below.</p.
    </e:box_header>

    <e:function alias="display_form" form="marketplace:product" record_id="~product_id~">

</e:box>

~~~

This template simply displays the "marketplace:product" form which we will define below, and populates it with
the appropriate product information.


### Data Table - marketplace:products

The table component allows you to quickly and easily deploy quality tables that support AJAX based pagination,
sort, search, and deletion.  We already created our table component, so open the newly created file at
*/src/marketplace/table/products.php*.  The top of this PHP class contains various properties, so start by
changing the columns and sortable to:

~~~php

    public $columns = array(
    'image' => "&nbsp;",
        'id' => 'ID',
        'name' => 'Name',
        'amount' => 'Amount',
        'manage' => 'Manage',
        'order' => 'Order'
    );

    // Sortable columns
    public $sortable = array('id', 'name', 'amount');

~~~

The above defines five columns in our table, and which columns can be sorted.  Not required, but it's always
nice if the keys of the `$columns` array are the same as the column names of the database table as it helps
for formatting, whereas the values of the sortable columns can only be column names that exist in the mySQL
database table.

Change the rest of the properties to:

~~~php

        // Other variables
    public $rows_per_page = 25;
    public $has_search = true;

    // Form field (left-most column)
    public $form_field = 'checkbox';
    public $form_name = 'product_id';
    public $form_value = 'id_raw';

    // Delete button
    public $delete_button = 'Delete Checked Products';
    public $delete_dbtable = 'market_products';
    public $delete_dbcolumn = 'id';

~~~

We turned the search on, meaning a small AJAX powered search box will appear in the top right corner of the
table.  The left most column will contain a checkbox with the name "order_id[]", and just below table will be
a delete button that calls a AJAX functions and automatically deletes the necessary table rows via AJAX.


##### __construct()

Add a `__construct()` function to the top of this PHP class with the following contents:

~~~php
public function __construct()
{

    if (registry::$panel != 'admin') {
        unset($this->columns['id']);
        unset($this->columns['manage']);
        $this->has_search = false;
        $this->form_field = 'none';
        $this->delete_button = '';
    } else {
        unset($this->columns['order']);
    }

}


~~~

The above function naturally is automatically executed every time the PHP class is loaded. This function
simply changes the columns within the table as necessary, depending whether the table is being viewed within
either the members area or admin panel.


##### get_total()

Scroll down in the PHP class to the `get_total()` function, and replace it with:

~~~php

public function get_total(string $search_term = ''):int
{

    // Get total
    if ($search_term != '') {
        $total = DB::get_field("SELECT count(*) FROM market_products WHERE name LIKE %ls", $search_term);
    } else {
        $total = DB::get_field("SELECT count(*) FROM market_products");
    }
    if ($total == '') { $total = 0; }

    // Return
    return (int) $total;

}

~~~

Apex will always try to create correct default code, but above we simply had to change the name of the
database table name from "marketplace_orders" to just "market_orders", and within the first SQL statement we
had to replace "some_column" with "name" so it searches the correct column.  Please note, many times you can
remove the search if / else statement altogether as a search box is not included in the table.


##### get_rows()

The next function within the PHP class is get_rows(), and simply modify the top of this function where it
retrives the rows to:

~~~php

    // Get rows
    if ($search_term != '') {
        $rows = DB::query("SELECT * FROM market_products WHERE name LIKE %ls ORDER BY $order_by LIMIT $start,$this->rows_per_page", $search_term);
    } else {
        $rows = DB::query("SELECT * FROM market_products ORDER BY $order_by LIMIT $start,$this->rows_per_page");
    }

~~~

Same as the `get_total()` function, all we did was change the name of the database table name, and the column
name when the search term is not empty.


##### format_row()

The last function within this PHP class is `format_row()`, and replace it with the below contents:

~~~php

public function format_row(array $row):array
{

    // Format row
    $row['id_raw'] = $row['id'];
    $row['image'] = '<center><img src="/image/product/' . $row['id'] . '/thumb" border="0" /></center>';
    $row['amount'] = fmoney((float) $row['amount']);
    $row['name'] = "<a href=\"javascript:open_modal('marketplace:view_product', 'product_id=$row[id]');\">" . $row['name'] . "</a>";
    $row['manage'] = "<center><a href=\"/admin/market/products_manage?product_id=$row[id]\" class=\"btn btn-primary btn-md\">Manage</a></center>";
        $row['order'] = "<center><a href=\"/members/financial/store_order?product_id=$row[id]\" class=\"btn btn-primary btn-md\">Order</a></center>";
    $row['id'] = '<center><b>' . $row['id'] . '</b></center>';

    // Return
    return $row;

}


~~~

That's it!  You can now save and close the PHP class, and we've written our new data table.  For full details
on data table component, please visit the documentation at [Component - Data Table](../components/table.md).


### Form -- marketplace:product

Next, let's define the *marketplace:product* HTML form we created at the beginning of tihs step.  Open the
newly created file at */src/marketplace/form/product.php*, and the first function is `get_fields()` which
should be replaced with the below contents:

~~~php

public function get_fields(array $data = array()):array
{

    // Set form fields
    $form_fields = array(
        'require_processing' => array('field' => 'boolean', 'value' => 1),
        'amount' => array('field' => 'amount', 'required' => 1),
        'recurring_interval' => array('field' => 'date_interval'),
        'recurring_amount' => array('field' => 'amount'),
        'image' => array('field' => 'textbox', 'type' => 'file', 'label' => 'Optional Image')
    );

    // Add current image, if updating
    if (isset($data['record_id']) && $data['record_id'] > 0) {
        $img = "<img src=\"/image/product/" . $data['record_id'] . "/thumb\" border=\"0\">";
        $form_fields['current_image'] = array('field' => 'custom', 'label' => 'Current Image', 'contents' => $img);
    }

    // Add name / description fields
    $form_fields['name'] = array('field' => 'textbox', 'required' => 1);
    $form_fields['description'] = array('field' => 'textarea');

    // Add submit button
    if (isset($data['record_id']) && $data['record_id'] > 0) {
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'update', 'label' => 'Update Product Details');
    } else {
        $form_fields['submit'] = array('field' => 'submit', 'value' => 'add', 'label' => 'Add New Product');
    }

    // Return
    return $form_fields;

}

~~~

This function simply generates and returns an array of associative arrays, which act as the form fields within
the form.  The form fields are defined in PHP to allow the form to be customized based on things such as
configuration variables, whether it's being viewd from the admin panel or member's area, attributes passed to
the `<e:function>` tag, etc.


##### get_record()

The next function within this PHP class is `get_record()`, and should look like:

~~~php

public function get_record(string $record_id):array
{

    // Get record
    if (!$row = DB::get_idrow('market_products', $record_id)) {
        return false;
    }

    // Return
    return $row;

}

~~~

If you recall within the products_manage.tpl template we created, there is a `<e:function>` tag that displays
this form and has a "record_id" attribute.  If that attribute exists within the `<e:function>` tag, this
function is automatically called to retrieve the record from the database, and pre-populate the form.

That's it, now feel free to load the Marketplace->Products menu of the administration panel in your web
browser, and it should appear fine with both, our new data table and form.


### Tie It All Together

Let's quickly finish up product management, and create a library to add / update products and tie everything
together.  In terminal, type:

`php apex.php create lib marketplace:product`

Then open the newly created file at */src/marketplace/product.php* and replace it with the following contents:

~~~php

<?php
declare(strict_types = 1);

namespace apex\marketplace;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\core\forms;
use apex\core\images;

/**
* Handles all product management functionality, such as
* adding, updating and deleting products
*/
class product
{

/**
* Add new product from POSTed form data.
*      @return mixed The iD# of the newly added product if successful, false otherwise
*/
public function add():int
{

    // Validate form
    if (!forms::validate_form('marketplace:product')) {
        return false;
    }

    // Add to database
    DB::insert('market_products', array(
        'require_processing' => registry::post('require_processing'),
        'amount' => registry::post('amount'),
        'recurring_amount' => registry::post('recurring_amount'),
        'recurring_interval' => forms::get_date_interval('recurring_interval'),
        'name' => registry::post('name'),
        'description' => registry::post('description'))
    );
    $product_id = DB::insert_id();

    // Add image, if needed
    if ($image_id = images::upload('image', 'product', $product_id)) {
        images::add_thumbnail('product', $product_id, 'thumb', (int) registry::config('marketplace:product_thumb_width'), (int) registry::config('marketplace:product_thumb_height'));
    }

    // Return
    return $product_id;

}

/**
* Update a product details from POSTed form data
*     @param int $product_id The ID# of the product to update
*     @return bool Whether or not the operation was successful.
*/
public function update(int $product_id):bool
{

    // Validate form
    if (!forms::validate_form('marketplace:product')) {
        return false;
    }

    // Update database
    DB::update('market_products', array(
        'require_processing' => registry::post('require_processing'),
        'amount' => registry::post('amount'),
        'recurring_amount' => registry::post('recurring_amount'),
        'recurring_interval' => forms::get_date_interval('recurring_interval'),
        'name' => registry::post('name'),
        'description' => registry::post('description')),
    "id = %i", $product_id);

    // Add image, if needed
    if ($image_id = images::upload('image', 'product', $product_id)) {
        images::add_thumbnail('product', $product_id, 'thumb', (int) registry::config('marketplace:product_thumb_width'), (int) registry::config('marketplace:product_thumb_height'));
    }

    // Return
    return true;

}

}


~~~

This is a simple PHP class that allows us to add / update products within the database. Last, to tie
everything together open the file at */views/php/admin/market/products.php* and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex;

use apex\marketplace\product;


// Add product
if (registry::$action == 'add') {

    // Add product
    $client = new product();
    if ($product_id = $client->add()) {
        template::add_message(fmsg("Successfully added new product, {1}", registry::post('name')));
    }

// Update product
} elseif (registry::$action == 'update') {

    // Update
    $client = new product();
    if ($client->update((int) registry::post('product_id'))) {
        template::add_message(fmsg("Successfully updated the product, {1}", registry::post('name')));
    }

}
~~~

The above code is executed everytime the Marketplace->Products template is displayed, and simply checks if a
submit button was pressed and adds / updates the product as necessary. Finally, open the file at
*/views/php/admin/market/products_manage.php* and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex;


// Template variables
template::assign('product_id', registry::get('product_id'));

~~~

This template simply assigns the template variable for the product ID that is being managed.  That's it, now
go ahead and visit the Marketplace->Products menu of the administration panel, and it should be fully
functioning with the ability to add, manage and update products.


### Modal -- marketplace:view_product

If you recall, within the `format_row()` function of our data table we defined above, we modified the name
column to be a hyperlink to the `open_modal()` Javascript function. Upon clicking this link, a popup modal
will appear in the middle of the screen with the product's information.  Let's create that modal now, so
within terminal type:

`php apex.php create modal marketplace:view_product`

Once created, open the file at */views/modal/marketplace/view_product.tpl* and enter the below contents:

~~~

<h1>~name~</h1>

<e:form_table><tr>
    <td valign="top">
        <img src="/image/product/~product_id~/full" border="0" />
    </td>

    <td valing="top">
        <b>Amount:</b> ~amount~<br /><br />

        <b>Description:</b><br />
        ~description~

        <br><center><a href="/members/financial/store_order?product_id=~product_id~" class="btn btn-primary btn-lg">Order Product</a></center><br />
    </td>
</tr></e:form_table>
~~~

Then open the file located at */src/marketplace/modal/view_product.php*, and just under the namespace
declaration add the two lines:

~~~php
use apex\template;
use apex\ApexException;
~~~

Plus change the contents of the one `show()` function to:

~~~php

public function show()
{

    // Get product
    if (!$row = DB::get_idrow('market_products', registry::post('product_id'))) {
        throw new ApexException('error', fmsg("Product does not exist in database, ID# {1}", registry::post('product_id')));
    }

    // Template variables
    template::assign('product_id', $row['id']);
    template::assign('amount', fmoney((float) $row['amount']));
    template::assign('name', $row['name']);
    template::assign('description', str_replace("\n", "<br />", $row['description']));


}

~~~



### Conclusion

That's it!  We now have a fully functioning Marketplace->Products menu within the administration panel, we can
add / update / delete products, plus click on the link of the product name to display a popup modal of that
product.  Let's move on to the next step in the training guide, [Member Area - Online
Store](members_online_store.md).  Nonetheless, we need to create a controller for this type of transaction, so
within terminal type:

`php apex.php create controller transaction:transaction:marketplace_ordr marketplace`



