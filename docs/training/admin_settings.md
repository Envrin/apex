
# Apex Training - Admin Panel Settings

Within the administration panel, you will notice the Settings->Marketplace menu that we added in the 
previous step, which we are going to fill in.  To do this, we first need to create a new template.  Within terminal, type:

~~~
php apex.php create template admin/settings/marketplace marketplace
~~~

All components are created via apex.php in the same manner, although since we created a template we also had to define which package it belongs to 
in order to ensure it is compiled with the package during publishing, etc.  For details on the various component types available and how to create them, please visit the below links.

- [Components Overview](../components.md)
- [CLI Component Commands](../cli_component.md)



### Template - /admin/settings/marketplace

Let's quickly fill in our new template.  Open the newly created file at */views/tpl/admin/settings/marketplace.tpl* and 
enter the following contents:

~~~

<h1>Marketplace Settings</h1>

<e:form file_upload="1">

<e:box>
    <e:box_header title="Settings">
        <p>Make any desired changes to the marketplace settings below.</p>
    </e:box_header>

    <e:form_table>
        <e:ft_seperator label="Product Images">
        <e:ft_textbox name="product_thumb_width" label="Thumbnail Width (px)" width="60px" value="~config.marketplace:product_thumb_width~">
        <e:ft_textbox name="product_thumb_height" label="Thumbnail Height (px)" width="60px" value="~config.marketplace:product_thumb_height~">
        <e:ft_textbox name="default_image" type="file" label="Default Image">
        <e:ft_submit value="update" label="Update Marketplace Settings">
    </e:form_table>

</e:box>

~~~

A few brief notes about the above TPL file:

- All templates are located within the /views/tpl/ directory, and are relative to the URI.  For example, the above template can be viewed at http://domain.com/admin/settings/marketplace, hence the same file location.
- Always start templates with `<h1> .. </h1>` tags, as the template engine picks out the first set of `<h1>` tags and uses it as the page title to ensure it looks proper across all themes, as many themes have the title embedded within various HTML code.
- Simply placing `<e:form>` will be replaced with a form tag with method of POST, that points to the template being displayed.  You can optionally add an "action" attribute to the tag if the form needs to point to a different template.
- Within the top "Settings" section you will notice the few form fields are prefixed with `ft_` which means they will be replaced with a full table row, the text label in the left column and form field in the right column.  You can place just the form fields without the table row by leaving out the `ft_` prefix (eg. `<e:boolean name="enable_netverify" value="1">`).
- Use `<e:box>` and `<e:box_header>` tags as shown above where appropriate, as it helps ensure a professional and standardized look across all packages, templates and themes.


For full details on templates, the special HTML tags, and the PHP functions available to communicate with the template engine, please visit the online documentation at [Template Structure / Engine](../templates.md).


### Template -- admin/settings/marketplace.php

Now that we have the TPL code in place for our template, let's add the necessary PHP code.  Open the newly 
created file at */views/php/admin/settings/marketplace.php* and enter the following contents:

~~~php

<?php
declare(strict_types = 1);

namespace apex;

use apex\core\images;


// Update settings
if (registry::$action == 'update') { 

    // Update config vars
    registry::update_config_var('marketplace:product_thumb_width', registry::post('product_thumb_width'));
    registry::update_config_var('marketplace:product_thumb_height', registry::post('product_thumb_height'));

    // Upload default image
    if ($image_id = images::upload('default_image', 'product', 'default', 1)) { 
        images::add_thumbnail('product', 'default', 'thumb', (int) registry::post('product_thumb_width'), (int) registry::post('product_thumb_height'), 1);
    }

    // User message
    template::add_message("Successfully updated marketplace settings");

}
~~~

The above form simply updates the configuration variables as necessary when the form is submitted.  The `registry` class is 
central to Apex, includes details on all requests such as the URI being displayed, inputted data (POST, GET, SERVER, etc.), configuration variables, redis connection, allows you to set the response contents, and more.  For full details on the registry class, 
please visit the documentation at [Request Handling (registry class)](../request_handling.md).

You will also notice this template takes advantage of the images library within the core package for the default product image.  This is a 
library available within the "core" package, and allows for the very easy uploading and management of images and thumbnails with minimal work.  For full details on the 
images library, please visit the documentation at [Image Handling](../core/images.md).


### Next

Now that we've defined our first template, let's move onto the next step, [Products Admin Menu](admin_products.md).




Last, let's define the PHP class for the form component we created at the beginning of this page.  open the newly created file 
at */src/users_verification/level.php*, and replace the contents of the first `get_fields()` function with:

~~~php

