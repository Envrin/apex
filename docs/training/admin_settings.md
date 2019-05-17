
# Apex Training - Admin Panel Settings

If you visit the Settings->Users menu of the administration panel, you will see the new item titled "Verification Levels" that we 
added in the previous step.  We're going to develop it during this step, which first requires us to create a 
few components.  Open terminal, and within the installation directory type:

~~~
php apex.php create template admin/settings/users_verification users_verification
php apex.php create table users_verification:levels
php apex.php create table users_verification:payment_methods
php apex.php create form users_verification:level
~~~

We created the necessary template, plus a data table and form component, all of which will be filled in below.  For information on the various 
components supported by Apex and the CLI commands, please visit the following pages within the documentation:

- [Components Overview](../components.md)
- [CLI Component Commands](../cli_component.md)


### Installation SQL Code

Many will groan about this, but over at Apex we strong believe SQL database schemas should be written in, well...  SQL, and not PHP, hence 
why you will never see database migrations in Apex.  If you do not know SQL, it's extremely easy to learn the basics, and helps you produce software architecture that provides 
better performance and stability.

Open the file at */etc/users_verification/install.sql* and enter the following contents:

~~~
DROP TABLE IF EXISTS users_verification_limits;
DROP TABLE IF EXISTS users_verification_levels;
DROP TABLE IF EXISTS users_verification_requests;

CREATE TABLE users_verification_levels (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    total_users INT NOT NULL DEFAULT 0, 
    manual_only TINYINT(1) NOT NULL DEFAULT 0, 
    require_email TINYINT(1) NOT NULL DEFAULT 0, 
    require_phone TINYINT(1) NOT NULL DEFAULT 0, 
    require_photo TINYINT(1) NOT NULL DEFAULT 0, 
    require_proof_address TINYINT(1) NOT NULL DEFAULT 0, 
    register_length VARCHAR(8) NOT NULL DEFAULT '', 
    current_balance DECIMAL(16,8) NOT NULL DEFAULT 0, 
    deposit_total DECIMAL(16,8) NOT NULL DEFAULT 0, 
    withdraw_total DECIMAL(16,8) NOT NULL DEFAULT 0, 
    deposit_30day DECIMAL(16,8) NOT NULL DEFAULT 0, 
    withdraw_30day DECIMAL(16,8) NOT NULL DEFAULT 0, 
    limit_balance DECIMAL(16,8) NOT NULL DEFAULT 0,
    limit_deposit_total DECIMAL(16,8) NOT NULL DEFAULT 0,
    limit_deposit_30day DECIMAL(16,8) NOT NULL DEFAULT 0,    
    limit_withdraw_total DECIMAL(16,8) NOT NULL DEFAULT 0,
    limit_withdraw_30day DECIMAL(16,8) NOT NULL DEFAULT 0,    
    name VARCHAR(100) NOT NULL
) engine=InnoDB;

CREATE TABLE users_verification_limits (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    level_id INT NOT NULL, 
    method_id INT NOT NULL, 
    fee DECIMAL(6,2) NOT NULL DEFAULT 0, 
    amount_total DECIMAL(16,8) NOT NULL DEFAULT 0, 
    amount_30day DECIMAL(16,8) NOT NULL DEFAULT 0, 
    amount_1week DECIMAL(16,8) NOT NULL DEFAULT 0, 
    amount_1day DECIMAL(16,8) NOT NULL DEFAULT 0, 
    FOREIGN KEY (level_id) REFERENCES users_verification_levels (id) ON DELETE CASCADE, 
    FOREIGN KEY (method_id) REFERENCES transaction_payment_methods (id) ON DELETE CASCADE
) engine=InnoDB;

CREATE TABLE users_verification_requests (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    userid INT NOT NULL, 
    status ENUM('approved','rejected','fraud','pending') NOT NULL DEFAULT 'pending', 
    ip_address VARCHAR(60) NOT NULL, 
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    date_processed DATETIME, 
    note TEXT, 
    FOREIGN KEY (userid) REFERENCES users (id) ON DELETE CASCADE
) engine=InnoDB;

~~~

Once saved, manually connect to your mySQL database and copy and paste the above SQL code to the mySQL prompt to create the database tables.  When 
this package is installed on a system, all SQL code within the install.sql file will be automatically executed on the mySQL database.


### Template - /admin/settings/users_verification

Let's quickly fill in our new template.  Open the newly created file at */views/tpl/admin/settings/user_verification.tpl* and 
enter the following contents:

~~~
<h1>Verification Levels</h1>

<e:form>

<e:box>
    <e:box_header title="Settings">
        <p>Modify the general verification settings below as desired.</p>
    </e:box_header>

        <e:ft_textbox name="netverify_apikey" label="NetVerify API Key" value="~config.users_verification:netverify_apikey~">
        <e:ft_submit value="settings" label="Update Verification Settings">
    </e:form_table>

</e:box>

<e:box>
    <e:box_header title="Existing Verification Levels">
            <p>The below table lists all existing verification levels which you may manage or delete.</p>
    </e:box_header>

    <e:function alias="display_table" table="users_verification:levels">
