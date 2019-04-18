
# Apex Training - Getting Started

First thing is first, visit the [Installation Guide](../install.md) and get an install of Apex up and running.  Once done, install a 
few base packages we will need with:

`php apex.php install users transaction support devkit`


### Create Package

Next, we need to create our new package which we will call "lending".  You can do this with:

`php apex.php create_package lending`

This will create our new package, including a directory at */src/lending*, which will later contain the bulk of all PHP code for our package.  There will also be 
a new */etc/lending* directory, which contains the configuration for our new package.    

The file located at */etc/lending/package.php* is the main configuration file for our package.  Open this file, and you will 
see a few variables at the time allowing you to define various properties of the package, but we can leave them for now. The `__construct()` method within this file 
is the most important, which is explained in full on the [package.php __construct() Function](../packages_construct.md) page of the documentation.  This method contains a few arrays, as described below.

Array | Description
------------- |------------- 
`$this->config` | Key-value pair of all configuration values used by the package, and their default value upon installation.
`$this->hash` | An array with the key being the name of the hash, and the value being an associative array of key-value pairs of all variables / options within the hash.  Define any sets of options for select / radio / checkbox lists in this array.
`$this->menus` | An array of arrays, and defined all menus that are included in the package within the administration panel, member's area, and public web site.
`$this->ext_files` | Any external files included in this page, which are not components.
`$this->placeholders` | Allows you to place `<e:placeholder>` tags within member area / public templates, which then are replaced with the contents defined by the administrator via the CMS->Placeholders menu of the admin panel.  This is where you define the placeholders that are available for the package.  Used so if / when a member area template is included in an upgrade, textual modifications the client has made do not get overwritten.
`$this->boxlists` | Used to add entries / define lists of settings.  For example, Settings->Users and Financial menus of the admin panel are examples of boxlists.


Now that we have the gist of this method, open up the */etc/lending/package.php* file, and change the `__construct()` method to:

~~~php
public function __construct()
{

// Configuration variables
$this->config = array(
    'min_limit' => 0, 
    'max_limit' => 0, 
    'fee_percent' => 0
);

// Hashes
$this->hash = array();
$this->hash['status'] = array(
    'active' => 'Active', 
    'pending' => 'Pending', 
    'complete' => 'Completed / Paid Back', 
    'defaulted' => 'Defaults / Not Paid Back', 
    'expired' => 'Expired', 
    'closed' => 'Closed by Administrator'
);

// Menus -- Admin header
$this->menus = array();
$this->menus[] = array(
    'area' => 'admin', 
    'type' => 'header', 
    'position' => 'after hdr_accounts', 
    'alias' => 'hdr_lending', 
    'name' => 'Lending'
);

// Menus - Admin panel
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after hdr_lending', 
    'type' => 'parent', 
    'icon' => 'fa fa-fw fa-money', 
    'alias' => 'loans', 
    'name' => 'Loans', 
    'menus' => array(
        'add' => 'Add New Loan', 
    'viewall' => 'View All Loans', 
    'summary' => 'View Summary'
    )
);

// Menus - Admin settings
$this->menus[] = array(
    'area' => 'admin', 
    'parent' => 'settings', 
    'position' => 'bottom', 
    'type' => 'internal', 
    'alias' => 'lending', 
    'name' => 'Lending'
);

// Menus - member area
$this->menus[] = array(
    'area' => 'members', 
    'position' => 'after account', 
    'type' => 'parent', 
    'icon' => 'fa fa-fw fa-money', 
    'alias' => 'lending', 
    'name' => 'Loans', 
    'menus' => array(
        'my_loans' => 'My Loans', 
        'view_pending' => 'View Pending Loans', 
        'request_loan' => 'Request New Loan'
    )
);



}
~~~

We've added a few configuration variables, one hash for the status of loans, and some menus into the administration panel.  Again, for full details on things such as 
the `$this->menus` array, please visit the [package.php __construct() Function](../packages_construct.md) page of the documentation.

### Scan Package

Every time you modify the package.php file, you must scan the package to update the database accordingly.  Open terminal, move to the installation directory, and type:

`php apex.php scan lending`

There we go.  Visit the administration panel in your web browser, and you will see the new menus that we added.


### Next 

Now that our new package is created with some base configuration, let's move to the next step, [Create Database Tables](create_database.md).