public function get_fields(array $data = array()):array
{

    // Set form fields
    $form_fields = array( 
        'name' => array('field' => 'textbox'), 
        'manual_only' => array('field' => 'boolean', 'label' => 'Manual Assign Only?', 'value' => 0), 
        'sep_basic' => array('field' => 'seperator', 'label' => 'Basic Requirements'), 
        'require_email' => array('field' => 'boolean', 'label' => 'Require Verified E-Mail?', 'value' => 0), 
        'require_phone' => array('field' => 'boolean', 'label' => 'Require Verified Phone?', 'value' => 0), 
        'require_photo' => array('field' => 'boolean', 'label' => 'Require Photo ID?', 'value' => 0), 
        'require_proof_address' => array('field' => 'boolean', 'label' => 'Require Proof of Address?', 'value' => 0), 
        'register_length' => array('field' => 'date_interval', 'label' => 'Registration Length'), 
        'sep_financial' => array('field' => 'seperator', 'label' => 'Financial Requirements'), 
        'current_balance' => array('field' => 'amount'), 
        'deposit_total' => array('field' => 'amount', 'label' => 'Total Deposits'), 
        'withdraw_total' => array('field' => 'amount', 'label' => 'Total Withdrawals'), 
        'deposit_30day' => array('field' => 'amount', 'label' => 'Deposits Past 30 Days?'), 
        'withdraw_30day' => array('field' => 'amount', 'label' => 'Withdrawals Past 30 Days?'), 
        'sep_limits' => array('field' => 'seperator', 'label' => 'Transaction Limits Upon Verification'), 
        'limit_balance' => array('field' => 'amount', 'label' => 'Maximum Balance'), 
        'limit_deposit_total' => array('field' => 'amount', 'label' => 'Maximum Total Deposits'), 
        'limit_deposit_30day' => array('field' => 'amount', 'label' => 'Maximum Monthly Deposits'), 
        'limit_withdraw_total' => array('field' => 'amount', 'label' => 'Maximum Total Withdrawals'), 
        'limit_withdraw_30day' => array('field' => 'amount', 'label' => 'Maximum Monthly Withdrawals')
    );

    // Return
    return $form_fields;

}

~~~


#### `get_record()`

The next function within this PHP class is `get_record()`, which you can replace with the contents:

~~~php

public function get_record(string $record_id):array 
{

    // Get record
    if (!$row = DB::get_idrow('users_verification_levels', $record_id)) { 
        return false;
    }

    // Return
    return $row;

}

~~~

This function simply obtains the appropriate record from the database and populates the form fields with its values 
when managing / updating a record.



### Data Table - users_verification:payment_methods

Last, let's modify the second data table we created, which will list all payment methods that have been created via the Settings->Financial->Payment methods menu, 
and allow the administrator to define different fees and limits for each when adding / updating a verification level.  Open the file at */src/users_verification/table/payment_methods.php* and first add the following line just below the namespace declaration:

`use apex\core\components;`

Then make the following changes to the properties:

~~~php

    // Columns
    public $columns = array(
        'method' => 'Method', 
        'processor' => 'Processor', 
        'fee' => 'Fee', 
        'amount_total' => 'Max Total', 
        'amount_30day' => 'Max / Month', 
        'amount_1week' => 'Max / Week', 
        'amount_1day' => 'Max / Day'
    );

    public $form_field = 'none';

~~~

Aside from modifying the two properties above, also delete the three "Delete button" properties at the bottom of the property list, as we 
don't need a delete button for this table.


#### `get_attributes()`

Change the contents of this function to:

~~~php

public function get_attributes(array $data = array())
{

    // Initialize
    $this->limits = array();
    $this->level_id = $data['level_id'] ?? 0;

    // Get current limits, if needed
    if ($this->level_id > 0) { 

        DB::query("SELECT * FROM users_verification_limits WHERE level_id = %i", $this->level_id);
        foreach ($rows as $row) { 
            $this->limits[$row['method_id']] = $row;
        }
    }

}

~~~

This function is automatically executed if it exists, and passes all attributes of the `<e:function>` tag that called the table.  It allows you to easily 
do things such as customize the columns or data sets being displayed as necessary.  In the above instance, if updating a level (ie. "level_id" attribute passed within `<e:function>` tag), it will grab the 
existing limits of that verification level.


#### `get_total()`

For this table we never want pagination, so replace the contents of `get_total()` th with simply:

~~~php

public function get_total(string $search_term = ''):int 
{
    return 1;
}

~~~


#### `get_rows()`

Within this function, simply replace the top section of code that retrieves the rows from the table with the below line, and 
leave the rest as is.

~~~php
    // Get rows
    $rows = DB::query("SELECT * FROM transaction_payment_methods ORDER BY method,controller");
~~~


#### `format_row()`

Change the contents of this function to:

~~~php








### Conclusion

Ok, give it all a try.  Login to the administration panel, visit the Settings->Users menu, and click on the "Verification Levels" item.  You should see our newly created 
template with data table and HTML form appear.

Next, let's quickly finalize this menu in the next page, [Admin Panel Settings - Part 2](admin_settings2.md).