</e:box>

<e:box>
    <e:box_header title="Add Verification Level">
        <p>You may add a new verification level by completing the below form.</p>
    </e:box_header>

    <e:function alias="display_form" form="users_verification:level">
</e:box>
~~~

A few brief notes about the above TPL file:

- All templates are located within the /views/tpl/ directory, and are relative to the URI.  For example, the above template can be viewed at http://domain.com/admin/settings/users_verification, hence the same file location.
- Always start templates with `<h1> .. </h1>` tags, as the template engine picks out the first set of `<h1>` tags and uses it as the page title to ensure it looks proper across all themes, as many themes have the title embedded within various HTML code.
- Simply placing `<e:form>` will be replaced with a form tag with method of POST, that points to the template being displayed.  You can optionally add an "action" attribute to the tag if the form needs to point to a different template.
- Within the top "Settings" section you will notice the few form fields are prefixed with `ft_` which means they will be replaced with a full table row, the text label in the left column and form field in the right column.  You can place just the form fields without the table row by leaving out the `ft_` prefix (eg. `<e:boolean name="enable_netverify" value="1">`).
- Use `<e:box>` and `<e:box_header>` tags as shown above where appropriate, as it helps ensure a professional and standardized look across all packages, templates and themes.


For full details on templates, the special HTML tags, and the PHP functions available to communicate with the template engine, please visit the online documentation at [Template Structure / Engine](../templates.md).


### Data Table - users_verification:levels

The table component allows you to quickly and easily deploy quality tables that support QJAX based pagination, sort, search, and deletion.  We already created 
our table component, so open the newly created file at */src/users_verification/table/levels.php*.  The top of this PHP class contains 
various properties, so start by changing the columns and sortable to:

~~~php

    public $columns = array(
        'id' => 'ID',
        'name' => 'Name', 
        'manual_only' => 'Manual?', 
        'total_users' => 'Users', 
        'manage' => 'Manage'
    );

    public $sortable = array('id','name','total_users');

~~~

The above defines five columns in our table, and which columns can be sorted.  Not required, but it's always nice if the keys of the `$columns` array 
are the same as the column names of the database table as it helps for formatting, whereas the values of the sortable columns can only be column names that exist in the mySQL database table.

We can leave the `$has_search` and `$rows_per_page` properties as they are, but change the form field and delete button properties to:

~~~php

    // Form field
    public $form_field = 'checkbox';
    public $form_name = 'level_id';
    public $form_value = 'id_raw'; 

    // Delete button
    public $delete_button = 'Delete Checked Levelss';
    public $delete_dbtable = 'users_verification_levels';
    public $delete_dbcolumn = 'id';

~~~

This will add a checkbox form field with the name "level_id" as the left-most column of every table.  We removed the "s" from the `$form_name` simply for OCD reasons, and changed the value to "id_raw" as the "id" column is going to 
be center and bold when we format the row.  Then we moved the extra "s" from the label of the delete button, and defined which database table and column to delete from.


#### `get_total()`

Scroll down in the PHP class to the `get_total()` function, and replace it with the following contents:

~~~php

public function get_total(string $search_term = ''):int 
{

    // Get total
    $total = DB::get_field("SELECT count(*) FROM users_verification_levels");
    if ($total == '') { $total = 0; }

    // Return
    return (int) $total;

}

~~~

We simply removed the if / else statement from the default code, as the `Has_search` property is set to false, hence there is no search box displayed for this table.  In other words, just for OCD reasons 
again.  This function simply grabs the total number of rows within the data set for things such as pagination links.


#### `get_rows()`

The next function in the PHP class is `get_rows()`, and replace it with the following contents:

~~~php

public function get_rows(int $start = 0, string $search_term = '', string $order_by = 'id asc'):array 
{

    // Get rows
    $rows = DB::query("SELECT * FROM users_verification_levels ORDER BY $order_by LIMIT $start,$this->rows_per_page");

    // Go through rows
    $results = array();
    foreach ($rows as $row) { 
        array_push($results, $this->format_row($row));
    }

    // Return
    return $results;

}

~~~

Once again, the only thing we did here was remove the if / else statement from the top, again only really for OCD reasons.  This function 
simply goes through all rows that need to be displayed, passes each row via the `format_row` function, then returns the results.


#### `format_row()`

Llast function in this PHP class is `format_row()`, and replace the contents of it with:

~~~php

public function format_row(array $row):array 
{

    // Format row
    $row['id_raw'] = $row['id'];
    $row['id'] = '<center><b>' . $row['id'] . '</b></center>';
    $row['manual_only'] = $row['manual_only'] == 1 ? 'Yes' : 'No';
    $row['manage'] = "<center><a href=\"/admin/settings/users_verification_manage?level_id=$row[id_raw]\" class=\"btn btn-primary brn-md\">Manage</a></center>";

    // Return
    return $row;

}

~~~

This simply formats the raw row from the database table into human readable format for the web browser.  For full details on this PHP class and component, 
please visit the documentation at [Componentt - Data Table](../components/table.md).


### Form - users_verification:level

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


