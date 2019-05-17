
# Apex Training - Getting Started

First thing is first, visit the [Installation Guide](../install.md) and get an install of Apex up and running.  Once done, install a 
few base packages we will need with:

`php apex.php install users transaction support devkit`


### Create Package

Next, we need to create our new package which we will call "users_verification".  You can do this with:

`php apex.php create_package users_verification`

This will create our new package including two directories at:

- */src/users_verification* -- Will hold the bulk of PHP code for this package.
- */etc/users_verification* -- The configuration of this package.

The file located at */etc/users_verification/package.php* is the main configuration file for our package.  Open this file, and you will 
see a few variables at the top allowing you to define various properties of the package, but we can leave them for now. The `__construct()` method within this file 
is the most important, which is explained in full on the [package.php __construct() Function](../packages_construct.md) page of the documentation.  This method contains a few arrays, as described below.

Array | Description
------------- |------------- 
`$this->config` | Key-value pair of all configuration values used by the package, and their default value upon installation.
`$this->hash` | An array with the key being the name of the hash, and the value being an associative array of key-value pairs of all variables / options within the hash.  Define any sets of options for select / radio / checkbox lists in this array.
`$this->menus` | An array of arrays, and defined all menus that are included in the package within the administration panel, member's area, and public web site.
`$this->ext_files` | Any external files included in this page, which are not components.
`$this->placeholders` | Allows you to place `<e:placeholder>` tags within member area / public templates, which then are replaced with the contents defined by the administrator via the CMS->Placeholders menu of the admin panel.  This is where you define the placeholders that are available for the package.  Used so if / when a member area template is included in an upgrade, textual modifications the client has made do not get overwritten.
`$this->boxlists` | Used to add entries / define lists of settings.  For example, Settings->Users and Financial menus of the admin panel are examples of boxlists.
`$this->notifications` | Allows you to have default e-mail notifications created upon package installation, which are managed via the Settings->Notifiations menu of the administration panel.

Now that we have the basic gist of this method, open up the */etc/users_verification/package.php* file, and change the `__construct()` method to:

~~~php
public function __construct()
{

// Configuration variables
$this->config = array(
    'netverify_apikey' => ''
);

// Hash
$this->hash = array();
$this->hash['idtype'] = array(
    'passport' => 'Passport', 
    'idcard' => 'National ID Card', 
    'license' => 'Driver License', 
    'other' => 'Other Government Issued Photo ID'
);

$this->hash['status'] = array(
        'pending' => 'Pending', 
    'approved' => 'Approved', 
    'rejected' => 'Rejected', 
    'fruad' => 'Fraudulent'
);

// Menu -- admin panel - Users->Verify Users
$this->menus = array();
$this->menus[] = array(
    'area' => 'admin', 
    'parent' => 'users', 
    'position' => 'after pending', 
    'alias' => 'verify', 
    'name' => 'Verify Users'
);

// Menu -- member area -- Account->Verify Account
$this->menus[] = array(
    'area' => 'members', 
    'parent' => 'account', 
    'position' => 'bottom', 
    'alias' => 'verify', 
    'name' => 'Verify Account'
);


// Boxlist (user settings)
$this->boxlists = array(
    array(
        'alias' => 'users:settings', 
        'href' => 'admin/settings/users_verification', 
        'title' => 'Verification Levels', 
        'description' => 'Define the various verification levels supported, including requirements and different deposit / withdrawal fees and limits that are available to each.'
    )
);
}

~~~

We will explain the code in detail just below, but every time you modify a package.php file, you must scan the package 
to update the database as necessary.  In terminal, simply type:

`php apex.php scan users_verification`

Once done, if you login to either the administration panel or member's area, you will see the new menu we added into each.  Plus if you visit the Settings->Users menu of the 
administration panel, you will see the one entry we added into the boxlist that is displayed on that page.  The rest of this page explains the above code in detail, but for full information on the `__construct()` function within 
the package.php file, please visit [package.php __construct() Function](../packages_construct.md).  


#### `$this->config` 

We added a single configuration variable for the Netverify API key.  The value of this variable can be accessed anywhere within the software 
with `registry::config('users_verification:netverify_apikey')`.  You may also update the configuration variable anywhere within the software with:

~~~php
$api_key = 'some_api_key';
registry::update_config_var('users_verification:netverify_apikey', $api_key);
~~~


#### `$this->hash`

We added two hashes, which are mostly used to easily generate select lists.  One for the type of ID being uploaded, and another for the 
status of the verification request.  Don't worry if this does not make sense right now, but for quick reference, you can add these into 
HTML forms with one of two ways:

**Form PHP Class**

~~~php
$form_fields['type'] = array('field' => 'select', 'data_source' => 'hash:users_verification:idtype', 'required' => 1);
~~~

**TPL Code**

~~~
<e:ft_select name="type" data_source="hash:users_verification:idtype" required="1">
~~~


#### `$this->menus`

As stated in the documentation, this is an array of associative arrays that defines the various menus to 
add for this package, and should be quite straight forward.  Although not needed for this package, and since this is a training guide, here's how add a parent menu into the administration panel with sub-menus.

~~~php

$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after funds', 
    'type' => 'parent', 
    'icon' => 'fa fa-fw fa-group', 
    'alias' => 'loans', 
    'name' => 'Loans', 
    'menus' => array(
        'add' => 'Add New Loan', 
        'pending' => 'Pending Applications', 
        'reconcile' => 'Reconcile Loans', 
        'overdue' => 'Overdue Payments'
    )
);

~~~

By adding the above into the package.php file, a new parent menu titled "Loans" will be added into the administration panel with four sub-menus.


#### `$this->boxlists`

Fairly straight forward, and is generally used for categories of settings pages such as the Settings->Users / Financial menus of the 
administration panel, and allows other develoeprs to easily expand on your package by adding their own category of settings in.  Refer to the documentation for full details.


### Next 

Now that our new package is created with some base configuration, let's move to the next step, [Admin Panel Settings](admin_settings.md).



